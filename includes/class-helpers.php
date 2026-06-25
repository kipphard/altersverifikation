<?php
/**
 * Gemeinsame Hilfsmethoden: Capability, Optionen, Sanitisierung.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Zustandslose Hilfsmethoden, die im gesamten Plugin verwendet werden.
 */
class Helpers {

	/** Capability für alle Admin-Aktionen. */
	const CAP = 'manage_options';

	/** Option-Key für die gespeicherten Plugin-Einstellungen. */
	const OPT_SETTINGS = 'avf_settings';

	/**
	 * Prüft ob die Pro-Lizenz aktiv ist.
	 *
	 * @return bool
	 */
	public static function is_pro() {
		return (bool) apply_filters( 'avf_is_pro', defined( 'AVF_PRO' ) && AVF_PRO );
	}

	/**
	 * Gibt die Standardwerte aller Einstellungen zurück.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		return array(
			// Allgemein.
			'min_age'        => 18,
			'mode'           => 'confirm',
			'scope'          => 'site',
			'pages'          => array(),
			// Texte.
			'heading'        => __( 'Altersverifikation', 'altersverifikation' ),
			'message'        => __( 'Diese Website enthält altersbeschränkte Inhalte. Bitte bestätige, dass du das erforderliche Mindestalter erreicht hast.', 'altersverifikation' ),
			'confirm_label'  => __( 'Ja, ich bin alt genug', 'altersverifikation' ),
			'decline_label'  => __( 'Nein, ich bin zu jung', 'altersverifikation' ),
			// Ablehnen.
			'decline_action'  => 'message',
			'decline_message' => __( 'Du musst das Mindestalter erreicht haben, um diese Seite zu besuchen.', 'altersverifikation' ),
			'decline_url'     => '',
			// Funktionscookie.
			'remember_days'  => 30,
			// Design.
			'overlay_color'  => '#0d0d0f',
			'accent_color'   => '#f0834e',
			'logo_url'       => '',
			'show_credit'    => true,
			// Pro-Felder.
			'wc_categories'  => array(),
			'geo_countries'  => array(),
			'custom_css'     => '',
		);
	}

	/**
	 * Liefert den gespeicherten Wert einer Einstellung, mit Fallback auf den Standardwert.
	 *
	 * @param string $key Einstellungsschlüssel.
	 * @return mixed
	 */
	public static function get( $key ) {
		$saved    = (array) get_option( self::OPT_SETTINGS, array() );
		$defaults = self::defaults();
		$merged   = array_merge( $defaults, $saved );
		return isset( $merged[ $key ] ) ? $merged[ $key ] : null;
	}

	/**
	 * Sanitisiert das Einstellungsformular strikt je Feld.
	 *
	 * @param array<string,mixed> $raw Rohe $_POST-Daten.
	 * @return array<string,mixed>
	 */
	public static function sanitize_settings( array $raw ) {
		$clean = array();

		// min_age: Ganzzahl, 0–99.
		$min_age = isset( $raw['min_age'] ) ? absint( $raw['min_age'] ) : 18;
		$clean['min_age'] = min( 99, max( 0, $min_age ) );

		// mode: nur erlaubte Werte.
		$mode = isset( $raw['mode'] ) ? sanitize_key( $raw['mode'] ) : 'confirm';
		$clean['mode'] = in_array( $mode, array( 'confirm', 'dob' ), true ) ? $mode : 'confirm';

		// scope: nur erlaubte Werte.
		$scope = isset( $raw['scope'] ) ? sanitize_key( $raw['scope'] ) : 'site';
		$clean['scope'] = in_array( $scope, array( 'site', 'pages' ), true ) ? $scope : 'site';

		// pages: entweder als Array (multiselect) oder als Rohtext (kommagetrennte IDs).
		if ( isset( $raw['pages'] ) && is_array( $raw['pages'] ) ) {
			$pages = $raw['pages'];
		} elseif ( isset( $raw['avf_pages_raw'] ) && '' !== trim( $raw['avf_pages_raw'] ) ) {
			$pages = explode( ',', $raw['avf_pages_raw'] );
		} else {
			$pages = array();
		}
		$clean['pages'] = array_filter( array_map( 'absint', $pages ) );

		// Textfelder.
		$clean['heading']       = isset( $raw['heading'] ) ? sanitize_text_field( wp_unslash( $raw['heading'] ) ) : '';
		$clean['confirm_label'] = isset( $raw['confirm_label'] ) ? sanitize_text_field( wp_unslash( $raw['confirm_label'] ) ) : '';
		$clean['decline_label'] = isset( $raw['decline_label'] ) ? sanitize_text_field( wp_unslash( $raw['decline_label'] ) ) : '';

		// message und decline_message erlauben einfaches HTML.
		$clean['message']         = isset( $raw['message'] ) ? wp_kses_post( wp_unslash( $raw['message'] ) ) : '';
		$clean['decline_message'] = isset( $raw['decline_message'] ) ? wp_kses_post( wp_unslash( $raw['decline_message'] ) ) : '';

		// Farbfelder.
		$clean['overlay_color'] = isset( $raw['overlay_color'] ) ? sanitize_hex_color( $raw['overlay_color'] ) : '#0d0d0f';
		$clean['accent_color']  = isset( $raw['accent_color'] ) ? sanitize_hex_color( $raw['accent_color'] ) : '#f0834e';

		// decline_action.
		$decline_action = isset( $raw['decline_action'] ) ? sanitize_key( $raw['decline_action'] ) : 'message';
		$clean['decline_action'] = in_array( $decline_action, array( 'message', 'redirect' ), true ) ? $decline_action : 'message';

		// decline_url.
		$clean['decline_url'] = isset( $raw['decline_url'] ) ? esc_url_raw( wp_unslash( $raw['decline_url'] ) ) : '';

		// remember_days: 1–3650.
		$remember_days = isset( $raw['remember_days'] ) ? absint( $raw['remember_days'] ) : 30;
		$clean['remember_days'] = min( 3650, max( 1, $remember_days ) );

		// logo_url.
		$clean['logo_url'] = isset( $raw['logo_url'] ) ? esc_url_raw( wp_unslash( $raw['logo_url'] ) ) : '';

		// Boolesche Felder.
		$clean['show_credit'] = ! empty( $raw['show_credit'] );

		// Pro: WooCommerce-Produktkategorien.
		$wc_cats = isset( $raw['wc_categories'] ) && is_array( $raw['wc_categories'] ) ? $raw['wc_categories'] : array();
		$clean['wc_categories'] = array_map( 'absint', $wc_cats );

		// Pro: Geo-Länder (2-stellige ISO-Codes, Großbuchstaben); als Array oder Rohtext.
		if ( isset( $raw['geo_countries'] ) && is_array( $raw['geo_countries'] ) ) {
			$geo = $raw['geo_countries'];
		} elseif ( isset( $raw['avf_geo_raw'] ) && '' !== trim( $raw['avf_geo_raw'] ) ) {
			$geo = explode( ',', $raw['avf_geo_raw'] );
		} else {
			$geo = array();
		}
		$clean['geo_countries'] = array_filter(
			array_map(
				static function ( $c ) {
					return strtoupper( sanitize_text_field( wp_unslash( $c ) ) );
				},
				$geo
			)
		);

		// Pro: Eigenes CSS.
		$clean['custom_css'] = isset( $raw['custom_css'] ) ? wp_strip_all_tags( wp_unslash( $raw['custom_css'] ) ) : '';

		return $clean;
	}

	/**
	 * Prüft Capability + Nonce für Admin-POST-Anfragen. Bricht bei Fehler ab.
	 *
	 * @param string $action Nonce-Aktion.
	 * @param string $field  Name des Nonce-Feldes.
	 */
	public static function guard_post( $action, $field = '_wpnonce' ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Keine Berechtigung.', 'altersverifikation' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( $action, $field );
	}
}
