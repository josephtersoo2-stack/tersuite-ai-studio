<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Rate_Limit_Gate {
    public static function check_throttle( string $endpoint_key, int $max_requests = 60, int $window_seconds = 60 ): bool {
        $user_id = get_current_user_id();
        $transient_key = 'ts_rate_' . $user_id . '_' . md5( $endpoint_key );
        $current_count = (int) get_transient( $transient_key );

        if ( $current_count >= $max_requests ) {
            return true;
        }

        if ( false === get_transient( $transient_key ) ) {
            set_transient( $transient_key, 1, $window_seconds );
        } else {
            set_transient( $transient_key, $current_count + 1, $window_seconds );
        }

        return false;
    }
}
