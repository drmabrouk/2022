<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-verify-portal" dir="rtl" style="max-width: 900px; margin: 0 auto; padding: 30px 0; font-family: 'Rubik', sans-serif;">

    <!-- Professional Portal Header -->
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 50px; height: 50px; background: #fff; border: 2px solid var(--sm-primary-color); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px; box-shadow: 0 4px 10px rgba(246, 48, 73, 0.15);">
            <span class="dashicons dashicons-shield-check" style="font-size: 24px; width: 24px; height: 24px; color: var(--sm-primary-color);"></span>
        </div>
        <h2 style="margin: 0; font-weight: 900; font-size: 1.4em; color: var(--sm-dark-color); border: none; padding: 0;"><?php echo esc_html(get_option('sm_verify_title', 'بوابة التحقق المهني الموحدة')); ?></h2>
        <p style="color: var(--sm-text-gray); font-size: 12px; margin-top: 2px; font-weight: 500;"><?php echo esc_html(get_option('sm_verify_desc', 'استعلام فوري ومعتمد من السجلات الرسمية للنقابة')); ?></p>
    </div>

    <!-- Compact Search Interface -->
    <div style="background: #fff; padding: 25px; border-radius: 10px; border: 1px solid var(--sm-border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 30px;">
        <form id="sm-verify-form">
            <div style="display: flex; gap: 8px; align-items: stretch;">
                <div style="flex: 1; position: relative;">
                    <span class="dashicons dashicons-search" style="position: absolute; right: 12px; top: 11px; color: #94a3b8; font-size: 18px;"></span>
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل الرقم القومي، القيد، أو الترخيص..."
                           style="width: 100%; height: 40px; border-radius: 6px; border: 1px solid #cbd5e0; background: #fff; padding: 0 38px 0 15px; font-weight: 600; font-size: 14px; transition: 0.2s;">
                    <div id="sm-verify-suggestions" class="sm-suggestions-box" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: #fff; border: 1px solid var(--sm-border-color); border-radius: 6px; z-index: 1000; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 40px; padding: 0 20px; font-weight: 700; font-size: 13px; border-radius: 6px; background: var(--sm-dark-color); color: #fff; border: none; cursor: pointer; transition: 0.2s;">
                    تحقق الآن
                </button>
            </div>
            <div style="margin-top: 6px; display: flex; align-items: center; gap: 4px; color: var(--sm-text-gray); font-size: 10px; font-weight: 500;">
                <span class="dashicons dashicons-info" style="font-size: 12px; width: 12px; height: 12px;"></span>
                <span><?php echo esc_html(get_option('sm_verify_help', 'النظام يتعرف تلقائياً على نوع الرقم المدخل.')); ?></span>
            </div>
        </form>
    </div>

    <div id="sm-verify-loading" style="display: none; text-align: center; padding: 30px;">
        <div class="sm-spinner-minimal" style="margin: 0 auto 8px;"></div>
        <p style="color: var(--sm-text-gray); font-size: 11px; font-weight: 600;">جاري مراجعة السجلات...</p>
    </div>

    <!-- Professional Verification Report Output -->
    <div id="sm-verify-results" style="display: grid; gap: 12px;"></div>

</div>

<style>
.sm-spinner-minimal {
    width: 20px; height: 20px;
    border: 2px solid rgba(17, 31, 53, 0.1);
    border-top: 2px solid var(--sm-dark-color);
    border-radius: 50%;
    animation: sm-spin 0.8s linear infinite;
}
@keyframes sm-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.sm-verification-report {
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--sm-border-color);
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    animation: smSlideUp 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

@keyframes smSlideUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.sm-report-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 30px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sm-report-section {
    padding: 30px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.sm-report-section:last-child { border-bottom: none; }

.sm-section-label {
    font-size: 11px;
    font-weight: 800;
    color: var(--sm-text-gray);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0.8;
}

.sm-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.sm-data-cell {
    padding: 20px 12px;
    background: #fcfcfc;
    border: 1px solid #f8fafc;
    border-radius: 6px;
    transition: 0.2s;
}
.sm-data-cell:hover { background: #fff; border-color: #e2e8f0; }

.sm-cell-label {
    display: block; font-size: 9px; color: #94a3b8; font-weight: 700; margin-bottom: 1px;
}
.sm-cell-value {
    font-weight: 700; color: var(--sm-dark-color); font-size: 13px;
}

.sm-status-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.sm-status-valid { background: #dcfce7; color: #166534; }
.sm-status-invalid { background: #fee2e2; color: #991b1b; }

.sm-verify-suggestion-item {
    padding: 20px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    font-size: 13px;
    font-weight: 600;
}
.sm-verify-suggestion-item:hover { background: #f8fafc; color: var(--sm-primary-color); }

@media (max-width: 640px) {
    .sm-data-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
(function($) {
    const searchInput = $('#sm-verify-value');
    const suggestions = $('#sm-verify-suggestions');
    let typingTimer;

    searchInput.on('input', function() {
        clearTimeout(typingTimer);
        const val = $(this).val();
        if (val.length < 3) { suggestions.hide(); return; }

        typingTimer = setTimeout(() => {
            fetch(`${ajaxurl}?action=sm_verify_suggest&query=${val}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data.length > 0) {
                    suggestions.empty().show();
                    res.data.forEach(item => {
                        suggestions.append(`<div class="sm-verify-suggestion-item" onclick="smSelectSuggestion('${item}')">${item}</div>`);
                    });
                } else suggestions.hide();
            });
        }, 300);
    });

    window.smSelectSuggestion = function(val) {
        searchInput.val(val);
        suggestions.hide();
        $('#sm-verify-form').submit();
    };

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#sm-verify-form').length) suggestions.hide();
    });

    $('#sm-verify-form').on('submit', function(e) {
        e.preventDefault();
        const val = searchInput.val();
        if (!val) return;

        const resultsArea = $('#sm-verify-results').empty();
        const loading = $('#sm-verify-loading').show();
        suggestions.hide();

        const fd = new FormData();
        fd.append('action', 'sm_verify_document');
        fd.append('search_value', val);

        fetch(ajaxurl, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            loading.hide();
            if (res.success) {
                renderResults(res.data, resultsArea);
            } else {
                resultsArea.append(`
                    <div style="background: #fff; padding: 30px; border-radius: 10px; text-align: center; border: 1px dashed #feb2b2;">
                        <div style="font-size: 30px; margin-bottom: 8px;">🔍</div>
                        <h3 style="color: #c53030; font-weight: 800; font-size: 1.05em; margin-bottom: 4px;">عذراً، لا توجد بيانات مطابقة</h3>
                        <p style="color: var(--sm-text-gray); font-size: 11px;">يرجى التأكد من الرقم المدخل وإعادة المحاولة.</p>
                    </div>
                `);
            }
        });
    });

    function renderResults(data, resultsArea) {
        const today = new Date();
        const owner = data.owner;

        const isStillValid = (dateStr) => {
            if (!dateStr || dateStr === 'غير محدد') return true;
            return new Date(dateStr) >= today;
        };

        // Professional Verification Report Template
        let html = `
            <div class="sm-verification-report">
                <!-- Header -->
                <div class="sm-report-header">
                    <div>
                        <h3 style="margin: 0; font-weight: 900; font-size: 1.1em; color: var(--sm-dark-color); border:none; padding:0;">تقرير التحقق الرقمي المعتمد</h3>
                        <div style="font-size: 10px; color: var(--sm-text-gray); font-weight: 600; margin-top: 2px;">تاريخ الاستعلام: ${new Date().toLocaleDateString('ar-EG')} | الرقم المرجعي: ${Math.random().toString(36).substr(2, 6).toUpperCase()}</div>
                    </div>
                    <div style="text-align: left;">
                        <div class="sm-status-badge sm-status-valid">
                            <span class="dashicons dashicons-yes-alt" style="font-size: 14px; width:14px; height:14px;"></span>
                            سجل رسمي
                        </div>
                    </div>
                </div>

                <!-- Section 1: Owner Profile -->
                <div class="sm-report-section">
                    <div class="sm-section-label">هوية صاحب السجل المهني</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">الاسم بالكامل</span><div class="sm-cell-value">${owner.name}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">الفرع التابع</span><div class="sm-cell-value">${owner.branch || '---'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">الدرجة المهنية</span><div class="sm-cell-value">${owner.grade || '---'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">التخصص</span><div class="sm-cell-value">${owner.specialization || '---'}</div></div>
                    </div>
                    <div style="margin-top: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div class="sm-data-cell"><span class="sm-cell-label">البريد الإلكتروني</span><div class="sm-cell-value" style="font-size: 12px;">${owner.email || '---'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">رقم التواصل</span><div class="sm-cell-value" dir="ltr" style="font-size: 12px;">${owner.phone || '---'}</div></div>
                    </div>
                </div>
        `;

        // Section 2: Credential Details (Membership, Permit, Facility)
        if (data.membership) {
            const m = data.membership;
            const valid = isStillValid(m.expiry);
            html += `
                <div class="sm-report-section" style="background: ${valid ? '#f0fff433' : '#fff5f533'};">
                    <div class="sm-section-label">بيانات العضوية والقيد النقابي</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0;">
                            <span class="sm-cell-label">رقم القيد</span>
                            <div class="sm-cell-value">${m.number}</div>
                        </div>
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0; text-align: left;">
                            <span class="sm-cell-label">حالة العضوية</span>
                            <div class="sm-status-badge ${valid ? 'sm-status-valid' : 'sm-status-invalid'}">${valid ? 'سارية' : 'منتهية'}</div>
                        </div>
                        <div class="sm-data-cell" style="grid-column: span 2; margin-top: 5px;">
                            <span class="sm-cell-label">تاريخ نهاية الصلاحية</span>
                            <div class="sm-cell-value" style="color: ${valid ? '#166534' : '#991b1b'};">${m.expiry || 'غير محدد'}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        if (data.practice) {
            const p = data.practice;
            const valid = isStillValid(p.expiry);
            html += `
                <div class="sm-report-section" style="background: ${valid ? '#f0f9ff33' : '#fff5f533'};">
                    <div class="sm-section-label">تصريح مزاولة المهنة المعتمد</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0;">
                            <span class="sm-cell-label">رقم التصريح</span>
                            <div class="sm-cell-value">${p.number}</div>
                        </div>
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0; text-align: left;">
                            <span class="sm-cell-label">تاريخ الإصدار</span>
                            <div class="sm-cell-value">${p.issue_date}</div>
                        </div>
                        <div class="sm-data-cell" style="grid-column: span 2; margin-top: 5px;">
                            <span class="sm-cell-label">صلاحية التصريح حتى</span>
                            <div class="sm-cell-value" style="color: ${valid ? '#075985' : '#991b1b'};">${p.expiry || '---'}</div>
                        </div>
                    </div>
                </div>
            `;
        }

        if (data.facility) {
            const f = data.facility;
            const valid = isStillValid(f.expiry);
            html += `
                <div class="sm-report-section">
                    <div class="sm-section-label">ترخيص المنشأة / الأكاديمية الرياضية</div>
                    <div class="sm-data-cell" style="margin-bottom: 8px;">
                        <span class="sm-cell-label">اسم المنشأة</span>
                        <div class="sm-cell-value" style="font-size: 1.1em; color: var(--sm-primary-color);">${f.name}</div>
                    </div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">رقم الترخيص</span><div class="sm-cell-value">${f.number}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">فئة المنشأة</span><div class="sm-cell-value">فئة (${f.category})</div></div>
                        <div class="sm-data-cell" style="grid-column: span 2;"><span class="sm-cell-label">تاريخ الانتهاء</span><div class="sm-cell-value" style="color:${valid ? '#166534' : '#991b1b'}">${f.expiry || '---'}</div></div>
                    </div>
                    <div class="sm-data-cell" style="margin-top: 8px; background: #fffbeb; border-color: #fef3c7;">
                        <span class="sm-cell-label" style="color: #92400e;">الموقع المسجل</span>
                        <div class="sm-cell-value" style="font-size: 11px; color: #92400e; font-weight: 600;">${f.address}</div>
                    </div>
                </div>
            `;
        }

        // Final Official Footer
        html += `
                <div style="background: #f8fafc; padding: 25px 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 9px; color: #94a3b8; font-weight: 700;">
                        * هذا التقرير صادر إلكترونياً من بوابة التحقق الرسمية ولا يعتد به كبديل عن أصول المستندات.
                    </div>
                    <div style="opacity: 0.3; filter: grayscale(1);">
                        <span class="dashicons dashicons-building" style="font-size: 20px;"></span>
                    </div>
                </div>
            </div>
        `;

        resultsArea.append(html);
    }
})(jQuery);
</script>
