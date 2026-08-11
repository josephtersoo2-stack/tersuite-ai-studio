<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Agent_Safety_Validator {
    public function audit_code_compliance( string $target_file, string $generated_code ): array {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $gateway  = $container->make( 'api_gateway' );
        $composer = $container->make( 'prompt_composer' );

        // Hydrate pristine auditing constraints context patterns out of composer lines
        $system_prompt = $composer->build_hardened_system_prompt( 'qa_auditor' );

        $user_payload = "Target File Scope under Review: " . $target_file . "\n\n"
                       . "Raw Code Source Buffer Stream to Inspect:\n" . $generated_code;

        $api_res = $gateway->dispatch_task( 'qa_auditor', $system_prompt, $user_payload );
        if ( ! $api_res['success'] ) {
            return $api_res;
        }

        $raw_text = $api_res['data']['raw_completion'] ?? '{}';
        $decoded = json_decode( $raw_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [
                'success'  => false,
                'message'  => 'Validation agent output malformed structure failure.',
                'code'     => 'malformed_validator_json',
                'debug_id' => $api_res['debug_id'],
                'data'     => [ 'passed' => false, 'issues' => [ 'Invalid agent compliance tracking array format parsing.' ] ]
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Safety audit execution completed.',
            'code'     => 'success',
            'debug_id' => $api_res['debug_id'],
            'data'     => [
                'passed' => (bool) ($decoded['passed'] ?? false),
                'issues' => $decoded['issues'] ?? [],
                'fixes'  => $decoded['fixes'] ?? []
            ]
        ];
    }
}
