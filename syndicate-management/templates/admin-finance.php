<?php if (!defined('ABSPATH')) exit;

$stats = SM_Finance::get_financial_stats();
$search = isset($_GET['member_search']) ? sanitize_text_field($_GET['member_search']) : '';
$members = SM_DB::get_members(['search' => $search]);

if (!empty($members)) {
    SM_Finance::prefetch_data(array_map(function($m) { return $m->id; }, $members));
}

$members_with_balance = [];
foreach ($members as $m) {
    $dues = SM_Finance::calculate_member_dues($m);
    if ($dues['balance'] > 0 || !empty($search)) {
        $m->finance = $dues;
        $members_with_balance[] = $m;
    }
}
?>

<div class="sm-finance-registry" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 style="margin:0;">إدارة الاستحقاقات المالية</h3>
        <div style="display:flex; gap:10px;">
             <button onclick="smOpenPrintCustomizer('finance')" class="sm-btn" style="background: #4a5568; width: auto;"><span class="dashicons dashicons-printer"></span> طباعة مخصصة</button>
             <div class="sm-actions-dropdown" style="position:relative; display:inline-block;">
                <button class="sm-btn" style="background: #2c3e50; width: auto;"><span class="dashicons dashicons-media-spreadsheet"></span> تقارير الاستحقاقات <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 10px;"></span></button>
                <div class="sm-actions-content" style="left:0; right:auto;">
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_finance_report&type=overdue_membership'); ?>" target="_blank" class="sm-action-item">
                        <span class="dashicons dashicons-id"></span> متأخرات العضوية
                    </a>
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_finance_report&type=unpaid_fines'); ?>" target="_blank" class="sm-action-item">
                        <span class="dashicons dashicons-warning"></span> الغرامات غير المسددة
                    </a>
                    <a href="<?php echo admin_url('admin-ajax.php?action=sm_export_finance_report&type=full_liabilities'); ?>" target="_blank" class="sm-action-item">
                        <span class="dashicons dashicons-calculator"></span> تقرير المديونيات الشامل
                    </a>
                </div>
             </div>
             <button onclick="location.reload()" class="sm-btn sm-btn-outline" style="width:auto;"><span class="dashicons dashicons-update"></span> تحديث البيانات</button>
        </div>
    </div>

    <!-- Overall Metrics -->
    <div class="sm-card-grid" style="margin-bottom: 30px;">
        <?php
        // Total Collected Amount
        $icon = 'dashicons-money-alt'; $label = 'إجمالي المبالغ المحصلة'; $value = number_format($stats['total_paid'], 2); $color = '#38a169'; $suffix = 'ج.م';
        include SM_PLUGIN_DIR . 'templates/component-stat-card.php';

        // Total Overdue Receivables
        $icon = 'dashicons-warning'; $label = 'إجمالي المستحقات المتأخرة'; $value = number_format($stats['total_balance'], 2); $color = '#dd6b20'; $suffix = 'ج.م';
        include SM_PLUGIN_DIR . 'templates/component-stat-card.php';

        // Total Assigned Penalties
        $icon = 'dashicons-hammer'; $label = 'إجمالي الغرامات المقررة'; $value = number_format($stats['total_penalty'], 2); $color = '#e53e3e'; $suffix = 'ج.م';
        include SM_PLUGIN_DIR . 'templates/component-stat-card.php';

        // Total Claims Value
        $icon = 'dashicons-calculator'; $label = 'القيمة الإجمالية للمطالبات'; $value = number_format($stats['total_owed'], 2); $color = '#111F35'; $suffix = 'ج.م';
        include SM_PLUGIN_DIR . 'templates/component-stat-card.php';
        ?>
    </div>

    <!-- Search & Filter -->
    <div style="background: #f8fafc; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
        <form method="get" style="display: flex; gap: 12px; align-items: flex-end;">
            <input type="hidden" name="sm_tab" value="finance">
            <div style="flex: 1;">
                <label class="sm-label">البحث عن عضو (الاسم أو الرقم القومي):</label>
                <input type="text" name="member_search" class="sm-input" value="<?php echo esc_attr($search); ?>" placeholder="أدخل بيانات العضو لتدقيق حسابه المالي...">
            </div>
            <button type="submit" class="sm-btn" style="width: auto; height: 42px;">بحث وتدقيق</button>
            <?php if ($search): ?>
                <a href="<?php echo remove_query_arg(['member_search']); ?>" class="sm-btn sm-btn-outline" style="width: auto; height: 42px; text-decoration:none; display:flex; align-items:center;">إلغاء البحث</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Members Balance Table -->
    <div class="sm-table-container">
        <table class="sm-table sm-table-dense">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" onclick="document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = this.checked)"></th>
                    <th>العضو</th>
                    <th>الرقم القومي</th>
                    <th>المستحق</th>
                    <th>المسدد</th>
                    <th>المتبقي</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members_with_balance)): ?>
                    <tr><td colspan="7" style="text-align:center; padding: 30px; color: #718096;">لا توجد مديونيات قائمة بناءً على معايير البحث.</td></tr>
                <?php else: ?>
                    <?php foreach ($members_with_balance as $m): ?>
                        <tr>
                            <td><input type="checkbox" class="member-checkbox" value="<?php echo $m->id; ?>"></td>
                            <td>
                                <div style="font-weight: 700; color: var(--sm-dark-color);"><?php echo esc_html($m->name); ?></div>
                                <div style="font-size: 11px; color: #718096;"><?php echo esc_html($m->membership_number); ?></div>
                            </td>
                            <td style="font-family: monospace;"><?php echo esc_html($m->national_id); ?></td>
                            <td style="font-weight: 600;"><?php echo number_format($m->finance['total_owed'], 2); ?></td>
                            <td style="color: #38a169; font-weight: 600;"><?php echo number_format($m->finance['total_paid'], 2); ?></td>
                            <td style="color: #e53e3e; font-weight: 800;"><?php echo number_format($m->finance['balance'], 2); ?></td>
                            <td>
                                <?php if ($m->finance['balance'] <= 0): ?>
                                    <span class="sm-badge sm-badge-low" style="background:#def7ec; color:#03543f;">خالص</span>
                                <?php else: ?>
                                    <span class="sm-badge sm-badge-high">مدين</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button onclick="smOpenFinanceModal(<?php echo $m->id; ?>)" class="sm-btn" style="height: 28px; font-size: 10px; width: auto; background: #111F35;">تفاصيل / سداد</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

