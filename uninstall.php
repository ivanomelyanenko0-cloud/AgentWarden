<?php
/**
 * Uninstall handler.
 *
 * The audit log is AgentWarden's own operational data (what agents read),
 * not merchant content, so it is always removed - there is no store data to
 * decide about yet in this skeleton (no settings, no options beyond the log).
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function agwd_uninstall_site() {
	delete_option( 'agwd_audit_log' );
}

if ( is_multisite() ) {
	foreach ( get_sites( array( 'fields' => 'ids' ) ) as $agwd_site_id ) {
		switch_to_blog( $agwd_site_id );
		agwd_uninstall_site();
		restore_current_blog();
	}
} else {
	agwd_uninstall_site();
}
