<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_License_Manager {
    private static function check_capability($cap) {
        if (!current_user_can($cap)) {
            wp_send_json_error(['message' => 'Unauthorized access.']);
        }
    }

    private static function validate_member_access($member_id) {
        if (!SM_Member_Manager::can_access_member($member_id)) {
            wp_send_json_error(['message' => 'Access denied to this member data.']);
        }
    }

    public static function ajax_update_license() {
        try {
            if (!current_user_can('sm_manage_licenses') && !current_user_can('manage_options')) {
                 wp_send_json_error(['message' => 'Unauthorized access.']);
            }
            check_ajax_referer('sm_add_member', 'nonce');
            $mid = intval($_POST['member_id']);
        self::validate_member_access($mid);

        $res = SM_DB::update_member($mid, [
            'license_number' => sanitize_text_field($_POST['license_number']),
            'license_issue_date' => sanitize_text_field($_POST['license_issue_date']),
            'license_expiration_date' => sanitize_text_field($_POST['license_expiration_date'])
        ]);

        if ($res === false) {
            wp_send_json_error(['message' => 'فشل في تحديث بيانات الترخيص في قاعدة البيانات.']);
        }

        SM_DB::add_document([
            'member_id' => $mid,
            'category' => 'licenses',
            'title' => "تصريح مزاولة مهنة رقم " . $_POST['license_number'],
            'file_url' => admin_url('admin-ajax.php?action=sm_print_license&member_id=' . $mid),
            'file_type' => 'application/pdf'
        ]);
            SM_Logger::log('تحديث ترخيص مزاولة', "العضو ID: $mid");
            wp_send_json_success();
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'Critical Error updating license: ' . $e->getMessage()]);
        }
    }

    public static function ajax_update_facility() {
        try {
            if (!current_user_can('sm_manage_licenses') && !current_user_can('manage_options')) {
                 wp_send_json_error(['message' => 'Unauthorized access.']);
            }
            check_ajax_referer('sm_add_member', 'nonce');
            $mid = intval($_POST['member_id']);
        self::validate_member_access($mid);

        $res = SM_DB::update_member($mid, [
            'facility_name' => sanitize_text_field($_POST['facility_name']),
            'facility_number' => sanitize_text_field($_POST['facility_number']),
            'facility_category' => sanitize_text_field($_POST['facility_category']),
            'facility_license_issue_date' => sanitize_text_field($_POST['facility_license_issue_date']),
            'facility_license_expiration_date' => sanitize_text_field($_POST['facility_license_expiration_date']),
            'facility_address' => sanitize_textarea_field($_POST['facility_address'])
        ]);

        if ($res === false) {
            wp_send_json_error(['message' => 'فشل في تحديث بيانات المنشأة في قاعدة البيانات.']);
        }

        SM_DB::add_document([
            'member_id' => $mid,
            'category' => 'licenses',
            'title' => "ترخيص منشأة: " . $_POST['facility_name'],
            'file_url' => admin_url('admin-ajax.php?action=sm_print_facility&member_id=' . $mid),
            'file_type' => 'application/pdf'
        ]);
            SM_Logger::log('تحديث منشأة', "العضو ID: $mid");
            wp_send_json_success();
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'Critical Error updating facility: ' . $e->getMessage()]);
        }
    }

    public static function ajax_verify_document() {
        try {
            $val = trim(sanitize_text_field($_POST['search_value'] ?? ''));
            if (empty($val)) {
                wp_send_json_error(['message' => 'يرجى إدخال قيمة للبحث']);
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
                    'role_label' => 'عضو عامل',
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
                'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                'specialization' => $specs[$member->specialization] ?? $member->specialization,
                'role_label' => 'عضو عامل',
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
                'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                'specialization' => $specs[$member->specialization] ?? $member->specialization,
                'role_label' => 'ممارس معتمد',
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
                'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
                'specialization' => $specs[$member->specialization] ?? $member->specialization,
                'role_label' => 'صاحب منشأة',
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
            $members = SM_DB::get_members(['search' => $val, 'limit' => 1]);
            $member = !empty($members) ? $members[0] : null;
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

            wp_send_json_error(['message' => 'عذراً، لم يتم العثور على أية بيانات مطابقة لقيمة البحث المدخلة في السجلات الرسمية.']);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'Critical Error during verification: ' . $e->getMessage()]);
        }
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
        try {
            $q = sanitize_text_field($_GET['query'] ?? '');
            if (strlen($q) < 3) {
                wp_send_json_success([]);
            }
            $res = SM_DB::get_member_suggestions($q, 5);
            $sug = [];
            foreach ($res as $r) {
                $sug[] = $r->name;
                $sug[] = $r->national_id;
            }
            wp_send_json_success(array_values(array_unique(array_filter($sug))));
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
