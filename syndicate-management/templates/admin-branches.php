<?php if (!defined('ABSPATH')) exit;
$mgmt_stats = SM_DB::get_branch_management_stats();
$can_manage_all = current_user_can('sm_full_access') || current_user_can('manage_options');
$current_user_gov = get_user_meta(get_current_user_id(), 'sm_governorate', true);
?>
<div class="sm-content-wrapper">
    <!-- Header & Search -->
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
        <div>
            <h2 style="margin:0; font-weight:800; color:var(--sm-dark-color);">إدارة فروع ولجان النقابة</h2>
            <p style="margin:5px 0 0 0; color:#64748b; font-size:13px;">إدارة التواجد الجغرافي، اللجان، والرسوم المالية الخاصة بالفروع.</p>
        </div>
        <?php if ($can_manage_all): ?>
            <button onclick="smOpenBranchModal()" class="sm-btn" style="width:auto; padding:0 25px; height:42px;">+ إضافة فرع جديد</button>
        <?php endif; ?>
    </div>

    <!-- Summary Stats -->
    <div class="sm-card-grid" style="margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <?php
        $stat_items = [
            ['label' => 'إجمالي الفروع', 'value' => $mgmt_stats['total_branches'], 'icon' => 'dashicons-location', 'color' => '#3182ce'],
            ['label' => 'الأعضاء (كافة الفروع)', 'value' => $mgmt_stats['total_members'], 'icon' => 'dashicons-admin-users', 'color' => '#38a169'],
            ['label' => 'تراخيص المزاولة', 'value' => $mgmt_stats['total_practice_licenses'], 'icon' => 'dashicons-id-alt', 'color' => '#e67e22'],
            ['label' => 'تراخيص المنشآت', 'value' => $mgmt_stats['total_facility_licenses'], 'icon' => 'dashicons-building', 'color' => '#e53e3e'],
        ];
        foreach ($stat_items as $s): ?>
            <div class="sm-stat-card-modern" style="border-right: 4px solid <?php echo $s['color']; ?>;">
                <div class="sm-stat-icon" style="color: <?php echo $s['color']; ?>; background: <?php echo $s['color']; ?>15;">
                    <span class="dashicons <?php echo $s['icon']; ?>"></span>
                </div>
                <div class="sm-stat-info">
                    <span class="sm-stat-label"><?php echo $s['label']; ?></span>
                    <span class="sm-stat-value"><?php echo number_format($s['value']); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Advanced Search Engine -->
    <div style="background:#fff; padding:25px; border-radius:15px; border:1px solid #e2e8f0; margin-bottom:30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items: flex-end;">
            <div class="sm-form-group">
                <label class="sm-label">البحث بالاسم أو المدير:</label>
                <input type="text" id="sm-branch-search-q" class="sm-input" placeholder="اكتب للبحث..." oninput="smHandleBranchSearch()">
            </div>
            <div class="sm-form-group">
                <label class="sm-label">الموقع / العنوان:</label>
                <input type="text" id="sm-branch-search-loc" class="sm-input" placeholder="محافظة أو مدينة..." oninput="smHandleBranchSearch()">
            </div>
            <div class="sm-form-group">
                <label class="sm-label">اللجان المنبثقة:</label>
                <input type="text" id="sm-branch-search-com" class="sm-input" placeholder="اسم اللجنة..." oninput="smHandleBranchSearch()">
            </div>
            <button onclick="smHandleBranchSearch()" class="sm-btn sm-btn-outline" style="height:42px;"><span class="dashicons dashicons-filter" style="font-size:16px; margin-top:4px;"></span> تصفية النتائج</button>
        </div>
    </div>

    <div id="sm-branches-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:25px;">
        <?php
        $branches = SM_DB::get_branches_data();
        if (empty($branches)): ?>
            <div style="grid-column: 1/-1; text-align:center; padding:50px; background:#fff; border-radius:15px; border:1px dashed #cbd5e0;">
                <p style="color:#718096;">لا توجد فروع مطابقة للبحث.</p>
            </div>
        <?php else: foreach($branches as $b):
            $is_hidden = !($b->is_active ?? 1);
            $can_edit = $can_manage_all || ($current_user_gov === $b->slug);
            $b_stats = SM_DB_Finance::get_statistics(['governorate' => $b->slug]);
        ?>
            <div class="sm-branch-card-complex"
                 data-name="<?php echo esc_attr($b->name); ?>"
                 data-manager="<?php echo esc_attr($b->manager); ?>"
                 data-address="<?php echo esc_attr($b->address); ?>"
                 data-committees="<?php echo esc_attr($b->committees); ?>"
                 style="background:<?php echo $is_hidden ? '#f8fafc' : '#fff'; ?>; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); transition:0.3s; opacity:<?php echo $is_hidden ? '0.75' : '1'; ?>;">

                <div style="padding: 25px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                        <div style="width:45px; height:45px; background:var(--sm-primary-color); border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff;">
                            <span class="dashicons dashicons-location"></span>
                        </div>
                        <div style="display:flex; gap:5px;">
                            <?php if ($can_edit): ?>
                                <button onclick='smEditBranch(<?php echo json_encode($b); ?>)' class="sm-btn" style="padding:6px 12px; font-size:11px; width:auto; height:auto;">تعديل البيانات</button>
                                <?php if ($can_manage_all): ?>
                                    <button onclick="smDeleteBranch(<?php echo $b->id; ?>)" class="sm-btn" style="background:#e53e3e; padding:6px 12px; font-size:11px; width:auto; height:auto;">حذف</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button onclick='smViewBranchDetails(<?php echo json_encode($b); ?>)' class="sm-btn sm-btn-outline" style="padding:6px 12px; font-size:11px; width:auto; height:auto;">عرض فقط</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h3 style="margin:0 0 5px 0; font-weight:800; color:var(--sm-dark-color);"><?php echo esc_html($b->name); ?></h3>
                    <div style="font-size:12px; color:#64748b; margin-bottom:15px; display:flex; align-items:center; gap:5px;">
                        <span class="dashicons dashicons-admin-site" style="font-size:14px; width:14px; height:14px;"></span>
                        <?php echo esc_html($b->slug); ?>
                        <?php if ($is_hidden): ?>
                            <span style="background:#edf2f7; color:#4a5568; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; margin-right:10px;">مخفي من القوائم</span>
                        <?php endif; ?>
                    </div>

                    <div style="background: #f8fafc; border-radius: 12px; padding: 15px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; border: 1px solid #edf2f7;">
                        <div style="text-align: center;">
                            <div style="font-size: 9px; color: #94a3b8; font-weight: 700; margin-bottom: 2px;">أعضاء</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--sm-dark-color);"><?php echo number_format($b_stats['total_members']); ?></div>
                        </div>
                        <div style="text-align: center; border-right: 1px solid #e2e8f0; border-left: 1px solid #e2e8f0;">
                            <div style="font-size: 9px; color: #94a3b8; font-weight: 700; margin-bottom: 2px;">تراخيص</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--sm-dark-color);"><?php echo number_format($b_stats['total_practice_licenses']); ?></div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 9px; color: #94a3b8; font-weight: 700; margin-bottom: 2px;">منشآت</div>
                            <div style="font-size: 13px; font-weight: 800; color: var(--sm-dark-color);"><?php echo number_format($b_stats['total_facility_licenses']); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($b->committees)): ?>
                    <div style="margin-bottom:15px;">
                        <div style="font-size:11px; font-weight:800; color:#94a3b8; margin-bottom:8px; text-transform:uppercase;">اللجان النشطة</div>
                        <div style="display:flex; flex-wrap:wrap; gap:5px;">
                            <?php foreach(explode(',', $b->committees) as $com): ?>
                                <span style="background:#ebf8ff; color:#2b6cb0; padding:3px 10px; border-radius:15px; font-size:11px; font-weight:600;"><?php echo esc_html(trim($com)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="background:#f1f5f9; padding:15px 25px; border-top:1px solid #e2e8f0; display:grid; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:#4a5568;">
                        <span class="dashicons dashicons-admin-users" style="font-size:16px; width:16px; height:16px; color:var(--sm-primary-color);"></span>
                        <strong>المدير:</strong> <?php echo esc_html($b->manager ?: 'غير محدد'); ?>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:#4a5568;">
                        <span class="dashicons dashicons-phone" style="font-size:16px; width:16px; height:16px; color:var(--sm-primary-color);"></span>
                        <strong>الهاتف:</strong> <?php echo esc_html($b->phone ?: '---'); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Refined Modular Branch Modal -->
<div id="sm-branch-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 850px;">
        <div class="sm-modal-header">
            <h3><span id="sm-branch-modal-title">إدارة بيانات الفرع</span></h3>
            <button class="sm-modal-close" onclick="document.getElementById('sm-branch-modal').style.display='none'">&times;</button>
        </div>

        <!-- Modal Tabs -->
        <div style="display:flex; background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:0 20px;">
            <button class="sm-branch-tab-btn active" onclick="smSwitchBranchTab('basic')">البيانات الأساسية</button>
            <button class="sm-branch-tab-btn" onclick="smSwitchBranchTab('visibility')">الظهور والصلاحيات</button>
            <button class="sm-branch-tab-btn" onclick="smSwitchBranchTab('finance')">إدارة الرسوم (خاص بالفرع)</button>
        </div>

        <form id="sm-branch-form">
            <input type="hidden" name="id" id="sm_branch_id">

            <!-- Tab 1: Basic Info -->
            <div id="sm-branch-tab-basic" class="sm-branch-tab-content active" style="padding:30px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="sm-form-group"><label class="sm-label">اسم الفرع / اللجنة:</label><input type="text" name="name" class="sm-input" required></div>
                    <div class="sm-form-group"><label class="sm-label">الكود التعريفي (Slug):</label><input type="text" name="slug" id="sm_branch_slug" class="sm-input" required placeholder="example-cairo" <?php echo !$can_manage_all ? 'readonly' : ''; ?>></div>
                    <div class="sm-form-group"><label class="sm-label">اسم مدير الفرع:</label><input type="text" name="manager" class="sm-input"></div>
                    <div class="sm-form-group"><label class="sm-label">رقم التواصل:</label><input type="text" name="phone" class="sm-input"></div>
                    <div class="sm-form-group" style="grid-column: span 2;"><label class="sm-label">البريد الإلكتروني الرسمي:</label><input type="email" name="email" class="sm-input"></div>
                    <div class="sm-form-group" style="grid-column: span 2;"><label class="sm-label">المقر / العنوان التفصيلي:</label><input type="text" name="address" class="sm-input"></div>
                    <div class="sm-form-group" style="grid-column: span 2;"><label class="sm-label">اللجان المنبثقة (فصل بفاصلة):</label><input type="text" name="committees" class="sm-input" placeholder="لجنة المسابقات، لجنة التدريب، لجنة شؤون اللاعبين..."></div>
                </div>
            </div>

            <!-- Tab 2: Visibility & Permissions -->
            <div id="sm-branch-tab-visibility" class="sm-branch-tab-content" style="padding:30px; display:none;">
                <div style="background:#fffaf0; border:1px solid #feebc8; padding:20px; border-radius:12px; margin-bottom:20px;">
                    <h5 style="margin:0 0 10px 0; color:#c05621; font-weight:800;">إعدادات الظهور</h5>
                    <p style="margin:0; font-size:13px; color:#7b341e;">تحكم في مدى ظهور الفرع في استمارات التسجيل العامة وقوائم النظام.</p>
                </div>
                <div class="sm-form-group">
                    <label style="display:flex; align-items:center; gap:12px; cursor:pointer; font-weight:700; background:#f8fafc; padding:15px; border-radius:10px; border:1px solid #e2e8f0;">
                        <input type="checkbox" name="is_active" id="sm_branch_is_active" value="1" style="width:20px; height:20px;">
                        تفعيل الفرع (ظهور في قوائم التسجيل والنظام)
                    </label>
                </div>
                <div style="margin-top:25px; border-top:1px solid #edf2f7; padding-top:20px;">
                    <h5 style="margin:0 0 15px 0; color:var(--sm-dark-color); font-weight:800;">البيانات البنكية للفرع</h5>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="sm-form-group"><label class="sm-label">اسم البنك:</label><input type="text" name="bank_name" class="sm-input"></div>
                        <div class="sm-form-group"><label class="sm-label">رقم الآيبان (IBAN):</label><input type="text" name="bank_iban" class="sm-input"></div>
                        <div class="sm-form-group"><label class="sm-label">انستا باي (Instapay):</label><input type="text" name="instapay_id" class="sm-input"></div>
                        <div class="sm-form-group"><label class="sm-label">المحفظة الإلكترونية:</label><input type="text" name="digital_wallet" class="sm-input"></div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Branch Specific Fees -->
            <div id="sm-branch-tab-finance" class="sm-branch-tab-content" style="padding:30px; display:none;">
                <div style="background:#ebf8ff; border:1px solid #bee3f8; padding:20px; border-radius:12px; margin-bottom:25px;">
                    <h5 style="margin:0 0 10px 0; color:#2b6cb0; font-weight:800;">تخصيص الرسوم المالية للفرع</h5>
                    <p style="margin:0; font-size:13px; color:#2c5282;">اترك الحقل فارغاً لاستخدام السعر العالمي الموحد. القيم المدخلة هنا ستطبق فقط على أعضاء هذا الفرع.</p>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <?php
                    $fee_fields = [
                        'membership_new' => 'انضمام عضوية جديدة',
                        'membership_renewal' => 'تجديد العضوية السنوي',
                        'membership_penalty' => 'غرامة تأخير العضوية',
                        'license_new' => 'إصدار ترخيص مزاولة جديد',
                        'license_renewal' => 'تجديد ترخيص المزاولة',
                        'license_penalty' => 'غرامة تأخير التراخيص',
                        'facility_c' => 'ترخيص منشأة (فئة ج)',
                        'test_entry_fee' => 'رسوم دخول الاختبارات'
                    ];
                    foreach ($fee_fields as $key => $label): ?>
                        <div class="sm-form-group">
                            <label class="sm-label"><?php echo $label; ?>:</label>
                            <input type="number" name="fees[<?php echo $key; ?>]" class="sm-input sm-fee-input" data-key="<?php echo $key; ?>" placeholder="السعر الموحد">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="padding:0 30px 30px;">
                <button type="submit" id="sm-save-branch-btn" class="sm-btn" style="width:100%;">حفظ كافة التغييرات</button>
            </div>
        </form>
    </div>
</div>

<style>
.sm-branch-tab-btn {
    padding: 15px 25px; border:none; background:none; cursor:pointer; font-weight:800; font-size:13px; color:#64748b; border-bottom: 3px solid transparent; transition: 0.3s;
}
.sm-branch-tab-btn.active { color:var(--sm-primary-color); border-bottom-color:var(--sm-primary-color); background:#fff; }
.sm-branch-tab-btn:hover:not(.active) { color:var(--sm-dark-color); background:#f1f5f9; }
.sm-branch-card-complex:hover { transform: translateY(-5px); }
</style>

<script>
window.smOpenBranchModal = function() {
    document.getElementById('sm-branch-form').reset();
    document.getElementById('sm_branch_id').value = '';
    document.getElementById('sm_branch_slug').readOnly = false;
    document.getElementById('sm-branch-modal-title').innerText = 'إضافة فرع جديد';
    smSwitchBranchTab('basic');
    document.getElementById('sm-branch-modal').style.display = 'flex';
};

window.smEditBranch = function(b) {
    const f = document.getElementById('sm-branch-form');
    document.getElementById('sm_branch_id').value = b.id;
    f.name.value = b.name;
    f.slug.value = b.slug;
    f.manager.value = b.manager || '';
    f.phone.value = b.phone || '';
    f.email.value = b.email || '';
    f.address.value = b.address || '';
    f.committees.value = b.committees || '';
    f.bank_name.value = b.bank_name || '';
    f.bank_iban.value = b.bank_iban || '';
    f.instapay_id.value = b.instapay_id || '';
    f.digital_wallet.value = b.digital_wallet || '';
    f.is_active.checked = (b.is_active != 0);

    // Load Fees
    document.querySelectorAll('.sm-fee-input').forEach(input => input.value = '');
    if (b.fees) {
        try {
            const fees = JSON.parse(b.fees);
            for (const [key, val] of Object.entries(fees)) {
                const input = f.querySelector(`[name="fees[${key}]"]`);
                if (input) input.value = val;
            }
        } catch(e) {}
    }

    document.getElementById('sm-branch-modal-title').innerText = 'تعديل بيانات: ' + b.name;
    smSwitchBranchTab('basic');
    document.getElementById('sm-branch-modal').style.display = 'flex';
};

window.smSwitchBranchTab = function(tab) {
    document.querySelectorAll('.sm-branch-tab-content').forEach(c => c.style.display = 'none');
    document.querySelectorAll('.sm-branch-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('sm-branch-tab-' + tab).style.display = 'block';
    event.target.classList.add('active');
};

window.smHandleBranchSearch = function() {
    const q = document.getElementById('sm-branch-search-q').value.toLowerCase();
    const loc = document.getElementById('sm-branch-search-loc').value.toLowerCase();
    const com = document.getElementById('sm-branch-search-com').value.toLowerCase();

    document.querySelectorAll('.sm-branch-card-complex').forEach(card => {
        const name = card.getAttribute('data-name').toLowerCase();
        const manager = card.getAttribute('data-manager').toLowerCase();
        const address = card.getAttribute('data-address').toLowerCase();
        const committees = card.getAttribute('data-committees').toLowerCase();

        const matchQ = !q || name.includes(q) || manager.includes(q);
        const matchLoc = !loc || address.includes(loc);
        const matchCom = !com || committees.includes(com);

        card.style.display = (matchQ && matchLoc && matchCom) ? 'block' : 'none';
    });
};

document.getElementById('sm-branch-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action', 'sm_save_branch');
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    const btn = document.getElementById('sm-save-branch-btn');
    btn.disabled = true;
    btn.innerText = 'جاري الحفظ...';

    fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert('خطأ: ' + res.data);
            btn.disabled = false;
            btn.innerText = 'حفظ كافة التغييرات';
        }
    });
});

window.smDeleteBranch = function(id) {
    if (!confirm('تحذير: سيؤدي حذف الفرع إلى فقدان الربط الجغرافي لبعض الأعضاء. هل أنت متأكد؟')) return;
    const fd = new FormData();
    fd.append('action', 'sm_delete_branch');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');
    fetch(ajaxurl, { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if (res.success) location.reload();
        else alert('خطأ: ' + res.data);
    });
};
</script>
