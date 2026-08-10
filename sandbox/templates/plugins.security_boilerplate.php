<?php
namespace {{PLUGIN_NAMESPACE}}\Security;

/**
 * Security utilities following 2026 WordPress Zero-Trust standards.
 */
final class Security {
    public static function verify_nonce(string $nonce, string $action): bool {
        return wp_verify_nonce($nonce, $action) === 1 || wp_verify_nonce($nonce, $action) === 2;
    }
    public static function user_can(string $cap, $object_id = null): bool {
        return current_user_can($cap, $object_id);
    }
    public static function sanitize_array(array $input): array {
        $clean = [];
        foreach ($input as $key => $value) {
            $clean_key = sanitize_key($key);
            if (is_string($value)) {
                $clean[$clean_key] = sanitize_text_field($value);
            } elseif (is_int($value) || is_float($value)) {
                $clean[$clean_key] = $value;
            } elseif (is_array($value)) {
                $clean[$clean_key] = self::sanitize_array($value);
            }
        }
        return $clean;
    }
}

final class Sanitizer {
    public static function sanitize_text($input): string {
        return sanitize_text_field($input);
    }

    public static function sanitize_email(string $email): string {
        return sanitize_email($email);
    }

    public static function sanitize_key(string $key): string {
        return sanitize_key($key);
    }

    public static function sanitize_options(array $options): array {
        // Deep sanitization for plugin options
        $clean = [];
        foreach ($options as $key => $value) {
            $clean_key = sanitize_key($key);
            if (is_array($value)) {
                $clean[$clean_key] = array_map([self::class, 'sanitize_text'], $value);
            } else {
                $clean[$clean_key] = sanitize_text_field($value);
            }
        }
        return $clean;
    }
}

final class CapabilityChecker {
    public static function can_manage(): bool {
        return current_user_can('manage_options');
    }

    public static function can_edit(): bool {
        return current_user_can('edit_posts');
    }
}
