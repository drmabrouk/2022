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
            <div style="display: flex; gap: 15px; align-items: stretch; margin-bottom: 15px;">
                <div style="width: 200px;">
                    <select id="sm-verify-type" class="sm-select" style="height: 55px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; font-weight: 600; width: 100%;">
                        <option value="auto">كشف تلقائي</option>
                        <option value="national_id">الرقم القومي</option>
                        <option value="membership">رقم القيد</option>
                        <option value="practice">رقم ترخيص المزاولة</option>
                        <option value="facility">رقم ترخيص المنشأة</option>
                        <option value="tracking">كود التتبع / الطلبات</option>
                    </select>
                </div>
                <div style="flex: 1; position: relative;">
                    <span class="dashicons dashicons-search" style="position: absolute; right: 15px; top: 16px; color: #94a3b8; font-size: 22px;"></span>
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل قيمة البحث هنا..."
                           style="width: 100%; height: 55px; border-radius: 12px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 50px 0 20px; font-weight: 600; font-size: 16px; transition: 0.3s;">
                    <div id="sm-verify-suggestions" class="sm-suggestions-box" style="display: none; position: absolute; top: 110%; left: 0; right: 0; background: #fff; border: 1px solid var(--sm-border-color); border-radius: 12px; z-index: 1000; box-shadow: 0 15px 30px rgba(0,0,0,0.1); overflow: hidden; animation: smFadeIn 0.2s ease;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 55px; padding: 0 40px; font-weight: 800; font-size: 16px; border-radius: 12px; background: var(--sm-dark-color); color: #fff; border: none; cursor: pointer; transition: 0.3s;">
                    بحث واستعلام
                </button>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; color: var(--sm-text-gray); font-size: 12px; font-weight: 500;">
                <span class="dashicons dashicons-info" style="font-size: 16px; width: 16px; height: 16px; color: <?php echo esc_attr($accent_color); ?>;"></span>
                <span><?php echo esc_html(get_option('sm_verify_help', 'النظام الموحد يتيح لك تتبع الطلبات والتحقق من التراخيص في مكان واحد.')); ?></span>
            </div>
        </form>
    </div>

    <div id="sm-verify-loading" style="display: none; text-align: center; padding: 50px;">
        <div class="sm-spinner-modern" style="margin: 0 auto 15px;"></div>
        <p style="color: var(--sm-text-gray); font-size: 14px; font-weight: 600;">جاري استرجاع البيانات من السجلات الرسمية...</p>
    </div>

    <!-- Hierarchical Verification Report Output -->
    <div id="sm-verify-results" style="display: grid; gap: 30px;"></div>

</div>

<style>
.sm-spinner-modern {
    width: 40px; height: 40px;
    border: 4px solid rgba(17, 31, 53, 0.05);
    border-top: 4px solid <?php echo esc_attr($accent_color); ?>;
    border-radius: 50%;
    animation: sm-spin 1s cubic-bezier(0.5, 0.1, 0.4, 0.9) infinite;
}

.sm-verification-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid var(--sm-border-color);
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    animation: smSlideUp 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.sm-card-banner {
    background: <?php echo esc_attr($accent_color); ?>;
    color: #fff;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    font-weight: 700;
}

.sm-card-header {
    background: linear-gradient(to bottom, #fcfcfc, #fff);
    padding: 30px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sm-card-body {
    padding: 30px;
}

.sm-card-section-label {
    font-size: 12px;
    font-weight: 800;
    color: #94a3b8;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
}
.sm-card-section-label::after { content: ''; flex: 1; height: 1px; background: #f1f5f9; }

.sm-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.sm-data-cell {
    padding: 15px 20px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    transition: 0.3s;
}
.sm-data-cell:hover { background: #fff; border-color: <?php echo esc_attr($accent_color); ?>33; }

.sm-cell-label {
    display: block; font-size: 11px; color: #94a3b8; font-weight: 700; margin-bottom: 4px;
}
.sm-cell-value {
    font-weight: 700; color: var(--sm-dark-color); font-size: 14px;
}

.sm-status-badge {
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.sm-status-valid { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.sm-status-invalid { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.sm-status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

@media (max-width: 768px) {
    .sm-data-grid { grid-template-columns: 1fr !important; }
    .sm-card-header { flex-direction: column; text-align: center; gap: 15px; }
}
</style>

<script>
(function($) {
    const searchInput = $('#sm-verify-value');
    const searchType = $('#sm-verify-type');
    const suggestions = $('#sm-verify-suggestions');
    let typingTimer;

    // config object initialized from PHP variables to address code review concerns
    const config = {
        show_membership: <?php echo (int)$show_membership; ?>,
        show_practice: <?php echo (int)$show_practice; ?>,
        show_facility: <?php echo (int)$show_facility; ?>,
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
        const type = searchType.val();
        if (!val) return;

        const resultsArea = $('#sm-verify-results').empty();
        const loading = $('#sm-verify-loading').show();
        suggestions.hide();

        const action = 'sm_verify_document';
        const fd = new FormData();
        fd.append('action', action);
        fd.append('search_value', val);
        fd.append('search_type', type);

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
                        <p style="color: var(--sm-text-gray); font-size: 14px;">يرجى التأكد من القيمة المدخلة ونوع البحث المختار. قد لا يكون السجل متاحاً في النظام الرقمي حالياً.</p>
                        ${errorMsg ? `<p style="font-size:12px; color:#e53e3e;">${errorMsg}</p>` : ''}
                    </div>
                `);
            }
        }).catch(err => {
            loading.hide();
            console.error(err);
            resultsArea.append('<div class="error">حدث خطأ تقني أثناء محاولة الاستعلام.</div>');
        });
    });

    function renderResults(data, resultsArea) {
        const today = new Date();
        const results = Array.isArray(data) ? data : [data];

        results.forEach(block => {
            if (block.type === 'profile') renderProfileBlock(block, resultsArea);
            else if (block.type === 'membership' && config.show_membership) renderMembershipBlock(block, resultsArea);
            else if (block.type === 'practice' && config.show_practice) renderPracticeBlock(block, resultsArea);
            else if (block.type === 'facility' && config.show_facility) renderFacilityBlock(block, resultsArea);
            else if (block.type === 'tracking') renderTrackingBlock(block, resultsArea);
        });

        if (results.length > 0) {
            resultsArea.append(`
                <div style="text-align: center; margin-top: 10px;">
                    <button onclick="window.print()" class="sm-btn sm-btn-outline" style="width: auto; border-radius: 10px; font-size: 13px;">
                        <span class="dashicons dashicons-printer" style="margin-left: 5px;"></span> طباعة تقرير الاستعلام
                    </button>
                </div>
            `);
        }
    }

    function renderProfileBlock(block, area) {
        const o = block.owner;
        area.append(`
            <div class="sm-verification-card">
                <div class="sm-card-banner">
                    <div><span class="dashicons dashicons-admin-users" style="vertical-align: middle;"></span> ملف العضو الموحد</div>
                    <div style="opacity: 0.8;">كود: ${o.national_id.substr(-6)}</div>
                </div>
                <div class="sm-card-header">
                    <div>
                        <h3 style="margin: 0; font-weight: 900; font-size: 1.6em; color: var(--sm-dark-color); border:none; padding:0;">${o.name}</h3>
                        <div style="font-size: 12px; color: var(--sm-text-gray); font-weight: 600; margin-top: 5px;">
                            ${o.role_label} | ${o.branch} | تاريخ الاستعلام: ${new Date().toLocaleDateString('ar-EG')}
                        </div>
                    </div>
                    <div style="background: #f0fff4; color: #166534; padding: 12px 20px; border-radius: 12px; border: 1px solid #bbf7d0; font-size: 12px; font-weight: 700;">
                        ${config.success_msg}
                    </div>
                </div>
                <div class="sm-card-body">
                    <div class="sm-card-section-label">الهوية المهنية</div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">الدرجة المهنية</span><div class="sm-cell-value">${o.grade}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">التخصص الدقيق</span><div class="sm-cell-value">${o.specialization}</div></div>
                    </div>
                </div>
            </div>
        `);
    }

    function renderMembershipBlock(block, area) {
        const m = block.membership;
        const isValid = !m.expiry || m.expiry === '---' || new Date(m.expiry) >= new Date();
        area.append(`
            <div class="sm-verification-card">
                <div class="sm-card-banner" style="background: #4a5568;">
                    <div><span class="dashicons dashicons-id-alt" style="vertical-align: middle;"></span> بيانات القيد النقابي</div>
                </div>
                <div class="sm-card-body">
                    <div class="sm-data-grid">
                        <div class="sm-data-cell">
                            <span class="sm-cell-label">رقم القيد</span>
                            <div class="sm-cell-value" style="font-size: 1.5em; color: var(--sm-primary-color);">${m.number}</div>
                        </div>
                        <div class="sm-data-cell">
                            <span class="sm-cell-label">حالة القيد</span>
                            <div class="sm-status-badge ${isValid ? 'sm-status-valid' : 'sm-status-invalid'}">
                                ${isValid ? 'ساري' : 'منتهي / بحاجة لتجديد'}
                            </div>
                        </div>
                        <div class="sm-data-cell"><span class="sm-cell-label">تاريخ نهاية الصلاحية</span><div class="sm-cell-value">${m.expiry || '---'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">حالة السداد</span><div class="sm-cell-value">${m.status}</div></div>
                    </div>
                </div>
            </div>
        `);
    }

    function renderPracticeBlock(block, area) {
        const p = block.practice;
        const isValid = !p.expiry || p.expiry === '---' || new Date(p.expiry) >= new Date();
        area.append(`
            <div class="sm-verification-card">
                <div class="sm-card-banner" style="background: #2b6cb0;">
                    <div><span class="dashicons dashicons-awards" style="vertical-align: middle;"></span> تصريح مزاولة المهنة</div>
                </div>
                <div class="sm-card-body">
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">رقم الترخيص</span><div class="sm-cell-value" style="font-size: 1.3em;">${p.number}</div></div>
                        <div class="sm-data-cell">
                            <span class="sm-cell-label">حالة التصريح</span>
                            <div class="sm-status-badge ${isValid ? 'sm-status-valid' : 'sm-status-invalid'}">
                                ${isValid ? 'معتمد وساري' : 'منتهي الصلاحية'}
                            </div>
                        </div>
                        <div class="sm-data-cell"><span class="sm-cell-label">تاريخ الإصدار</span><div class="sm-cell-value">${p.issue_date || '---'}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">تاريخ الانتهاء</span><div class="sm-cell-value">${p.expiry || '---'}</div></div>
                    </div>
                </div>
            </div>
        `);
    }

    function renderFacilityBlock(block, area) {
        const f = block.facility;
        area.append(`
            <div class="sm-verification-card">
                <div class="sm-card-banner" style="background: #2c7a7b;">
                    <div><span class="dashicons dashicons-store" style="vertical-align: middle;"></span> تراخيص المنشآت</div>
                </div>
                <div class="sm-card-body">
                    <div style="margin-bottom: 15px; background: #f8fafc; padding: 15px; border-radius: 12px;">
                        <span class="sm-cell-label">اسم المنشأة</span>
                        <div class="sm-cell-value" style="font-size: 1.2em;">${f.name}</div>
                    </div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">رقم الترخيص</span><div class="sm-cell-value">${f.number}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">الفئة</span><div class="sm-cell-value">${f.category}</div></div>
                        <div class="sm-data-cell" style="grid-column: span 2;"><span class="sm-cell-label">العنوان</span><div class="sm-cell-value" style="font-size: 12px;">${f.address}</div></div>
                    </div>
                </div>
            </div>
        `);
    }

    function renderTrackingBlock(block, area) {
        const t = block.tracking;
        area.append(`
            <div class="sm-verification-card">
                <div class="sm-card-banner" style="background: #d69e2e;">
                    <div><span class="dashicons dashicons-backup" style="vertical-align: middle;"></span> تتبع حالة الطلب</div>
                </div>
                <div class="sm-card-body">
                    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; background: #fffaf0; padding: 20px; border-radius: 15px; border: 1px solid #feebc8;">
                        <div>
                            <span class="sm-cell-label">نوع الطلب / الخدمة</span>
                            <div class="sm-cell-value" style="font-size: 1.2em;">${t.service}</div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">كود: ${t.id} | تاريخ: ${t.date}</div>
                        </div>
                        <div class="sm-status-badge sm-status-pending" style="padding: 8px 15px;">${t.status}</div>
                    </div>
                    <div class="sm-data-grid">
                        <div class="sm-data-cell"><span class="sm-cell-label">صاحب الطلب</span><div class="sm-cell-value">${t.member}</div></div>
                        <div class="sm-data-cell"><span class="sm-cell-label">الفرع المختص</span><div class="sm-cell-value">${t.branch}</div></div>
                        ${t.notes ? `<div class="sm-data-cell" style="grid-column: span 2; background: #fff5f5; border-color: #feb2b2;">
                            <span class="sm-cell-label" style="color: #c53030;">ملاحظات الإدارة</span>
                            <div class="sm-cell-value" style="font-size: 12px; color: #9b2c2c;">${t.notes}</div>
                        </div>` : ''}
                    </div>
                </div>
            </div>
        `);
    }

})(jQuery);
</script>
