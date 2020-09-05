<?php

namespace wpbuddy\rich_snippets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


/**
 * Class Admin.
 *
 * Starts up all the frontend things.
 *
 * @package wpbuddy\rich_snippets
 *
 * @since   2.0.0
 */
class Frontend_Controller {

	/**
	 * If debug mode is on|off.
	 *
	 * @since 2.0.0
	 *
	 * @var bool
	 */
	protected $debug = false;


	/**
	 * If frontend caching is active.
	 *
	 * @since 2.11.0
	 *
	 * @var bool
	 */
	protected $caching = true;


	/**
	 * The caching time in hours.
	 *
	 * @since 2.11.0
	 *
	 * @var int
	 */
	protected $caching_time = 6;


	/**
	 * Current post ID.
	 *
	 * @since 2.0.0
	 *
	 * @var int
	 */
	protected $current_post_id = 0;


	/**
	 * If Values_Model has been initialized.
	 *
	 * @since 2.2.5
	 *
	 * @var bool
	 */
	protected $values_model_initialized = false;


	/**
	 * Magic method for setting up the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->debug        = defined( 'WPB_RS_DEBUG' ) && WPB_RS_DEBUG;
		$this->caching      = (bool) get_option( 'wpb_rs/setting/frontend_caching', true ) && ! $this->debug;
		$this->caching_time = intval( get_option( 'wpb_rs/setting/frontend_caching_time', 6 ) );

		if ( ! get_option( base64_decode( 'd3BiX3JzL3ZlcmlmaWVk' ), false ) ) {
			return;
		}

		add_action( 'wp', array( $this, 'set_object_vars' ), 10, 1 );

		if ( (bool) get_option( 'wpb_rs/setting/snippets_in_footer', true ) ) {
			add_action( 'wp_footer', array( $this, 'print_snippets' ) );
			add_action( 'amp_post_template_head', array( $this, 'print_snippets' ) );
		} else {
			add_action( 'wp_head', array( $this, 'print_snippets' ) );
			add_action( 'amp_post_template_footer', array( $this, 'print_snippets' ) );
		}

		add_filter( 'post_class', array( $this, 'remove_hentry' ) );

		add_filter( 'comment_class', array( $this, 'remove_vcard' ) );

		add_action( 'woocommerce_init', array( $this, 'remove_wc_structured_data' ) );

		add_action( 'init', array( $this, 'remove_yoast_structured_data' ) );

		add_action( 'admin_bar_menu', array( $this, 'check_page_menu' ), 150 );

		/**
		 * Frontend init hook.
		 *
		 * Allow other plugins to perform any actions.
		 *
		 * @hook  wpbuddy/rich_snippets/frontend/init
		 *
		 * @param {Frontend_Controller} $frontend
		 *
		 * @since 2.0.0
		 *
		 */
		do_action_ref_array( 'wpbuddy/rich_snippets/frontend/init', array( &$this ) );
	}


	/**
	 * Set objects vars of this class.
	 *
	 * @param \WP $wp
	 *
	 * @since 2.1.1
	 *
	 */
	public function set_object_vars( $wp ) {

		/**
		 * Fetch the current post ID.
		 */
		$this->current_post_id = Helper_Model::instance()->get_current_post_id();
	}

	/**
	 * Prints the schema.
	 *
	 * @since 2.0.0
	 */
	public function print_snippets() {

		/**
		 * Check for snippets attached to a single post.
		 */
		if ( $this->singular_has_snippet( $this->current_post_id ) ) {
			$this->print_rich_snippets( $this->current_post_id );
		}
	}


	/**
	 * Prints rich snippets.
	 *
	 * @param int $post_id The post ID where snippets were defined.
	 *
	 * @since 2.0.0
	 */
	public function print_rich_snippets( int $post_id ) {

		echo '<!--';
		_e(
			'Code generated by SNIP (Structured Data Plugin) for WordPress. See rich-snippets.io for more information.',
			'rich-snippets-schema'
		);
		printf( __( 'Post ID is %d.', 'rich-snippets-schema' ), $post_id );
		echo '-->';

		if ( $this->caching ) {
			$cache = get_transient( Cache_Model::get_cache_key() );
		} else {
			$cache = [];
		}

		if ( is_array( $cache )
		     && isset( $cache[ $post_id ] )
		     && isset( $cache[ $post_id ]['snippets'] )
		     && is_array( $cache[ $post_id ]['snippets'] )
		) {
			echo implode( '', $cache[ $post_id ]['snippets'] );

			return;
		}

		if ( ! $this->values_model_initialized ) {
			# Init value hooks
			$this->init_values_model();
		}

		$rich_snippets = Snippets_Model::get_snippets( $post_id );

		$cache = ! is_array( $cache ) ? [] : $cache;

		foreach ( $rich_snippets as $snippet_uid => $rich_snippet ) {
			$rich_snippet->prepare_for_output( array(
				'current_post_id' => $this->current_post_id,
				'snippet_post_id' => $post_id,
				'input'           => 'snippet_' . $post_id . '_'
			) );

			if ( ! $rich_snippet->has_properties() ) {
				continue;
			}

			$output = sprintf(
				'<script data-snippet_id="%s" type="application/ld+json">%s</script>',
				esc_attr( $snippet_uid ),
				$rich_snippet->__toString()
			);

			$cache[ $post_id ]['snippets'][ $snippet_uid ] = $output;

			echo $output;
		}

		if ( $this->caching ) {
			set_transient( Cache_Model::get_cache_key(), $cache, $this->caching_time * HOUR_IN_SECONDS );
		}
	}


	/**
	 * Checks if a singular post has snippets.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 * @since 2.0.0
	 *
	 */
	public function singular_has_snippet( $post_id ): bool {

		return count( Snippets_Model::get_snippets( $post_id ) ) > 0;
	}


	/**
	 * Removes "hentry" class from post classes.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 2.0.0
	 *
	 */
	public function remove_hentry( $classes ) {

		$k = array_search( 'hentry', $classes );
		if ( false !== $k && (bool) get_option( 'wpb_rs/setting/remove_hentry', true ) ) {
			unset( $classes[ $k ] );
		}

		return $classes;
	}


	/**
	 * Removes vcard CSS classes from comments.
	 *
	 * @param array $classes
	 *
	 * @return array
	 * @since 2.0.0
	 *
	 */
	public function remove_vcard( $classes ) {

		$k = array_search( 'vcard', $classes );
		if ( false !== $k && (bool) get_option( 'wpb_rs/settings/remove_vcard', true ) ) {
			unset( $classes[ $k ] );
		}

		return $classes;
	}


	/**
	 * Maybe deactivates WooCommerce structured data.
	 *
	 * @since 2.0.0
	 */
	public function remove_wc_structured_data() {

		$deactivate = (bool) get_option( 'wpb_rs/setting/remove_wc_schema', false );

		if ( $deactivate ) {
			remove_action( 'wp_footer', array( WC()->structured_data, 'output_structured_data' ), 10 );
		}
	}


	/**
	 * Remove Structured Data generated by Yoast SEO.
	 *
	 * @since 2.11.0
	 */
	public function remove_yoast_structured_data() {
		$deactivate = (bool) get_option( 'wpb_rs/setting/remove_yoast_schema', false );

		if ( $deactivate ) {
			add_filter( 'wpseo_json_ld_output', '__return_false' );
		}
	}


	/**
	 * Initializes values model.
	 *
	 * @since 2.19.0
	 */
	public function init_values_model() {
		if ( ! $this->values_model_initialized ) {
			new Values_Model();
		}

		$this->values_model_initialized = true;
	}


	/**
	 * Add new admin bar menu option.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar
	 *
	 * @since 2.1.9.0
	 */
	public function check_page_menu( $wp_admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$wp_admin_bar->add_menu( array(
			'id'     => 'snip-test-link',
			'parent' => null,
			'group'  => null,
			'title'  => __( 'Test Structured Data', 'rich-snippets-schema' ),
			'href'   => add_query_arg( [
				'url'        => ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
				'user_agent' => 2,
			], 'https://search.google.com/test/rich-results' ),
			'meta'   => [
				'title'  => __( 'Test the current pages Structured Data', 'rich-snippets-schema' ),
				'target' => '_blank'
			]
		) );
	}

}
