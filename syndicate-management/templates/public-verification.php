<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-verify-portal" dir="rtl" style="max-width: 900px; margin: 0 auto; padding: 30px 15px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <!-- Minimal Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <div style="width: 60px; height: 60px; background: #fef2f2; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
            <span class="dashicons dashicons-shield-check" style="font-size: 32px; width: 32px; height: 32px; color: #e53e3e;"></span>
        </div>
        <h1 style="margin: 0; font-weight: 800; font-size: 1.8em; color: #1a202c; letter-spacing: -0.5px;">منظومة الاستعلام الرقمي الموحد</h1>
        <p style="color: #718096; font-size: 14px; margin-top: 8px;">التحقق الفوري من صحة العضويات والتراخيص المهنية المسجلة</p>
    </div>

    <!-- Professional Search Input -->
    <div style="background: #fff; padding: 25px; border-radius: 16px; border: 1px solid #edf2f7; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <form id="sm-verify-form">
            <div style="display: flex; gap: 12px; align-items: stretch;">
                <div style="flex: 1; position: relative;">
                    <input type="text" id="sm-verify-value" class="sm-input" autocomplete="off"
                           placeholder="أدخل الرقم القومي، رقم القيد، أو رقم الترخيص..."
                           style="width: 100%; height: 50px; border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0 15px; font-weight: 600; font-size: 15px; transition: 0.2s;">
                    <div id="sm-verify-suggestions" style="display: none; position: absolute; top: 105%; left: 0; right: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; z-index: 1000; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); overflow: hidden;"></div>
                </div>
                <button type="submit" class="sm-btn" style="height: 50px; padding: 0 30px; font-weight: 700; font-size: 15px; border-radius: 10px; display: flex; align-items: center; gap: 8px; background: #1a202c; color: #fff; border: none; cursor: pointer; transition: 0.2s;">
                    <span class="dashicons dashicons-search"></span> بحث وتحقق
                </button>
            </div>
            <p style="margin: 10px 5px 0; font-size: 11px; color: #a0aec0; font-weight: 500;">* النظام يتعرف تلقائياً على نوع الرقم المدخل (قومي / قيد / ترخيص)</p>
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
    width: 30px;
    height: 30px;
    border: 3px solid rgba(26, 32, 44, 0.05);
    border-top: 3px solid #1a202c;
    border-radius: 50%;
    animation: sm-spin 0.8s linear infinite;
}
@keyframes sm-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.sm-result-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    overflow: hidden;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
    animation: smFadeInUp 0.4s ease-out;
}

@keyframes smFadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.sm-result-header {
    padding: 18px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f7fafc;
}

.sm-result-body {
    padding: 25px;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.sm-data-item {
    padding: 12px 15px;
    background: #fcfcfc;
    border: 1px solid #f7fafc;
    border-radius: 8px;
}

.sm-data-label {
    display: block;
    font-size: 10px;
    color: #a0aec0;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 3px;
}

.sm-data-value {
    font-weight: 700;
    color: #2d3748;
    font-size: 14px;
}

.sm-status-pill {
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.sm-status-active { background: #f0fff4; color: #2f855a; border: 1px solid #c6f6d5; }
.sm-status-expired { background: #fff5f5; color: #c53030; border: 1px solid #fed7d7; }

.sm-verify-suggestion-item {
    padding: 10px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f7fafc;
    font-size: 13px;
    font-weight: 500;
    color: #4a5568;
}
.sm-verify-suggestion-item:hover { background: #f7fafc; color: #e53e3e; }

@media (max-width: 640px) {
    .sm-result-body { grid-template-columns: 1fr; }
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
                    <div style="background: #fff; padding: 40px; border-radius: 16px; text-align: center; border: 1px dashed #feb2b2;">
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

        for (let k in data) {
            const doc = data[k];
            let isValid = true;
            let expiryStr = doc.expiry || 'غير محدد';

            if (doc.expiry) {
                const expiry = new Date(doc.expiry);
                if (expiry < today) isValid = false;
            }

            let html = `
                <div class="sm-result-card">
                    <div class="sm-result-header" style="background: ${isValid ? '#f0fff4' : '#fff5f5'};">
                        <div>
                            <span style="font-size: 10px; color: #718096; font-weight: 700; display: block; margin-bottom: 2px;">سجل التحقق المعتمد</span>
                            <h3 style="margin: 0; font-weight: 800; font-size: 1.1em; color: #1a202c;">${doc.label}</h3>
                        </div>
                        <div class="sm-status-pill ${isValid ? 'sm-status-active' : 'sm-status-expired'}">
                            <span class="dashicons ${isValid ? 'dashicons-shield-check' : 'dashicons-warning'}" style="font-size: 16px; width:16px; height:16px;"></span>
                            <span>${isValid ? 'بيانات سارية' : 'منتهي الصلاحية'}</span>
                        </div>
                    </div>
                    <div class="sm-result-body">
            `;

            // Field mapping based on document type
            const fields = [];
            if (k === 'membership') {
                fields.push({ label: 'اسم العضو', value: doc.name });
                fields.push({ label: 'رقم القيد', value: doc.number });
                fields.push({ label: 'الدرجة المهنية', value: doc.grade });
                fields.push({ label: 'التخصص', value: doc.specialization });
                fields.push({ label: 'تاريخ الانتهاء', value: expiryStr, span: 2, highlight: true });
            } else if (k === 'license') {
                fields.push({ label: 'اسم المنشأة', value: doc.facility_name, span: 2 });
                fields.push({ label: 'رقم الترخيص', value: doc.number });
                fields.push({ label: 'الفئة', value: 'فئة (' + doc.category + ')' });
                fields.push({ label: 'العنوان المسجل', value: doc.address, span: 2 });
                fields.push({ label: 'صلاحية الترخيص', value: expiryStr, span: 2, highlight: true });
            } else if (k === 'practice') {
                fields.push({ label: 'صاحب التصريح', value: doc.name, span: 2 });
                fields.push({ label: 'رقم التصريح', value: doc.number });
                fields.push({ label: 'تاريخ الإصدار', value: doc.issue_date });
                fields.push({ label: 'صلاحية التصريح', value: expiryStr, span: 2, highlight: true });
            } else if (k === 'profile') {
                fields.push({ label: 'الاسم الكامل', value: doc.name, span: 2 });
                fields.push({ label: 'الرقم القومي', value: doc.national_id });
                fields.push({ label: 'رقم القيد', value: doc.membership_number });
                fields.push({ label: 'الدرجة والفرع', value: doc.professional_grade + ' - ' + doc.governorate, span: 2 });
                fields.push({ label: 'حالة الحساب', value: doc.status, highlight: true });
                fields.push({ label: 'نهاية العضوية', value: expiryStr, highlight: true });
            }

            fields.forEach(f => {
                if (!f.value || f.value === 'غير محدد') return;
                let spanStyle = f.span ? `grid-column: span ${f.span};` : '';
                let bgStyle = f.highlight ? `background: #f8fafc; border-color: #edf2f7;` : '';
                let valStyle = f.highlight ? `color: ${isValid ? '#2f855a' : '#c53030'};` : '';

                html += `
                    <div class="sm-data-item" style="${spanStyle} ${bgStyle}">
                        <span class="sm-data-label">${f.label}</span>
                        <div class="sm-data-value" style="${valStyle}">${f.value}</div>
                    </div>
                `;
            });

            html += `</div></div>`;
            resultsArea.append(html);
        }
    }
})(jQuery);
</script>
