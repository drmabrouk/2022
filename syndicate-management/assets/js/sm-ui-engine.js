/**
 * SYNDICATE MANAGEMENT - CORE UI ENGINE (ULTRA HARDENED V5)
 * Centralized script for handling admin and portal UI interactions.
 */
(function(window) {
    // Ensure ajaxurl is available globally with absolute path detection
    if (typeof window.ajaxurl === 'undefined' || !window.ajaxurl) {
        if (typeof ajaxurl !== 'undefined' && ajaxurl) {
            window.ajaxurl = ajaxurl;
        } else {
            const scripts = document.getElementsByTagName('script');
            for (let i = 0; i < scripts.length; i++) {
                if (scripts[i].src && scripts[i].src.includes('/wp-includes/js/jquery/jquery')) {
                    window.ajaxurl = scripts[i].src.split('/wp-includes/')[0] + '/wp-admin/admin-ajax.php';
                    break;
                }
            }
            if (!window.ajaxurl) {
                const pathParts = window.location.pathname.split('/');
                if (pathParts.includes('wp-admin')) {
                    window.ajaxurl = 'admin-ajax.php';
                } else {
                    window.ajaxurl = '/wp-admin/admin-ajax.php';
                }
            }
        }
    }

    const SM_UI = {
        showNotification: function(message, isError = false) {
            const toast = document.createElement('div');
            toast.className = 'sm-toast';
            toast.style.cssText = "position:fixed; top:20px; left:50%; transform:translateX(-50%); background:white; padding:15px 30px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:10001; display:flex; align-items:center; gap:10px; border-right:5px solid " + (isError ? '#e53e3e' : '#38a169');
            toast.innerHTML = `<strong>${isError ? '✖' : '✓'}</strong> <span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = '0.5s'; setTimeout(() => toast.remove(), 500); }, 3000);
        },

        handleAjaxError: function(err, customMsg = 'حدث خطأ أثناء تنفيذ العملية') {
            console.error('SM_AJAX_ERROR_RAW:', err);
            let msg = '';

            if (err instanceof Response) {
                const status = err.status;
                const statusText = err.statusText;
                err.text().then(body => {
                    console.error('Server Response Body:', body);
                    let finalMsg = customMsg + `: (Status ${status} ${statusText})`;
                    if (body.trim() === "0") {
                        finalMsg += " - WordPress Error 0: Action not found or Permission denied.";
                    } else if (body.trim() === "-1") {
                        finalMsg += " - WordPress Error -1: Security nonce verification failed.";
                    } else if (body.length > 0) {
                        try {
                           const json = JSON.parse(body);
                           if (json.data && json.data.message) finalMsg += " - " + json.data.message;
                           else if (json.message) finalMsg += " - " + json.message;
                        } catch(e) {
                           finalMsg += " - Response: " + (body.length > 100 ? body.substring(0, 100) + '...' : body);
                        }
                    }
                    this.showNotification(finalMsg, true);
                }).catch(() => {
                    this.showNotification(customMsg + `: Network Error ${status}`, true);
                });
                return;
            }

            if (err === 0 || err === "0") {
                msg = 'WordPress returned 0. (Action not found or Permission denied).';
            } else if (err === -1 || err === "-1") {
                msg = 'WordPress returned -1. (Security nonce expired).';
            } else if (typeof err === 'string') {
                msg = err;
            } else if (err && err.message) {
                msg = err.message;
            } else if (err && err.data) {
                msg = typeof err.data === 'string' ? err.data : (err.data.message || JSON.stringify(err.data));
            } else if (err && typeof err === 'object') {
                msg = err.message || JSON.stringify(err);
            } else {
                msg = String(err);
            }
            this.showNotification(customMsg + ': ' + msg, true);
        },

        openInternalTab: function(tabId, element) {
            const target = document.getElementById(tabId);
            if (!target || !element) return;

            const container = target.parentElement;
            container.querySelectorAll('.sm-internal-tab').forEach(p => p.style.setProperty('display', 'none', 'important'));
            target.style.setProperty('display', 'block', 'important');

            const parent = element.parentElement;
            parent.querySelectorAll('.sm-tab-btn, .sm-portal-nav-btn').forEach(b => b.classList.remove('sm-active'));
            element.classList.add('sm-active');
        }
    };

    window.smShowNotification = SM_UI.showNotification;
    window.smHandleAjaxError = SM_UI.handleAjaxError.bind(SM_UI);
    window.smOpenInternalTab = SM_UI.openInternalTab;

    window.smRefreshDashboard = function() {
        const action = 'sm_refresh_dashboard';
        fetch(ajaxurl + '?action=' + action)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم تحديث البيانات');
            }
        });
    };

    window.smViewLogDetails = function(log) {
        const detailsBody = document.getElementById('log-details-body');
        let detailsText = log.details;

        if (log.details.startsWith('ROLLBACK_DATA:')) {
            try {
                const data = JSON.parse(log.details.replace('ROLLBACK_DATA:', ''));
                detailsText = `<pre style="background:#f4f4f4; padding:10px; border-radius:5px; font-size:11px; overflow-x:auto;">${JSON.stringify(data, null, 2)}</pre>`;
            } catch(e) {
                detailsText = log.details;
            }
        }

        detailsBody.innerHTML = `
            <div style="display:grid; gap:15px;">
                <div><strong>المشغل:</strong> ${log.display_name || 'نظام'}</div>
                <div><strong>الوقت:</strong> ${log.created_at}</div>
                <div><strong>الإجراء:</strong> <span class="sm-badge sm-badge-low">${log.action}</span></div>
                <div><strong>بيانات العملية:</strong><br>${detailsText}</div>
            </div>
        `;
        document.getElementById('log-details-modal').style.display = 'flex';
    };

    window.smRollbackLog = function(logId, nonce) {
        if (!confirm('هل أنت متأكد من رغبتك في استعادة هذه البيانات؟ سيتم محاولة عكس العملية.')) return;

        const action = 'sm_rollback_log_ajax';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('log_id', logId);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تمت الاستعادة بنجاح');
                setTimeout(() => location.reload(), 500);
            } else {
                smHandleAjaxError(res.data, 'فشل استعادة البيانات');
            }
        }).catch(err => smHandleAjaxError(err));
    };

    window.smSubmitPayment = function(btn, nonce) {
        const form = document.getElementById('record-payment-form');
        if (!form) return;
        const action = 'sm_record_payment_ajax';
        const formData = new FormData(form);
        if (!formData.has('action')) formData.append('action', action);
        if (!formData.has('nonce')) formData.append('nonce', nonce);

        btn.disabled = true;
        btn.innerText = 'جاري المعالجة...';

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم تسجيل الدفعة بنجاح');
                const midField = form.querySelector('[name="member_id"]');
                if (typeof smOpenFinanceModal === 'function' && midField) {
                    smOpenFinanceModal(midField.value);
                } else {
                    location.reload();
                }
            } else {
                smHandleAjaxError(res.data, 'فشل تسجيل الدفعة');
                btn.disabled = false;
                btn.innerText = 'تأكيد استلام المبلغ';
            }
        }).catch(err => {
            smHandleAjaxError(err);
            btn.disabled = false;
            btn.innerText = 'تأكيد استلام المبلغ';
        });
    };

    window.smDeleteGovData = function(nonce) {
        const govEl = document.getElementById('sm_gov_action_target');
        const gov = govEl ? govEl.value : '';
        if (!gov) {
            smShowNotification('يرجى اختيار الفرع أولاً', true);
            return;
        }
        if (!confirm('هل أنت متأكد من حذف كافة بيانات فرع ' + gov + '؟ لا يمكن التراجع عن هذا الإجراء.')) return;

        const action = 'sm_delete_gov_data_ajax';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('governorate', gov);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم حذف بيانات الفرع بنجاح');
                setTimeout(() => location.reload(), 1000);
            } else {
                smHandleAjaxError(res.data, 'فشل حذف البيانات');
            }
        }).catch(err => smHandleAjaxError(err));
    };

    window.smSubmitProfRequest = function(type, memberId, nonce) {
        if (!confirm('هل أنت متأكد من إرسال هذا الطلب؟')) return;

        const action = 'sm_submit_professional_request';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('member_id', memberId);
        fd.append('request_type', type);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم إرسال الطلب بنجاح. سيظهر في تبويب الطلبات لدى الإدارة.');
                const menus = document.querySelectorAll('.sm-dropdown-menu');
                menus.forEach(m => m.style.display = 'none');
            } else {
                smHandleAjaxError(res.data, 'فشل تقديم الطلب');
            }
        }).catch(err => smHandleAjaxError(err));
    };

    window.smMergeGovData = function(input, nonce) {
        const govEl = document.getElementById('sm_gov_action_target');
        const gov = govEl ? govEl.value : '';
        if (!gov) {
            smShowNotification('يرجى اختيار الفرع أولاً لدمج البيانات إليها', true);
            return;
        }
        if (!input.files.length) return;

        const action = 'sm_merge_gov_data_ajax';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('governorate', gov);
        fd.append('backup_file', input.files[0]);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم دمج البيانات بنجاح.');
                setTimeout(() => location.reload(), 1000);
            } else {
                smHandleAjaxError(res.data, 'فشل دمج البيانات');
            }
        }).catch(err => smHandleAjaxError(err));
    };

    window.smResetSystem = function(nonce) {
        const password = prompt('تحذير نهائي: سيتم مسح كافة بيانات النظام بالكامل. يرجى إدخال كلمة مرور مدير النظام للتأكيد:');
        if (!password) return;

        if (!confirm('هل أنت متأكد تماماً؟ لا يمكن التراجع عن هذا الإجراء.')) return;

        const action = 'sm_reset_system_ajax';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('admin_password', password);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تمت إعادة تهيئة النظام بنجاح');
                setTimeout(() => location.reload(), 1000);
            } else {
                smHandleAjaxError(res.data, 'فشل إعادة التهيئة');
            }
        }).catch(err => smHandleAjaxError(err));
    };

    window.smDeleteLog = function(logId, nonce) {
        if (!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;
        const action = 'sm_delete_log';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('log_id', logId);
        fd.append('nonce', nonce);
        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json()).then(res => {
            if (res.success) location.reload();
            else smHandleAjaxError(res.data);
        }).catch(err => smHandleAjaxError(err));
    };

    window.smDownloadBackupNow = function(nonce, modules = 'all') {
        const action = 'sm_download_backup';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('modules', modules);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'syndicate-backup-' + new Date().toISOString().split('T')[0] + '.smb';
            document.body.appendChild(a);
            a.click();
            a.remove();
            smRefreshBackupHistory();
        });
    };

    window.smRefreshBackupHistory = function() {
        const body = document.getElementById('sm-backup-history-body');
        if (!body) return;

        const action = 'sm_get_backup_history';
        fetch(ajaxurl + '?action=' + action)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (res.data.length === 0) {
                    body.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#94a3b8;">لا توجد نسخ احتياطية مسجلة.</td></tr>';
                    return;
                }
                body.innerHTML = res.data.map(b => `
                    <tr>
                        <td style="font-size:11px; font-family:monospace;">${b.filename}</td>
                        <td>${b.date}</td>
                        <td><span class="sm-badge sm-badge-low">${b.size}</span></td>
                        <td>
                            <button onclick="smDownloadStoredBackup('${b.filename}')" class="sm-btn" style="width:auto; padding:4px 10px; font-size:10px; background:#38a169;">تحميل</button>
                        </td>
                    </tr>
                `).join('');
            }
        });
    };

    window.smRunHealthCheck = function(nonce) {
        const btn = document.getElementById('run-health-btn');
        const results = document.getElementById('health-check-results');
        btn.disabled = true;
        btn.innerText = 'جاري الفحص...';
        results.innerHTML = '<div style="grid-column: 1/-1; text-align:center; padding:50px;"><div class="sm-loader-mini" style="margin-bottom:15px;"></div><p>يتم الآن إجراء تدقيق شامل لكافة سجلات النظام...</p></div>';

        const fd = new FormData();
        const action = 'sm_run_health_check';
        fd.append('action', action);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false; btn.innerText = 'بدء الفحص الشامل الآن';
            if (res.success) {
                let html = '';
                for (const [key, check] of Object.entries(res.data)) {
                    const statusColor = check.status === 'success' ? '#38a169' : (check.status === 'danger' ? '#e53e3e' : '#d69e2e');
                    const statusBg = check.status === 'success' ? '#f0fff4' : (check.status === 'danger' ? '#fff5f5' : '#fffaf0');
                    const statusIcon = check.status === 'success' ? '✓' : '!';

                    html += `
                        <div style="background:${statusBg}; border:1px solid ${statusColor}33; border-radius:10px; padding:20px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                                <h4 style="margin:0; font-size:14px; color:var(--sm-dark-color);">${check.label}</h4>
                                <span style="background:${statusColor}; color:white; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900;">${statusIcon}</span>
                            </div>
                            <div style="font-size:24px; font-weight:900; color:${statusColor};">${check.count}</div>
                            <div style="font-size:11px; color:#64748b; margin-top:5px;">سجلات تحتاج للمراجعة</div>
                            ${check.count > 0 ? `
                                <button onclick="smShowHealthDetails('${key}', ${JSON.stringify(check.items).replace(/"/g, '&quot;')})" style="background:none; border:none; color:var(--sm-primary-color); font-size:11px; font-weight:800; cursor:pointer; padding:0; margin-top:10px; text-decoration:underline;">عرض القائمة</button>
                            ` : ''}
                        </div>
                    `;
                }
                results.innerHTML = html;
            }
        });
    };

    window.smUpdateBackupFreq = function(freq, nonce) {
        const action = 'sm_update_backup_freq';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('frequency', freq);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم تحديث جدولة النسخ الاحتياطي');
            }
        });
    };

    window.smDeleteAllLogs = function(nonce) {
        if (!confirm('هل أنت متأكد من مسح كافة السجلات؟')) return;
        const action = 'sm_clear_all_logs';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', nonce);
        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json()).then(res => {
            if (res.success) location.reload();
        });
    };

    window.smToggleUserDropdown = function() {
        const menu = document.getElementById('sm-user-dropdown-menu');
        if (menu.style.display === 'none') {
            menu.style.display = 'block';
            document.getElementById('sm-profile-view').style.display = 'block';
            document.getElementById('sm-profile-edit').style.display = 'none';
            const notif = document.getElementById('sm-notifications-menu');
            if (notif) notif.style.display = 'none';
        } else {
            menu.style.display = 'none';
        }
    };

    window.smToggleNotifications = function() {
        const menu = document.getElementById('sm-notifications-menu');
        if (menu.style.display === 'none') {
            menu.style.display = 'block';
            const userMenu = document.getElementById('sm-user-dropdown-menu');
            if (userMenu) userMenu.style.display = 'none';
        } else {
            menu.style.display = 'none';
        }
    };

    window.smOpenFinanceModal = function(memberId) {
        const modal = document.getElementById('sm-finance-member-modal');
        const body = document.getElementById('sm-finance-modal-body');
        if (!modal || !body) return;
        modal.style.display = 'flex';
        body.innerHTML = '<div style="text-align:center; padding: 15px;">جاري تحميل البيانات...</div>';

        const action = 'sm_get_member_finance_html';
        fetch(ajaxurl + '?action=' + action + '&member_id=' + memberId)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                body.innerHTML = res.data.html;
            }
        });
    };

    window.smSaveProfile = function(nonce) {
        const name = document.getElementById('sm_edit_display_name').value;
        const email = document.getElementById('sm_edit_user_email').value;
        const pass = document.getElementById('sm_edit_user_pass').value;
        const action = 'sm_update_profile_ajax';

        const formData = new FormData();
        formData.append('action', action);
        formData.append('display_name', name);
        formData.append('user_email', email);
        formData.append('user_pass', pass);
        formData.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم تحديث الملف الشخصي بنجاح');
                setTimeout(() => location.reload(), 500);
            }
        });
    };

    window.smProcessProfRequest = function(id, status, nonce) {
        const notes = prompt('ملاحظات إضافية (اختياري):');
        if (notes === null) return;

        const action = 'sm_process_professional_request';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('request_id', id);
        fd.append('status', status);
        fd.append('notes', notes);
        fd.append('nonce', nonce);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                smShowNotification('تم تحديث حالة الطلب');
                setTimeout(() => location.reload(), 1000);
            }
        });
    };

    window.smDeleteAlert = function(id, nonce) {
        if(!confirm('هل أنت متأكد من حذف هذا التنبيه؟')) return;
        const action = 'sm_delete_alert';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('id', id);
        fd.append('nonce', nonce);
        fetch(ajaxurl + '?action=' + action, {method: 'POST', body: fd})
        .then(r=>r.json())
        .then(res=>{
            if(res.success) {
                smShowNotification('تم حذف التنبيه بنجاح');
                setTimeout(() => location.reload(), 500);
            }
        });
    };

    window.smAcknowledgeAlert = function(aid, nonce) {
        const action = 'sm_acknowledge_alert';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('alert_id', aid);
        fd.append('nonce', nonce);
        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            const el = document.getElementById('sm-global-alert-' + aid);
            if (el) el.remove();
        });
    };

})(window);
