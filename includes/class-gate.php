<?php
/**
 * Frontend Age Gate: Overlay-Ausgabe, Skripte und Styles.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Rendert den Altersverifikations-Overlay auf der Frontend-Seite.
 */
class Gate {

	/**
	 * WordPress-Hooks registrieren.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'inline_critical_css' ) );
		add_action( 'wp_footer', array( $this, 'render_overlay' ) );
	}

	/**
	 * Assets (CSS + JS) einbinden und Konfiguration per wp_localize_script übergeben.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_render() ) {
			return;
		}

		wp_enqueue_style(
			'avf-gate',
			AVF_URL . 'assets/gate.css',
			array(),
			AVF_VERSION
		);

		wp_enqueue_script(
			'avf-gate',
			AVF_URL . 'assets/gate.js',
			array(),
			AVF_VERSION,
			true
		);

		// Nur nicht-personenbezogene Anzeigekonfiguration an den Browser übertragen.
		wp_localize_script(
			'avf-gate',
			'avfData',
			array(
				'minAge'        => (int) Helpers::get( 'min_age' ),
				'mode'          => Helpers::get( 'mode' ),
				'rememberDays'  => (int) Helpers::get( 'remember_days' ),
				'declineAction' => Helpers::get( 'decline_action' ),
				'declineUrl'    => Helpers::get( 'decline_url' ),
				'cookieName'    => 'avf_ok',
			)
		);
	}

	/**
	 * Minimales Inline-CSS im <head>: Overlay standardmäßig sichtbar (Fail-Safe).
	 * Verhindert Content-Flash, bevor gate.css geladen ist.
	 */
	public function inline_critical_css() {
		if ( ! $this->should_render() ) {
			return;
		}
		// Overlay per CSS sichtbar; JS entfernt es nach Cookie-Prüfung.
		echo '<style id="avf-critical">#avf-overlay{display:flex!important}</style>' . "\n";
	}

	/**
	 * Overlay-Markup im Footer ausgeben.
	 */
	public function render_overlay() {
		if ( ! $this->should_render() ) {
			return;
		}

		$heading         = Helpers::get( 'heading' );
		$message         = Helpers::get( 'message' );
		$confirm_label   = Helpers::get( 'confirm_label' );
		$decline_label   = Helpers::get( 'decline_label' );
		$decline_message = Helpers::get( 'decline_message' );
		$mode            = Helpers::get( 'mode' );
		$overlay_color   = Helpers::get( 'overlay_color' );
		$accent_color    = Helpers::get( 'accent_color' );
		$logo_url        = Helpers::get( 'logo_url' );
		$show_credit     = (bool) Helpers::get( 'show_credit' );
		$min_age         = (int) Helpers::get( 'min_age' );

		$overlay_color = $overlay_color ? $overlay_color : '#0d0d0f';
		$accent_color  = $accent_color ? $accent_color : '#f0834e';

		$inline_style = sprintf(
			'--avf-overlay-color:%s;--avf-accent-color:%s',
			esc_attr( $overlay_color ),
			esc_attr( $accent_color )
		);
		?>
		<div id="avf-overlay" role="dialog" aria-modal="true" aria-labelledby="avf-heading"
			style="<?php echo esc_attr( $inline_style ); ?>">
			<div class="avf-card">

				<?php if ( $logo_url ) : ?>
					<img class="avf-logo" src="<?php echo esc_url( $logo_url ); ?>"
						alt="<?php esc_attr_e( 'Logo', 'altersverifikation' ); ?>">
				<?php endif; ?>

				<h1 id="avf-heading" class="avf-heading"><?php echo esc_html( $heading ); ?></h1>

				<div class="avf-message"><?php echo wp_kses_post( $message ); ?></div>

				<?php if ( 'dob' === $mode ) : ?>
					<div class="avf-dob-wrap">
						<label for="avf-day"><?php esc_html_e( 'Geburtstag', 'altersverifikation' ); ?></label>
						<div class="avf-dob-fields">
							<input type="number" id="avf-day" name="avf_day" placeholder="TT"
								min="1" max="31" inputmode="numeric" autocomplete="bday-day"
								aria-label="<?php esc_attr_e( 'Tag', 'altersverifikation' ); ?>">
							<input type="number" id="avf-month" name="avf_month" placeholder="MM"
								min="1" max="12" inputmode="numeric" autocomplete="bday-month"
								aria-label="<?php esc_attr_e( 'Monat', 'altersverifikation' ); ?>">
							<input type="number" id="avf-year" name="avf_year"
								placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"
								min="1900" max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"
								inputmode="numeric" autocomplete="bday-year"
								aria-label="<?php esc_attr_e( 'Jahr', 'altersverifikation' ); ?>">
						</div>
						<p id="avf-dob-error" class="avf-error" aria-live="polite" style="display:none;">
							<?php
							printf(
								/* translators: %d: required minimum age */
								esc_html__( 'Du musst mindestens %d Jahre alt sein, um diese Seite zu besuchen.', 'altersverifikation' ),
								$min_age
							);
							?>
						</p>
					</div>
				<?php endif; ?>

				<div class="avf-actions">
					<button type="button" id="avf-confirm" class="avf-btn avf-btn-confirm">
						<?php echo esc_html( $confirm_label ); ?>
					</button>
					<button type="button" id="avf-decline" class="avf-btn avf-btn-decline">
						<?php echo esc_html( $decline_label ); ?>
					</button>
				</div>

				<div id="avf-decline-message" class="avf-decline-message" style="display:none;" aria-live="polite">
					<?php echo wp_kses_post( $decline_message ); ?>
				</div>

				<?php if ( ! ( Helpers::is_pro() && ! $show_credit ) ) : ?>
					<p class="avf-credit">
						<a href="https://products.kipphard.com/altersverifikation" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Altersverifikation by Kipphard', 'altersverifikation' ); ?>
						</a>
					</p>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Ermittelt ob der Overlay auf der aktuellen Seite angezeigt werden soll.
	 *
	 * @return bool
	 */
	public function should_render() {
		// Im Adminbereich nie anzeigen.
		if ( is_admin() ) {
			return false;
		}

		// Login-Seite ausschließen.
		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return false;
		}

		// Admins nie gate-n.
		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}

		$scope = Helpers::get( 'scope' );
		if ( 'pages' === $scope ) {
			$pages = (array) Helpers::get( 'pages' );
			if ( empty( $pages ) || ! is_page( $pages ) ) {
				$show = false;
			} else {
				$show = true;
			}
		} else {
			$show = true;
		}

		/**
		 * Erlaubt es anderen Klassen (z. B. der Pro-WooCommerce-Klasse),
		 * die Gate-Entscheidung zu überschreiben.
		 *
		 * @param bool $show Ob der Overlay angezeigt werden soll.
		 */
		return (bool) apply_filters( 'avf_should_gate', $show );
	}
}
