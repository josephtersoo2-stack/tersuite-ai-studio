<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TERSOSTUDIO_Service_Container {
    private static ?TERSOSTUDIO_Service_Container $instance = null;
    private array $bindings = [];
    private array $singletons = [];
    private array $resolved_singletons = [];

    private function __construct() {
        $this->register_default_providers();
    }

    public static function get_instance(): TERSOSTUDIO_Service_Container {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind( string $abstract, callable $factory ): void {
        $this->bindings[ $abstract ] = $factory;
        unset( $this->resolved_singletons[ $abstract ], $this->singletons[ $abstract ] );
    }

    public function singleton( string $abstract, callable $factory ): void {
        $this->singletons[ $abstract ] = $factory;
        unset( $this->bindings[ $abstract ], $this->resolved_singletons[ $abstract ] );
    }

    public function make( string $abstract ) {
        if ( isset( $this->resolved_singletons[ $abstract ] ) ) {
            return $this->resolved_singletons[ $abstract ] ;
        }

        if ( isset( $this->singletons[ $abstract ] ) ) {
            $factory = $this->singletons[ $abstract ];
            $this->resolved_singletons[ $abstract ] = $factory( $this );
            return $this->resolved_singletons[ $abstract ];
        }

        if ( isset( $this->bindings[ $abstract ] ) ) {
            $factory = $this->bindings[ $abstract ];
            return $factory( $this );
        }

        if ( class_exists( $abstract ) ) {
            return new $abstract();
        }

        return null;
    }

    private function register_default_providers(): void {
        add_action( 'tersostudio_register_service_provider', [ $this, 'resolve_addon_provider_registration' ] );
    }

    public function resolve_addon_provider_registration( array $provider_manifest ): void {
        $slug = sanitize_key( $provider_manifest['slug'] ?? '' );
        $factory = $provider_manifest['factory'] ?? null;

        if ( ! empty( $slug ) && is_callable( $factory ) ) {
            if ( ! empty( $provider_manifest['singleton'] ) ) {
                $this->singleton( $slug, $factory );
            } else {
                $this->bind( $slug, $factory );
            }
        }
    }

    private function __clone() {}
    public function __wakeup() {
        throw new \Exception( "Cannot unserialize a singleton container instance." );
    }
}
