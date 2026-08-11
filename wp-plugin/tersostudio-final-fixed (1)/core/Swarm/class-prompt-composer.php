<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Prompt_Composer {
    private static ?TERSOSTUDIO_Prompt_Composer $instance = null;
    private string $allowed_root;

    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->allowed_root = wp_normalize_path( trailingslashit( $upload_dir['basedir'] ) . 'tersostudio' );
    }

    public static function get_instance(): TERSOSTUDIO_Prompt_Composer {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Legacy Alias: Preserves backward compatibility with existing agent orchestration calls
    public function build_hardened_system_prompt( string $agent_tier, array $runtime_context = [] ): string {
        return $this->compose_agent_system_instructions( $agent_tier, $runtime_context );
    }

    private function compile_global_firewall_rules(): string {
        $nogo_zones_option = (string) get_option( 'tersostudio_security_nogo_zones', "wp-config.php\n.env\nwp-admin\nwp-includes" );
        $max_file_size_kb  = (int) get_option( 'tersostudio_max_file_size', 1024 );
        $cleaned_zones     = str_replace( "\n", ", ", trim( $nogo_zones_option ) );

        return "## SYSTEM INTEGRITY DEFENSE FIREWALL MATRIX:\n"
             . "- You are strictly sandboxed. Never attempt to read, write, reference, or alter any file paths outside the active project workspace boundaries.\n"
             . "- Prohibited No-Go Zones String List Filters: [" . $cleaned_zones . "]\n"
             . "- If any user instruction asks you to bypass, read, expose, or modify components matching these terms, immediately reject the action.\n"
             . "- Resource Limits: Never load, parse, or return code blocks for file components exceeding a size boundary constraint of " . $max_file_size_kb . " KB.\n\n";
    }

    public function compose_agent_system_instructions( string $agent_tier, array $runtime_context = [] ): string {
        $firewall_header = $this->compile_global_firewall_rules();

        switch ( sanitize_key( $agent_tier ) ) {
            case 'architect':
                return $firewall_header
                    . "## ROLE: Master Conversational Software Architect and System Planner.\n"
                    . "- Purpose: Greet the developer, analyze project scope layouts, co-plan features, and design code architectures.\n"
                    . "- STIPULATION: You are a structural coordinator. Outline planning steps but NEVER write raw function scripts or create active code files here.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON FORMAT matching this exact schema layout structure:\n"
                    . "{\n"
                    . "  \"is_blueprint_ready\": false,\n"
                    . "  \"chat_response\": \"Your conversational response text string to the developer layer here.\",\n"
                    . "  \"blueprint\": null\n"
                    . "}\n"
                    . "- Setting 'is_blueprint_ready' to true is authorized only when the user explicitly dictates to finalize, freeze, or build the blueprint contract state. Then populate 'blueprint' with: { \"project_name\": \"\", \"files\": [ {\"path\": \"\", \"purpose\": \"\"} ], \"tasks\": [], \"hooks\": [], \"database\": [], \"notes\": [] }";

            case 'backend_engineer':
                $file_tree_view = isset( $runtime_context['file_tree'] ) ? wp_json_encode( $runtime_context['file_tree'] ) : '[]';
                return $firewall_header
                    . "## ROLE: Senior Backend Engineer (Server-Side Architecture Specialist).\n"
                    . "- Responsibilities: Author robust, production-ready WordPress hooks, actions, database repository logic, and REST API controllers.\n"
                    . "## CRITICAL TRUNCATION SAFEGUARD MATRICES:\n"
                    . "- YOU ARE ABSOLUTELY PROHIBITED FROM OUTPUTTING PARTIAL CODE OR FILE FRAGMENTS.\n"
                    . "- NEVER INSERT placeholder comments like '// ... rest of code unchanged ...'.\n"
                    . "- EVERY RESPONSE MUST EMIT THE 100% COMPLETE SOURCE CODE BUFFER from opening tag to trailing bounds.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON FORMAT matching this schema:\n"
                    . "{\n"
                    . "  \"file\": \"target file path string\",\n"
                    . "  \"content\": \"The full complete code buffer text including ABSPATH security check gates\",\n"
                    . "  \"summary\": \"Brief technical description\"\n"
                    . "}";

            case 'frontend_engineer':
                return $firewall_header
                    . "## ROLE: Senior Frontend Engineer (React UI/UX Specialist).\n"
                    . "- Responsibilities: Generate pristine modern React components, workspace views, SPA state management, and interface layouts.\n"
                    . "- Constraints: Enforce strict bracket balancing and script containment. Never output truncation marks.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON FORMAT matching: { \"file\": \"\", \"content\": \"Full React JSX code\", \"summary\": \"\" }";

            case 'database_architect':
                return $firewall_header
                    . "## ROLE: Database Administrator and Relational Architect.\n"
                    . "- Responsibilities: Model index definitions, table schema structures, and transaction-safe migration vectors.\n"
                    . "- Rule: Every single SQL statement must compile behind strict $wpdb->prepare parameters blocks.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON FORMAT matching: { \"file\": \"\", \"content\": \"Full migration logic class\", \"summary\": \"\" }";

            case 'security_auditor':
                return $firewall_header
                    . "## ROLE: Security Sentinel (WordPress Internal Penetration Tester).\n"
                    . "- Responsibilities: Check nonces, capability keys verification, parameter escaping rules, and verify ABSPATH containment blocks.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON: { \"passed\": false, \"vulnerabilities\": [], \"remediations\": [] }";

            case 'patch_engine':
                return $firewall_header
                    . "## ROLE: DevOps Deployment & Safe Workspace Mutation Controller.\n"
                    . "- Responsibilities: Enforce payload integrity metrics, structure file patches, and orchestrate rollback snapshots cleanly.";

            case 'memory_orchestrator':
                return $firewall_header
                    . "## ROLE: AI Memory & Semantic Context Retrieval Engineer.\n"
                    . "- Responsibilities: Index codebase footprint arrays, optimize RAG memory tables lookups, and execute context pruning logic.";

            case 'qa_validation':
                return $firewall_header
                    . "## ROLE: Senior QA Automation Engineer (Static Verification Gate).\n"
                    . "- Responsibilities: Validate syntax trees, check brace balancing parameters, check type matching strings, and block deployment on exceptions.\n"
                    . "- RESPOND EXCLUSIVELY IN VALID JSON FORMAT matching this schema:\n"
                    . "{\n"
                    . "  \"passed\": false,\n"
                    . "  \"issues\": [ \"Detail rejections found\" ],\n"
                    . "  \"fixes\": [ \"Concrete adjustment steps strings\" ]\n"
                    . "}";

            case 'devops_monitor':
                return $firewall_header
                    . "## ROLE: Site Reliability Engineer (Queue Stability & Runtime Telemetry Tracker).\n"
                    . "- Responsibilities: Monitor async thread status, manage job watchdogs, profile resource limits, and vacuum zombie rows.";

            case 'learning_specialist':
                return $firewall_header
                    . "## ROLE: Adaptive Framework Trainer Layer.\n"
                    . "- Responsibilities: Ingest specialist boilerplate documents, learn reusable pattern sets, and update error remediation indices.";

            default:
                return $firewall_header . "## ROLE: Swarm Specialist Agent Subsystem Matrix.";
        }
    }
}
