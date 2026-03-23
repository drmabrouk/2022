<?php
if (!defined('ABSPATH')) exit;

$accent_color = get_option('sm_verify_accent_color', '#F63049');
$show_membership = get_option('sm_verify_show_membership', 1);
$show_practice = get_option('sm_verify_show_practice', 1);
$show_facility = get_option('sm_verify_show_facility', 1);
?>
<div class="sm-verify-portal" dir="rtl" style="max-width: 1000px; margin: 40px auto; padding: 0 20px; font-family: 'Rubik', sans-serif;">

    <!-- Professional Portal Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <div style="width: 70px; height: 70px; background: #fff; border: 3px solid <?php echo esc_attr($accent_color); ?>; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <span class="dashicons dashicons-shield-check" style="font-size: 32px; width: 32px; height: 32px; color: <?php echo esc_attr($accent_color); ?>;"></span>
        </div>
        <h2 style="margin: 0; font-weight: 900; font-size: 2.2em; color: var(--sm-dark-color); border: none; padding: 0;"><?php echo esc_html(get_option('sm_verify_title', 'بوابة التحقق المهني الموحدة')); ?></h2>
        <p style="color: var(--sm-text-gray); font-size: 14px; margin-top: 5px; font-weight: 500;"><?php echo esc_html(get_option('sm_verify_desc', 'استعلام فوري ومعتمد من السجلات الرسمية للنقابة')); ?></p>
    </div>

    <!-- Enhanced Search Interface -->
    <div style="background: #fff; padding: 40px; border-radius: 20px; border: 1px solid var(--sm-border-color); box-shadow: 0 20px 40px rgba(0,0,0,0.03); margin-bottom: 40px;">
        <form id="sm-verify-form">
            <div style="display: flex; gap: 15px; align-items: stretch;">
                <div style="flex: 1; position: relative;">
                    <span class="dashicons dashicons-search" style="position: absolute; right: 15px; top: 16px; color: #94a3b8; font-size: 22px;"></span>
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل الرقم القومي، رقم القيد، أو رقم الترخيص..."
                           style="width: 100%; height: 55px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 50px 0 20px; font-weight: 600; font-size: 16px; transition: 0.3s;">
                    <div id="sm-verify-suggestions" class="sm-suggestions-box" style="display: none; position: absolute; top: 110%; left: 0; right: 0; background: #fff; border: 1px solid var(--sm-border-color); border-radius: 12px; z-index: 1000; box-shadow: 0 15px 30px rgba(0,0,0,0.1); overflow: hidden; animation: smFadeIn 0.2s ease;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 55px; padding: 0 40px; font-weight: 800; font-size: 16px; border-radius: 12px; background: var(--sm-dark-color); color: #fff; border: none; cursor: pointer; transition: 0.3s;">
                    تحقق الآن
                </button>
            </div>
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 8px; color: var(--sm-text-gray); font-size: 12px; font-weight: 500;">
                <span class="dashicons dashicons-info" style="font-size: 16px; width: 16px; height: 16px; color: <?php echo esc_attr($accent_color); ?>;"></span>
                <span><?php echo esc_html(get_option('sm_verify_help', 'النظام يتعرف تلقائياً على نوع الرقم المدخل.')); ?></span>
            </div>
        </form>
    </div>

    <div id="sm-verify-loading" style="display: none; text-align: center; padding: 50px;">
        <div class="sm-spinner-modern" style="margin: 0 auto 15px;"></div>
        <p style="color: var(--sm-text-gray); font-size: 14px; font-weight: 600;">جاري تدقيق السجلات الرسمية...</p>
    </div>

    <!-- Hierarchical Verification Report Output -->
    <div id="sm-verify-results" style="display: grid; gap: 20px;"></div>

</div>

<style>
.sm-spinner-modern {
    width: 40px; height: 40px;
    border: 4px solid rgba(17, 31, 53, 0.05);
    border-top: 4px solid <?php echo esc_attr($accent_color); ?>;
    border-radius: 50%;
    animation: sm-spin 1s cubic-bezier(0.5, 0.1, 0.4, 0.9) infinite;
}

.sm-verification-report {
    background: #fff;
    border-radius: 20px;
    border: 1px solid var(--sm-border-color);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    animation: smSlideUp 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.sm-report-banner {
    background: <?php echo esc_attr($accent_color); ?>;
    color: #fff;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sm-report-header {
    background: linear-gradient(to bottom, #fcfcfc, #fff);
    padding: 40px 30px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sm-report-section {
    padding: 40px 30px;
    border-bottom: 1px solid #f8fafc;
}
.sm-report-section:last-child { border-bottom: none; }

.sm-section-label {
    font-size: 13px;
    font-weight: 800;
    color: #94a3b8;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.sm-section-label::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }

.sm-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.sm-data-cell {
    padding: 20px 25px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    transition: 0.3s;
}
.sm-data-cell:hover { background: #fff; border-color: <?php echo esc_attr($accent_color); ?>33; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }

.sm-cell-label {
    display: block; font-size: 11px; color: #94a3b8; font-weight: 700; margin-bottom: 5px;
}
.sm-cell-value {
    font-weight: 700; color: var(--sm-dark-color); font-size: 15px;
}

.sm-status-badge {
    padding: 6px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sm-status-valid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.sm-status-invalid { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

@media (max-width: 768px) {
    .sm-data-grid { grid-template-columns: 1fr !important; }
    .sm-report-header { flex-direction: column; text-align: center; gap: 20px; }
}
</style>

<script>
(function($) {
    const searchInput = $('#sm-verify-value');
    const suggestions = $('#sm-verify-suggestions');
    let typingTimer;

    const config = {
        show_membership: <?php echo $show_membership; ?>,
        show_practice: <?php echo $show_practice; ?>,
        show_facility: <?php echo $show_facility; ?>,
        success_msg: "<?php echo esc_js(get_option('sm_verify_success_msg', 'تم العثور على سجل رسمي معتمد في قاعدة بيانات النقابة.')); ?>"
    };

    searchInput.on('input', function() {
        clearTimeout(typingTimer);
        const val = $(this).val();
        if (val.length < 3) { suggestions.hide(); return; }

        typingTimer = setTimeout(() => {
            const action = 'sm_verify_suggest';
            fetch(`${ajaxurl}?action=${action}&query=${val}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    suggestions.empty().show();
                    res.data.forEach(item => {
                        suggestions.append(`<div class="sm-verify-suggestion-item" onclick="smSelectSuggestion('${item}')">${item}</div>`);
                    });
                } else suggestions.hide();
            }).catch(err => {
                console.error(err);
                suggestions.hide();
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

        const action = 'sm_verify_document';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('search_value', val);

        fetch(ajaxurl + '?action=' + action, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            loading.hide();
            if (res.success && res.data) {
                renderResults(res.data, resultsArea);
            } else {
                let errorMsg = '';
                if (res.data) {
                    errorMsg = typeof res.data === 'string' ? res.data : (res.data.message || '');
                }
                resultsArea.append(`
                    <div style="background: #fff; padding: 50px; border-radius: 20px; text-align: center; border: 2px dashed #feb2b2;">
                        <div style="font-size: 50px; margin-bottom: 20px;">🔍</div>
                        <h3 style="color: #c53030; font-weight: 900; font-size: 1.4em; margin-bottom: 10px;">عذراً، لا توجد بيانات مطابقة</h3>
                        <p style="color: var(--sm-text-gray); font-size: 14px;">يرجى التأكد من الرقم المدخل وإعادة المحاولة. قد لا يكون العضو مسجلاً في النظام الرقمي حالياً.</p>
                        ${errorMsg ? `<p style="font-size:12px; color:#e53e3e;">${errorMsg}</p>` : ''}
                    </div>
                `);
            }
        }).catch(err => {
            loading.hide();
            console.error(err);
            resultsArea.append('<div class="error">حدث خطأ تقني أثناء محاولة التحقق.</div>');
        });
    });

    function renderResults(data, resultsArea) {
        const today = new Date();
        const owner = data.owner;

        const isStillValid = (dateStr) => {
            if (!dateStr || dateStr === 'غير محدد' || dateStr === '---') return true;
            return new Date(dateStr) >= today;
        };

        // Header Section with Success Message
        let html = `
            <div class="sm-verification-report">
                <div class="sm-report-banner">
                    <div style="font-weight: 800; font-size: 14px;">
                        <span class="dashicons dashicons-yes-alt" style="vertical-align: middle; margin-left: 5px;"></span>
                        التحقق ناجح: سجل معتمد
                    </div>
                    <div style="font-size: 11px; opacity: 0.9; font-weight: 600;">رقم العملية: ${Math.random().toString(36).substr(2, 8).toUpperCase()}</div>
                </div>

                <div class="sm-report-header">
                    <div>
                        <h3 style="margin: 0; font-weight: 900; font-size: 1.6em; color: var(--sm-dark-color); border:none; padding:0;">${owner.name}</h3>
                        <div style="font-size: 12px; color: var(--sm-text-gray); font-weight: 600; margin-top: 5px;">
                            ${owner.role_label} | ${owner.branch} | تاريخ الاستعلام: ${new Date().toLocaleDateString('ar-EG')}
                        </div>
                    </div>
                    <div>
                        <div style="background: #f0fff4; color: #166534; padding: 15px 25px; border-radius: 12px; border: 1px solid #bbf7d0; font-size: 13px; font-weight: 700; line-height: 1.5; max-width: 300px; text-align: center;">
                            ${config.success_msg}
                        </div>
                    </div>
                </div>

                <!-- Section 1: Professional Identity -->
                <div class="sm-report-section">
                    <div class="sm-section-label">الهوية المهنية المعتمدة</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">الدرجة المهنية</span><div class="sm-cell-value">${owner.grade}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">التخصص الدقيق</span><div class="sm-cell-value">${owner.specialization}</div></div>
                    </div>
                </div>
        `;

        // Section 2: Membership Data
        if (data.membership && config.show_membership) {
            const m = data.membership;
            const valid = isStillValid(m.expiry);
            html += `
                <div class="sm-report-section" style="background: ${valid ? '#f0fff422' : '#fff5f522'};">
                    <div class="sm-section-label">بيانات القيد والعضوية النقابية</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0;">
                            <span class="sm-cell-label">رقم العضوية (القيد)</span>
                            <div class="sm-cell-value" style="font-size: 20px; color: var(--sm-dark-color);">${m.number}</div>
                        </div>
                        <div class="sm-data-cell" style="background:transparent; border:none; padding:0; text-align: left;">
                            <span class="sm-cell-label">حالة السجل</span>
                            <div class="sm-status-badge ${valid ? 'sm-status-valid' : 'sm-status-invalid'}">
                                ${valid ? 'عضوية عاملة / سارية' : 'عضوية منتهية / بانتظار التجديد'}
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="sm-data-cell"><span class="sm-cell-label">تاريخ نهاية الصلاحية</span><div class="sm-cell-value" style="color: ${valid ? '#166534' : '#991b1b'};">${m.expiry || 'غير محدد'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">الرقم القومي (مخفي جزئياً)</span><div class="sm-cell-value">********${owner.national_id.substr(-6)}</div></div>
                    </div>
                </div>
            `;
        }

        // Section 3: Practice Permit
        if (data.practice && config.show_practice) {
            const p = data.practice;
            const valid = isStillValid(p.expiry);
            html += `
                <div class="sm-report-section" style="background: ${valid ? '#f0f9ff22' : '#fff5f522'};">
                    <div class="sm-section-label">تصريح الممارسة المهنية (الترخيص الفردي)</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">رقم ترخيص المزاولة</span><div class="sm-cell-value" style="font-size: 1.4em; color: <?php echo esc_attr($accent_color); ?>;">${p.number}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">تاريخ الإصدار الرسمي</span><div class="sm-cell-value">${p.issue_date}</div></div>
                        <div class="sm-data-cell" style="grid-column: span 2;">
                            <span class="sm-cell-label">حالة الصلاحية حتى ${p.expiry || '---'}</span>
                            <div class="sm-status-badge ${valid ? 'sm-status-valid' : 'sm-status-invalid'}" style="width: 100%; justify-content: center;">
                                ${valid ? 'ترخيص مزاولة مهنة معتمد وساري' : 'ترخيص ممارسة مهنية منتهي الصلاحية'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Section 4: Facility Data
        if (data.facility && config.show_facility) {
            const f = data.facility;
            const valid = isStillValid(f.expiry);
            html += `
                <div class="sm-report-section">
                    <div class="sm-section-label">تراخيص المنشآت الرياضية التابعة</div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 15px; padding: 25px;">
                        <div style="margin-bottom: 20px;">
                            <span class="sm-cell-label">اسم المنشأة المسجل</span>
                            <div class="sm-cell-value" style="font-size: 1.3em; color: var(--sm-dark-color);">${f.name}</div>
                        </div>
                        <div class="sm-data-grid">
                            <div class="sm-data-cell"><span class="sm-cell-label">رقم ترخيص المنشأة</span><div class="sm-cell-value">${f.number}</div></div>
                            <div class="sm-data-cell"><span class="sm-cell-label">تصنيف المنشأة</span><div class="sm-cell-value">فئة (${f.category})</div></div>
                            <div class="sm-data-cell" style="grid-column: span 2;"><span class="sm-cell-label">العنوان والموقع الجغرافي</span><div class="sm-cell-value" style="font-size: 13px; color: #64748b;">${f.address}</div></div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Final Official Footer
        html += `
                <div style="background: #f8fafc; padding: 30px; border-top: 1px solid #f1f5f9; text-align: center;">
                    <div style="font-size: 11px; color: #94a3b8; font-weight: 700; margin-bottom: 10px;">
                        * هذا التقرير صادر إلكترونياً من بوابة التحقق الرسمية لنقابة المهن الرياضية ولا يعتد به كبديل عن أصول المستندات الورقية.
                    </div>
                    <div style="opacity: 0.15; filter: grayscale(1);">
                        <span class="dashicons dashicons-building" style="font-size: 30px; width:30px; height:30px;"></span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" class="sm-btn sm-btn-outline" style="width: auto; border-radius: 10px; font-size: 13px;">
                    <span class="dashicons dashicons-printer" style="margin-left: 5px;"></span> طباعة تقرير التحقق
                </button>
            </div>
        `;

        resultsArea.append(html);
    }
})(jQuery);
</script>
