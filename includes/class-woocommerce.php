<?php
/**
 * WooCommerce-Integration: Gate auf bestimmte Produktkategorien beschränken (Pro).
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Erweitert die Gate-Logik für WooCommerce-Produktkategorien (nur Pro).
 */
class Woocommerce {

	/**
	 * Hooks registrieren – nur wenn Pro aktiv und WooCommerce vorhanden.
	 */
	public function hooks() {
		if ( ! Helpers::is_pro() || ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_filter( 'avf_should_gate', array( $this, 'filter_should_gate' ) );
	}

	/**
	 * Gibt true zurück, wenn das aktuelle Produkt in einer der konfigurierten Kategorien liegt.
	 *
	 * @param bool $should_gate Aktueller Gate-Status aus vorherigen Filtern.
	 * @return bool
	 */
	public function filter_should_gate( $should_gate ) {
		// Wenn bereits true: nichts zu tun.
		if ( $should_gate ) {
			return true;
		}

		if ( ! is_product() ) {
			return $should_gate;
		}

		$wc_categories = (array) Helpers::get( 'wc_categories' );
		if ( empty( $wc_categories ) ) {
			return $should_gate;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $should_gate;
		}

		return has_term( array_map( 'absint', $wc_categories ), 'product_cat', $post_id );
	}
}
