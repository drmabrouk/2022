<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Access Control Centralization
 * Handles capability checks, nonce verification, and data scoping.
 */
class SM_Access {

    /**
     * Check if current user has a specific capability.
     * Ends execution with JSON error if unauthorized.
     */
    public static function check_capability($cap) {
        if (!current_user_can($cap) && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized access: Missing capability ' . $cap]);
        }
    }

    /**
     * Check multiple capabilities (OR logic)
     */
    public static function check_any_capability($caps) {
        $authorized = current_user_can('manage_options');
        if (!$authorized) {
            foreach ((array)$caps as $cap) {
                if (current_user_can($cap)) {
                    $authorized = true;
                    break;
                }
            }
        }

        if (!$authorized) {
            wp_send_json_error(['message' => 'Unauthorized access.']);
        }
    }

    /**
     * Centralized Nonce Verification
     */
    public static function verify_nonce($action, $key = 'nonce') {
        if (isset($_POST[$key])) {
            check_ajax_referer($action, $key);
        } elseif (isset($_POST['_wpnonce'])) {
            check_ajax_referer($action, '_wpnonce');
        } elseif (isset($_REQUEST[$key])) {
            check_ajax_referer($action, $key);
        } else {
            wp_send_json_error(['message' => 'Security check failed: Nonce missing.']);
        }
    }

    /**
     * Validate if current user can access a specific member's data.
     */
    public static function validate_member_access($member_id) {
        if (!self::can_access_member($member_id)) {
            wp_send_json_error(['message' => 'Access denied to this member data.']);
        }
    }

    /**
     * Logic for member access scoping
     * Strictly enforces the 4-tier role hierarchy constraints.
     */
    public static function can_access_member($member_id) {
        if (current_user_can('sm_full_access') || current_user_can('manage_options')) {
            return true;
        }

        $member = SM_DB::get_member_by_id($member_id);
        if (!$member) {
            return false;
        }

        $user = wp_get_current_user();
        $roles = (array)$user->roles;

        // 1. Syndicate Members (Regular Members) - ONLY their own data
        if (in_array('sm_syndicate_member', $roles) || in_array('sm_member', $roles)) {
            return (int)$member->wp_user_id === (int)$user->ID;
        }

        // 2. Branch Officers (sm_syndicate_admin) - Scoped by governorate
        if (in_array('sm_syndicate_admin', $roles)) {
            $my_gov = get_user_meta($user->ID, 'sm_governorate', true);
            if ($my_gov && $member->governorate !== $my_gov) {
                return false;
            }
            return true;
        }

        // Note: sm_system_admin and sm_general_officer are handled by sm_full_access capability check above.

        return false;
    }

    /**
     * Check if a specific module is enabled for the current user's role.
     */
    public static function is_module_enabled($module) {
        if (current_user_can('manage_options')) return true;

        $user = wp_get_current_user();
        $roles = (array)$user->roles;
        $primary_role = !empty($roles) ? reset($roles) : '';

        $permissions = get_option('sm_role_permissions', self::get_default_permissions());

        if (isset($permissions[$primary_role][$module])) {
            return (bool)$permissions[$primary_role][$module];
        }

        // Fallback to capability check if not explicitly defined in permissions
        $cap_map = [
            'members' => 'sm_manage_members',
            'finance' => 'sm_manage_finance',
            'licenses' => 'sm_manage_licenses',
            'system' => 'sm_manage_system'
        ];

        return isset($cap_map[$module]) ? current_user_can($cap_map[$module]) : true;
    }

    public static function get_default_permissions() {
        return [
            'sm_system_admin' => [
                'members' => true, 'finance' => true, 'licenses' => true, 'services' => true, 'surveys' => true, 'branches' => true, 'system' => true
            ],
            'sm_general_officer' => [
                'members' => true, 'finance' => true, 'licenses' => true, 'services' => true, 'surveys' => true, 'branches' => true, 'system' => false
            ],
            'sm_syndicate_admin' => [
                'members' => true, 'finance' => true, 'licenses' => true, 'services' => true, 'surveys' => true, 'branches' => false, 'system' => false
            ],
            'sm_syndicate_member' => [
                'members' => false, 'finance' => false, 'licenses' => false, 'services' => true, 'surveys' => true, 'branches' => false, 'system' => false
            ]
        ];
    }

    public static function ajax_save_role_permissions() {
        try {
            self::check_capability('manage_options');
            self::verify_nonce('sm_admin_action');

            $permissions = $_POST['permissions'] ?? [];
            if (empty($permissions)) {
                wp_send_json_error(['message' => 'No data received']);
            }

            // Sanitize boolean values
            foreach ($permissions as $role => $mods) {
                foreach ($mods as $mod => $val) {
                    $permissions[$role][$mod] = (string)$val === 'true' || $val === '1';
                }
            }

            update_option('sm_role_permissions', $permissions);
            SM_Logger::log('تحديث صلاحيات الأدوار', "تم تعديل مصفوفة الوصول للمودولات");
            wp_send_json_success('Permissions saved');
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
}
