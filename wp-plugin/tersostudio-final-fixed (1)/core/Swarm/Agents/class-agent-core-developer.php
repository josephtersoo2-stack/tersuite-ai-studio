<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Agent_Core_Developer {
    public function build_file_code( string $target_file, array $master_blueprint ): array {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $gateway   = $container->make( 'api_gateway' );
        $rag       = $container->make( 'rag_orchestrator' );
        $learning  = $container->make( 'learning_engine' );
        $composer  = $container->make( 'prompt_composer' );

        // Extract and bundle dynamic structure environment values for system prompt validation mapping
        $runtime_context = [ 'file_tree' => $master_blueprint['files'] ?? [] ];
        $system_prompt   = $composer->build_hardened_system_prompt( 'developer_core', $runtime_context );

        $rag_context = '';
        if ( $rag ) {
            $rag_res = $rag->retrieve_context( $target_file, 'wp_core' );
            $rag_context = $rag_res['data']['context_string'] ?? '';
        }

        $historical_remedies_context = '';
        if ( $learning ) {
            $target_error_fingerprint = md5( $target_file );
            $remedies = $learning->query_learned_memory( $target_error_fingerprint );
            if ( ! empty( $remedies ) ) {
                $historical_remedies_context = "\n\n\u26a0\ufe0f CRITICAL HISTORICAL COMPLIANCE MEMORY ENTRIES:\n"
                                             . "The validation tier previously blocked compilation on this file configuration due to architectural errors.\n"
                                             . "Review these broken code samples and their matching resolutions to ensure you do not emit these mistakes again:\n"
                                             . wp_json_encode( $remedies );
            }
        }

        $user_payload = "Target File to Build: " . $target_file . "\n"
                       . "Master Build Blueprint Plan Constraints:\n" . wp_json_encode( $master_blueprint ) . "\n\n"
                       . "WordPress Coding Reference Context Lookups:\n" . $rag_context
                       . $historical_remedies_context;

        $api_res = $gateway->dispatch_task( 'developer_core', $system_prompt, $user_payload );
        if ( ! $api_res['success'] ) {
            return $api_res;
        }

        $raw_text = $api_res['data']['raw_completion'] ?? '{}';
        $decoded = json_decode( $raw_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE || empty( $decoded['content'] ) ) {
            return [
                'success'  => false,
                'message'  => 'Developer script generation broke validation contract specifications.',
                'code'     => 'malformed_developer_json',
                'debug_id' => $api_res['debug_id'],
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Source code generation completed.',
            'code'     => 'success',
            'debug_id' => $api_res['debug_id'],
            'data'     => [ 'content' => $decoded['content'], 'summary' => $decoded['summary'] ?? '' ]
        ];
    }
}
