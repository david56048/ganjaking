<?php

class GP_Unique_ID extends GWPerk {

    public $version = GP_UNIQUE_ID_VERSION;
    public $min_gravity_perks_version = '1.2.8.3';
    public $min_gravity_forms_version = '2.0';
    public $field_obj;

    private static $instance = null;

    public static function get_instance( $perk_file ) {
        if( null == self::$instance )
            self::$instance = new self( $perk_file );
        return self::$instance;
    }

    public function init() {

        parent::init();

	    load_plugin_textdomain( 'gp-unique-id', false, basename( dirname( __file__ ) ) . '/languages/' );

	    $this->tooltips();

	    require_once( $this->get_base_path() . '/includes/class-gf-field-unique-id.php' );

        $this->field_obj = gp_unique_id_field();

    }

    protected function setup() {

        $this->create_tables();

    }

    protected function create_tables() {
		global $wpdb;

        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

        if( ! empty( $wpdb->charset ) ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }

        if( ! empty( $wpdb->collate ) ) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }

        $sql = "
			CREATE TABLE {$wpdb->prefix}gpui_sequence (
                form_id mediumint(8) unsigned not null,
                field_id smallint(5) unsigned not null,
                current int(10) unsigned not null,
                UNIQUE KEY form_field (form_id,field_id)
            ) $charset_collate;";

        if( function_exists( 'gf_upgrade' ) ) {
	        // Use GF's DB delta and get some common fixes for free.
	        gf_upgrade()->dbDelta( $sql );
        } else {
	        add_filter( 'dbdelta_create_queries', array( 'GFForms', 'dbdelta_fix_case' ) );
	        dbDelta( $sql );
	        remove_filter( 'dbdelta_create_queries', array( 'GFForms', 'dbdelta_fix_case' ) );
        }

    }

    public function uninstall() {
        global $wpdb;

        $this->drop_options();
        $this->drop_tables( $wpdb->prefix . 'gpui_sequence' );

    }

    public function tooltips() {

    	$this->add_tooltip(
		    $this->key( 'type' ),
		    sprintf( '<h6>%s</h6> %s', __( 'Unique ID Type', 'gp-unique-id' ), $this->get_unique_id_type_tooltip_content() )
	    );

	    $this->add_tooltip(
		    $this->key( 'prefix' ),
		    sprintf( '<h6>%s</h6> %s',
			    __( 'Unique ID Prefix', 'gp-unique-id' ),
			    sprintf( __( 'Prepend a short string to the beginning of the generated ID (i.e. %1$s%3$sabc%4$s123890678%2$s).', 'gp-unique-id' ), '<code>', '</code>', '<strong style="background-color:#fffbcc;">', '</strong>' )
		    )
	    );

	    $this->add_tooltip(
		    $this->key( 'suffix' ),
		    sprintf( '<h6>%s</h6> %s',
			    __( 'Unique ID Suffix', 'gp-unique-id' ),
			    sprintf( __( 'Append a short string to the end of the generated ID (i.e. %1$s123890678%3$sxyz%4$s%2$s).', 'gp-unique-id' ), '<code>', '</code>', '<strong style="background-color:#fffbcc;">', '</strong>' )
		    )
	    );

	    $this->add_tooltip(
		    $this->key( 'length' ),
		    sprintf( '<h6>%s</h6> %s',
			    __( 'Unique ID Length', 'gp-unique-id' ),
			    $this->get_unique_id_length_tooltip_content()
		    )
	    );

	    $this->add_tooltip(
		    $this->key( 'starting_number' ),
		    sprintf( '<h6>%s</h6> %s', __( 'Unique ID Starting Number', 'gp-unique-id' ), __( 'Set the starting number for sequential IDs; only available when "Sequential" type is selected.', 'gp-unique-id' ) )
	    );

	    $this->add_tooltip(
		    $this->key( 'reset' ),
		    sprintf( '<h6>%s</h6> %s',
			    __( 'Reset Starting Number', 'gp-unique-id' ),
			    __( 'Reset the sequence to the specified starting number when it is a lower number than the current sequence.<br /><br />By default, the starting number will only apply when the current sequence is lower than the specified starting number (i.e. if the current sequence is \'1\' and the starting number is \'99\', the sequence would be updated to \'99\').<br /><br />This option is useful after you have submitted a number of test entries and would like to reset the current sequence (i.e. if the current sequence is \'12\' and you would like to reset it to \'1\').', 'gp-unique-id' )
		    )
	    );

    }

	public function get_unique_id_type_tooltip_content() {

		$intro = __( 'Select the type of unique ID you would like to generate.', 'gp-unique-id' );
		$type_descriptions = array();

		foreach( $this->get_unique_id_types() as $type ) {
			$type_descriptions[] = sprintf( '<strong>%s</strong><br />%s', $type['label'], $type['description'] );
		}

		return $intro . '<ul style=\'margin-top:10px;\'><li>' . implode( '</li><li>', $type_descriptions ) . '</li></ul>';
	}

	public function get_unique_id_length_tooltip_content() {

		$intro = __( 'Set a specific length for the generated ID (excluding the prefix and suffix) or leave empty to use default length. There are some differences in length requirements for each ID type.', 'gp-unique-id' );

		$uid_types = $this->get_unique_id_types();
		$length_descriptions = array(
			'alphanumeric' => sprintf( '<strong>%s</strong><br />%s', $uid_types['alphanumeric']['label'], __( 'Requires a minimum length of <code>4</code>.', 'gp-unique-id' ) ),
			'numeric'      => sprintf( '<strong>%s</strong><br />%s', $uid_types['numeric']['label'], __( 'Requires a minimum length of <code>9</code> and a maximum length of <code>19</code>.', 'gp-unique-id' ) ),
			'sequential'   => sprintf( '<strong>%s</strong><br />%s', $uid_types['sequential']['label'], __( 'Length is used to pad the number with zeros (i.e. an ID of <code>1</code> with a length of <code>5</code> would be <code>00001</code>). There is no minimum length.', 'gp-unique-id' ) )
		);

		return $intro . '<ul style=\'margin-top:10px;\'><li>' . implode( '</li><li>', $length_descriptions ) . '</li></ul>';
	}

    public function get_unique_id_types() {

        $print_vars = array(
            '<code>',
            '</code>'
        );

        $uid_types = array(
            'alphanumeric' => array(
                'label'       => __( 'Alphanumeric', 'gp-unique-id' ),
                'description' => sprintf( __( 'Contains letters and numbers (i.e. %sa12z9%s).', 'gp-unique-id' ), $print_vars[0], $print_vars[1] )
            ),
            'numeric'      => array(
                'label'       => __( 'Numeric', 'gp-unique-id' ),
                'description' => sprintf( __( 'Contains only numbers (i.e. %s152315902%s).', 'gp-unique-id' ), $print_vars[0], $print_vars[1] )
            ),
            'sequential'   => array(
                'label'       => __( 'Sequential', 'gp-unique-id' ),
                'description' => sprintf( __( 'Contains only numbers and is sequential with previously generated IDs per field (i.e. %1$s1%2$s, %1$s2%2$s, %1$s3%2$s).', 'gp-unique-id' ), $print_vars[0], $print_vars[1] )
            )
        );

        return $uid_types;
    }

    public function get_unique( $form_id, $field, $length = 5, $atts = array(), $entry = false ) {

        $field_atts = array_filter( array(
            'type'            => $field->{$this->key( 'type' )},
            'starting_number' => $field->{$this->key( 'starting_number' )},
            'length'          => $field->{$this->key( 'length' )},
            'prefix'          => $field->{$this->key( 'prefix' )},
            'suffix'          => $field->{$this->key( 'suffix' )},
        ) );

        $atts = wp_parse_args( $field_atts, array(
            'type'            => 'alphanumeric', // also accepts 'numeric', 'sequential'
            'starting_number' => 1, // or any other positive integer
            'length'          => $length,
            'prefix'          => '',
            'suffix'          => ''
        ) );

	    // allow $form_id and $field_id to be overridden via 'gpui_unique_id_attributes' filter
	    $field_id = $field['id'];
	    $entry    = $entry === false ? GFFormsModel::get_current_lead() : $entry;

	    // Used to determine if length has been filtered and whether we should enforce our default minimums.
	    $unfiltered_length = intval( $atts['length'] );

        /**
         * Modify the attributes that will be used to generate a unique ID.
         *
         * @since 1.0.0
         *
         * @param array $atts {
         *     An array of attributes that will be used to generate the unique ID.
         *
         *     @type string $type The type of unique ID to generate: 'alphanumeric', 'numeric', 'sequential'.
         *     @type string $starting_number The number at which to start when creating a sequential unique ID.
         *     @type string $length The length of the unique ID.
         *     @type string $prefix A string of characters to be prepended to the unique ID.
         *     @type string $suffix A string of characters to be appended to the unique ID.
         * }
         * @param integer $form_id The ID of the form for which the unique ID is being generated.
         * @param integer $field_id The ID of the field for which the unique ID is being generated.
         *
         * @see https://gist.github.com/spivurno/a40ba89899a65659f708
         */
        $atts = gf_apply_filters( array( 'gpui_unique_id_attributes', $form_id, $field_id ), $atts, $form_id, $field_id );

        extract( $atts, EXTR_OVERWRITE ); // gives us $length, $type, and $starting_number

        $length = intval( $length );

        if( $type == 'sequential' ) {

            $starting_number = max( intval( $starting_number ), 1 );
            $unique = $this->get_sequential_unique_id( $form_id, $field_id, $starting_number );

            if( $length !== false ) {
                $unique = str_pad( $unique, $length, '0', STR_PAD_LEFT );
            }

            $unique = $prefix . $unique . $suffix;
            $unique = GFCommon::replace_variables( $unique, GFAPI::get_form( $form_id ), $entry, false, true, false, 'text' );
            $unique = apply_filters( 'gpui_unique_id', $unique, $form_id, $field_id );

        } else {

            for( $i = 0; $i <= 9; $i++ ) {

                switch( $type ) {
                    case 'alphanumeric':
                        $length = max( $length, 4 ); // gives us 1,413,720 possible unique IDs
                        $unique = '';
                        do {
                            $unique .= uniqid();
                        } while( strlen( $unique ) < $length );
                        $unique = substr( $unique, -$length );
                        break;
                    case 'numeric':
	                    // If length is filtered, don't force our minimum length.
	                    $length       = $length != $unfiltered_length ? $length : max( $length, apply_filters( 'gpui_numeric_minimum_length', 6 ) );
                        $length       = min( $length, 19 ); // maximum value for a 64-bit signed integer
                        $range_bottom = intval( str_pad( '1', $length, '0' ) );
                        $range_top    = intval( str_pad( '', $length, '9' ) );
                        $unique       = rand( $range_bottom, $range_top  );
                        break;
                }

                $unique    = $prefix . $unique . $suffix;
	            $unique    = GFCommon::replace_variables( $unique, GFAPI::get_form( $form_id ), $entry, false, true, false, 'text' );
                $unique    = apply_filters( 'gpui_unique_id', $unique, $form_id, $field_id );

                $is_unique = $this->check_unique( $unique, $form_id, $field_id );

                if( $is_unique ) {
                    break;
                } else {
                    $unique = false;
                }

            }

        }

        return $unique;
    }

    public function check_unique( $unique, $form_id, $field_id ) {
        global $wpdb;

	    if ( ! is_callable( array( 'GFFormsModel', 'get_database_version' ) ) || version_compare( GFFormsModel::get_database_version(), '2.3-dev-1', '<' ) ) {

	        $query = array(
	            'select' => 'SELECT ld.value',
	            'from'   => "FROM {$wpdb->prefix}rg_lead_detail ld",
	            'join'   => '',
	            'where'  => $wpdb->prepare( '
	                WHERE ld.form_id = %d
	                AND CAST( ld.field_number as unsigned ) = %d
	                AND ld.value = %s',
	                    $form_id, $field_id, $unique
	                )
	        );

		} else {
			
			// Get entry meta table name.
			$entry_meta_table = GFFormsModel::get_entry_meta_table_name();
			
	        $query = array(
	            'select' => 'SELECT entry.meta_value',
	            'from'   => "FROM {$entry_meta_table} entry",
	            'join'   => '',
	            'where'  => $wpdb->prepare( '
	                WHERE entry.form_id = %d
	                AND CAST( entry.meta_key as unsigned ) = %d
	                AND entry.meta_value = %s',
	                    $form_id, $field_id, $unique
	                )
	        );

		}

        $query  = apply_filters( 'gpui_check_unique_query', $query, $form_id, $field_id, $unique );
        $sql    = implode( ' ', $query );
        $result = $wpdb->get_var( $sql );

        $is_unique = empty( $result );

        return $is_unique;
    }

    public function get_sequential_unique_id( $form_id, $field_id, $starting_number = 1 ) {
        global $wpdb;

        $uid = gf_apply_filters( 'gpui_sequential_unique_id_pre_insert', array( $form_id, $field_id ), false, $form_id, $field_id, $starting_number );
        if( $uid !== false ) {
	        return $uid;
        }

        $sql = $wpdb->prepare(
            'INSERT INTO ' . $wpdb->prefix . 'gpui_sequence ( form_id, field_id, current ) VALUES ( %d, %d, ( @next := 1 ) ) ON DUPLICATE KEY UPDATE current = ( @next := current + 1 )',
            $form_id, $field_id
        );

        $wpdb->query( $sql );
        $uid = $wpdb->get_var( 'SELECT @next' );

        if( $uid >= 1 && $uid < $starting_number && $starting_number !== null ) {
            // set the starting number as one less than the actual starting number and then make a new request for the current sequence
            $this->set_sequential_starting_number( $form_id, $field_id, $starting_number - 1 );
            $uid = $this->get_sequential_unique_id( $form_id, $field_id, null );
        }

        return $uid;
    }

    public function set_sequential_starting_number( $form_id, $field_id, $starting_number ) {
        global $wpdb;

        $result = $wpdb->update( $wpdb->prefix . 'gpui_sequence', array( 'current' => $starting_number ), array( 'form_id' => $form_id, 'field_id' => $field_id ) );

        return $result;
    }

    public function documentation() {
        return array(
            'type' => 'url',
            'value' => 'http://gravitywiz.com/documentation/gp-unique-id/'
        );
    }

}

function gp_unique_id() {
    return GP_Unique_ID::get_instance( null );
}

function gp_unique_id_uninstall() {

}