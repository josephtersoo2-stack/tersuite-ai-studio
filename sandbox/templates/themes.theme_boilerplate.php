<?php
/**
 * Theme Name: {{THEME_NAME}}
 * Theme URI: {{THEME_URI}}
 * Description: {{THEME_DESCRIPTION}}
 * Version: {{VERSION}}
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Author: {{AUTHOR}}
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

namespace {{THEME_NAMESPACE}};

final class Theme {
    public static function instance(): self {
        static $instance = null;
        return $instance ?: $instance = new self();
    }

    private function __construct() {
        add_action('after_setup_theme', [$this, 'setup']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function setup(): void {
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
        add_theme_support('html5', ['search-form', 'comment-form', 'gallery', 'caption']);
    }

    public function enqueue_assets(): void {
        wp_enqueue_style('theme-style', get_stylesheet_uri());
    }
}

Theme::instance();
