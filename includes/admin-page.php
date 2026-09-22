<?php
/**
 * Admin page: lists the abilities AgentWarden has registered and the most
 * recent audit log entries. Read-only by design - there is nothing to
 * configure yet in this skeleton (no limits/policies exist until the Pro
 * write axis lands, per AGENTWARDEN_STRATEGY.md §3-4).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function agwd_register_admin_page() {
	add_menu_page(
		__( 'AgentWarden', 'agentwarden' ),
		__( 'AgentWarden', 'agentwarden' ),
		'manage_woocommerce',
		'agentwarden',
		'agwd_render_admin_page',
		'dashicons-shield',
		56
	);
}
add_action( 'admin_menu', 'agwd_register_admin_page' );

function agwd_render_admin_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	if ( isset( $_POST['agwd_clear_log'] ) && check_admin_referer( 'agwd_clear_log' ) ) {
		agwd_audit_log_clear();
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Audit log cleared.', 'agentwarden' ) . '</p></div>';
	}

	$abilities = wp_get_abilities( array( 'category' => AGWD_ABILITY_NAMESPACE ) );
	$log       = agwd_audit_log_get( 50 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AgentWarden', 'agentwarden' ); ?></h1>
		<p><?php esc_html_e( 'A trust layer between AI agents and your WooCommerce store. Below is every ability currently registered and what agents have read recently.', 'agentwarden' ); ?></p>

		<h2><?php esc_html_e( 'Registered abilities', 'agentwarden' ); ?></h2>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Label', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Description', 'agentwarden' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $abilities ) ) : ?>
					<tr><td colspan="3"><?php esc_html_e( 'No abilities registered.', 'agentwarden' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $abilities as $ability ) : ?>
						<tr>
							<td><code><?php echo esc_html( $ability->get_name() ); ?></code></td>
							<td><?php echo esc_html( $ability->get_label() ); ?></td>
							<td><?php echo esc_html( $ability->get_description() ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Recent activity', 'agentwarden' ); ?></h2>
		<form method="post" style="margin-bottom: 1em;">
			<?php wp_nonce_field( 'agwd_clear_log' ); ?>
			<button type="submit" name="agwd_clear_log" value="1" class="button" onclick="return confirm('<?php echo esc_js( __( 'Clear the audit log?', 'agentwarden' ) ); ?>');">
				<?php esc_html_e( 'Clear log', 'agentwarden' ); ?>
			</button>
		</form>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'When', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Ability', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Object', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Count', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'User', 'agentwarden' ); ?></th>
					<th><?php esc_html_e( 'Result', 'agentwarden' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $log ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No activity yet.', 'agentwarden' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $log as $entry ) : ?>
						<?php $user = $entry['actor'] ? get_userdata( $entry['actor'] ) : false; ?>
						<tr>
							<td><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $entry['time'] ) ); ?></td>
							<td><code><?php echo esc_html( $entry['ability'] ); ?></code></td>
							<td><?php echo esc_html( $entry['object_type'] . ( $entry['object_id'] ? ' #' . $entry['object_id'] : '' ) ); ?></td>
							<td><?php echo esc_html( (string) $entry['count'] ); ?></td>
							<td><?php echo esc_html( $user ? $user->user_login : __( '(unknown)', 'agentwarden' ) ); ?></td>
							<td><?php echo $entry['success'] ? esc_html__( 'OK', 'agentwarden' ) : esc_html__( 'Error', 'agentwarden' ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
