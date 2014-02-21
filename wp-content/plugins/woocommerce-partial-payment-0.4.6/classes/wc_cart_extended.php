<?php

/**
 * Class for cart extension -- allows us to clone existing woocommerce cart
 */
class Woocommerce_Cart_Extended extends WC_Cart {

	/**
	 * Applies a coupon code passed to the method.
	 *
	 * @param string $coupon_code - The code to apply
	 * @return bool	True if the coupon is applied, false if it does not exist or cannot be applied
	 */
	public function add_discount( $coupon_code ) {
		global $woocommerce;

		// Coupons are globally disabled
		if ( ! $woocommerce->cart->coupons_enabled() )
			return false;

		// Sanitize coupon code
		$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_code );

		// Get the coupon
		$the_coupon = new WC_Coupon( $coupon_code );

		if ( $the_coupon->id ) {

			// Check it can be used with cart
			if ( ! $the_coupon->is_valid() ) {
				$woocommerce->add_error( $the_coupon->get_error_message() );
				return false;
			}

			// Check if applied
			if ( $this->has_discount( $coupon_code ) ) {
				$the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED );
				return false;
			}

			// If its individual use then remove other coupons
			if ( $the_coupon->individual_use == 'yes' ) {
				$this->applied_coupons = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $this->applied_coupons );
			}

			if ( $this->applied_coupons ) {
				foreach ( $this->applied_coupons as $code ) {

					$existing_coupon = new WC_Coupon( $code );

					if ( $existing_coupon->individual_use == 'yes' && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $existing_coupon, $this->applied_coupons ) ) {

						// Reject new coupon
						$existing_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY );

						return false;
					}
				}
			}

			$this->applied_coupons[] = $coupon_code;

			// Choose free shipping
			if ( $the_coupon->enable_free_shipping() ) {
				$woocommerce->session->chosen_shipping_method = 'free_shipping';
			}

			$this->calculate_totals();

			$the_coupon->add_coupon_message( WC_Coupon::WC_COUPON_SUCCESS );

			do_action( 'woocommerce_applied_coupon', $coupon_code );

			return true;

		} else {
			$the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_NOT_EXIST );
			return false;
		}
		return false;
	}

}