<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Agent_Spec_Architect {
    public function generate_specification( array $chat_history_rows ): array {
        $container = TERSOSTUDIO_Service_Container::get_instance();
        $gateway  = $container->make( 'api_gateway' );
        $rag      = $container->make( 'rag_orchestrator' );
        $composer = $container->make( 'prompt_composer' );

        // Delegate context aggregation to the specialized Prompt Composer engine service
        $system_prompt = $composer->build_hardened_system_prompt( 'architect' );

        $formatted_timeline_history = '';
        foreach ( $chat_history_rows as $row ) {
            $actor = ( 'user' === $row['sender_role'] ) ? 'Developer (User)' : 'Architect Agent';
            $formatted_timeline_history .= "\n[{$actor}]: " . $row['message_body'];
        }

        $rag_context = '';
        if ( $rag && ! empty( $formatted_timeline_history ) ) {
            $rag_res = $rag->retrieve_context( $formatted_timeline_history, 'blueprint' );
            $rag_context = $rag_res['data']['context_string'] ?? '';
        }

        $user_payload = "Chronological Conversation Logs Tracking (Short-Term Memory Matrix):\n" . $formatted_timeline_history . "\n\n"
                       . "Relevant Framework References Lookups:\n" . $rag_context;

        $api_res = $gateway->dispatch_task( 'architect_reasoning', $system_prompt, $user_payload );
        if ( ! $api_res['success'] ) {
            return $api_res;
        }

        $raw_text = $api_res['data']['raw_completion'] ?? '{}';
        $decoded = json_decode( $raw_text, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return [
                'success'  => false,
                'message'  => 'Architect structural contract failed validation container parsing: ' . json_last_error_msg(),
                'code'     => 'malformed_agent_json',
                'debug_id' => $api_res['debug_id'],
                'data'     => []
            ];
        }

        return [
            'success'  => true,
            'message'  => 'Architect analysis pipeline step complete.',
            'code'     => 'success',
            'debug_id' => $api_res['debug_id'],
            'data'     => [ 'response' => $decoded ]
        ];
    }
}
