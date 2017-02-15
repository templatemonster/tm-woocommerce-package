<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Tm_Woo_Product_Thumbnails' ) ) {

	/**
	 * Define Tm_Woo_Product_Thumbnails class
	 */
	class Tm_Woo_Product_Thumbnails {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Is loop trigger
		 *
		 * @var boolean
		 */
		private $is_loop = false;

		/**
		 * Is enabled trigger.
		 *
		 * @var boolean
		 */
		private $is_enabled = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			add_filter( 'woocommerce_before_shop_loop_item', array( $this, 'set_thumb_trigger' ), 0 );
			add_filter( 'woocommerce_after_shop_loop_item',  array( $this, 'clear_thumb_trigger' ), 999 );
			add_filter( 'post_thumbnail_html', array( $this, 'advanced_thumb' ), 10, 5 );

			add_filter( 'woocommerce_products_general_settings', array( $this, 'add_options' ) );
		}

		/**
		 * Set is_loop thumbnails trigger
		 */
		public function set_thumb_trigger() {
			$this->is_loop = true;
		}

		/**
		 * Clear is_loop thumbnails trigger
		 */
		public function clear_thumb_trigger() {
			$this->is_loop = false;
		}

		/**
		 * Add one more thumbnail for products in loop
		 *
		 * @param  [type] $html         [description]
		 * @param  [type] $post_id      [description]
		 * @param  [type] $thumbnail_id [description]
		 * @param  [type] $size         [description]
		 * @param  [type] $attr         [description]
		 * @return [type]               [description]
		 */
		public function advanced_thumb( $html, $post_id, $thumbnail_id, $size, $attr ) {

			if ( ! $this->is_thumb_required( $post_id ) ) {
				return $html;
			}

			global $post, $product, $woocommerce;

			if ( ! is_object( $product ) ) {
				return $html;
			}

			$attachment_ids = $product->get_gallery_attachment_ids();

			if ( empty( $attachment_ids[1] ) ) {
				return $html;
			}

			$effect         = get_option( 'tm_woo_thumb_effect', 'slide' );
			$additional_id  = $attachment_ids[1];
			$additional_img = wp_get_attachment_image( $additional_id, $size, false, $attr );

			return sprintf(
				'<div class="tm-thumbs-wrap effect-%3$s"><div class="tm-thumbs-wrap__inner">%1$s%2$s</div></div>',
				$html, $additional_img, $effect
			);
		}

		/**
		 * Check if thumbnails required
		 *
		 * @param  [type]  $post_id [description]
		 * @return boolean          [description]
		 */
		public function is_thumb_required( $post_id ) {

			if ( ! $this->is_loop || ! $this->is_enabled() ) {
				return false;
			}

			if ( 'product' !== get_post_type( $post_id ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Check thumb is enabled
		 *
		 * @return boolean [description]
		 */
		public function is_enabled() {

			if ( null !== $this->is_enabled ) {
				return $this->is_enabled;
			}

			$is_enabled = get_option( 'tm_woo_thumb_enabled', 'no' );

			if ( 'no' === $is_enabled ) {
				$this->is_enabled = false;
			} else {
				$this->is_enabled = true;
			}

			return $this->is_enabled;
		}

		/**
		 * Add thumb options
		 *
		 * @return boolean [description]
		 */
		public function add_options( $options ) {

			$options = array_merge( $options, array(

				array(
					'title' => __( 'Advanced Thumbnails', 'tm-woocommerce-package' ),
					'type'  => 'title',
					'id'    => 'tm_woo_advanced_thumb'
				),
				array(
					'title'    => __( 'Enable advanced thumbnails', 'tm-woocommerce-package' ),
					'desc'     => __( 'Check to enable thumbnails switch on hover', 'tm-woocommerce-package' ),
					'id'       => 'tm_woo_thumb_enabled',
					'default'  => 'no',
					'type'     => 'checkbox',
					'autoload' => yes,
				),
				array(
					'title'    => __( 'Thumbnails switch navigation', 'tm-woocommerce-package' ),
					'desc'     => __( 'Animation effect on thumbnails switch.', 'tm-woocommerce-package' ),
					'id'       => 'tm_woo_thumb_effect',
					'class'    => 'wc-enhanced-select',
					'default'  => 'slide',
					'type'     => 'select',
					'options'  => array(
						'slide'  => __( 'Slide', 'tm-woocommerce-package' ),
						'rotate' => __( 'Rotate', 'tm-woocommerce-package' ),
						'fade'   => __( 'Fade', 'tm-woocommerce-package' ),
					),
					'desc_tip' =>  true,
				),

				array(
					'type' 	=> 'sectionend',
					'id' 	=> 'tm_woo_advanced_thumb'
				),
			) );

			return $options;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

Tm_Woo_Product_Thumbnails::get_instance();
