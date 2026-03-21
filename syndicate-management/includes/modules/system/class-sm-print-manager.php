<?php
if (!defined('ABSPATH')) exit;

class SM_Print_Manager {
    private static function check_capability($cap) {
        if (!current_user_can($cap)) {
            wp_send_json_error(['message' => 'Unauthorized access.']);
        }
    }

    public static function ajax_get_custom_print() {
        self::check_capability('sm_print_reports');
        check_ajax_referer('sm_admin_action', 'nonce');

        $module = sanitize_text_field($_POST['module']);
        $fields = isset($_POST['fields']) ? array_map('sanitize_text_field', $_POST['fields']) : [];
        $ids = isset($_POST['ids']) ? array_map('intval', explode(',', $_POST['ids'])) : [];
        $all_records = isset($_POST['all_records']) && $_POST['all_records'] === 'true';

        $current_user = wp_get_current_user();
        $is_admin = current_user_can('sm_full_access') || current_user_can('manage_options');
        $my_gov = get_user_meta($current_user->ID, 'sm_governorate', true);

        $data = [];
        $title = '';

        switch ($module) {
            case 'members':
                $title = 'كشف بيانات الأعضاء';
                $args = $all_records ? ['limit' => -1] : ['include' => $ids];
                if (!$is_admin && $my_gov) $args['governorate'] = $my_gov;
                $results = SM_DB::get_members($args);

                if (!empty($results)) {
                    SM_Finance::prefetch_data(array_map(fn($m) => $m->id, $results));
                }

                foreach ($results as $row) {
                    $item = [];
                    $dues = null;
                    foreach ($fields as $f) {
                        switch ($f) {
                            case 'name': $item['الاسم'] = $row->name; break;
                            case 'national_id': $item['الرقم القومي'] = $row->national_id; break;
                            case 'membership_number': $item['رقم العضوية'] = $row->membership_number; break;
                            case 'professional_grade':
                                $grades = SM_Settings::get_professional_grades();
                                $item['الدرجة'] = $grades[$row->professional_grade] ?? $row->professional_grade;
                                break;
                            case 'specialization':
                                $specs = SM_Settings::get_specializations();
                                $item['التخصص'] = $specs[$row->specialization] ?? $row->specialization;
                                break;
                            case 'governorate': $item['الفرع'] = SM_Settings::get_branch_name($row->governorate); break;
                            case 'outstanding_fees':
                                if ($dues === null) $dues = SM_Finance::calculate_member_dues($row);
                                $item['المستحقات'] = number_format($dues['balance'], 2);
                                break;
                            case 'phone': $item['الهاتف'] = $row->phone; break;
                        }
                    }
                    $data[] = $item;
                }
                break;

            case 'finance':
                $title = 'تقرير العمليات المالية';
                $results = SM_DB::get_payments(['limit' => $all_records ? -1 : 500]);

                foreach ($results as $row) {
                    if (!$all_records && !empty($ids) && !in_array($row->id, $ids)) continue;
                    $member = SM_DB::get_member_by_id($row->member_id);
                    $item = [];
                    foreach ($fields as $f) {
                        switch ($f) {
                            case 'invoice_code': $item['رقم الفاتورة'] = $row->digital_invoice_code; break;
                            case 'member_name': $item['اسم العضو'] = $member ? $member->name : 'N/A'; break;
                            case 'amount': $item['المبلغ'] = number_format($row->amount, 2); break;
                            case 'payment_type': $item['النوع'] = $row->payment_type; break;
                            case 'payment_date': $item['التاريخ'] = $row->payment_date; break;
                            case 'governorate': $item['الفرع'] = $member ? SM_Settings::get_branch_name($member->governorate) : 'N/A'; break;
                        }
                    }
                    $data[] = $item;
                }
                break;

            case 'practice_licenses':
                $title = 'سجل تراخيص مزاولة المهنة';
                $args = ['limit' => -1];
                if (!$is_admin && $my_gov) $args['governorate'] = $my_gov;
                $members = SM_DB::get_members($args);

                foreach ($members as $row) {
                    if (empty($row->license_number)) continue;
                    if (!$all_records && !empty($ids) && !in_array($row->id, $ids)) continue;

                    $item = [];
                    foreach ($fields as $f) {
                        switch ($f) {
                            case 'license_number': $item['رقم الترخيص'] = $row->license_number; break;
                            case 'member_name': $item['اسم العضو'] = $row->name; break;
                            case 'issue_date': $item['تاريخ الإصدار'] = $row->license_issue_date; break;
                            case 'expiry_date': $item['تاريخ الانتهاء'] = $row->license_expiration_date; break;
                            case 'governorate': $item['الفرع'] = SM_Settings::get_branch_name($row->governorate); break;
                            case 'specialization':
                                $specs = SM_Settings::get_specializations();
                                $item['التخصص'] = $specs[$row->specialization] ?? $row->specialization;
                                break;
                        }
                    }
                    $data[] = $item;
                }
                break;

            case 'facility_licenses':
                $title = 'سجل تراخيص المنشآت';
                $args = ['limit' => -1];
                if (!$is_admin && $my_gov) $args['governorate'] = $my_gov;
                $members = SM_DB::get_members($args);

                foreach ($members as $row) {
                    if (empty($row->facility_number)) continue;
                    if (!$all_records && !empty($ids) && !in_array($row->id, $ids)) continue;

                    $item = [];
                    foreach ($fields as $f) {
                        switch ($f) {
                            case 'facility_number': $item['رقم الترخيص'] = $row->facility_number; break;
                            case 'facility_name': $item['اسم المنشأة'] = $row->facility_name; break;
                            case 'owner_name': $item['المالك'] = $row->name; break;
                            case 'facility_category': $item['الفئة'] = $row->facility_category; break;
                            case 'expiry_date': $item['تاريخ الانتهاء'] = $row->facility_license_expiration_date; break;
                            case 'governorate': $item['الفرع'] = SM_Settings::get_branch_name($row->governorate); break;
                        }
                    }
                    $data[] = $item;
                }
                break;
        }

        ob_start();
        include SM_PLUGIN_DIR . 'templates/print-custom-list.php';
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }
}
