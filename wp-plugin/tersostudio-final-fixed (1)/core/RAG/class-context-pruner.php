<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Context_Pruner {
    private static ?TERSOSTUDIO_Context_Pruner $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_Context_Pruner {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function prune_chat_history( array $chat_history, int $character_limit = 8000 ): array {
        if ( empty( $chat_history ) ) {
            return [];
        }

        $pruned_history = [];
        $current_total_length = 0;

        $reversed_history = array_reverse( $chat_history );

        foreach ( $reversed_history as $message ) {
            $role = sanitize_text_field( $message['sender_role'] ?? 'user' );
            $body = sanitize_textarea_field( $message['message_body'] ?? '' );
            
            if ( empty( $body ) ) {
                continue;
            }

            $message_length = strlen( $body );

            if ( ( $current_total_length + $message_length ) > $character_limit ) {
                $compressed_body = $this->compress_message_body( $body );
                $compressed_length = strlen( $compressed_body );

                if ( ( $current_total_length + $compressed_length ) <= $character_limit ) {
                    $pruned_history[] = [
                        'sender_role'  => $role,
                        'message_body' => '[Compressed Memory Record]: ' . $compressed_body,
                        'token_count'  => intval( $message['token_count'] ?? 0 )
                    ];
                    $current_total_length += $compressed_length;
                }
                continue;
            }

            $pruned_history[] = [
                'sender_role'  => $role,
                'message_body' => $body,
                'token_count'  => intval( $message['token_count'] ?? 0 )
                    ];
            $current_total_length += $message_length;
        }

        return array_reverse( $pruned_history );
    }

    private function compress_message_body( string $body ): string {
        $body = preg_replace( '/\s+/', ' ', $body );
        
        $phrases_to_remove = [
            '/could you please/i',
            '/i would like you to/i',
            '/can you help me to/i',
            '/thank you very much/i',
            '/is it possible to/i',
            '/make sure that/i'
        ];
        $body = preg_replace( $phrases_to_remove, '', $body );

        if ( strlen( $body ) > 300 ) {
            $body = substr( $body, 0, 280 ) . '... [Truncated Stream Content Frame]';
        }

        return trim( $body );
    }
}
