<?php
/**
 * Plugin Name:       AgentWarden
 * Plugin URI:        https://cognitolab.net/products/agentwarden
 * Description:       A trust layer between AI agents and your WooCommerce store: safe, audited read access via the WordPress Abilities API.
 * Version:           0.1.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            CognitoLab
 * Author URI:        https://cognitolab.net
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       agentwarden
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * "AgentWarden" is a working name and may still change before the first
 * public release (see AGENTWARDEN_STRATEGY.md) - every internal identifier
 * lives behind the AGWD_/agwd_ prefix below so a rename stays a mechanical
 * find/replace instead of an architecture change.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AGWD_VERSION', '0.1.0' );
define( 'AGWD_PLUGIN_FILE', __FILE__ );
define( 'AGWD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AGWD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Namespace prefix used for every ability name and the ability category
 * (`{namespace}/{ability-slug}`, e.g. `agentwarden/list-products`), per the
 * Abilities API's namespaced-slug requirement. Centralized here so the
 * working-name rename above only has to happen once.
 */
define( 'AGWD_ABILITY_NAMESPACE', 'agentwarden' );

require_once AGWD_PLUGIN_DIR . 'includes/audit-log.php';
require_once AGWD_PLUGIN_DIR . 'includes/ability-category.php';
require_once AGWD_PLUGIN_DIR . 'includes/abilities/read-products.php';
require_once AGWD_PLUGIN_DIR . 'includes/abilities/read-orders.php';
require_once AGWD_PLUGIN_DIR . 'includes/abilities/read-customers.php';
require_once AGWD_PLUGIN_DIR . 'includes/admin-page.php';

/**
 * No load_plugin_textdomain() call: discouraged since WP 4.6 for plugins
 * hosted on wordpress.org - core auto-loads translations for wp.org-hosted
 * plugins using the plugin slug, no manual loading needed.
 */
