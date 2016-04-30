<?php
/**
 * Displays WebDevStudios products in a sidebar on the add/edit screens for post types and taxonomies.
 *
 * @package    BuddyExtender
 * @subpackage ProductSidebar
 * @author     WebDevStudios
 * @since      1.0.0
 */

if ( ! function_exists( 'bpextender_products_sidebar' ) ) {

	/**
	 * Displays WebDevStudios products in a sidebar on the add/edit screens for post types and taxonomies.
	 * We hope you don't mind.
	 *
	 * @since 1.0.0
	 *
	 * @internal
	 */
	function bpextender_products_sidebar() {
		if ( false === ( $ads = get_transient( 'pluginize_promos' ) ) ) {
			if ( 200 === wp_remote_retrieve_response_code( $ads ) ) {
				$ads = json_decode( wp_remote_retrieve_body( $ads ) );
				set_transient( 'pluginize_promos', $ads, DAY_IN_SECONDS );
			}
		}

		if ( ! empty( $ads ) ) {
			echo '<div class="pluginizepromos">';
			foreach ( $ads->buddyextender->sidebar as $ad ) {
				$the_ad = $ad->text;
				$image  = wp_remote_get( $ad->image );
				if ( 200 === wp_remote_retrieve_response_code( $image ) ) {
					$the_ad = sprintf(
						'<img src="%s" alt="%s">',
						$ad->image,
						$ad->text
					);
				}

				printf(
					'<a href="%s">%s</a>',
					$ad->url,
					$the_ad
				);
			}
			echo '</div>';

		}
	}
}
