<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Central_Exception_Handler {
    private static ?TERSOSTUDIO_Central_Exception_Handler $instance = null;

    private function __construct() {
        set_exception_handler( [ $this, 'handle_unhandled_exception' ] );
    }

    public static function get_instance(): TERSOSTUDIO_Central_Exception_Handler {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Intercepts unhandled top-level infrastructure exceptions and translates them into uniform JSON responses.
     */
    public function handle_unhandled_exception( $exception ): void {
        $message  = $exception->getMessage();
        $error_code = 'internal_kernel_exception';
        
        $repo = TERSOSTUDIO_Service_Container::get_instance()->make( 'project_state_repo' );
        if ( $repo ) {
            $repo->log_error( 'fatal', 'Unhandled Kernel Exception: ' . $message, [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ] );
        }

        // Check if the execution boundary dropped inside a REST API request thread flow
        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
            $response = TERSOSTUDIO_REST_Response_Factory::error(
                'A critical unhandled system execution exception was intercepted: ' . $message,
                $error_code,
                500
            );
            wp_send_json( $response->get_data(), 500 );
            exit;
        }

        // Fallback processing closure for standard administrative context bounds
        if ( is_admin() ) {
            wp_die( sprintf( '<div class="notice notice-error"><p><strong>TERSOSTUDIO KERNEL CRASH:</strong> %s</p></div>', esc_html( $message ) ), 'Kernel Execution Exception Reference', [ 'response' => 500 ] );
        }
    }
}
