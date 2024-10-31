<?php
/**
 * Plugin Name: RomanCart Ecommerce
 * Plugin URI: https://www.romancart.com/
 * Description: Add Buy Buttons, Widgets or an entire Storefront to your pages and sell products, tickets and downloads in minutes.
 * Version: 2.0.7
 * Author: RomanCart Development
 * Author URI: https://www.romancart.com
 * License: ROC_LICENSE
 */

/*  Copyright 2015-2022  RomanCart Development  (email : support@romancart.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

// admin menu, page
add_action('admin_menu', 'romancart_admin');
function romancart_admin() {
	add_options_page('RomanCart', 'RomanCart', 'manage_options', 'plugin', 'romancart_adminpage');
}

function romancart_adminpage() {
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$ROC_optName = 'ROC_storeId';
	$ROC_fieldName = 'ROC_storeId';
	$ROC_nonceField = 'ROC_nonce';
	$ROC_nonceId = 'ROC_nonce';

	$ROC_optVal = get_option( $ROC_optName );

	if (isset($_POST[$ROC_nonceField]) 
		&& wp_verify_nonce($_POST[$ROC_nonceField], $ROC_nonceId)
	) {
		if (ctype_digit($_POST[$ROC_fieldName])) {
			$ROC_optVal = strval(intval($_POST[$ROC_fieldName]));
			update_option( $ROC_optName, $ROC_optVal );
		}
?>
		<div class="updated"><p><strong><?php esc_html_e('settings saved.', 'romancart-ecommerce' ); ?></strong></p></div>
<?php
	}
	echo '<div class="wrap">';
	echo "<h2>" . esc_html(__( 'RomanCart Ecommerce Settings', 'romancart-ecommerce' )) . "</h2>";
?>
<p><b>Enter your store ID from RomanCart in the box below and click on 'Save Changes'</b></p>
<p>Use the following shortcodes to add buttons, links, widgets and storefront to your website</p>

<b>For the fastest way to start selling on your website simply use the following button shortcode:<br>
[romancart_button itemname='A Great Product' price=55]</b><br>
Change the itemname and price to your own product and then you can start selling right away!<br><br>
For a complete storefront you will need to set up your products on the RomanCart Product Manager first, then you can use :<br>
<b>[romancart_storefront]</b><br>
<br>
You can use the catid to open the storefront on a particular category<br>
<b>[romancart_storefront catid=2]</b><br>
<br>
For buy now buttons use:<br>
<b>[romancart_button itemname='A Great Product' price=55]</b><br>
<br>
If you are using the RomanCart Product Manager and have entered your product details onto RomanCart - use the itemcode instead of itemname and price<br>
<b>[romancart_button itemcode='product1']</b><br>
<br>
For Hyperlinks use <br>
<b>[romancart_link itemname='A Great Product' price=55]</b><br>
<br>
You can use the same parameters as with the buttons.<br>
The full list of parameters are<br>
itemname<br>
itemcode<br>
price<br>
bltext - text for button or link<br>
blclass - specify a class for the button or hyperlink<br>
<br>
For Widgets use<br>
<b>[romancart_widget productid=1]</b><br>
Get the productid from the product properties on the RomanCart Product Manager. Click on the itemcode and it is at the top of the properties on the right.<br>
If you have more than one widget on the page then you need to give each one a number. (The first one does not need a number).<br>
<br>
<b>[romancart_widget productid=12 widgetid=2]</b><br>
<br>
Note that you define the actual cart pages on RomanCart as they are hosted pages. Log onto RomanCart at <a href='https://www.romancart.com' target='RomanCart'>https://www.romancart.com</a><br><br>
<br>
	<form name="ROC_SettingsForm" method="post" action="">
	<input type="hidden" name="<?php echo esc_attr($ROC_nonceField) ?>" value="<?php echo esc_attr(wp_create_nonce($ROC_nonceId)) ?>">
	<p><?php esc_html_e("Store ID:", 'romancart-ecommerce' ); ?> 
		<input type="text" name="<?php echo esc_attr($ROC_fieldName) ?>" value="<?php echo esc_attr($ROC_optVal) ?>" size="20">
	</p><hr />
	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>
	</form></div>
<?php
}

// Extended subscription function with subscription type variable
function romancart_storefront_shortcode( $atts ) {

	if (!is_admin()) {
		$ROC_storeId = get_option( 'ROC_storeId' );
		$ROC_atts=shortcode_atts( array(
			'catid' => ''
		), $atts);
	  
		$ROC_pageCode = "<div id='ROC_cart'></div>\n";
		$ROC_pageCode .= "<div id='ROC_catnav'></div>\n";
		$ROC_pageCode .= "<div id='ROC_display'><div style='text-align:center'><img id='ROC_loading_image' src='https://remote.romancart.com/rmimages/loading.gif' style='margin-top:250px'></div></div>\n";
			$ROC_jsUrl = "https://remote.romancart.com/display.asp?storeid=".$ROC_storeId."&catnav=ok&categoryid=".$ROC_atts['catid']."&cart=ok";
	
		if ( $screen->parent_base == 'edit' ) {
			//dont do it
		} else {
			wp_enqueue_script('romancart',$ROC_jsUrl,array(),'',true);
		}
		return $ROC_pageCode;
	}
}

add_shortcode( 'romancart_storefront', 'romancart_storefront_shortcode' );

function romancart_button_shortcode( $atts ) {
	if (!is_admin()) {
		$ROC_storeId = get_option( 'ROC_storeId' );
		$ROC_atts=shortcode_atts( array(
			'itemcode' => '',
			'itemname' => '',
			'price' => '',
			'bltext' => 'Add to Basket',
			'blclass' => ''
		), $atts);
	  
		$blclass="";
		if ($ROC_atts['blclass']<>'') {
			$blclass=" class='".$ROC_atts['blclass']."'";
		} 
		
		if ($ROC_atts['itemname']<>'') {
			$ROC_buttonCode = "<form action='https://www.romancart.com/cart.asp?storeid=".$ROC_storeId."&itemname=".$ROC_atts['itemname']."&price=".$ROC_atts['price']."'><input ".$blclass." type=submit value='".$ROC_atts['bltext']."'><input type=hidden name=storeid value='".$ROC_storeId."'><input type=hidden name=itemname value='".$ROC_atts['itemname']."'><input type=hidden name=price value='".$ROC_atts['price']."'></form>";
		} else {
			$ROC_buttonCode = "<form action='https://www.romancart.com/cart.asp?storeid=".$ROC_storeId."&itemname=".$ROC_atts['itemname']."&price=".$ROC_atts['price']."'><input ".$blclass." type=submit value='".$ROC_atts['bltext']."'><input type=hidden name=storeid value='".$ROC_storeId."'><input type=hidden name=itemcode value='".$ROC_atts['itemcode']."'></form>";
		}
		
	
	    return $ROC_buttonCode;
	}
}

add_shortcode( 'romancart_button', 'romancart_button_shortcode' );

function romancart_link_shortcode( $atts ) {
	if (!is_admin()) {
		$ROC_storeId = get_option( 'ROC_storeId' );
	    $ROC_atts=shortcode_atts( array(
	        	'itemcode' => '',
			'itemname' => '',
			'price' => '',
			'bltext' => 'Add to Basket',
			'blclass' => ''
    	), $atts);
  
		$blclass="";
		if ($ROC_atts['blclass']<>'') {
			$blclass=" class='".$ROC_atts['blclass']."'";
		} 
	
		//must be a link
		if ($ROC_atts['itemname']<>'') {
			$ROC_linkCode = "<a href='https://www.romancart.com/cart.asp?storeid=".$ROC_storeId."&itemname=".$ROC_atts['itemname']."&price=".$ROC_atts['price']."'".$blclass.">".$ROC_atts['bltext']."</a>";
		} else {
			$ROC_linkCode = "<a href='https://www.romancart.com/cart.asp?storeid=".$ROC_storeId."&itemcode=".$ROC_atts['itemcode']."' ".$blclass.">".$ROC_atts['bltext']."</a>";
		}
		
	    return $ROC_linkCode;
	}
}

add_shortcode( 'romancart_link', 'romancart_link_shortcode' );

function romancart_widget_shortcode( $atts ) {
	if (!is_admin()) {
		$ROC_storeId = get_option( 'ROC_storeId' );
		$ROC_atts=shortcode_atts( array(
			'productid' => '0',
			'widgetid' => ''
		), $atts);
	
		if ($ROC_atts['widgetid']='') {
			$ROC_widgetCode = "<div id='ROC_widget'></div><script>ROC_buttonWidget('ROC_widget','".$ROC_storeId."',".$ROC_atts['productid'].",0);</script>";
		} else {
			$ROC_widgetCode = "<div id='ROC_widget".$ROC_atts['widgetid']."'></div><script>ROC_buttonWidget('ROC_widget','".$ROC_storeId."',".$ROC_atts['productid'].",0);</script>";
		}
		
	    return $ROC_widgetCode;
	}
}

add_shortcode( 'romancart_widget', 'romancart_widget_shortcode' );

//Force the JS into the header
function romancart_script_init() {
	if (!is_admin()) {
		if ( shortcode_exists( 'romancart_widget' ) ) {
			$ROC_storeId = get_option( 'ROC_storeId' );
	    	$ROC_jsUrl = "https://remote.romancart.com/js/roc_button.asp?storeid=" . $ROC_storeId;
	     	wp_enqueue_script('romancart_widget_js',$ROC_jsUrl,array(),'',false);
	    }
	}
}

add_action('wp_enqueue_scripts', 'romancart_script_init');

?>