<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_RAG_Orchestrator {
    private static ?TERSOSTUDIO_RAG_Orchestrator $instance = null;

    private function __construct() {}

    public static function get_instance(): TERSOSTUDIO_RAG_Orchestrator {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function retrieve_context( string $user_prompt, string $context_tag = '' ): array {
        global $wpdb;
        $debug_id = 'ts_rag_' . wp_generate_password( 8, false, false );
        $table = $wpdb->prefix . 'ts_knowledge';

        if ( empty( trim( $user_prompt ) ) ) {
            return [
                'success'  => true,
                'message'  => 'Empty prompt provided. Skipping context injection.',
                'code'     => 'empty_prompt',
                'debug_id' => $debug_id,
                'data'     => [ 'context_string' => '' ]
            ];
        }

        $keywords = $this->extract_search_keywords( $user_prompt );
        if ( empty( $keywords ) ) {
            return [
                'success'  => true,
                'message'  => 'No structural keywords extracted.',
                'code'     => 'no_keywords',
                'debug_id' => $debug_id,
                'data'     => [ 'context_string' => '' ]
            ];
        }

        $search_term = implode( ' ', $keywords );
        
        if ( ! empty( $context_tag ) ) {
            $query = $wpdb->prepare(
                "SELECT reference_text, MATCH(lookup_keyword, reference_text) AGAINST(%s) as score 
                 FROM {$table} 
                 WHERE context_tag = %s AND MATCH(lookup_keyword, reference_text) AGAINST(%s) 
                 ORDER BY score DESC LIMIT 3",
                $search_term, $context_tag, $search_term
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT reference_text, MATCH(lookup_keyword, reference_text) AGAINST(%s) as score 
                 FROM {$table} 
                 WHERE MATCH(lookup_keyword, reference_text) AGAINST(%s) 
                 ORDER BY score DESC LIMIT 3",
                $search_term, $search_term
            );
        }

        $results = $wpdb->get_results( $query, ARRAY_A );

        if ( empty( $results ) && ! empty( $context_tag ) ) {
            $query = $wpdb->prepare(
                "SELECT reference_text FROM {$table} 
                 WHERE MATCH(lookup_keyword, reference_text) AGAINST(%s) 
                 ORDER BY MATCH(lookup_keyword, reference_text) AGAINST(%s) DESC LIMIT 3",
                $search_term, $search_term
            );
            $results = $wpdb->get_results( $query, ARRAY_A );
        }

        $context_chunks = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                if ( ! empty( $row['reference_text'] ) ) {
                    $context_chunks[] = $row['reference_text'];
                }
            }
        }

        $context_string = implode( "\n\n---\n\n", $context_chunks );

        return [
            'success'  => true,
            'message'  => sprintf( 'RAG search matching completed. Found %d snippets.', count( $context_chunks ) ),
            'code'     => 'success',
            'debug_id' => $debug_id,
            'data'     => [ 'context_string' => $context_string ]
        ];
    }

    private function extract_search_keywords( string $text ): array {
        $text = strtolower( strip_tags( $text ) );
        $text = preg_replace( '/[^a-z0-9_\-\s]/', '', $text );
        $words = explode( ' ', $text );
        
        $stopwords = [
            'i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 
            'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 
            'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which', 
            'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 
            'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 
            'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 
            'with', 'about', 'against', 'between', 'into', 'through', 'during', 'before', 'after', 
            'above', 'below', 'to', 'from', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 
            'again', 'further', 'then', 'once', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 
            'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 
            'only', 'own', 'same', 'so', 'than', 'too', 'very', 's', 't', 'can', 'will', 'just', 'don', 
            'should', 'now', 'want', 'please', 'make', 'create', 'add', 'fix', 'write', 'plugin'
        ];

        $filtered_words = [];
        foreach ( $words as $word ) {
            $word = trim( $word );
            if ( strlen( $word ) > 2 && ! in_array( $word, $stopwords, true ) ) {
                $filtered_words[] = $word;
            }
        }

        return array_values( array_unique( $filtered_words ) );
    }

    public function inject_knowledge_base_record( string $keyword, string $tag, string $reference ): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'ts_knowledge';
        $result = $wpdb->insert(
            $table,
            [
                'lookup_keyword' => sanitize_text_field( $keyword ),
                'context_tag'    => sanitize_key( $tag ),
                'reference_text' => wp_kses_post( $reference )
            ],
            [ '%s', '%s', '%s' ]
        );
        return false !== $result;
    }
}
