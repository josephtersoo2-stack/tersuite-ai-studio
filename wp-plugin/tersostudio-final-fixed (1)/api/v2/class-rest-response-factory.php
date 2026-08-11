<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_REST_Response_Factory {
    public static function success( string $message, array $data = [], int $status = 200, string $code = 'success' ): WP_REST_Response {
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        return new WP_REST_Response( [
            'success'  => true,
            'message'  => $message,
            'code'     => $code,
            'debug_id' => $debug_id,
            'data'     => $data
        ], $status );
    }

    public static function error( string $message, string $code = 'error', int $status = 400, array $data = [] ): WP_REST_Response {
        $debug_id = 'ts_api_' . wp_generate_password( 8, false, false );
        return new WP_REST_Response( [
            'success'  => false,
            'message'  => $message,
            'code'     => $code,
            'debug_id' => $debug_id,
            'data'     => $data
        ], $status );
    }
}
