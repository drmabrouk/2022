<?php
if (!defined('ABSPATH')) exit;

$accent_color = get_option('sm_verify_accent_color', '#F63049');
$show_membership = get_option('sm_verify_show_membership', 1);
$show_practice = get_option('sm_verify_show_practice', 1);
$show_facility = get_option('sm_verify_show_facility', 1);
?>
<div class="sm-verify-portal" dir="rtl" style="max-width: 1000px; margin: 60px auto; padding: 0 20px; font-family: 'Rubik', sans-serif;">

    <!-- Minimal Professional Header -->
    <div style="text-align: center; margin-bottom: 60px;">
        <div style="display: inline-block; background: <?php echo esc_attr($accent_color); ?>10; padding: 8px 25px; border-radius: 50px; margin-bottom: 20px; animation: smFadeIn 0.5s ease;">
            <span style="color: <?php echo esc_attr($accent_color); ?>; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px;">Official Syndicate Portal</span>
        </div>
        <h2 style="margin: 0; font-weight: 900; font-size: 2.8em; color: var(--sm-dark-color); border: none; padding: 0; letter-spacing: -1px;"><?php echo esc_html(get_option('sm_verify_title', 'بوابة التحقق المهني الموحدة')); ?></h2>
        <p style="color: #64748b; font-size: 18px; margin-top: 15px; font-weight: 500; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.7;"><?php echo esc_html(get_option('sm_verify_desc', 'منصة مركزية ذكية للاستعلام والتحقق اللحظي من السجلات المهنية، التراخيص، وحالة الطلبات الرقمية في مكان واحد.')); ?></p>
    </div>

    <!-- Unified Search Control Center -->
    <div style="background: #fff; padding: 12px; border-radius: 28px; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08); margin-bottom: 40px; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative;" id="sm-search-container">
        <form id="sm-verify-form" style="margin: 0;">
            <div style="display: flex; gap: 8px; align-items: stretch;">
                <div style="width: 240px; flex-shrink: 0;">
                    <select id="sm-verify-type" class="sm-select" style="height: 68px; border-radius: 20px; border: 2px solid #f1f5f9; background: #f8fafc; font-weight: 800; width: 100%; font-size: 15px; padding: 0 20px; cursor: pointer; transition: 0.3s; color: var(--sm-dark-color); outline: none;">
                        <option value="auto">✨ كشف تلقائي ذكي</option>
                        <option value="national_id">🆔 الرقم القومي (14)</option>
                        <option value="membership">💳 رقم القيد النقابي</option>
                        <option value="practice">📜 ترخيص مزاولة المهنة</option>
                        <option value="facility">🏢 ترخيص المنشأة</option>
                        <option value="tracking">📡 كود تتبع الطلبات</option>
                    </select>
                </div>
                <div style="flex: 1; position: relative;">
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل البيانات المراد التحقق منها هنا..."
                           style="width: 100%; height: 68px; border-radius: 20px; border: 2px solid #f1f5f9; background: #f8fafc; padding: 0 25px; font-weight: 600; font-size: 17px; transition: 0.3s; color: var(--sm-dark-color); outline: none;">
                    <div id="sm-verify-suggestions" class="sm-suggestions-box" style="display: none; position: absolute; top: calc(100% + 12px); left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; z-index: 1000; box-shadow: 0 30px 60px -12px rgba(0,0,0,0.15); overflow: hidden; animation: smFadeIn 0.3s ease;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 68px; padding: 0 55px; font-weight: 900; font-size: 16px; border-radius: 20px; background: var(--sm-dark-color); color: #fff; border: none; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px -5px rgba(17, 31, 53, 0.3); display: flex; align-items: center; gap: 12px;">
                    <span class="dashicons dashicons-search" style="font-size: 20px; width: 20px; height: 20px;"></span> بحث واستعلام
                </button>
            </div>
        </form>
    </div>

    <!-- Real-time Validation Info -->
    <div id="sm-verify-help-area" style="text-align: center; margin-top: -30px; margin-bottom: 60px;">
        <span id="sm-validation-tip" style="display: inline-flex; align-items: center; gap: 8px; background: #fff; padding: 10px 25px; border-radius: 40px; font-size: 13px; color: #64748b; font-weight: 700; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: 0.3s;">
            <span class="dashicons dashicons-info" style="font-size: 18px; width: 18px; height: 18px; color: <?php echo esc_attr($accent_color); ?>;"></span>
            <span id="sm-tip-text"><?php echo esc_html(get_option('sm_verify_help', 'النظام الموحد يكتشف نوع البيانات تلقائياً، أو يمكنك الاختيار من القائمة.')); ?></span>
        </span>
    </div>

    <!-- Loading State -->
    <div id="sm-verify-loading" style="display: none; text-align: center; padding: 120px 0;">
        <div class="sm-professional-loader">
            <div class="sm-loader-ring"></div>
            <div class="sm-loader-pulse"></div>
        </div>
        <p style="color: #64748b; font-size: 17px; font-weight: 800; margin-top: 30px; letter-spacing: 0.5px;">جاري فحص وتدقيق البيانات في السجلات الرسمية...</p>
    </div>

    <!-- Search Results Output -->
    <div id="sm-verify-results" style="display: grid; gap: 30px;"></div>

</div>

<style>
@keyframes sm-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes smFadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes smSlideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
@keyframes sm-pulse { 0% { transform: scale(0.8); opacity: 0.4; } 50% { transform: scale(1.2); opacity: 0.7; } 100% { transform: scale(0.8); opacity: 0.4; } }

#sm-search-container:focus-within { border-color: <?php echo esc_attr($accent_color); ?>; box-shadow: 0 25px 60px -12px <?php echo esc_attr($accent_color); ?>15; }

.sm-professional-loader { position: relative; width: 90px; height: 90px; margin: 0 auto; }
.sm-loader-ring {
    position: absolute; width: 100%; height: 100%;
    border: 5px solid #f1f5f9; border-top-color: <?php echo esc_attr($accent_color); ?>;
    border-radius: 50%; animation: sm-spin 1s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}
.sm-loader-pulse {
    position: absolute; top: 20%; left: 20%; width: 60%; height: 60%;
    background: <?php echo esc_attr($accent_color); ?>30; border-radius: 50%;
    animation: sm-pulse 1.5s ease-out infinite;
}

.sm-verify-card {
    background: #fff;
    border-radius: 28px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 4px 15px -3px rgba(0,0,0,0.04);
    transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: smSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.sm-verify-card:hover { transform: translateY(-8px); box-shadow: 0 30px 50px -15px rgba(0,0,0,0.12); border-color: <?php echo esc_attr($accent_color); ?>20; }

.sm-verify-card-header {
    padding: 30px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
}

.sm-verify-card-label {
    display: flex; align-items: center; gap: 15px; font-weight: 900; font-size: 17px; color: var(--sm-dark-color);
}

.sm-verify-card-body { padding: 40px; }

.sm-result-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.sm-result-item {
    background: #f8fafc;
    padding: 22px;
    border-radius: 20px;
    border: 2px solid transparent;
    transition: 0.3s;
}
.sm-result-item:hover { background: #fff; border-color: #f1f5f9; transform: scale(1.02); }

.sm-result-key { display: block; font-size: 12px; color: #94a3b8; font-weight: 800; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.8px; }
.sm-result-val { display: block; font-weight: 800; color: var(--sm-dark-color); font-size: 16px; }

.sm-badge-status {
    padding: 8px 18px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.sm-badge-success { background: #dcfce7; color: #15803d; }
.sm-badge-warning { background: #fef3c7; color: #b45309; }
.sm-badge-danger { background: #fee2e2; color: #b91c1c; }

.sm-verify-suggestion-item {
    padding: 18px 25px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    font-weight: 700;
    transition: 0.2s;
    font-size: 15px;
}
.sm-verify-suggestion-item:hover { background: #f8fafc; color: <?php echo esc_attr($accent_color); ?>; padding-right: 35px; }
.sm-verify-suggestion-item:last-child { border-bottom: none; }

@media (max-width: 768px) {
    #sm-verify-form > div { flex-direction: column; gap: 10px; }
    #sm-verify-type { width: 100% !important; height: 60px; }
    #sm-verify-value { height: 60px; }
    #sm-verify-form button { width: 100%; height: 60px; justify-content: center; }
    .sm-verify-portal { margin: 30px auto; }
    .sm-verify-card-header { padding: 25px; flex-direction: column; gap: 15px; text-align: center; }
}
</style>

<script>
(function($) {
    const form = $('#sm-verify-form');
    const input = $('#sm-verify-value');
    const typeSelect = $('#sm-verify-type');
    const resultsArea = $('#sm-verify-results');
    const loading = $('#sm-verify-loading');
    const suggestions = $('#sm-verify-suggestions');
    const tipArea = $('#sm-validation-tip');
    const tipText = $('#sm-tip-text');
    let typingTimer;

    const config = {
        show_membership: <?php echo (int)$show_membership; ?>,
        show_practice: <?php echo (int)$show_practice; ?>,
        show_facility: <?php echo (int)$show_facility; ?>,
        success_msg: "<?php echo esc_js(get_option('sm_verify_success_msg', 'تم التحقق بنجاح واعتماد السجل الموحد.')); ?>"
    };

    // Professional Context Management
    typeSelect.on('change', function() {
        const val = $(this).val();
        updateTip(val);
        input.trigger('input');
        input.focus();
    });

    function updateTip(type) {
        let text = 'يرجى إدخال البيانات المطلوبة للاستعلام الموحد';
        if (type === 'national_id') text = 'الرقم القومي (14 رقماً) كما هو مدون في البطاقة الشخصية';
        else if (type === 'membership') text = 'رقم القيد النقابي المعتمد والمدون على الكارنيه الرسمي';
        else if (type === 'tracking') text = 'كود تتبع الطلب (REG- أو SR- أو الرقم التعريفي)';
        else if (type === 'auto') text = 'محرك البحث سيتعرف ذكياً على نوع البيانات المدخلة';

        tipText.fadeOut(150, function() {
            $(this).text(text).fadeIn(150);
        });
    }

    input.on('input', function() {
        clearTimeout(typingTimer);
        const val = $(this).val().trim();
        const type = typeSelect.val();

        // High-level visual feedback
        if (type === 'national_id' && val.length > 0) {
            const isValid = /^[0-9]{14}$/.test(val);
            tipArea.css({
                'background': isValid ? '#f0fff4' : '#fff5f5',
                'border-color': isValid ? '#bbf7d0' : '#feb2b2',
                'color': isValid ? '#166534' : '#b91c1c'
            });
        } else {
            tipArea.css({'background': '#fff', 'border-color': '#e2e8f0', 'color': '#64748b'});
        }

        if (val.length < 3) { suggestions.hide(); return; }

        typingTimer = setTimeout(() => {
            const action = 'sm_verify_suggest';
            fetch(`${ajaxurl}?action=${action}&query=${val}&type=${type}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data && res.data.length > 0) {
                    suggestions.empty().show();
                    res.data.forEach(item => {
                        suggestions.append(`<div class="sm-verify-suggestion-item" onclick="smSelectSuggestion('${item}')">${item}</div>`);
                    });
                } else suggestions.hide();
            });
        }, 250);
    });

    window.smSelectSuggestion = function(val) {
        input.val(val);
        suggestions.hide();
        form.submit();
    };

    $(document).on('click', e => { if (!$(e.target).closest('#sm-verify-form').length) suggestions.hide(); });

    form.on('submit', function(e) {
        e.preventDefault();
        const val = input.val().trim();
        const type = typeSelect.val();

        if (!val) {
            smNotify('يرجى إدخال قيمة صحيحة للبحث والاستعلام', true);
            return;
        }

        if (type === 'national_id' && !/^[0-9]{14}$/.test(val)) {
            smNotify('الرقم القومي غير صحيح (يجب أن يتكون من 14 رقم)', true);
            return;
        }

        resultsArea.hide().empty();
        loading.show();
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
                renderAllBlocks(res.data);
                resultsArea.fadeIn(400);
                setTimeout(() => {
                    const offset = resultsArea.offset().top - 40;
                    $('html, body').animate({ scrollTop: offset }, 600);
                }, 100);
            } else {
                renderError(res.data || 'عذراً، لم نتمكن من العثور على أية سجلات مطابقة لهذه البيانات.');
                resultsArea.fadeIn(400);
            }
        }).catch(err => {
            loading.hide();
            renderError('حدث خطأ غير متوقع في محرك البحث الموحد. يرجى المحاولة لاحقاً أو التواصل مع الإدارة.');
            resultsArea.fadeIn(400);
        });
    });

    function smNotify(msg, isError = false) {
        if (typeof smShowNotification === 'function') smShowNotification(msg, isError);
        else alert(msg);
    }

    function renderAllBlocks(data) {
        const blocks = Array.isArray(data) ? data : [data];
        if (blocks.length === 0) { renderError('لم يتم العثور على سجلات مطابقة'); return; }

        blocks.forEach(block => {
            let html = '';
            switch(block.type) {
                case 'profile': html = getProfileCard(block.owner); break;
                case 'membership': if(config.show_membership) html = getMembershipCard(block.membership); break;
                case 'practice': if(config.show_practice) html = getPracticeCard(block.practice); break;
                case 'facility': if(config.show_facility) html = getFacilityCard(block.facility); break;
                case 'tracking': html = getTrackingCard(block.tracking); break;
            }
            if (html) resultsArea.append(html);
        });

        resultsArea.append(`
            <div style="text-align: center; margin-top: 50px; padding: 40px; background: #f8fafc; border-radius: 30px; border: 2px dashed #e2e8f0; animation: smFadeIn 0.8s ease;">
                <h4 style="margin: 0 0 15px 0; font-weight: 800; color: #64748b; font-size: 15px;">هل ترغب في الحصول على نسخة من تقرير الاستعلام؟</h4>
                <button onclick="window.print()" class="sm-btn" style="width: auto; padding: 0 60px; height: 60px; border-radius: 18px; font-weight: 900; font-size: 17px; display: inline-flex; align-items: center; gap: 15px;">
                    <span class="dashicons dashicons-printer" style="font-size:22px; width:22px; height:22px;"></span> طباعة التقرير الموحد المعتمد
                </button>
            </div>
        `);
    }

    function getProfileCard(o) {
        return `
            <div class="sm-verify-card">
                <div class="sm-verify-card-header" style="background: #f8fafc;">
                    <div class="sm-verify-card-label"><span class="dashicons dashicons-admin-users" style="color:var(--sm-primary-color); font-size:24px; width:24px; height:24px;"></span> الهوية الشخصية والمهنية المعتمدة</div>
                    <div class="sm-badge-status sm-badge-success"><span class="dashicons dashicons-yes" style="font-size:16px; width:16px; height:16px; vertical-align:middle; margin-left:8px;"></span> ${config.success_msg}</div>
                </div>
                <div class="sm-verify-card-body">
                    <div style="display: flex; gap: 40px; align-items: center; margin-bottom: 40px;">
                        <div style="width: 100px; height: 100px; background: #fff; border: 3px solid #f1f5f9; border-radius: 28px; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
                            <span class="dashicons dashicons-businessman" style="font-size: 50px; width: 50px; height: 50px; color: #cbd5e0;"></span>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-weight: 900; font-size: 2.2em; color: var(--sm-dark-color); border:none; padding:0; line-height:1.2;">${o.name}</h3>
                            <div style="font-size: 14px; color: #64748b; font-weight: 700; margin-top: 8px;">${o.role_label} | فرع: ${o.branch} | استعلام: ${new Date().toLocaleDateString('ar-EG')}</div>
                        </div>
                    </div>
                    <div class="sm-result-grid">
                        <div class="sm-result-item"><span class="sm-result-key">الدرجة المهنية</span><span class="sm-result-val">${o.grade}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">التخصص الدقيق</span><span class="sm-result-val">${o.specialization}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">الرقم القومي</span><span class="sm-result-val">********${o.national_id.substr(-6)}</span></div>
                    </div>
                </div>
            </div>
        `;
    }

    function getMembershipCard(m) {
        const valid = !m.expiry || m.expiry === '---' || new Date(m.expiry) >= new Date();
        return `
            <div class="sm-verify-card">
                <div class="sm-verify-card-header">
                    <div class="sm-verify-card-label"><span class="dashicons dashicons-id-alt" style="color:#4a5568; font-size:24px; width:24px; height:24px;"></span> بيانات القيد والسجل النقابي</div>
                    <div class="sm-badge-status ${valid ? 'sm-badge-success' : 'sm-badge-danger'}">${valid ? 'عضوية سارية المفعول' : 'عضوية منتهية الصلاحية'}</div>
                </div>
                <div class="sm-verify-card-body">
                    <div class="sm-result-grid">
                        <div class="sm-result-item"><span class="sm-result-key">رقم القيد الموحد</span><span class="sm-result-val" style="font-size: 1.6em; color: var(--sm-primary-color);">${m.number}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">تاريخ انتهاء الصلاحية</span><span class="sm-result-val">${m.expiry || 'غير محدد'}</span></div>
                        <div class="sm-result-item" style="grid-column: span 2;"><span class="sm-result-key">حالة سداد الاشتراكات السنوية</span><span class="sm-result-val">${m.status}</span></div>
                    </div>
                </div>
            </div>
        `;
    }

    function getPracticeCard(p) {
        const valid = !p.expiry || p.expiry === '---' || new Date(p.expiry) >= new Date();
        return `
            <div class="sm-verify-card">
                <div class="sm-verify-card-header">
                    <div class="sm-verify-card-label"><span class="dashicons dashicons-awards" style="color:#2b6cb0; font-size:24px; width:24px; height:24px;"></span> تصريح مزاولة المهنة (الترخيص الفردي)</div>
                    <div class="sm-badge-status ${valid ? 'sm-badge-success' : 'sm-badge-danger'}">${valid ? 'ترخيص سارٍ ومعتمد' : 'ترخيص منتهي الصلاحية'}</div>
                </div>
                <div class="sm-verify-card-body">
                    <div class="sm-result-grid">
                        <div class="sm-result-item"><span class="sm-result-key">رقم ترخيص المزاولة</span><span class="sm-result-val" style="font-size: 1.6em;">${p.number}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">تاريخ الإصدار الرسمي</span><span class="sm-result-val">${p.issue_date || '---'}</span></div>
                        <div class="sm-result-item" style="grid-column: span 2;"><span class="sm-result-key">صلاحية الترخيص المهني</span><span class="sm-result-val">صالح حتى: ${p.expiry || '---'}</span></div>
                    </div>
                </div>
            </div>
        `;
    }

    function getFacilityCard(f) {
        return `
            <div class="sm-verify-card">
                <div class="sm-verify-card-header">
                    <div class="sm-verify-card-label"><span class="dashicons dashicons-store" style="color:#2c7a7b; font-size:24px; width:24px; height:24px;"></span> تراخيص المنشأة الرياضية التابعة</div>
                    <div class="sm-badge-status sm-badge-success">منشأة رياضية مرخصة</div>
                </div>
                <div class="sm-verify-card-body">
                    <div style="margin-bottom: 30px; background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid #f1f5f9;">
                        <span class="sm-result-key">الاسم التجاري للمنشأة / الأكاديمية</span>
                        <span class="sm-result-val" style="font-size: 1.5em; line-height: 1.4;">${f.name}</span>
                    </div>
                    <div class="sm-result-grid">
                        <div class="sm-result-item"><span class="sm-result-key">رقم ترخيص التشغيل</span><span class="sm-result-val">${f.number}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">تصنيف المنشأة</span><span class="sm-result-val">فئة (${f.category})</span></div>
                        <div class="sm-result-item" style="grid-column: span 2;"><span class="sm-result-key">الموقع الجغرافي المسجل والفرع</span><span class="sm-result-val">${f.address}</span></div>
                    </div>
                </div>
            </div>
        `;
    }

    function getTrackingCard(t) {
        const isApproved = t.status === 'تم القبول والتفعيل' || t.status === 'مكتمل / معتمد';
        const isRejected = t.status === 'مرفوض';
        return `
            <div class="sm-verify-card">
                <div class="sm-verify-card-header">
                    <div class="sm-verify-card-label"><span class="dashicons dashicons-backup" style="color:#d69e2e; font-size:24px; width:24px; height:24px;"></span> تتبع الطلبات والخدمات الرقمية اللحظي</div>
                    <div class="sm-badge-status ${isApproved ? 'sm-badge-success' : (isRejected ? 'sm-badge-danger' : 'sm-badge-warning')}">${t.status}</div>
                </div>
                <div class="sm-verify-card-body">
                    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-start; background: #fffaf0; padding: 25px; border-radius: 24px; border: 1px solid #feebc8;">
                        <div>
                            <span class="sm-result-key">نوع الطلب المقدم للنقابة</span>
                            <span class="sm-result-val" style="font-size: 1.4em;">${t.service}</span>
                            <div style="font-size: 13px; color: #94a3b8; margin-top: 10px; font-weight: 700;">كود التتبع الموحد: ${t.id} | تاريخ التقديم: ${t.date}</div>
                        </div>
                    </div>
                    <div class="sm-result-grid">
                        <div class="sm-result-item"><span class="sm-result-key">مقدم الطلب</span><span class="sm-result-val">${t.member}</span></div>
                        <div class="sm-result-item"><span class="sm-result-key">الجهة النقابية المختصة</span><span class="sm-result-val">${t.branch}</span></div>
                        ${t.notes ? `<div class="sm-result-item" style="grid-column: span 2; background: #fff5f5; border-color: #feb2b2;">
                            <span class="sm-result-key" style="color: #c53030;">ملاحظات الإدارة الفنية والتدقيق</span>
                            <span class="sm-result-val" style="font-size: 14px; color: #9b2c2c; line-height: 1.7;">${t.notes}</span>
                        </div>` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    function renderError(msg) {
        resultsArea.append(`
            <div class="sm-verify-card" style="border-style: dashed; border-color: #feb2b2; background: #fff5f510;">
                <div class="sm-verify-card-body" style="text-align: center; padding: 80px 40px;">
                    <div style="width: 80px; height: 80px; background: #fff5f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; color: #e53e3e;">
                        <span class="dashicons dashicons-search" style="font-size: 40px; width: 40px; height: 40px;"></span>
                    </div>
                    <h3 style="color: #c53030; font-weight: 900; font-size: 1.8em; margin-bottom: 15px; border:none; padding:0;">لم نتمكن من العثور على أية سجلات</h3>
                    <p style="color: #718096; font-size: 16px; line-height: 1.8; max-width: 500px; margin: 0 auto;">${msg}. يرجى التأكد من دقة البيانات المدخلة أو مراجعة نوع البحث المختار من القائمة.</p>
                    <div style="margin-top: 40px; display: flex; justify-content: center; gap: 20px;">
                        <button onclick="location.reload()" class="sm-btn sm-btn-outline" style="width: auto; padding: 0 40px; height: 55px; font-weight: 700; border-radius: 16px;">تفريغ الحقول</button>
                        <button onclick="$('#sm-verify-value').focus()" class="sm-btn" style="width: auto; padding: 0 40px; height: 55px; font-weight: 700; border-radius: 16px;">تعديل البيانات</button>
                    </div>
                </div>
            </div>
        `);
    }

})(jQuery);
</script>
