<?php
defined('ABSPATH') || exit;

/**
 * Subscription Manager
 */
class Tersuite_AI_Subscription_Manager {

    protected $api;

    public function __construct() {
        $this->api = new Tersuite_AI_API_Client();
    }

    public function plans() {
        return $this->api->get('api/v1/subscriptions/plans');
    }

    public function status() {
        return $this->api->get('api/v1/subscriptions/status');
    }

    public function subscribe($plan, $gateway = 'stripe') {
        $payload = array(
            'plan'    => sanitize_text_field($plan),
            'gateway' => sanitize_text_field($gateway),
        );
        return $this->api->post('api/v1/subscriptions/subscribe', $payload);
    }
}
