<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-verify-portal" dir="rtl" style="max-width: 900px; margin: 0 auto; padding: 20px 15px; font-family: 'Rubik', sans-serif;">

    <!-- Enhanced Header -->
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 50px; height: 50px; background: #fff; border: 2px solid #e53e3e; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 4px 10px rgba(229, 62, 62, 0.1);">
            <span class="dashicons dashicons-shield-check" style="font-size: 24px; width: 24px; height: 24px; color: #e53e3e;"></span>
        </div>
        <h1 style="margin: 0; font-weight: 900; font-size: 1.6em; color: #111F35; letter-spacing: -0.5px; border: none; padding: 0;">منظومة التحقق الرقمي الموحدة</h1>
        <p style="color: #64748b; font-size: 13px; margin-top: 5px; font-weight: 500;">بوابة الاستعلام الفوري عن العضويات والتراخيص المعتمدة</p>
    </div>

    <!-- Redesigned Search Input -->
    <div style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); margin-bottom: 20px;">
        <form id="sm-verify-form">
            <div style="display: flex; gap: 10px; align-items: stretch;">
                <div style="flex: 1; position: relative;">
                    <span class="dashicons dashicons-search" style="position: absolute; right: 12px; top: 13px; color: #94a3b8; font-size: 20px;"></span>
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل الرقم القومي، رقم القيد، أو رقم الترخيص للتحقق..."
                           style="width: 100%; height: 46px; border-radius: 8px; border: 1px solid #cbd5e0; background: #fff; padding: 0 40px 0 15px; font-weight: 600; font-size: 14px; transition: 0.2s; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                    <div id="sm-verify-suggestions" class="sm-suggestions-box" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; z-index: 1000; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 46px; padding: 0 25px; font-weight: 700; font-size: 14px; border-radius: 8px; display: flex; align-items: center; gap: 8px; background: #2c3e50; color: #fff; border: none; cursor: pointer; transition: 0.2s;">
                    تحقق الآن
                </button>
            </div>
            <div style="margin-top: 8px; display: flex; align-items: center; gap: 5px; color: #718096; font-size: 11px;">
                <span class="dashicons dashicons-info" style="font-size: 14px; width: 14px; height: 14px;"></span>
                <span>التعرف الذكي على المدخلات نشط (قومي / قيد / ترخيص / منشأة)</span>
            </div>
        </form>
    </div>

    <div id="sm-verify-loading" style="display: none; text-align: center; padding: 40px;">
        <div class="sm-spinner-minimal" style="margin: 0 auto 15px;"></div>
        <p style="color: #718096; font-size: 13px;">جاري مراجعة السجلات الرسمية...</p>
    </div>

    <!-- Structured Results Area -->
    <div id="sm-verify-results" style="display: grid; gap: 20px;"></div>

</div>

<style>
.sm-spinner-minimal {
    width: 24px;
    height: 24px;
    border: 3px solid rgba(44, 62, 80, 0.1);
    border-top: 3px solid #2c3e50;
    border-radius: 50%;
    animation: sm-spin 0.8s linear infinite;
}
@keyframes sm-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.sm-report-container {
    animation: smFadeInUp 0.4s ease-out;
}

@keyframes smFadeInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.sm-data-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.sm-data-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.sm-data-item {
    padding: 12px 15px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
    border-radius: 8px;
    transition: 0.2s;
}
.sm-data-item:hover { background: #fff; border-color: #cbd5e0; }

.sm-data-label {
    display: block;
    font-size: 10px;
    color: #718096;
    font-weight: 700;
    margin-bottom: 2px;
}

.sm-data-value {
    font-weight: 800;
    color: #1a202c;
    font-size: 14px;
    word-break: break-all;
}

.sm-status-pill {
    padding: 4px 14px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
}

.sm-status-active { background: #f0fff4; color: #22543d; border-color: #c6f6d5; }
.sm-status-expired { background: #fff5f5; color: #742a2a; border-color: #fed7d7; }

.sm-verify-suggestion-item {
    padding: 12px 18px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    font-weight: 600;
    color: #2d3748;
    transition: 0.2s;
}
.sm-verify-suggestion-item:hover { background: #f8fafc; color: #e53e3e; padding-right: 25px; }

@media (max-width: 768px) {
    .sm-data-grid, .sm-data-grid-3 {
        grid-template-columns: 1fr !important;
    }
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
                    <div style="background: #fff; padding: 40px; border-radius: 12px; text-align: center; border: 1px dashed #feb2b2;">
                        <div style="font-size: 40px; margin-bottom: 15px;">🔍</div>
                        <h3 style="color: #c53030; font-weight: 800; font-size: 1.1em; margin-bottom: 5px;">بيانات غير متطابقة</h3>
                        <p style="color: #718096; font-size: 13px;">${res.data}</p>
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

        // 1. Report Container & Header
        let html = `
            <div class="sm-report-container" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                <div style="background: #f8fafc; padding: 20px 25px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 45px; height: 45px; background: #2c3e50; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff;">
                            <span class="dashicons dashicons-id-alt" style="font-size: 24px; width: 24px; height: 24px;"></span>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-weight: 800; font-size: 1.15em; color: #111F35; border:none; padding:0;">تقرير التحقق الرقمي</h3>
                            <div style="font-size: 11px; color: #718096; font-weight: 600; margin-top: 2px;">تاريخ الاستعلام: ${new Date().toLocaleDateString('ar-EG')}</div>
                        </div>
                    </div>
                    <div class="sm-status-pill sm-status-active" style="background: #ebf8ff; color: #2b6cb0; border-color: #bee3f8;">
                        <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        <span>بيانات مطابقة ومعتمدة</span>
                    </div>
                </div>

                <div style="padding: 25px;">
                    <!-- Owner Info Section -->
                    <div style="margin-bottom: 25px;">
                        <h4 style="margin: 0 0 15px 0; font-size: 13px; color: #2c3e50; font-weight: 800; display: flex; align-items: center; gap: 8px; border:none; padding:0;">
                            <span style="width: 4px; height: 16px; background: #e53e3e; border-radius: 2px; display: inline-block;"></span>
                            بيانات صاحب السجل المهني
                        </h4>
                        <div class="sm-data-grid">
                            <div class="sm-data-item"><span class="sm-data-label">الاسم الكامل</span><div class="sm-data-value">${owner.name}</div></div>
                            <div class="sm-data-item"><span class="sm-data-label">الفرع النقابي</span><div class="sm-data-value">${owner.branch || '---'}</div></div>
                            <div class="sm-data-item"><span class="sm-data-label">الدرجة الحالية</span><div class="sm-data-value">${owner.grade || '---'}</div></div>
                            <div class="sm-data-item"><span class="sm-data-label">التخصص المعتمد</span><div class="sm-data-value">${owner.specialization || '---'}</div></div>
                            <div class="sm-data-item"><span class="sm-data-label">رقم الهاتف</span><div class="sm-data-value" dir="ltr">${owner.phone || '---'}</div></div>
                            <div class="sm-data-item"><span class="sm-data-label">البريد الإلكتروني</span><div class="sm-data-value">${owner.email || '---'}</div></div>
                        </div>
                    </div>
        `;

        // 2. Specific Documents Grid
        html += `<div style="display: grid; grid-template-columns: 1fr; gap: 15px;">`;

        if (data.membership) {
            const m = data.membership;
            const valid = isStillValid(m.expiry);
            html += `
                <div style="background: #fdfdfd; border: 1px solid #edf2f7; border-radius: 10px; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h5 style="margin: 0; font-weight: 800; color: #2d3748; font-size: 13px; border:none; padding:0;">بيانات القيد النقابي</h5>
                        <div class="sm-status-pill ${valid ? 'sm-status-active' : 'sm-status-expired'}" style="font-size: 10px; padding: 3px 10px;">
                            ${valid ? 'عضوية سارية' : 'عضوية منتهية'}
                        </div>
                    </div>
                    <div class="sm-data-grid">
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">رقم القيد</span><div class="sm-data-value">${m.number}</div></div>
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">صلاحية العضوية حتى</span><div class="sm-data-value" style="color:${valid ? '#2f855a' : '#c53030'}">${m.expiry || '---'}</div></div>
                    </div>
                </div>
            `;
        }

        if (data.practice) {
            const p = data.practice;
            const valid = isStillValid(p.expiry);
            html += `
                <div style="background: #fdfdfd; border: 1px solid #edf2f7; border-radius: 10px; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h5 style="margin: 0; font-weight: 800; color: #2d3748; font-size: 13px; border:none; padding:0;">تصريح مزاولة المهنة</h5>
                        <div class="sm-status-pill ${valid ? 'sm-status-active' : 'sm-status-expired'}" style="font-size: 10px; padding: 3px 10px;">
                            ${valid ? 'تصريح سارٍ' : 'تصريح منتهي'}
                        </div>
                    </div>
                    <div class="sm-data-grid">
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">رقم التصريح</span><div class="sm-data-value">${p.number}</div></div>
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">صالح لغاية</span><div class="sm-data-value" style="color:${valid ? '#2f855a' : '#c53030'}">${p.expiry || '---'}</div></div>
                    </div>
                </div>
            `;
        }

        if (data.facility) {
            const f = data.facility;
            const valid = isStillValid(f.expiry);
            html += `
                <div style="background: #fdfdfd; border: 1px solid #edf2f7; border-radius: 10px; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h5 style="margin: 0; font-weight: 800; color: #2d3748; font-size: 13px; border:none; padding:0;">ترخيص المنشأة / الأكاديمية</h5>
                        <div class="sm-status-pill ${valid ? 'sm-status-active' : 'sm-status-expired'}" style="font-size: 10px; padding: 3px 10px;">
                            ${valid ? 'ترخيص سارٍ' : 'ترخيص منتهي'}
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">اسم المنشأة</span><div class="sm-data-value" style="font-size:1.1em;">${f.name}</div></div>
                        <div class="sm-data-grid-3">
                            <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">رقم الترخيص</span><div class="sm-data-value">${f.number}</div></div>
                            <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">الفئة</span><div class="sm-data-value">(${f.category})</div></div>
                            <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">نهاية الترخيص</span><div class="sm-data-value" style="color:${valid ? '#2f855a' : '#c53030'}">${f.expiry || '---'}</div></div>
                        </div>
                        <div class="sm-data-item" style="border: none; background: #fff;"><span class="sm-data-label">الموقع المسجل</span><div class="sm-data-value" style="font-size:0.9em; font-weight:600; color:#4a5568;">${f.address}</div></div>
                    </div>
                </div>
            `;
        }

        html += `</div></div></div>`; // End Body & Container
        resultsArea.append(html);
    }
})(jQuery);
</script>
