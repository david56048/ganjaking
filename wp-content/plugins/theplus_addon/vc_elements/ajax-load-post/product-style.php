<?php 
	$category_filter='';
		if($filter_category=='true'){				
			$terms = get_the_terms( $loop->ID,$texonomy_category);
			if ( $terms != null ){
				foreach( $terms as $term ) {
					$category_filter .=' '.$term->slug.' ';
					unset($term);
				}
			}
		}
		$data_attr='';		
		echo '<div class="grid-item metro-item'.esc_attr($i).' '.esc_attr($style).' '.esc_attr($desktop_class).' '.esc_attr($tablet_class).' '.esc_attr($mobile_class).' '.esc_attr($animated_columns).' '.esc_attr($category_filter).' ">';
			
			if(!empty($style)){
			$product_style=$style;
				include THEPLUS_PLUGIN_PATH. 'vc_elements/products/product.php'; 
			}

		echo '</div>';