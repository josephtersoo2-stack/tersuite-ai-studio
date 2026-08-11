<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_AI_Gateway {
    private static ?TERSOSTUDIO_AI_Gateway $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_AI_Gateway {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatch_task( string $tier_slug, string $system_prompt, string $user_prompt ): array {
        $debug_id = 'ts_gw_' . wp_generate_password( 8, false, false );
        $tier_slug = sanitize_key( $tier_slug );

        $model_id = $this->resolve_model_id( $tier_slug );
        if ( '' === $model_id ) {
            return [
                'success'  => false,
                'message'  => 'Model configuration is empty for the selected agent tier. Please set it explicitly in the Agents Swarm panel.',
                'code'     => 'missing_model_configuration',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $provider = $this->resolve_provider_from_model( $model_id );
        $api_key  = $this->retrieve_api_key( $provider );

        if ( '' === $api_key ) {
            return [
                'success'  => false,
                'message'  => sprintf( 'API Communication blocked: %s credentials missing. Configure them in Settings.', ucfirst( $provider ) ),
                'code'     => 'missing_credentials',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }

        $cooldown = $this->get_provider_cooldown( $provider, $model_id );
        if ( ! empty( $cooldown ) ) {
            return [
                'success'  => false,
                'message'  => $cooldown['message'],
                'code'     => 'service_busy',
                'debug_id' => $debug_id,
                'data'     => [ 'retry_after_seconds' => intval( $cooldown['retry_after_seconds'] ?? 60 ) ]
            ];
        }

        try {
            if ( 'openai' === $provider ) {
                return $this->call_openai( $model_id, $api_key, $system_prompt, $user_prompt, $debug_id );
            }

            if ( 'anthropic' === $provider ) {
                return $this->call_anthropic( $model_id, $api_key, $system_prompt, $user_prompt, $debug_id );
            }

            return $this->call_gemini( $model_id, $api_key, $system_prompt, $user_prompt, $debug_id );
        } catch ( \Throwable $e ) {
            return [
                'success'  => false,
                'message'  => 'Gateway Runtime Exception: ' . $e->getMessage(),
                'code'     => 'gateway_exception',
                'debug_id' => $debug_id,
                'data'     => []
            ];
        }
    }

    private function resolve_model_id( string $tier_slug ): string {
        $map = [
            'architect_simple'     => 'tersostudio_agent_architect_simple',
            'architect_reasoning'  => 'tersostudio_agent_architect_reasoning',
            'backend_engineer'     => 'tersostudio_agent_backend_engineer',
            'developer_core'       => 'tersostudio_agent_backend_engineer',
            'frontend_engineer'    => 'tersostudio_agent_frontend_engineer',
            'database_architect'   => 'tersostudio_agent_database_architect',
            'security_auditor'     => 'tersostudio_agent_security_auditor',
            'patch_engine'         => 'tersostudio_agent_patch_engine',
            'memory_orchestrator'  => 'tersostudio_agent_memory_orchestrator',
            'qa_validation'        => 'tersostudio_agent_qa_validation',
            'qa_auditor'           => 'tersostudio_agent_qa_validation',
            'devops_monitor'       => 'tersostudio_agent_devops_monitor',
            'learning_specialist'  => 'tersostudio_agent_learning_specialist'
        ];

        $option_name = $map[ $tier_slug ] ?? '';
        if ( '' === $option_name ) {
            return '';
        }

        return trim( (string) get_option( $option_name, '' ) );
    }

    private function resolve_provider_from_model( string $model_id ): string {
        $model_id = strtolower( trim( $model_id ) );

        if ( str_starts_with( $model_id, 'gpt' ) || str_starts_with( $model_id, 'o1' ) ) {
            return 'openai';
        }

        if ( str_starts_with( $model_id, 'claude' ) ) {
            return 'anthropic';
        }

        return 'gemini';
    }

    private function retrieve_api_key( string $provider ): string {
        switch ( $provider ) {
            case 'openai':
                return trim( (string) get_option( 'tersostudio_openai_key', '' ) );
            case 'anthropic':
                return trim( (string) get_option( 'tersostudio_claude_key', '' ) );
            case 'gemini':
            default:
                return trim( (string) get_option( 'tersostudio_gemini_key', '' ) );
        }
    }

    private function cooldown_transient_key( string $provider, string $model_id ): string {
        return 'tersostudio_ai_cd_' . md5( sanitize_key( $provider ) . '|' . sanitize_text_field( $model_id ) );
    }

    private function get_provider_cooldown( string $provider, string $model_id ): array {
        $cooldown = get_transient( $this->cooldown_transient_key( $provider, $model_id ) );
        return is_array( $cooldown ) ? $cooldown : [];
    }

    private function set_provider_cooldown( string $provider, string $model_id, int $seconds, string $message ): void {
        set_transient(
            $this->cooldown_transient_key( $provider, $model_id ),
            [
                'message' => $message,
                'retry_after_seconds' => $seconds,
            ],
            max( 30, $seconds )
        );
    }

    private function call_gemini( string $model_id, string $api_key, string $system_prompt, string $user_prompt, string $debug_id ): array {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode( $model_id ) . ':generateContent?key=' . rawurlencode( $api_key );

        $request_structure = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        [ 'text' => "System Operation Matrix Instructions:\n" . $system_prompt . "\n\nInput Work Requirements Payload:\n" . $user_prompt ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'responseMimeType' => 'application/json'
            ]
        ];

        return $this->perform_remote_request( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $request_structure ),
            'timeout' => 45,
        ], $debug_id, $model_id, 'gemini', static function( array $decoded ) {
            $raw_completion = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
            return trim( (string) $raw_completion );
        } );
    }

    private function call_openai( string $model_id, string $api_key, string $system_prompt, string $user_prompt, string $debug_id ): array {
        $url = 'https://api.openai.com/v1/chat/completions';

        $body = [
            'model' => $model_id,
            'messages' => [
                [ 'role' => 'system', 'content' => $system_prompt ],
                [ 'role' => 'user', 'content' => $user_prompt ],
            ],
            'temperature' => 0.2,
            'response_format' => [ 'type' => 'json_object' ],
        ];

        return $this->perform_remote_request( $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 45,
        ], $debug_id, $model_id, 'openai', static function( array $decoded ) {
            $raw_completion = $decoded['choices'][0]['message']['content'] ?? '';
            return trim( (string) $raw_completion );
        } );
    }

    private function call_anthropic( string $model_id, string $api_key, string $system_prompt, string $user_prompt, string $debug_id ): array {
        $url = 'https://api.anthropic.com/v1/messages';

        $body = [
            'model' => $model_id,
            'system' => $system_prompt,
            'messages' => [
                [ 'role' => 'user', 'content' => $user_prompt ],
            ],
            'max_tokens' => 4096,
            'temperature' => 0.2,
        ];

        return $this->perform_remote_request( $url, [
            'headers' => [
                'x-api-key'         => $api_key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 45,
        ], $debug_id, $model_id, 'anthropic', static function( array $decoded ) {
            $raw_completion = $decoded['content'][0]['text'] ?? '';
            return trim( (string) $raw_completion );
        } );
    }

    private function perform_remote_request( string $url, array $args, string $debug_id, string $model_id, string $provider, callable $extractor ): array {
        $max_retries = 3;
        $base_delay_seconds = 2;

        for ( $attempt = 1; $attempt <= $max_retries; $attempt++ ) {
            $response = wp_remote_post( $url, $args );

            if ( is_wp_error( $response ) ) {
                if ( $attempt === $max_retries ) {
                    return [
                        'success'  => false,
                        'message'  => 'Network request failed: ' . $response->get_error_message(),
                        'code'     => 'network_fault',
                        'debug_id' => $debug_id,
                        'data'     => []
                    ];
                }
                sleep( $base_delay_seconds * $attempt );
                continue;
            }

            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body  = wp_remote_retrieve_body( $response );
            $decoded        = json_decode( $response_body, true );

            if ( 503 === $response_code || 429 === $response_code ) {
                $retry_seconds = 60 + ( $attempt * 15 );
                $message = 'Remote service capacity limits exhausted (HTTP Code ' . $response_code . '). Try again shortly.';
                $this->set_provider_cooldown( $provider, $model_id, $retry_seconds, $message );

                if ( $attempt === $max_retries ) {
                    return [
                        'success'  => false,
                        'message'  => $message,
                        'code'     => 'service_busy',
                        'debug_id' => $debug_id,
                        'data'     => [ 'retry_after_seconds' => $retry_seconds ]
                    ];
                }
                sleep( $base_delay_seconds * $attempt );
                continue;
            }

            if ( is_array( $decoded ) && ! empty( $decoded['error']['message'] ) ) {
                return [
                    'success'  => false,
                    'message'  => 'API Error Code (' . ( $decoded['error']['code'] ?? 'ERR' ) . '): ' . $decoded['error']['message'],
                    'code'     => 'api_error_response',
                    'debug_id' => $debug_id,
                    'data'     => []
                ];
            }

            if ( ! is_array( $decoded ) ) {
                return [
                    'success'  => false,
                    'message'  => 'Remote endpoint returned an invalid JSON payload.',
                    'code'     => 'invalid_remote_json',
                    'debug_id' => $debug_id,
                    'data'     => []
                ];
            }

            $raw_completion = trim( (string) $extractor( $decoded ) );
            if ( '' === $raw_completion ) {
                return [
                    'success'  => false,
                    'message'  => 'Empty text matrix response from generation endpoint.',
                    'code'     => 'empty_completion',
                    'debug_id' => $debug_id,
                    'data'     => []
                ];
            }

            if ( str_contains( $raw_completion, '```json' ) ) {
                $raw_completion = str_replace( [ '```json', '```' ], '', $raw_completion );
                $raw_completion = trim( $raw_completion );
            }

            return [
                'success'  => true,
                'message'  => 'Outbound request completed safely.',
                'code'     => 'success',
                'debug_id' => $debug_id,
                'data'     => [ 'raw_completion' => $raw_completion ]
            ];
        }

        return [
            'success'  => false,
            'message'  => 'Network fallback loop timeout.',
            'code'     => 'timeout',
            'debug_id' => $debug_id,
            'data'     => []
        ];
    }
}
