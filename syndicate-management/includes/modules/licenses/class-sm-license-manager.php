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
            $type = sanitize_text_field($_POST['search_type'] ?? 'auto');

            if (empty($val)) {
                wp_send_json_error(['message' => 'يرجى إدخال قيمة للبحث']);
            }

            $blocks = [];
            $grades = SM_Settings::get_professional_grades();
            $specs = SM_Settings::get_specializations();

            // Unified Detection Logic
            if ($type === 'auto') {
                if (preg_match('/^[0-9]{14}$/', $val)) $type = 'national_id';
                elseif (strpos($val, 'REG-') === 0 || strpos($val, 'SR-') === 0 || (strlen($val) > 8 && is_numeric($val))) $type = 'tracking';
                // Add more auto-detect rules if needed
            }

            // 1. Search by National ID -> Profile + All Records
            if ($type === 'national_id') {
                $member = SM_DB::get_member_by_national_id($val);
                if ($member) {
                    $blocks[] = [
                        'type' => 'profile',
                        'owner' => self::format_owner_data($member, $grades, $specs)
                    ];
                    if ($member->membership_number) {
                        $blocks[] = [
                            'type' => 'membership',
                            'membership' => [
                                'number' => $member->membership_number,
                                'status' => $member->membership_status ?: 'Active',
                                'expiry' => $member->membership_expiration_date
                            ]
                        ];
                    }
                    if ($member->license_number) {
                        $blocks[] = [
                            'type' => 'practice',
                            'practice' => [
                                'number' => $member->license_number,
                                'issue_date' => $member->license_issue_date,
                                'expiry' => $member->license_expiration_date
                            ]
                        ];
                    }
                    if ($member->facility_number) {
                        $blocks[] = [
                            'type' => 'facility',
                            'facility' => [
                                'name' => $member->facility_name,
                                'number' => $member->facility_number,
                                'category' => $member->facility_category,
                                'address' => $member->facility_address,
                                'expiry' => $member->facility_license_expiration_date
                            ]
                        ];
                    }

                    // Also search for related service/membership requests
                    $reqs = self::find_tracking_by_national_id($val);
                    foreach ($reqs as $r) {
                        $blocks[] = ['type' => 'tracking', 'tracking' => $r];
                    }

                    wp_send_json_success($blocks);
                }

                // If no member, check for membership requests only
                $reqs = self::find_tracking_by_national_id($val);
                if (!empty($reqs)) {
                    foreach ($reqs as $r) {
                        $blocks[] = ['type' => 'tracking', 'tracking' => $r];
                    }
                    wp_send_json_success($blocks);
                }
            }

            // 2. Search by Membership Number
            if ($type === 'membership') {
                $member = SM_DB::get_member_by_membership_number($val);
                if ($member) {
                    $blocks[] = ['type' => 'profile', 'owner' => self::format_owner_data($member, $grades, $specs)];
                    $blocks[] = ['type' => 'membership', 'membership' => [
                        'number' => $member->membership_number,
                        'status' => $member->membership_status ?: 'Active',
                        'expiry' => $member->membership_expiration_date
                    ]];
                    wp_send_json_success($blocks);
                }
            }

            // 3. Search by Practice License
            if ($type === 'practice') {
                $member = SM_DB::get_member_by_license_number($val);
                if ($member) {
                    $blocks[] = ['type' => 'profile', 'owner' => self::format_owner_data($member, $grades, $specs)];
                    $blocks[] = ['type' => 'practice', 'practice' => [
                        'number' => $member->license_number,
                        'issue_date' => $member->license_issue_date ?: '---',
                        'expiry' => $member->license_expiration_date
                    ]];
                    wp_send_json_success($blocks);
                }
            }

            // 4. Search by Facility License
            if ($type === 'facility') {
                $member = SM_DB::get_member_by_facility_number($val);
                if ($member) {
                    $blocks[] = ['type' => 'profile', 'owner' => self::format_owner_data($member, $grades, $specs)];
                    $blocks[] = ['type' => 'facility', 'facility' => [
                        'name' => $member->facility_name,
                        'number' => $member->facility_number,
                        'category' => $member->facility_category,
                        'address' => $member->facility_address ?: '---',
                        'expiry' => $member->facility_license_expiration_date
                    ]];
                    wp_send_json_success($blocks);
                }
            }

            // 5. Search by Tracking Code
            if ($type === 'tracking') {
                $track = self::find_tracking_by_code($val);
                if ($track) {
                    $blocks[] = ['type' => 'tracking', 'tracking' => $track];
                    wp_send_json_success($blocks);
                }
            }

            // Fallback: Search by Name (Partial)
            if ($type === 'auto' && strlen($val) >= 3) {
                $members = SM_DB::get_members(['search' => $val, 'limit' => 1]);
                $member = !empty($members) ? $members[0] : null;
                if ($member) {
                    $blocks[] = ['type' => 'profile', 'owner' => self::format_owner_data($member, $grades, $specs)];
                    $blocks[] = ['type' => 'membership', 'membership' => [
                        'number' => $member->membership_number ?: '---',
                        'status' => $member->membership_status ?: 'Active',
                        'expiry' => $member->membership_expiration_date
                    ]];
                    wp_send_json_success($blocks);
                }
            }

            wp_send_json_error(['message' => 'عذراً، لم يتم العثور على أية بيانات مطابقة لقيمة البحث المدخلة في السجلات الرسمية.']);
        } catch (Throwable $e) {
            wp_send_json_error(['message' => 'خطأ تقني: ' . $e->getMessage()]);
        }
    }

    private static function format_owner_data($member, $grades, $specs) {
        return [
            'name' => $member->name,
            'national_id' => $member->national_id,
            'email' => $member->email,
            'phone' => $member->phone,
            'branch' => SM_Settings::get_branch_name($member->governorate),
            'grade' => $grades[$member->professional_grade] ?? $member->professional_grade,
            'specialization' => $specs[$member->specialization] ?? $member->specialization,
            'role_label' => 'عضو نقابة معتمد',
        ];
    }

    private static function find_tracking_by_national_id($nid) {
        $found = [];

        // Membership Requests
        $req = SM_DB::get_membership_request_by_national_id($nid);
        if ($req) {
            $found[] = self::map_membership_request($req);
        }

        // Service Requests
        $member = SM_DB::get_member_by_national_id($nid);
        if ($member) {
            $s_reqs = SM_DB_Services::get_service_requests(['member_id' => $member->id]);
            foreach ($s_reqs as $sr) {
                $found[] = self::map_service_request($sr);
            }
        }

        return $found;
    }

    private static function find_tracking_by_code($code) {
        // Membership Request check
        if (strpos($code, 'REG-') === 0 || is_numeric($code)) {
            $id = str_replace('REG-', '', $code);
            if (strlen($id) > 8 && is_numeric($id)) $id = substr($id, 8); // Remove date prefix if present
            $req = SM_DB::get_membership_request((int)$id);
            if ($req) return self::map_membership_request($req);
        }

        // Service Request check
        $id = 0;
        if (strpos($code, 'SR-') === 0) $id = str_replace('SR-', '', $code);
        elseif (strlen($code) > 8 && is_numeric($code)) $id = substr($code, 8);
        elseif (is_numeric($code)) $id = $code;

        if ($id) {
            $req = SM_DB_Services::get_service_request_by_id((int)$id);
            if ($req) return self::map_service_request($req);
        }

        return null;
    }

    private static function map_membership_request($req) {
        $map = [
            'Pending Payment' => 'بانتظار السداد',
            'Pending Payment Verification' => 'قيد مراجعة الدفع',
            'Awaiting Physical Documents' => 'بانتظار الملف الورقي',
            'Under Review' => 'قيد المراجعة والتدقيق',
            'approved' => 'تم القبول والتفعيل',
            'rejected' => 'مرفوض'
        ];
        return [
            'id' => 'REG-' . $req->id,
            'service' => 'طلب قيد عضوية جديدة',
            'status' => $map[$req->status] ?? $req->status,
            'notes' => $req->notes ?? ($req->rejection_reason ?? ''),
            'date' => date('Y-m-d', strtotime($req->created_at)),
            'member' => $req->name,
            'branch' => SM_Settings::get_branch_name($req->governorate)
        ];
    }

    private static function map_service_request($req) {
        $statuses = [
            'pending' => 'قيد الانتظار',
            'processing' => 'جاري التنفيذ',
            'approved' => 'مكتمل / معتمد',
            'rejected' => 'مرفوض'
        ];
        return [
            'id' => date('Ymd', strtotime($req->created_at)) . $req->id,
            'service' => $req->service_name ?? 'خدمة رقمية',
            'status' => $statuses[$req->status] ?? $req->status,
            'notes' => $req->admin_notes ?? '',
            'date' => date('Y-m-d', strtotime($req->created_at)),
            'member' => $req->member_name ?: '---',
            'branch' => SM_Settings::get_branch_name($req->governorate ?? '')
        ];
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
