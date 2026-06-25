<?php
/**
 * WordPress Admin-UI: Menüs, Seiten und POST-Handler.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Registriert Admin-Menüs und verarbeitet Formular-Einsendungen.
 */
class Admin {

	/**
	 * Alle WordPress-Hooks für den Adminbereich registrieren.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_avf_save_settings', array( $this, 'handle_save_settings' ) );
	}

	/**
	 * Hauptmenüeintrag registrieren.
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Altersverifikation', 'altersverifikation' ),
			__( 'Altersverifikation', 'altersverifikation' ),
			Helpers::CAP,
			AVF_SLUG,
			array( $this, 'render_settings' ),
			'dashicons-lock',
			81
		);
	}

	/**
	 * Admin-Assets nur auf Plugin-Seiten einbinden.
	 *
	 * @param string $hook Aktueller Admin-Seiten-Hook-Suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . AVF_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'avf-admin',
			AVF_URL . 'assets/admin.css',
			array(),
			AVF_VERSION
		);
		wp_enqueue_script(
			'avf-admin',
			AVF_URL . 'assets/admin.js',
			array(),
			AVF_VERSION,
			true
		);
	}

	// -------------------------------------------------------------------------
	// POST-Handler
	// -------------------------------------------------------------------------

	/**
	 * Verarbeitet das Einstellungsformular.
	 */
	public function handle_save_settings() {
		Helpers::guard_post( 'avf_save_settings' );

		$clean = Helpers::sanitize_settings( $_POST );
		update_option( Helpers::OPT_SETTINGS, $clean );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'   => AVF_SLUG,
					'notice' => 'saved',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	// -------------------------------------------------------------------------
	// Seiten-Renderer
	// -------------------------------------------------------------------------

	/**
	 * Einstellungsseite ausgeben.
	 */
	public function render_settings() {
		if ( ! current_user_can( Helpers::CAP ) ) {
			return;
		}

		$notice  = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
		$is_pro  = Helpers::is_pro();
		$s       = Helpers::defaults();
		$saved   = (array) get_option( Helpers::OPT_SETTINGS, array() );
		$s       = array_merge( $s, $saved );

		$mode_options = array(
			'confirm' => __( 'Bestätigung (Ja / Nein)', 'altersverifikation' ),
			'dob'     => __( 'Geburtsdatum eingeben', 'altersverifikation' ),
		);
		$scope_options = array(
			'site'  => __( 'Gesamte Website', 'altersverifikation' ),
			'pages' => __( 'Nur bestimmte Seiten', 'altersverifikation' ),
		);
		$decline_action_options = array(
			'message'  => __( 'Meldung anzeigen', 'altersverifikation' ),
			'redirect' => __( 'Weiterleitung zu URL', 'altersverifikation' ),
		);
		?>
		<div class="wrap avf-wrap">
			<h1><?php esc_html_e( 'Altersverifikation – Einstellungen', 'altersverifikation' ); ?></h1>

			<?php if ( 'saved' === $notice ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Einstellungen gespeichert.', 'altersverifikation' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="avf_save_settings">
				<?php wp_nonce_field( 'avf_save_settings' ); ?>

				<h2><?php esc_html_e( 'Allgemein', 'altersverifikation' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="avf-min-age"><?php esc_html_e( 'Mindestalter', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="number" id="avf-min-age" name="min_age" min="0" max="99"
								value="<?php echo esc_attr( (int) $s['min_age'] ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'Erforderliches Mindestalter in Jahren (z. B. 18).', 'altersverifikation' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-mode"><?php esc_html_e( 'Verifikationsmodus', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<select id="avf-mode" name="mode">
								<?php foreach ( $mode_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['mode'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-scope"><?php esc_html_e( 'Geltungsbereich', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<select id="avf-scope" name="scope">
								<?php foreach ( $scope_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['scope'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr id="avf-pages-row">
						<th scope="row">
							<label for="avf-pages"><?php esc_html_e( 'Seiten (IDs)', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="text" id="avf-pages" name="avf_pages_raw" class="regular-text"
								value="<?php echo esc_attr( implode( ', ', array_map( 'absint', (array) $s['pages'] ) ) ); ?>">
							<p class="description"><?php esc_html_e( 'Kommagetrennte Seiten-IDs, wenn der Geltungsbereich auf "Bestimmte Seiten" eingestellt ist.', 'altersverifikation' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Texte', 'altersverifikation' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="avf-heading"><?php esc_html_e( 'Überschrift', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="text" id="avf-heading" name="heading" class="regular-text"
								value="<?php echo esc_attr( $s['heading'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-message"><?php esc_html_e( 'Nachricht', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<textarea id="avf-message" name="message" rows="3" class="large-text"><?php echo esc_textarea( $s['message'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Einfaches HTML erlaubt (z. B. <strong>, <a>).', 'altersverifikation' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-confirm-label"><?php esc_html_e( 'Bestätigen-Schaltfläche', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="text" id="avf-confirm-label" name="confirm_label" class="regular-text"
								value="<?php echo esc_attr( $s['confirm_label'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-decline-label"><?php esc_html_e( 'Ablehnen-Schaltfläche', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="text" id="avf-decline-label" name="decline_label" class="regular-text"
								value="<?php echo esc_attr( $s['decline_label'] ); ?>">
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Ablehnen-Verhalten', 'altersverifikation' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="avf-decline-action"><?php esc_html_e( 'Aktion bei Ablehnung', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<select id="avf-decline-action" name="decline_action">
								<?php foreach ( $decline_action_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['decline_action'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-decline-message"><?php esc_html_e( 'Ablehnungs-Meldung', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<textarea id="avf-decline-message" name="decline_message" rows="2" class="large-text"><?php echo esc_textarea( $s['decline_message'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-decline-url"><?php esc_html_e( 'Weiterleitungs-URL', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="url" id="avf-decline-url" name="decline_url" class="regular-text"
								value="<?php echo esc_attr( $s['decline_url'] ); ?>">
							<p class="description"><?php esc_html_e( 'Nur relevant wenn "Weiterleitung zu URL" gewählt ist.', 'altersverifikation' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Design', 'altersverifikation' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="avf-overlay-color"><?php esc_html_e( 'Overlay-Hintergrundfarbe', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="color" id="avf-overlay-color" name="overlay_color"
								value="<?php echo esc_attr( $s['overlay_color'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-accent-color"><?php esc_html_e( 'Akzentfarbe (Schaltflächen)', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="color" id="avf-accent-color" name="accent_color"
								value="<?php echo esc_attr( $s['accent_color'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="avf-logo-url"><?php esc_html_e( 'Logo-URL', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="url" id="avf-logo-url" name="logo_url" class="regular-text"
								value="<?php echo esc_attr( $s['logo_url'] ); ?>">
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Cookie', 'altersverifikation' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="avf-remember-days"><?php esc_html_e( 'Merkdauer (Tage)', 'altersverifikation' ); ?></label>
						</th>
						<td>
							<input type="number" id="avf-remember-days" name="remember_days" min="1" max="3650"
								value="<?php echo esc_attr( (int) $s['remember_days'] ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'Wie lange das funktionale Cookie die Bestätigung speichert (1–3650 Tage).', 'altersverifikation' ); ?></p>
						</td>
					</tr>
				</table>

				<?php if ( $is_pro ) : ?>
					<?php $this->render_pro_settings( $s ); ?>
				<?php else : ?>
					<?php $this->render_pro_teaser(); ?>
				<?php endif; ?>

				<?php submit_button( __( 'Einstellungen speichern', 'altersverifikation' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Pro-Einstellungsbereich ausgeben (nur für lizenzierte Nutzer).
	 *
	 * @param array<string,mixed> $s Aktuelle Einstellungen.
	 */
	private function render_pro_settings( array $s ) {
		$wc_active = class_exists( 'WooCommerce' );
		?>
		<h2><?php esc_html_e( 'Pro-Einstellungen', 'altersverifikation' ); ?></h2>
		<table class="form-table" role="presentation">

			<?php if ( $wc_active ) : ?>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'WooCommerce-Produktkategorien', 'altersverifikation' ); ?>
					</th>
					<td>
						<?php
						$terms = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => false,
							)
						);
						if ( is_wp_error( $terms ) || empty( $terms ) ) {
							esc_html_e( 'Keine Produktkategorien gefunden.', 'altersverifikation' );
						} else {
							$selected_cats = (array) $s['wc_categories'];
							foreach ( $terms as $term ) {
								if ( ! ( $term instanceof \WP_Term ) ) {
									continue;
								}
								$checked = in_array( (int) $term->term_id, array_map( 'absint', $selected_cats ), true );
								printf(
									'<label style="display:block;margin-bottom:4px;"><input type="checkbox" name="wc_categories[]" value="%d"%s> %s</label>',
									(int) $term->term_id,
									$checked ? ' checked' : '',
									esc_html( $term->name )
								);
							}
						}
						?>
						<p class="description"><?php esc_html_e( 'Gate nur für Produkte in diesen Kategorien anzeigen.', 'altersverifikation' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row">
					<label for="avf-geo-countries"><?php esc_html_e( 'Geo-Länder (ISO)', 'altersverifikation' ); ?></label>
				</th>
				<td>
					<input type="text" id="avf-geo-countries" name="avf_geo_raw" class="regular-text"
						value="<?php echo esc_attr( implode( ', ', (array) $s['geo_countries'] ) ); ?>">
					<p class="description"><?php esc_html_e( 'Kommagetrennte 2-stellige ISO-Ländercodes (z. B. DE, AT, CH). Leer = alle Länder.', 'altersverifikation' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="avf-show-credit"><?php esc_html_e( 'Branding-Hinweis', 'altersverifikation' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" id="avf-show-credit" name="show_credit" value="1"
							<?php checked( (bool) $s['show_credit'] ); ?>>
						<?php esc_html_e( '"Altersverifikation by Kipphard"-Hinweis im Overlay anzeigen', 'altersverifikation' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Deaktivieren um das Branding auszublenden (White-Label).', 'altersverifikation' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="avf-custom-css"><?php esc_html_e( 'Eigenes CSS', 'altersverifikation' ); ?></label>
				</th>
				<td>
					<textarea id="avf-custom-css" name="custom_css" rows="6" class="large-text code"><?php echo esc_textarea( $s['custom_css'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Eigenes CSS zur Anpassung des Overlays.', 'altersverifikation' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Pro-Upgrade-Teaser für Free-Nutzer ausgeben.
	 */
	private function render_pro_teaser() {
		?>
		<div class="avf-pro-teaser card" style="margin-top:1.5em;">
			<h2><?php esc_html_e( 'Altersverifikation Pro', 'altersverifikation' ); ?></h2>
			<ul class="avf-pro-features">
				<li>
					<span class="dashicons dashicons-products"></span>
					<?php esc_html_e( 'WooCommerce: Gate nur für bestimmte Produktkategorien aktivieren', 'altersverifikation' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-location"></span>
					<?php esc_html_e( 'Geo-Targeting: Gate nur für Besucher aus bestimmten Ländern', 'altersverifikation' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-art"></span>
					<?php esc_html_e( 'White-Label: Kipphard-Branding im Overlay ausblenden', 'altersverifikation' ); ?>
				</li>
				<li>
					<span class="dashicons dashicons-editor-code"></span>
					<?php esc_html_e( 'Eigenes CSS für vollständige Design-Kontrolle', 'altersverifikation' ); ?>
				</li>
			</ul>
			<p>
				<a href="https://products.kipphard.com/altersverifikation" target="_blank" rel="noopener noreferrer" class="button button-secondary">
					<?php esc_html_e( 'Jetzt upgraden', 'altersverifikation' ); ?>
				</a>
			</p>
		</div>
		<?php
	}
}
