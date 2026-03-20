<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_License_Manager {
    public static function ajax_update_license() {
        if (!current_user_can('sm_manage_licenses')) {
            wp_send_json_error('Unauthorized');
        }
        check_ajax_referer('sm_add_member', 'nonce');
        $mid = intval($_POST['member_id']);
        if (!SM_Member_Manager::can_access_member($mid)) {
            wp_send_json_error('Access denied');
        }
        SM_DB::update_member($mid, [
            'license_number' => sanitize_text_field($_POST['license_number']),
            'license_issue_date' => sanitize_text_field($_POST['license_issue_date']),
            'license_expiration_date' => sanitize_text_field($_POST['license_expiration_date'])
        ]);
        SM_DB::add_document([
            'member_id' => $mid,
            'category' => 'licenses',
            'title' => "تصريح مزاولة مهنة رقم " . $_POST['license_number'],
            'file_url' => admin_url('admin-ajax.php?action=sm_print_license&member_id=' . $mid),
            'file_type' => 'application/pdf'
        ]);
        SM_Logger::log('تحديث ترخيص مزاولة', "العضو ID: $mid");
        wp_send_json_success();
    }

    public static function ajax_update_facility() {
        if (!current_user_can('sm_manage_licenses')) {
            wp_send_json_error('Unauthorized');
        }
        check_ajax_referer('sm_add_member', 'nonce');
        $mid = intval($_POST['member_id']);
        if (!SM_Member_Manager::can_access_member($mid)) {
            wp_send_json_error('Access denied');
        }
        SM_DB::update_member($mid, [
            'facility_name' => sanitize_text_field($_POST['facility_name']),
            'facility_number' => sanitize_text_field($_POST['facility_number']),
            'facility_category' => sanitize_text_field($_POST['facility_category']),
            'facility_license_issue_date' => sanitize_text_field($_POST['facility_license_issue_date']),
            'facility_license_expiration_date' => sanitize_text_field($_POST['facility_license_expiration_date']),
            'facility_address' => sanitize_textarea_field($_POST['facility_address'])
        ]);
        SM_DB::add_document([
            'member_id' => $mid,
            'category' => 'licenses',
            'title' => "ترخيص منشأة: " . $_POST['facility_name'],
            'file_url' => admin_url('admin-ajax.php?action=sm_print_facility&member_id=' . $mid),
            'file_type' => 'application/pdf'
        ]);
        SM_Logger::log('تحديث منشأة', "العضو ID: $mid");
        wp_send_json_success();
    }

    public static function ajax_verify_document() {
        global $wpdb;
        $val = trim(sanitize_text_field($_POST['search_value'] ?? ''));
        if (empty($val)) {
            wp_send_json_error('يرجى إدخال قيمة للبحث');
        }

        $results = [];
        $grades = SM_Settings::get_professional_grades();
        $specs = SM_Settings::get_specializations();

        // 1. Intelligent Input Detection: National ID (14 digits) -> Full Profile
        if (preg_match('/^[0-9]{14}$/', $val)) {
            $member = SM_DB::get_member_by_national_id($val);
            if ($member) {
                $results['type'] = 'profile';
                $results['owner'] = [
                    'name' => $member->name,
                    'national_id' => $member->national_id,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'branch' => SM_Settings::get_branch_name($member->governorate),
                    'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                    'specialization' => $specs[$member->specialization] ?? $member->specialization,
                ];
                $results['membership'] = [
                    'number' => $member->membership_number,
                    'status' => $member->membership_status ?: 'Active',
                    'expiry' => $member->membership_expiration_date
                ];
                if ($member->license_number) {
                    $results['practice'] = [
                        'number' => $member->license_number,
                        'issue_date' => $member->license_issue_date,
                        'expiry' => $member->license_expiration_date
                    ];
                }
                if ($member->facility_number) {
                    $results['facility'] = [
                        'name' => $member->facility_name,
                        'number' => $member->facility_number,
                        'category' => $member->facility_category,
                        'address' => $member->facility_address,
                        'expiry' => $member->facility_license_expiration_date
                    ];
                }
                wp_send_json_success($results);
            }
        }

        // 2. Check for Membership Number Match
        $member = SM_DB::get_member_by_membership_number($val);
        if ($member) {
            $results['type'] = 'membership';
            $results['owner'] = [
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'branch' => SM_Settings::get_branch_name($member->governorate),
            ];
            $results['membership'] = [
                'label' => 'بيانات العضوية والتسجيل النقابي',
                'number' => $member->membership_number,
                'status' => $member->membership_status ?: 'Active',
                'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                'specialization' => $specs[$member->specialization] ?? $member->specialization,
                'expiry' => $member->membership_expiration_date
            ];
            wp_send_json_success($results);
        }

        // 3. Check for Practice Permit Number Match
        $member = SM_DB::get_member_by_license_number($val);
        if ($member) {
            $results['type'] = 'practice';
            $results['owner'] = [
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'branch' => SM_Settings::get_branch_name($member->governorate),
            ];
            $results['practice'] = [
                'label' => 'بيانات تصريح مزاولة المهنة المعتمد',
                'number' => $member->license_number,
                'issue_date' => $member->license_issue_date ?: 'غير محدد',
                'expiry' => $member->license_expiration_date,
                'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                'specialization' => $specs[$member->specialization] ?? $member->specialization,
            ];
            wp_send_json_success($results);
        }

        // 4. Check for Facility License Number Match
        $member = SM_DB::get_member_by_facility_number($val);
        if ($member) {
            $results['type'] = 'facility';
            $results['owner'] = [
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'branch' => SM_Settings::get_branch_name($member->governorate),
            ];
            $results['facility'] = [
                'label' => 'رخصة تشغيل المنشأة / الأكاديمية',
                'name' => $member->facility_name,
                'number' => $member->facility_number,
                'category' => $member->facility_category,
                'address' => $member->facility_address ?: 'غير محدد',
                'expiry' => $member->facility_license_expiration_date
            ];
            wp_send_json_success($results);
        }

        // 5. Fallback: Search by Name (Partial) -> Basic Info
        if (strlen($val) >= 3) {
            $member = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}sm_members WHERE name LIKE %s LIMIT 1",
                '%' . $wpdb->esc_like($val) . '%'
            ));
            if ($member) {
                $results['type'] = 'search';
                $results['owner'] = [
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'branch' => SM_Settings::get_branch_name($member->governorate),
                    'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                    'specialization' => $specs[$member->specialization] ?? $member->specialization,
                ];
                $results['membership'] = [
                    'number' => $member->membership_number ?: '---',
                    'status' => $member->membership_status ?: 'Active',
                    'expiry' => $member->membership_expiration_date
                ];
                wp_send_json_success($results);
            }
        }

        wp_send_json_error('عذراً، لم يتم العثور على أية بيانات مطابقة لقيمة البحث المدخلة في السجلات الرسمية.');
    }

    public static function ajax_print_license() {
        if (!current_user_can('sm_print_reports')) {
            wp_die('Unauthorized');
        }
        $mid = intval($_GET['member_id'] ?? 0);
        if (!$mid || !SM_Member_Manager::can_access_member($mid)) {
            wp_die('Access denied');
        }
        include SM_PLUGIN_DIR . 'templates/print-practice-license.php';
        exit;
    }

    public static function ajax_print_facility() {
        if (!current_user_can('sm_print_reports')) {
            wp_die('Unauthorized');
        }
        $mid = intval($_GET['member_id'] ?? 0);
        if (!$mid || !SM_Member_Manager::can_access_member($mid)) {
            wp_die('Access denied');
        }
        include SM_PLUGIN_DIR . 'templates/print-facility-license.php';
        exit;
    }

    public static function ajax_verify_suggest() {
        global $wpdb;
        $q = sanitize_text_field($_GET['query'] ?? '');
        if (strlen($q) < 3) {
            wp_send_json_success([]);
        }
        $s = '%' . $wpdb->esc_like($q) . '%';
        $res = $wpdb->get_results($wpdb->prepare("SELECT name, national_id FROM {$wpdb->prefix}sm_members WHERE name LIKE %s OR national_id LIKE %s LIMIT 5", $s, $s));
        $sug = [];
        foreach ($res as $r) {
            $sug[] = $r->name;
            $sug[] = $r->national_id;
        }
        wp_send_json_success(array_values(array_unique(array_filter($sug))));
    }
}
