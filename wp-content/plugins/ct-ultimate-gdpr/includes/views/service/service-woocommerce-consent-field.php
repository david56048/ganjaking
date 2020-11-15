<?php

/**
 * The template for displaying WooCommerce service view in wp-admin
 *
 * You can overwrite this template by copying it to yourtheme/ct-ultimate-gdpr/service folder
 *
 * @version 1.0
 *
 */

?>

<input class="ct-ultimate-gdpr-consent-field" type="checkbox" name="ct-ultimate-gdpr-consent-field" required id="ct-ultimate-gdpr-consent-field-woocommerce"/>
<label for="ct-ultimate-gdpr-consent-field-woocommerce">
	<?php echo esc_html__( 'I consent to the storage of my data according to the Privacy Policy', 'ct-ultimate-gdpr' ); ?>
</label>