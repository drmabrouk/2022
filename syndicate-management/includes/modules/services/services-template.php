<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-public-page" dir="rtl">
    <div class="sm-services-layout" style="display: flex; gap: 30px; margin-top: 30px; align-items: flex-start;">
        <div class="sm-services-sidebar" style="width: 280px; flex-shrink: 0; background: #fff; border: 1px solid var(--sm-border-color); border-radius: 20px; padding: 25px; position: sticky; top: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
            <h4 style="margin: 0 0 20px 0; font-weight: 800; color: var(--sm-dark-color); display: flex; align-items: center; gap: 10px; font-size: 1em;">
                <span style="display:flex; align-items:center; justify-content:center; width:28px; height:28px; background:var(--sm-primary-color); color:#fff; border-radius:8px;">
                    <span class="dashicons dashicons-filter" style="font-size: 16px; width: 16px; height: 16px;"></span>
                </span> فلترة الخدمات
            </h4>
            <div style="margin-bottom: 20px;">
                <label class="sm-label" style="font-size: 12px; margin-bottom: 5px; display: block; color: #64748b;">تصنيف الخدمة:</label>
                <select id="sm_service_cat_filter" class="sm-select" onchange="smApplyServiceFilters()" style="width: 100%; border-radius: 10px; font-size: 13px;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat); ?>"><?php echo esc_html($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="sm-services-grid-wrapper" style="flex: 1;">
            <div id="sm-services-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <?php if (empty($services)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #94a3b8; background: #fff; border-radius: 15px; border: 1px dashed #cbd5e0;"><p>لا توجد خدمات متاحة حالياً.</p></div>
                <?php else:
                    $count = 0;
                    foreach ($services as $s):
                        $count++;
                        $s_cat = $s->category ?: 'عام';
                        $access_type = $s->requires_login ? 'members' : 'public';
                ?>
                    <div class="sm-service-card-modern" data-category="<?php echo esc_attr($s_cat); ?>" data-name="<?php echo esc_attr($s->name); ?>" data-access="<?php echo $access_type; ?>" style="background: #fff; border: 1px solid var(--sm-border-color); border-radius: 20px; padding: 25px; display: <?php echo $count > 6 ? 'none' : 'flex'; ?>; flex-direction: column; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                            <div class="sm-service-icon" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--sm-primary-color), var(--sm-secondary-color)); border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 8px 12px -3px rgba(246, 48, 73, 0.2);"><span class="dashicons <?php echo esc_attr($s->icon ?: 'dashicons-cloud'); ?>" style="font-size: 24px; width: 24px; height: 24px;"></span></div>
                            <div><span style="display: inline-block; padding: 4px 10px; background: #f0f4f8; color: #4a5568; border-radius: 8px; font-size: 10px; font-weight: 700;"><?php echo esc_html($s_cat); ?></span></div>
                        </div>
                        <h3 style="margin: 0 0 10px 0; font-weight: 800; color: var(--sm-dark-color); font-size: 1.3em; line-height: 1.3;"><?php echo esc_html($s->name); ?></h3>
                        <p style="font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 20px; flex: 1;"><?php echo esc_html($s->description); ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                            <div style="display: flex; flex-direction: column;"><span style="font-size: 10px; color: #94a3b8; font-weight: 600;">رسوم الخدمة</span><span style="font-weight: 900; color: var(--sm-primary-color); font-size: 1.1em;"><?php echo $s->fees > 0 ? number_format($s->fees, 2) . ' <small>ج.م</small>' : 'خدمة مجانية'; ?></span></div>
                            <?php $btn_onclick = $is_logged_in ? "smOpenProgressiveForm(this, " . esc_attr(json_encode($s)) . ")" : "window.location.href='" . esc_url($login_url) . "'"; ?>
                            <button onclick='<?php echo $btn_onclick; ?>' class="sm-btn-sleek sm-service-trigger" style="background: var(--sm-dark-color); color: #fff; padding: 8px 20px; border: none; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.3s;">طلب خدمة</button>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <?php if (count($services) > 6): ?>
                <div style="text-align: center; margin-top: 40px;">
                    <button id="sm_load_more_services" onclick="smLoadMoreServices()" class="sm-btn sm-btn-outline" style="width: auto; padding: 12px 50px; font-weight: 800; font-size: 15px; border-radius: 15px;">عرض المزيد من الخدمات</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="sm-service-dropdown-container" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter:blur(5px); z-index:100000; justify-content:center; align-items:center; padding:20px;"><div id="sm-service-dropdown-content" style="background:#fff; width:100%; max-width:550px; border-radius:24px; padding:40px; position:relative;"><button onclick="document.getElementById('sm-service-dropdown-container').style.display='none'" style="position:absolute; top:20px; left:20px; border:none; background:none; font-size:24px; cursor:pointer; color:#94a3b8;">&times;</button><div id="sm-dropdown-body"></div></div></div>

<script>
window.smLoadMoreServices = function() {
    const hiddenCards = document.querySelectorAll('.sm-service-card-modern[style*="display: none"]');
    for (let i = 0; i < Math.min(hiddenCards.length, 6); i++) {
        hiddenCards[i].style.display = 'flex';
    }
    if (document.querySelectorAll('.sm-service-card-modern[style*="display: none"]').length === 0) {
        document.getElementById('sm_load_more_services').style.display = 'none';
    }
};

window.smOpenProgressiveForm = function(btn, s) {
    const container = document.getElementById('sm-service-dropdown-container');
    const body = document.getElementById('sm-dropdown-body');
    container.style.display = 'flex';

    let reqFields = []; try { reqFields = JSON.parse(s.required_fields); } catch(e){}

    const branchInfo = <?php echo $current_member ? wp_json_encode(SM_DB::get_branches_data()) : '[]'; ?>;
    const myBranch = branchInfo.find(b => b.slug === '<?php echo $current_member ? $current_member->governorate : ""; ?>') || branchInfo[0] || {};

    const renderStep = (step) => {
        let html = `<div style="margin-bottom:25px; border-bottom:1px solid #eee; pb:15px;"><h3 style="margin:0; font-weight:900; color:var(--sm-dark-color); font-size:1.4em;">${s.name}</h3></div>`;

        if (step === 1) {
            html += `<div id="service-step-1">
                <h4 style="margin-bottom:15px; color:var(--sm-primary-color); font-weight:800; font-size:15px;">المرحلة الأولى: استكمال البيانات</h4>
                <div id="service-req-fields">
                    ${reqFields.length > 0 ? reqFields.map(f => `<div class="sm-form-group"><label class="sm-label">${f.label}:</label><input id="f_${f.name}" type="${f.type||'text'}" class="sm-input" required value="${currentFormData[f.name] || ''}"></div>`).join('') : '<p style="color:#64748b; font-size:13px;">لا توجد حقول إضافية مطلوبة لهذه الخدمة.</p>'}
                </div>
                <button onclick="smServiceGoTo(2)" class="sm-btn" style="width:100%; margin-top:20px;">التالي: مراجعة وتأكيد البيانات</button>
            </div>`;
        } else if (step === 2) {
            html += `<div id="service-step-2">
                <h4 style="margin-bottom:15px; color:var(--sm-primary-color); font-weight:800; font-size:15px;">المرحلة الثانية: مراجعة وتأكيد البيانات</h4>
                <div style="background:#f8fafc; padding:20px; border-radius:15px; border:1px solid #e2e8f0; margin-bottom:20px;">
                    ${reqFields.map(f => `<div style="margin-bottom:10px; font-size:14px;"><strong>${f.label}:</strong> ${currentFormData[f.name] || '---'}</div>`).join('')}
                    ${reqFields.length === 0 ? '<p style="color:#64748b;">لا توجد بيانات إضافية للمراجعة.</p>' : ''}
                </div>
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:10px;">
                    <button onclick="smServiceGoTo(1)" class="sm-btn sm-btn-outline">تعديل البيانات</button>
                    <button onclick="smServiceGoTo(3)" class="sm-btn">تأكيد البيانات والمتابعة</button>
                </div>
            </div>`;
        } else if (step === 3) {
            html += `<div id="service-step-3">
                <h4 style="margin-bottom:15px; color:var(--sm-primary-color); font-weight:800; font-size:15px;">المرحلة الثالثة: الشروط والأحكام</h4>
                <div style="background:#f8fafc; padding:20px; border-radius:15px; font-size:13px; color:#4a5568; line-height:1.8; max-height:200px; overflow-y:auto; margin-bottom:20px; border:1px solid #e2e8f0;">
                    1. أقر بصحة كافة البيانات المدخلة في هذا الطلب.<br>
                    2. أتعهد بسداد الرسوم المقررة للخدمة عبر القنوات المعتمدة.<br>
                    3. للنقابة الحق في رفض الطلب في حال عدم مطابقة البيانات أو الوثائق.<br>
                    4. يتم معالجة الطلب خلال مدة 3-5 أيام عمل من تاريخ تأكيد السداد.
                </div>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:25px;">
                    <input type="checkbox" id="sm_terms_agree" style="width:20px; height:20px;">
                    <label for="sm_terms_agree" style="font-weight:700; font-size:14px; color:var(--sm-dark-color); cursor:pointer;">أوافق على الشروط والأحكام المذكورة أعلاه</label>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:10px;">
                    <button onclick="smServiceGoTo(2)" class="sm-btn sm-btn-outline">السابق</button>
                    <button onclick="smServiceGoTo(4)" class="sm-btn">التالي: سداد الرسوم</button>
                </div>
            </div>`;
        } else if (step === 4) {
            const feesText = s.fees > 0 ? `${s.fees} ج.م` : 'مجانية';
            html += `<div id="service-step-4">
                <h4 style="margin-bottom:15px; color:var(--sm-primary-color); font-weight:800; font-size:15px;">المرحلة الرابعة: سداد الرسوم المقررة</h4>
                <div style="background:#fffaf0; border:1px solid #feebc8; padding:15px; border-radius:12px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px;"><strong>إجمالي الرسوم:</strong> <span style="color:var(--sm-primary-color); font-weight:900;">${feesText}</span></div>
                    <div style="font-size:12px; color:#9c4221;">يرجى التحويل للفرع التابع له باستخدام البيانات التالية:</div>
                </div>
                <div style="display:grid; gap:10px; margin-bottom:25px; font-size:13px;">
                    ${myBranch.bank_iban ? `<div style="background:#f1f5f9; padding:10px; border-radius:8px;"><strong>IBAN:</strong> ${myBranch.bank_iban}</div>` : ''}
                    ${myBranch.instapay_id ? `<div style="background:#f1f5f9; padding:10px; border-radius:8px;"><strong>Instapay:</strong> ${myBranch.instapay_id}</div>` : ''}
                    ${myBranch.digital_wallet ? `<div style="background:#f1f5f9; padding:10px; border-radius:8px;"><strong>المحفظة:</strong> ${myBranch.digital_wallet}</div>` : ''}
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">رقم عملية التحويل (Reference):</label>
                    <input id="sm_trans_code" type="text" class="sm-input" placeholder="أدخل رقم الإيصال أو العملية">
                </div>
                <div class="sm-form-group">
                    <label class="sm-label">صورة الإيصال (اختياري):</label>
                    <input id="sm_trans_file" type="file" class="sm-input" accept="image/*">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:10px; margin-top:10px;">
                    <button onclick="smServiceGoTo(3)" class="sm-btn sm-btn-outline">السابق</button>
                    <button onclick="smServiceGoTo(5)" class="sm-btn">التالي: مراجعة نهائية</button>
                </div>
            </div>`;
        } else if (step === 5) {
            html += `<div id="service-step-5">
                <h4 style="margin-bottom:15px; color:var(--sm-primary-color); font-weight:800; font-size:15px;">المرحلة الخامسة: المراجعة النهائية والإرسال</h4>
                <div style="background:#f0fff4; padding:20px; border-radius:15px; border:1px solid #c6f6d5; margin-bottom:25px;">
                    <p style="margin:0; font-size:14px; color:#22543d; font-weight:700;">لقد قمت باستكمال كافة مراحل الطلب. يرجى التأكد من دقة بيانات التحويل المرفقة قبل الإرسال النهائي.</p>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:10px;">
                    <button onclick="smServiceGoTo(4)" class="sm-btn sm-btn-outline">السابق</button>
                    <button onclick="smSubmitFinalServiceRequest()" class="sm-btn" style="background:var(--sm-dark-color);">إرسال الطلب النهائي</button>
                </div>
            </div>`;
        }
        body.innerHTML = html;
    };

    let currentFormData = {};
    window.smServiceGoTo = (step) => {
        if (step === 2) {
            const inputs = document.querySelectorAll('#service-req-fields input');
            for(let i of inputs) {
                if(i.required && !i.value) {
                    smShowNotification('يرجى ملء الحقول المطلوبة.', true);
                    return;
                }
                currentFormData[i.id.replace('f_','')] = i.value;
            }
        }
        if (step === 4) {
            if (!document.getElementById('sm_terms_agree').checked) {
                smShowNotification('يجب الموافقة على الشروط للمتابعة.', true);
                return;
            }
        }
        renderStep(step);
    };

    window.smSubmitFinalServiceRequest = () => {
        const transCode = document.getElementById('sm_trans_code').value;
        const transFile = document.getElementById('sm_trans_file').files[0];

        const fd = new FormData();
        fd.append('action', 'sm_submit_service_request');
        fd.append('service_id', s.id);
        fd.append('member_id', '<?php echo $current_member ? $current_member->id : 0; ?>');
        fd.append('request_data', JSON.stringify(currentFormData));
        fd.append('transaction_code', transCode);
        if(transFile) fd.append('payment_receipt', transFile);

        const btn = document.querySelector('#service-step-5 .sm-btn:last-child');
        if (btn) { btn.disabled = true; btn.innerText = 'جاري التقديم...'; }

        const action = 'sm_submit_service_request';
        fetch(ajaxurl + '?action=' + action, {method:'POST', body:fd}).then(r=>r.json()).then(res=>{
            if(res.success) {
                body.innerHTML = `<div style="text-align:center; padding:20px;"><div style="font-size:60px; margin-bottom:20px;">✅</div><h3 style="font-weight:900; font-size:1.8em;">تم تقديم طلبك بنجاح!</h3><p style="color:#64748b; margin-bottom:20px;">كود تتبع الطلب الخاص بك:</p><div style="background:#f8fafc; border:2px dashed var(--sm-primary-color); padding:15px; font-size:24px; font-weight:900; color:var(--sm-primary-color); border-radius:15px; margin-bottom:30px;">${res.data}</div><button onclick="location.reload()" class="sm-btn" style="width:100%;">إغلاق</button></div>`;
            } else {
                smHandleAjaxError(res.data, 'فشل تقديم الطلب');
                if (btn) { btn.disabled = false; btn.innerText = 'تأكيد وإرسال الطلب'; }
            }
        });
    };

    renderStep(1);
};

function smApplyServiceFilters() {
    const cat = document.getElementById('sm_service_cat_filter').value;
    const cards = document.querySelectorAll('.sm-service-card-modern');
    let visibleCount = 0;
    cards.forEach(card => {
        const matches = (cat === 'الكل' || card.dataset.category === cat);
        if (matches) {
            visibleCount++;
            card.style.display = visibleCount <= 6 ? 'flex' : 'none';
        } else {
            card.style.display = 'none';
        }
    });
    const loadMoreBtn = document.getElementById('sm_load_more_services');
    if (loadMoreBtn) loadMoreBtn.style.display = visibleCount > 6 ? 'inline-block' : 'none';
}
</script>
