<?php
/**
 * Registers the ability category every AgentWarden ability belongs to.
 * Categories must be registered before any ability references them, on the
 * dedicated `wp_abilities_api_categories_init` hook (core Abilities API,
 * WP 6.9+) - a separate hook from ability registration itself.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function agwd_register_ability_category() {
	wp_register_ability_category(
		AGWD_ABILITY_NAMESPACE,
		array(
			'label'       => __( 'AgentWarden', 'agentwarden' ),
			'description' => __( 'Safe, audited read access to WooCommerce store data for AI agents.', 'agentwarden' ),
		)
	);
}
add_action( 'wp_abilities_api_categories_init', 'agwd_register_ability_category' );
