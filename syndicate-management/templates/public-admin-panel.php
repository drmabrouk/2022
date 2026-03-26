<?php if (!defined('ABSPATH')) exit; ?>
<?php
$user = wp_get_current_user();
$roles = (array)$user->roles;
$is_admin = in_array('administrator', $roles) || current_user_can('manage_options');
$is_sys_admin = in_array('sm_system_admin', $roles);
$is_syndicate_admin = in_array('sm_syndicate_admin', $roles);
$is_syndicate_member = in_array('sm_syndicate_member', $roles);
$is_member = in_array('sm_member', $roles);
$is_officer = $is_syndicate_admin || $is_syndicate_member;

$is_restricted = ($is_member || $is_syndicate_member) && !current_user_can('sm_manage_members');
$default_tab = $is_restricted ? 'my-profile' : 'summary';
$active_tab = isset($_GET['sm_tab']) ? sanitize_text_field($_GET['sm_tab']) : $default_tab;

if ($is_restricted && !in_array($active_tab, ['my-profile', 'member-profile', 'digital-services', 'surveys'])) {
    $active_tab = 'my-profile';
}

$syndicate = SM_Settings::get_syndicate_info();
$labels = SM_Settings::get_labels();
$appearance = SM_Settings::get_appearance();
$stats = array();

if ($active_tab === 'summary') {
    $stats = SM_DB::get_statistics();
}

$hour = (int)current_time('G');
$greeting = ($hour >= 5 && $hour < 12) ? 'صباح الخير' : 'مساء الخير';
?>

<div class="sm-admin-dashboard" dir="rtl" style="font-family: 'Rubik', sans-serif; background: <?php echo $appearance['bg_color']; ?>; border: 1px solid var(--sm-border-color); border-radius: 12px; overflow: hidden; color: <?php echo $appearance['font_color']; ?>; font-size: <?php echo $appearance['font_size']; ?>; font-weight: <?php echo $appearance['font_weight']; ?>; line-height: <?php echo $appearance['line_spacing']; ?>;">
    <!-- OFFICIAL SYSTEM HEADER -->
    <div class="sm-main-header">
        <div style="display: flex; align-items: center; gap: 20px;">
            <?php if (!empty($syndicate['syndicate_logo'])): ?>
                <div style="background: white; padding: 5px; border: 1px solid var(--sm-border-color); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <img src="<?php echo esc_url($syndicate['syndicate_logo']); ?>" style="height: 45px; width: auto; object-fit: contain; display: block;">
                </div>
            <?php else: ?>
                <div style="background: #f1f5f9; padding: 5px; border: 1px solid var(--sm-border-color); border-radius: 10px; height: 45px; width: 45px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                    <span class="dashicons dashicons-building" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
            <?php endif; ?>
            <div>
                <h1 style="margin:0; border: none; padding: 0; color: var(--sm-dark-color); font-weight: 800; font-size: 1.3em; text-decoration: none; line-height: 1;">
                    <?php echo esc_html($syndicate['syndicate_name']); ?>
                </h1>
                <div style="display: inline-flex; align-items: center; padding: 6px 16px; background: #f0f4f8; color: #111F35; border-radius: 12px; font-size: 11px; font-weight: 700; margin-top: 8px; border: 1px solid #cbd5e0; line-height: 1.4; gap: 8px;">
                    <div style="color: #4a5568;">
                        <?php
                        if ($is_admin || $is_sys_admin) echo 'مدير النظام';
                        elseif ($is_syndicate_admin) echo 'مسؤول النقابة';
                        elseif ($is_syndicate_member) echo 'عضو النقابة';
                        elseif ($is_member) echo 'عضو';
                        else echo 'مستخدم النظام';
                        ?>
                    </div>
                    <?php
                    $my_gov_key = get_user_meta($user->ID, 'sm_governorate', true);
                    $govs = SM_Settings::get_governorates();
                    $my_gov_label = $govs[$my_gov_key] ?? '';
                    if ($my_gov_label): ?>
                        <div style="width: 1px; height: 14px; background: #cbd5e0;"></div>
                        <div style="color: var(--sm-primary-color);"><?php echo esc_html($my_gov_label); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="sm-header-info-box" style="text-align: right; border-left: 1px solid var(--sm-border-color); padding-left: 15px;">
                <div style="font-size: 0.85em; font-weight: 700; color: var(--sm-dark-color);"><?php echo date_i18n('l j F Y'); ?></div>
            </div>

            <?php if ($is_admin || $is_sys_admin || $is_syndicate_admin): ?>
                <div style="display: flex; gap: 10px;">
                    <button onclick="window.location.href='<?php echo add_query_arg('sm_tab', 'global-archive'); ?>&sub_tab=finance'" class="sm-btn" style="background: #e67e22; height: 38px; font-size: 11px; color: white !important; width: auto;"><span class="dashicons dashicons-portfolio" style="font-size: 16px; margin-top: 4px;"></span> الأرشيف الرقمي</button>
                    <button onclick="window.location.href='<?php echo add_query_arg('sm_tab', 'practice-licenses'); ?>&action=new'" class="sm-btn" style="background: #2c3e50; height: 38px; font-size: 11px; color: white !important; width: auto;" title="إصدار تصريح جديد">+ إصدار تصريح</button>
                    <button onclick="window.location.href='<?php echo add_query_arg('sm_tab', 'facility-licenses'); ?>&action=new'" class="sm-btn" style="background: #27ae60; height: 38px; font-size: 11px; color: white !important; width: auto;" title="تسجيل منشأة أو مؤسسة">+ تسجيل منشأة</button>
                </div>
            <?php endif; ?>

            <div style="display: flex; gap: 15px; align-items: center; border-left: 1px solid var(--sm-border-color); padding-left: 20px;">
                <a href="<?php echo home_url(); ?>" class="sm-header-circle-icon" title="الرئيسية"><span class="dashicons dashicons-admin-home"></span></a>
                <a href="<?php echo $is_restricted ? add_query_arg(['sm_tab' => 'my-profile', 'profile_tab' => 'correspondence']) : add_query_arg('sm_tab', 'messaging'); ?>" class="sm-header-circle-icon" title="المراسلات والشكاوى">
                    <span class="dashicons dashicons-email"></span>
                    <?php
                    $unread_msgs = SM_DB_Communications::get_unread_count($user->ID);
                    if ($is_restricted) {
                        $member = SM_DB_Members::get_member_by_wp_user_id($user->ID);
                        if ($member) $unread_msgs += intval(SM_DB_Communications::get_unread_tickets_count($member->id));
                    }
                    if ($unread_msgs > 0): ?><span class="sm-icon-badge" style="background: #e53e3e;"><?php echo $unread_msgs; ?></span><?php endif; ?>
                </a>
                <div class="sm-notifications-dropdown" style="position: relative;">
                    <a href="javascript:void(0)" onclick="smToggleNotifications()" class="sm-header-circle-icon" title="التنبيهات">
                        <span class="dashicons dashicons-bell"></span>
                        <?php
                        $notif_alerts = [];
                        if ($is_restricted) {
                            $member_by_wp = SM_DB_Members::get_member_by_wp_user_id($user->ID);
                            if ($member_by_wp && $member_by_wp->last_paid_membership_year < date('Y')) $notif_alerts[] = ['text' => 'يوجد متأخرات في تجديد العضوية السنوية', 'type' => 'warning'];
                        }
                        if (current_user_can('sm_manage_members')) {
                            $pending_updates = SM_DB_Members::count_pending_update_requests();
                            if ($pending_updates > 0) $notif_alerts[] = ['text' => 'يوجد ' . $pending_updates . ' طلبات تحديث بيانات بانتظار المراجعة', 'type' => 'info'];
                        }
                        $sys_alerts = SM_DB::get_active_alerts_for_user($user->ID);
                        foreach($sys_alerts as $sa) $notif_alerts[] = ['text' => $sa->title, 'type' => 'system', 'id' => $sa->id, 'details' => $sa->message];
                        if (count($notif_alerts) > 0): ?><span class="sm-icon-badge" style="background: #f6ad55;"><?php echo count($notif_alerts); ?></span><?php endif; ?>
                    </a>
                    <div id="sm-notifications-menu" style="display: none; position: absolute; top: 150%; left: 0; background: white; border: 1px solid var(--sm-border-color); border-radius: 8px; width: 300px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; padding: 15px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 14px; border-bottom: 1px solid #eee; padding-bottom: 8px;">التنبيهات والإشعارات</h4>
                        <?php if (empty($notif_alerts)): ?><div style="font-size: 12px; color: #94a3b8; text-align: center; padding: 20px;">لا توجد تنبيهات جديدة حالياً</div>
                        <?php else: foreach ($notif_alerts as $a): ?>
                            <div style="font-size: 12px; padding: 8px; border-bottom: 1px solid #f9fafb; color: #4a5568; display: flex; gap: 8px; align-items: flex-start;">
                                <span class="dashicons <?php echo $a['type'] == 'system' ? 'dashicons-megaphone' : 'dashicons-warning'; ?>" style="font-size: 16px; color: <?php echo $a['type'] == 'system' ? 'var(--sm-primary-color)' : '#d69e2e'; ?>;"></span>
                                <span><strong style="display:block; margin-bottom:2px;"><?php echo esc_html($a['text']); ?></strong>
                                <?php if($a['type'] == 'system'): ?>
                                <div style="font-size:10px; color:#718096; margin-bottom:5px;"><?php echo esc_html(mb_strimwidth(strip_tags($a['details']), 0, 80, "...")); ?></div>
                                <a href="javascript:smAcknowledgeAlert(<?php echo intval($a['id']); ?>, '<?php echo wp_create_nonce('sm_admin_action'); ?>')" style="font-size:10px; color:var(--sm-primary-color); font-weight:700;">عرض التفاصيل / إغلاق</a>
                                <?php endif; ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <div class="sm-user-dropdown" style="position: relative;">
                <div class="sm-user-profile-nav" onclick="smToggleUserDropdown()" style="display: flex; align-items: center; gap: 12px; background: white; padding: 6px 12px; border-radius: 50px; border: 1px solid var(--sm-border-color); cursor: pointer;">
                    <div style="text-align: right;">
                        <div style="font-size: 0.85em; font-weight: 700; color: var(--sm-dark-color);"><?php echo $greeting . '، ' . $user->display_name; ?></div>
                        <div style="font-size: 0.7em; color: #38a169;">متصل الآن <span class="dashicons dashicons-arrow-down-alt2" style="font-size: 10px; width: 10px; height: 10px;"></span></div>
                    </div>
                    <div style="width: 36px; height: 36px; border-radius: 50%; border: 2px solid #e53e3e; padding: 2px; background: #fff; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <?php echo get_avatar($user->ID, 36, '', '', array('style' => 'border-radius: 50%; width: 100%; height: 100%; object-fit: cover;')); ?>
                    </div>
                </div>
                <div id="sm-user-dropdown-menu" style="display: none; position: absolute; top: 110%; left: 0; background: white; border: 1px solid var(--sm-border-color); border-radius: 8px; width: 260px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; padding: 20px 0;">
                    <div id="sm-profile-view">
                        <div style="padding: 20px 20px; border-bottom: 1px solid #f0f0f0; margin-bottom: 5px;"><div style="font-weight: 800; color: var(--sm-dark-color);"><?php echo $user->display_name; ?></div><div style="font-size: 11px; color: var(--sm-text-gray);"><?php echo $user->user_email; ?></div></div>
                        <?php if (!$is_member): ?><a href="javascript:smEditProfile()" class="sm-dropdown-item"><span class="dashicons dashicons-edit"></span> تعديل البيانات الشخصية</a><?php endif; ?>
                        <?php if ($is_member): ?><a href="javascript:smEditProfile()" class="sm-dropdown-item"><span class="dashicons dashicons-lock"></span> تغيير كلمة المرور</a><?php endif; ?>
                        <?php if ($is_admin): ?><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>" class="sm-dropdown-item"><span class="dashicons dashicons-admin-generic"></span> إعدادات النظام</a><?php endif; ?>
                        <a href="javascript:location.reload()" class="sm-dropdown-item"><span class="dashicons dashicons-update"></span> تحديث الصفحة</a>
                    </div>
                    <div id="sm-profile-edit" style="display: none; padding: 15px;">
                        <div style="font-weight: 800; margin-bottom: 15px; font-size: 13px; border-bottom: 1px solid #eee; padding-bottom: 10px;">تعديل الملف الشخصي</div>
                        <div class="sm-form-group" style="margin-bottom: 20px;"><label class="sm-label" style="font-size: 11px;">الاسم المفضل:</label><input type="text" id="sm_edit_display_name" class="sm-input" style="padding: 8px; font-size: 12px;" value="<?php echo esc_attr($user->display_name); ?>" <?php if ($is_member) echo 'disabled style="background:#f1f5f9; cursor:not-allowed;"'; ?>></div>
                        <div class="sm-form-group" style="margin-bottom: 20px;"><label class="sm-label" style="font-size: 11px;">البريد الإلكتروني:</label><input type="email" id="sm_edit_user_email" class="sm-input" style="padding: 8px; font-size: 12px;" value="<?php echo esc_attr($user->user_email); ?>" <?php if ($is_member) echo 'disabled style="background:#f1f5f9; cursor:not-allowed;"'; ?>></div>
                        <div class="sm-form-group" style="margin-bottom: 15px;"><label class="sm-label" style="font-size: 11px;">كلمة مرور جديدة:</label><input type="password" id="sm_edit_user_pass" class="sm-input" style="padding: 8px; font-size: 12px;" placeholder="********"></div>
                        <div style="display: flex; gap: 8px;"><button onclick="smSaveProfile('<?php echo wp_create_nonce('sm_profile_action'); ?>')" class="sm-btn" style="flex: 1; height: 28px; font-size: 11px; padding: 0;">حفظ</button><button onclick="document.getElementById('sm-profile-edit').style.display='none'; document.getElementById('sm-profile-view').style.display='block';" class="sm-btn sm-btn-outline" style="flex: 1; height: 28px; font-size: 11px; padding: 0;">إلغاء</button></div>
                    </div>
                    <hr style="margin: 5px 0; border: none; border-top: 1px solid #eee;"><a href="<?php echo wp_logout_url(home_url('/sm-login')); ?>" class="sm-dropdown-item" style="color: #e53e3e;"><span class="dashicons dashicons-logout"></span> تسجيل الخروج</a>
                </div>
            </div>
        </div>
    </div>

    <div class="sm-admin-layout" style="display: flex; min-height: 800px;">
        <?php if (!$is_restricted): ?>
        <div class="sm-sidebar" style="width: 280px; flex-shrink: 0; background: <?php echo $appearance['sidebar_bg_color']; ?>; border-left: 1px solid var(--sm-border-color); padding: 15px 0;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li class="sm-sidebar-item <?php echo $active_tab == 'summary' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'summary'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-dashboard"></span> <?php echo $labels['tab_summary']; ?></a></li>
                <?php if ($is_admin || $is_sys_admin || $is_syndicate_admin): ?>
                    <li class="sm-sidebar-item <?php echo in_array($active_tab, ['members', 'update-requests', 'membership-requests', 'professional-requests']) ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'members'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-groups"></span> <?php echo $labels['tab_members']; ?></a>
                        <ul class="sm-sidebar-dropdown" style="display: <?php echo in_array($active_tab, ['members', 'update-requests', 'membership-requests', 'professional-requests']) ? 'block' : 'none'; ?>;">
                            <li><a href="<?php echo add_query_arg('sm_tab', 'members'); ?>" class="<?php echo $active_tab == 'members' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-admin-users"></span> قائمة الأعضاء</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'membership-requests'); ?>" class="<?php echo $active_tab == 'membership-requests' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-plus-alt"></span> طلبات العضوية</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'update-requests'); ?>" class="<?php echo $active_tab == 'update-requests' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-edit"></span> طلبات التحديث</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'professional-requests'); ?>" class="<?php echo $active_tab == 'professional-requests' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-awards"></span> طلبات الترقية والمهنة</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (!$is_restricted && ($is_admin || $is_sys_admin || $is_syndicate_admin)): ?>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'finance' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'finance'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-money-alt"></span> المحاسبة والمالية</a></li>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'practice-licenses' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'practice-licenses'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-id-alt"></span> تراخيص مزاولة المهنة</a></li>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'facility-licenses' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'facility-licenses'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-building"></span> تراخيص المنشآت</a></li>
                <?php endif; ?>
                <?php if ($is_admin || $is_sys_admin || $is_syndicate_admin || $is_syndicate_member || $is_member): ?>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'digital-services' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'digital-services'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-cloud"></span> الخدمات الرقمية</a></li>
                    <li class="sm-sidebar-item <?php echo in_array($active_tab, ['surveys', 'test-questions']) ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'surveys'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-welcome-learn-more"></span> اختبارات الممارسة المهنية</a>
                        <?php if($is_admin || $is_sys_admin || $is_syndicate_admin): ?><ul class="sm-sidebar-dropdown" style="display: <?php echo in_array($active_tab, ['surveys', 'test-questions']) ? 'block' : 'none'; ?>;">
                            <li><a href="<?php echo add_query_arg('sm_tab', 'surveys'); ?>" class="<?php echo $active_tab == 'surveys' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-chart-bar"></span> نتائج ومشاركات</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'test-questions'); ?>" class="<?php echo $active_tab == 'test-questions' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-admin-settings"></span> بنك الأسئلة والإعدادات</a></li>
                        </ul><?php endif; ?>
                    </li>
                <?php endif; ?>
                <?php if ($is_admin || $is_sys_admin || $is_syndicate_admin): ?>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'branches' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg(['sm_tab' => 'branches']); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-networking"></span> فروع النقابة</a></li>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'global-settings' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-admin-generic"></span> إعدادات النظام</a>
                        <ul class="sm-sidebar-dropdown" style="display: <?php echo $active_tab == 'global-settings' ? 'block' : 'none'; ?>;">
                            <li><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>&sub=init" class="<?php echo (!isset($_GET['sub']) || $_GET['sub'] == 'init') ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-admin-tools"></span> تهيئة النظام</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>&sub=academic" class="<?php echo ($_GET['sub'] ?? '') == 'academic' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-welcome-learn-more"></span> مسميات الحقول</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>&sub=finance" class="<?php echo ($_GET['sub'] ?? '') == 'finance' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-money-alt"></span> الرسوم والغرامات</a></li>
                            <li><a href="<?php echo add_query_arg('sm_tab', 'global-settings'); ?>&sub=notifications" class="<?php echo ($_GET['sub'] ?? '') == 'notifications' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-email"></span> التنبيهات والبريد</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($is_admin || $is_sys_admin): ?>
                    <li class="sm-sidebar-item <?php echo $active_tab == 'advanced-settings' ? 'sm-active' : ''; ?>"><a href="<?php echo add_query_arg('sm_tab', 'advanced-settings'); ?>" class="sm-sidebar-link"><span class="dashicons dashicons-admin-tools"></span> الإعدادات المتقدمة</a>
                        <ul class="sm-sidebar-dropdown" style="display: <?php echo $active_tab == 'advanced-settings' ? 'block' : 'none'; ?>;">
                            <li><a href="<?php echo add_query_arg(['sm_tab' => 'advanced-settings', 'sub' => 'staff']); ?>" class="<?php echo (!isset($_GET['sub']) || $_GET['sub'] == 'staff') ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-admin-users"></span> مستخدمي النظام</a></li>
                            <li><a href="<?php echo add_query_arg(['sm_tab' => 'advanced-settings', 'sub' => 'alerts']); ?>" class="<?php echo ($_GET['sub'] ?? '') == 'alerts' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-megaphone"></span> تنبيهات النظام</a></li>
                            <li><a href="<?php echo add_query_arg(['sm_tab' => 'advanced-settings', 'sub' => 'backup']); ?>" class="<?php echo ($_GET['sub'] ?? '') == 'backup' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-database-export"></span> النسخ الاحتياطي</a></li>
                            <li><a href="<?php echo add_query_arg(['sm_tab', 'advanced-settings', 'sub' => 'logs']); ?>" class="<?php echo ($_GET['sub'] ?? '') == 'logs' ? 'sm-sub-active' : ''; ?>"><span class="dashicons dashicons-list-view"></span> سجل النشاطات</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- CONTENT AREA -->
        <div class="sm-main-panel" style="flex: 1; min-width: 0; padding: 30px; background: #fff;">
            <?php
            switch ($active_tab) {
                case 'summary': include SM_PLUGIN_DIR . 'templates/public-dashboard-summary.php'; break;
                case 'members': case 'membership-requests': case 'update-requests': case 'professional-requests':
                    if ($is_admin || current_user_can('sm_manage_members')): ?>
                        <div class="sm-member-management-wrap"><h3 style="margin-top:0; margin-bottom: 15px;">إدارة شؤون الأعضاء والطلبات</h3>
                        <div class="sm-tabs-wrapper" style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #eee;">
                            <a href="<?php echo add_query_arg('sm_tab', 'members'); ?>" class="sm-tab-btn <?php echo $active_tab == 'members' ? 'sm-active' : ''; ?>" style="text-decoration:none;">قائمة الأعضاء</a>
                            <a href="<?php echo add_query_arg('sm_tab', 'membership-requests'); ?>" class="sm-tab-btn <?php echo $active_tab == 'membership-requests' ? 'sm-active' : ''; ?>" style="text-decoration:none;">طلبات العضوية</a>
                            <a href="<?php echo add_query_arg('sm_tab', 'update-requests'); ?>" class="sm-tab-btn <?php echo $active_tab == 'update-requests' ? 'sm-active' : ''; ?>" style="text-decoration:none;">طلبات التحديث</a>
                            <a href="<?php echo add_query_arg('sm_tab', 'professional-requests'); ?>" class="sm-tab-btn <?php echo $active_tab == 'professional-requests' ? 'sm-active' : ''; ?>" style="text-decoration:none;">طلبات الترقية</a>
                        </div>
                        <div class="sm-tab-content-area"><?php
                        if ($active_tab == 'members') include SM_PLUGIN_DIR . 'templates/admin-members.php';
                        elseif ($active_tab == 'membership-requests') include SM_PLUGIN_DIR . 'templates/admin-membership-requests.php';
                        elseif ($active_tab == 'update-requests') include SM_PLUGIN_DIR . 'templates/admin-update-requests.php';
                        elseif ($active_tab == 'professional-requests') include SM_PLUGIN_DIR . 'templates/admin-professional-requests.php';
                        ?></div></div><?php endif; break;
                case 'finance': if ($is_admin || $is_officer) include SM_PLUGIN_DIR . 'templates/admin-finance.php'; break;
                case 'practice-licenses': if ($is_admin || $is_officer) include SM_PLUGIN_DIR . 'templates/admin-practice-licenses.php'; break;
                case 'facility-licenses': if ($is_admin || $is_officer) include SM_PLUGIN_DIR . 'templates/admin-facility-licenses.php'; break;
                case 'messaging': include SM_PLUGIN_DIR . 'templates/messaging-center.php'; break;
                case 'member-profile': case 'my-profile':
                    if ($active_tab === 'my-profile') {
                        $member_by_wp = SM_DB_Members::get_member_by_wp_user_id(get_current_user_id());
                        if ($member_by_wp) $_GET['member_id'] = $member_by_wp->id;
                    }
                    include SM_PLUGIN_DIR . 'templates/admin-member-profile.php'; break;
                case 'digital-services': include SM_PLUGIN_DIR . 'templates/public-services.php'; break;
                case 'global-archive': include SM_PLUGIN_DIR . 'templates/admin-global-archive.php'; break;
                case 'branches': include SM_PLUGIN_DIR . 'templates/admin-branches.php'; break;
                case 'surveys': case 'test-questions': include SM_PLUGIN_DIR . 'templates/admin-surveys.php'; break;
                case 'advanced-settings':
                    if ($is_admin || $is_sys_admin): $sub = $_GET['sub'] ?? 'staff'; ?>
                        <div class="sm-tabs-wrapper" style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #eee; overflow-x: auto; white-space: nowrap; padding-bottom: 10px;">
                            <button class="sm-tab-btn <?php echo ($sub == 'alerts') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('system-alerts-settings', this)">تنبيهات النظام</button>
                            <button class="sm-tab-btn <?php echo ($sub == 'verification') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('verification-settings', this)">إعدادات التحقق</button>
                            <button class="sm-tab-btn <?php echo ($sub == 'staff') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('system-users-settings', this)">مستخدمي النظام</button>
                            <button class="sm-tab-btn <?php echo ($sub == 'backup') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('backup-settings', this)">النسخ الاحتياطي</button>
                            <button class="sm-tab-btn <?php echo ($sub == 'logs') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('activity-logs', this)">سجل النشاطات</button>
                            <button class="sm-tab-btn <?php echo ($sub == 'health') ? 'sm-active' : ''; ?>" onclick="smOpenInternalTab('system-health-tab', this)">صحة النظام</button>
                        </div>
                        <div id="system-health-tab" class="sm-internal-tab" style="display: <?php echo ($sub == 'health') ? 'block' : 'none'; ?>;">
                            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding: 25px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;"><h3 style="margin:0;">تدقيق سلامة البيانات</h3><button onclick="smRunHealthCheck('<?php echo wp_create_nonce('sm_admin_action'); ?>')" class="sm-btn" id="run-health-btn" style="width:auto; padding:0 30px;">بدء الفحص</button></div>
                                <div id="health-check-results" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;"></div>
                            </div>
                        </div>
                        <div id="system-users-settings" class="sm-internal-tab" style="display: <?php echo ($sub == 'staff') ? 'block' : 'none'; ?>;"><?php include SM_PLUGIN_DIR . 'templates/admin-staff.php'; ?></div>
                        <div id="backup-settings" class="sm-internal-tab" style="display: <?php echo ($sub == 'backup') ? 'block' : 'none'; ?>;">
                            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding: 25px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:30px;"><h3 style="margin:0;">مركز النسخ الاحتياطي</h3><button onclick="smDownloadBackupNow('<?php echo wp_create_nonce('sm_admin_action'); ?>')" class="sm-btn" style="width:auto; padding:0 25px;">+ إنشاء نسخة الآن</button></div>
                                <div id="sm-backup-history-body"></div>
                            </div>
                        </div>
                        <div id="activity-logs" class="sm-internal-tab" style="display: <?php echo ($sub == 'logs') ? 'block' : 'none'; ?>;">
                            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:15px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px;"><h4>سجل النشاطات</h4><button onclick="smDeleteAllLogs('<?php echo wp_create_nonce('sm_admin_action'); ?>')" class="sm-btn" style="background:#e53e3e; width:auto;">تفريغ السجل</button></div>
                                <div class="sm-table-container"><table class="sm-table"><thead><tr><th>الوقت</th><th>المستخدم</th><th>الإجراء</th><th>التفاصيل</th><th>إجراءات</th></tr></thead><tbody>
                                <?php foreach (SM_Logger::get_logs(50) as $log): ?>
                                    <tr><td><?php echo $log->created_at; ?></td><td><?php echo $log->display_name; ?></td><td><?php echo $log->action; ?></td><td><?php echo mb_strimwidth($log->details, 0, 80, "..."); ?></td>
                                    <td><button onclick='smViewLogDetails(<?php echo esc_attr(json_encode($log)); ?>)' class="sm-btn sm-btn-outline" style="padding:4px 10px; font-size:10px;">عرض</button></td></tr>
                                <?php endforeach; ?></tbody></table></div>
                            </div>
                        </div>
                    <?php endif; break;
            } ?>
        </div>
    </div>
</div>

<div id="sm-print-customizer-modal" class="sm-modal-overlay"><div class="sm-modal-content" style="max-width: 600px;"><div class="sm-modal-header"><h3>تخصيص الطباعة</h3><button class="sm-modal-close" onclick="document.getElementById('sm-print-customizer-modal').style.display='none'">&times;</button></div><div style="padding: 25px;"><input type="hidden" id="sm-print-module-input"><div id="sm-print-fields-container"></div><button onclick="smExecuteCustomPrint()" class="sm-btn" style="width:100%; height:50px; font-weight:800; margin-top:20px;">استخراج للطباعة</button></div></div></div>
<div id="log-details-modal" class="sm-modal-overlay"><div class="sm-modal-content"><div class="sm-modal-header"><h3>تفاصيل العملية</h3><button class="sm-modal-close" onclick="document.getElementById('log-details-modal').style.display='none'">&times;</button></div><div id="log-details-body" style="padding: 15px;"></div></div></div>
<div id="sm-finance-member-modal" class="sm-modal-overlay"><div class="sm-modal-content" style="max-width: 900px;"><div class="sm-modal-header"><h3>التفاصيل المالية للعضو</h3><button class="sm-modal-close" onclick="document.getElementById('sm-finance-member-modal').style.display='none'">&times;</button></div><div id="sm-finance-modal-body" style="padding: 15px;"></div></div></div>
