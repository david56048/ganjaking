<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Freshdesk Forum Category Integration.
 *
 * @package  WC_Freshdesk_Forum_Category
 * @category Integration
 * @author   WooThemes
 */
class WC_Freshdesk_Forum_Category extends WC_Freshdesk_Abstract_Integration {

	/**
	 * Postmeta name for forum category data.
	 *
	 * @var string
	 */
	public $category_data = '_forum_category';

	/**
	 * Postmeta name for forum category id.
	 *
	 * @var string
	 */
	public $category_id = '_forum_category_id';

	/**
	 * Get a forum category data.
	 *
	 * @param  int   $post_id Post ID.
	 * @param  int   $category_id  Forum Category ID.
	 *
	 * @return void
	 */
	public function get_category( $post_id, $category_id ) {
		$url = esc_url( $this->url ) . 'discussions/categories/' . esc_attr( $category_id );
		$params = array(
			'method'  => 'GET',
			'timeout' => 60,
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
				'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':X' )
			)
		);

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Getting Forum category...' );
		}

		$response = wp_safe_remote_get( $url, $params );

		if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 && ( strcmp( $response['response']['message'], 'OK' ) == 0 ) ) {
			$response_data = json_decode( $response['body'] );

			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Gets Forum category successfully!' );
			}

			// Save post meta.
			update_post_meta( $post_id, $this->category_id, esc_attr( $category_id ) );
			$category_save_data = array(
				'enable'      => 'yes',
				'title'       => sanitize_text_field( $response_data->name ),
				'description' => wp_kses( $response_data->description, array() )
			);
			update_post_meta( $post_id, $this->category_data, $category_save_data );
		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Failed to get forum category: ' . print_r( $response, true ) );
			}

			update_post_meta( $post_id, '_integration_messages', sprintf( __( 'Failed to get Forum Category, the %s category does not exist.', 'woocommerce-freshdesk' ), esc_attr( $category_id ) ) );
		}
	}

	/**
	 * Create forum category.
	 *
	 * @param  int   $post_id Post ID.
	 * @param  array $data    Forum Category data.
	 *
	 * @return void
	 */
	protected function create_category( $post_id, $data ) {
		$url = esc_url( $this->url ) . 'discussions/categories';
		$params = array(
			'method'  => 'POST',
			'body'    => json_encode( $data ),
			'timeout' => 60,
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
				'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':X' )
			)
		);

		$response = wp_safe_remote_post( $url, $params );

		if ( ! is_wp_error( $response ) && $response['response']['code'] == 201 && ( strcmp( $response['response']['message'], 'Created' ) == 0 ) ) {
			$response_data = json_decode( $response['body'] );

			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Forum category synchronized successfully!' );
			}

			// Save post meta.
			update_post_meta( $post_id, $this->category_id, esc_attr( $response_data->id ) );
			$category_save_data = array(
				'enable'      => 'yes',
				'title'       => $data['forum_category']['name'],
				'description' => $data['forum_category']['description']
			);
			update_post_meta( $post_id, $this->category_data, $category_save_data );

		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Forum category synchronization failed: ' . print_r( $response, true ) );
			}

			update_post_meta( $post_id, '_integration_messages', __( 'Failed to create Forum Category.', 'woocommerce-freshdesk' ) );
		}
	}

	/**
	 * Update forum category.
	 *
	 * @param  int   $post_id     Post ID.
	 * @param  array $data        Forum category data.
	 * @param  int   $category_id Forum category ID.
	 *
	 * @return void
	 */
	protected function update_category( $post_id, $data, $category_id ) {
		$url = esc_url( $this->url ) . 'discussions/categories/' . $category_id;
		$params = array(
			'method'  => 'PUT',
			'body'    => json_encode( $data ),
			'timeout' => 60,
			'headers' => array(
				'Content-Type' => 'application/json;charset=UTF-8',
				'Authorization' => 'Basic ' . base64_encode( $this->api_key . ':X' )
			)
		);

		$response = wp_safe_remote_post( $url, $params );

		if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 && ( strcmp( $response['response']['message'], 'OK' ) == 0 ) ) {

			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Forum category synchronized successfully!' );
			}

			// Save post meta.
			$category_save_data = array(
				'enable'      => 'yes',
				'title'       => $data['forum_category']['name'],
				'description' => $data['forum_category']['description']
			);
			update_post_meta( $post_id, $this->category_data, $category_save_data );

		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Forum category synchronization failed: ' . print_r( $response, true ) );
			}

			update_post_meta( $post_id, '_integration_messages', __( 'Failed to update Forum Category.', 'woocommerce-freshdesk' ) );
		}
	}

	/**
	 * Sync Forum Category.
	 *
	 * @param  int    $post_id     Post/Product ID.
	 * @param  string $name        Category name/title.
	 * @param  string $description Category description.
	 *
	 * @return void
	 */
	public function sync_category( $post_id, $name, $description = '' ) {
		$category_id   = get_post_meta( $post_id, $this->category_id, true );
		$category_data = get_post_meta( $post_id, $this->category_data, true );

		$data = apply_filters( 'woocommerce_freshdesk_sync_forum_category_data', array(
			'forum_category' => array(
				'name'        => sanitize_text_field( $name ),
				'description' => wp_kses( $description, array() )
			)
		), $post_id );

		// Test with need create or update.
		if ( $category_data == array( 'enable' => 'yes', 'title' => $name, 'description' => $description ) ) {
			return;
		}

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Synchronizing the forum category for the product #' . intval( $post_id ) );
		}

		// If doesn't exists a category ID, then create a new one.
		if ( empty( $category_id ) ) {
			$this->create_category( $post_id, $data );
		} else {
			$this->update_category( $post_id, $data, $category_id );
		}
	}
}
