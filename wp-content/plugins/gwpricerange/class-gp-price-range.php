<?php 

class GP_Price_Range extends GWPerk {
    
    function init() {
        
        $this->enqueue_field_settings();
        $this->add_tooltip( "{$this->slug}_price_range", '<h6>' . __( 'Price Range', 'gp-price-range' ) . '</h6>' . __( 'Specify a minimum and/or maximum price users can enter for this field.', 'gp-price-range' ) );

        add_filter( 'gform_field_validation', array( $this, 'range_validation' ), 10, 4 );

    }
    
    function field_settings_ui() {
        ?>
        
        <li class="price_range_setting gwp_field_setting field_setting">
            <label style="clear:both;" class="section_label"><?php _e('Price Range', 'gravityperks'); ?> <?php gform_tooltip("{$this->slug}_price_range"); ?></label>
            <div style="width:90px; float:left;">
                <input type="text" onkeyup="SetFieldProperty('priceRangeMin', gperk.cleanPriceRange(this.value));" size="10" id="price_range_min">
                <label for="price_range_min"><?php _e('Min', 'gravityperks'); ?></label>
            </div>
            <div style="width:90px; float:left;">
                <input type="text" onkeyup="SetFieldProperty('priceRangeMax', gperk.cleanPriceRange(this.value));" size="10" id="price_range_max">
                <label for="price_range_max"><?php _e('Max', 'gravityperks'); ?></label>
            </div>
            <br class="clear" />
        </li>
        
        <?php
    }

    function field_settings_js() {
        ?>
        <script type="text/javascript">
        
        fieldSettings['price'] += ", .price_range_setting";
        
        jQuery(document).bind('gwsFieldTabSelected', function(event) {
            
            var currency = GetCurrentCurrency();
            
            jQuery('#price_range_min').val(field.priceRangeMin ? currency.toMoney(field.priceRangeMin) : '');
            jQuery('#price_range_max').val(field.priceRangeMax ? currency.toMoney(field.priceRangeMax) : '');
            
            jQuery('.price_range_setting input').blur(function() {
                
                var number = jQuery(this).val();
                var price = currency.toMoney(number);
                   
                   console.log( number, price );
                    
                if(price)
                    jQuery(this).val(price);
                
            });
            
        });
        
        gperk.cleanPriceRange = function(value) {
            
            var currency = GetCurrentCurrency();
            var price = currency.toMoney(value);
            
            return price ? currency.toNumber(price) : '';
        }
        
        </script>
        <?php
        }

	/**
	 * @param $result
	 * @param $value
	 * @param $form
	 * @param GF_Field $field
	 *
	 * @return mixed
	 */
    function range_validation( $result, $value, $form, $field ) {
            
		if ( $field->get_input_type() !== 'price' ) {
			return $result;
		}

		if( rgblank( $value ) ) {
			return $result;
		}

		$price = GFCommon::to_number( $value );
		$min = $field->priceRangeMin;
		$max = $field->priceRangeMax;

		if( ( $min && $price < $min ) || ( $max && $price > $max ) ) {

			$result['is_valid'] = false;
			$messages = $this->get_validation_messages( $min, $max, $form['id'], $field->id );

			if ( $min && $max ) {
				$result['message'] = $messages['min_and_max'];
			} else if ( $min ) {
				$result['message'] = $messages['min'];
			} else if ( $max ) {
				$result['message'] = $messages['max'];
			}
		}

		return $result;
    }

    public function get_validation_messages( $min, $max, $form_id, $field_id ) {

        $messages = array(
	        'min_and_max' => sprintf( __( 'Please enter a price between <strong>%s</strong> and <strong>%s</strong>.' ), GFCommon::to_money( $min ), GFCommon::to_money( $max ) ),
	        'min'         => sprintf( __( 'Please enter a price greater than or equal to <strong>%s</strong>.' ), GFCommon::to_money( $min ) ),
	        'max'         => sprintf( __( 'Please enter a price less than or equal to <strong>%s</strong>.' ), GFCommon::to_money( $max ) ),
        );

        return gf_apply_filters( array( 'gppr_validation_messages', $form_id, $field_id ), $messages, $min, $max, $form_id, $field_id );
    }
    
}   

class GWPriceRange extends GP_Price_Range { }