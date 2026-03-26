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

        // Members can access their own data
        if ((in_array('sm_syndicate_member', $roles) || in_array('sm_member', $roles)) && $member->wp_user_id == $user->ID) {
            return true;
        }

        // Officers/Admins are scoped by governorate
        $my_gov = get_user_meta($user->ID, 'sm_governorate', true);
        if (in_array('sm_syndicate_admin', $roles) || in_array('sm_syndicate_member', $roles)) {
            if ($my_gov && $member->governorate !== $my_gov) {
                return false;
            }
            return true;
        }

        return false;
    }
}
