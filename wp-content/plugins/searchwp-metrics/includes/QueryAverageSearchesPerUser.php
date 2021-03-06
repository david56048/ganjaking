<?php

namespace SearchWP_Metrics;

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class QueryAverageSearchesPerUser
 * @package SearchWP_Metrics
 */
class QueryAverageSearchesPerUser extends Query {

	protected $engine;
	protected $after;
	protected $before;

	private $defaults = array(
		'engine' => 'default',
		'after'  => '30 days ago',
		'before' => 'now',
	);


	function __construct( $args = array() ) {
		parent::__construct( $args );

		$args = wp_parse_args( $args, $this->defaults );

		$args = apply_filters( 'searchwp_metrics_query_average_searches_per_user_args', $args );

		if ( is_array( $args ) ) {
			foreach ( $args as $property => $val ) {
				$this->__set( $property, $val );
			}
		}
	}

	function set_sql_fields() {
		$this->sql['select'] = "SELECT `{$this->tables['searches']}`.`uid`";
	}

	function set_sql_from() {
		$this->sql['from'] = "FROM `{$this->tables['searches']}`";
	}

	function set_sql_join() {}

	function set_sql_where() {
		parent::set_sql_where();
	}

	function set_sql_group_by() {
		$this->sql['group_by'] = "GROUP BY `{$this->tables['searches']}`.`query`, `{$this->tables['searches']}`.`uid`";
	}

	function set_sql_having() {}

	function set_sql_order_by() {}
}
