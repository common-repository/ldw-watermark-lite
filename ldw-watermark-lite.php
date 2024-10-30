<?php
/*
Plugin Name:	LDW Watermark Lite
Description:	Watermark your images on the fly - without altering the originals!
Version:    	1.1.0
Author:     	Lake District Walks
Text Domain: 	ldw-watermark-lite
Domain Path:	/lang
*/

add_action( 'admin_menu', 'ldw_watermark_lite_add_admin_menu' );
add_action( 'admin_init', 'ldw_watermark_lite_settings_init' );
register_deactivation_hook( __FILE__, 'ldw_watermark_lite_deactivate' );

function ldw_watermark_lite_deactivate(  ) { 
	$path = wp_upload_dir();
	$upload_dir = trailingslashit($path['basedir']);
	unlink($upload_dir . ".htaccess");
}

function ldw_watermark_lite_add_admin_menu(  ) { 
	$page = add_options_page( 'LDW Watermark Lite', 'LDW Watermark Lite', 'manage_options', 'ldw_watermark_lite', 'ldw_watermark_lite_options_page' );
    add_action( 'admin_print_styles-' . $page, 'ldw_watermark_lite_admin_styles' );
}

function ldw_watermark_lite_admin_styles() {
       wp_enqueue_style( 'ldw_watermark_lite_stylesheet' );
}

function ldw_watermark_lite_settings_init(  ) { 
    wp_register_style( 'ldw_watermark_lite_stylesheet', plugins_url('style.css', __FILE__) );
	register_setting( 'ldw_watermark_lite_plugin_page', 'ldw_watermark_lite_sizes' );
	add_settings_section(
		'ldw_watermark_lite_plugin_page_section1', 
		__( 'Image Sizes To Watermark', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_sizes_section_callback', 
		'ldw_watermark_lite_plugin_page'
	);
	add_settings_field( 
		'ldw_watermark_lite_checkbox_field_0', 
		__( 'Original Image', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_checkbox_field_0_render', 
		'ldw_watermark_lite_plugin_page', 
		'ldw_watermark_lite_plugin_page_section1',
		'original'
	);
	$sizes = ldw_watermark_lite_get_image_sizes();
	$i = 1;
	foreach ($sizes as $key => $value) {
		if ($value['width'] && $value['height'] != "0") {
			add_settings_field( 
				'ldw_watermark_lite_checkbox_field_' . $i, 
				__( 'Width: ' . $value['width'] . ' / Height: ' . $value['height'], 'ldw-watermark-lite' ), 
				'ldw_watermark_lite_checkbox_field_' . $i . '_render', 
				'ldw_watermark_lite_plugin_page', 
				'ldw_watermark_lite_plugin_page_section1',
				$value['width'] . 'x' . $value['height']
			);
		}
		$i++;
		if ($i > 9) {
			break;
		}
	}
	register_setting( 'ldw_watermark_lite_plugin_page', 'ldw_watermark_lite_label' );
	add_settings_section(
		'ldw_watermark_lite_plugin_page_section7', 
		__( 'Watermark Text', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_label_section_callback', 
		'ldw_watermark_lite_plugin_page'
	);
	add_settings_field( 
		'ldw_watermark_lite_text_field_0', 
		__( 'Maximum 64 Characters', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_text_field_0_render', 
		'ldw_watermark_lite_plugin_page', 
		'ldw_watermark_lite_plugin_page_section7'
	);
	register_setting( 'ldw_watermark_lite_plugin_page', 'ldw_watermark_lite_image', 'ldw_watermark_lite_image_check' );
	add_settings_section(
		'ldw_watermark_lite_plugin_page_section9', 
		__( 'Watermark Image', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_image_section_callback', 
		'ldw_watermark_lite_plugin_page'
	);
	add_settings_field( 
		'ldw_watermark_lite_file_field_0', 
		__( '', 'ldw-watermark-lite' ), 
		'ldw_watermark_lite_file_field_0_render', 
		'ldw_watermark_lite_plugin_page', 
		'ldw_watermark_lite_plugin_page_section9'
	);
}

function ldw_watermark_lite_image_check(  ) {
	$label = get_option( 'ldw_watermark_lite_label' );
	$font = get_option( 'ldw_watermark_lite_font' );
	if ($label['ldw_watermark_lite_text_field_0'] != '') {
		$font = plugin_dir_path( __FILE__ ) . 'fonts/DejaVuSans.ttf';
		$font_size = 40;
		$font_angle = 0;
		$transparency = 0;
		$the_box = ldw_watermark_lite_calculate_text_box($label['ldw_watermark_lite_text_field_0'], $font, $font_size, $font_angle);
		$text_padding = 2;
		$width = $the_box["width"] + $text_padding; 
		$height = $the_box["height"] + $text_padding; 
		$im = imagecreatetruecolor($width, $height);
		imagesavealpha($im, true);
		imagealphablending($im, false);
		$white = imagecolorallocate($im, 255, 255, 255);
		$black = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $black);
		imagettftext($im, $font_size, 0, $the_box["left"] + ($width / 2) - ($the_box["width"] / 2), $the_box["top"] + ($height / 2) - ($the_box["height"] / 2), $white, $font, $label['ldw_watermark_lite_text_field_0']);
		$plugin_path = plugin_dir_path( __FILE__ );
		$plugin_dir = basename($plugin_path);
		$path = wp_upload_dir();
		$upload_dir = trailingslashit($path['basedir']);
		imagepng($im, $upload_dir . $plugin_dir . '/watermarks/ldw_watermark_text.png', 0);
		imagedestroy($im);
	}
	$new_name = "ldw_watermark_text.png";
	return $new_name;
}

function ldw_watermark_lite_checkbox_field_0_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_0]' <?php checked( $options['ldw_watermark_lite_checkbox_field_0'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_1_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_1]' <?php checked( $options['ldw_watermark_lite_checkbox_field_1'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_2_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_2]' <?php checked( $options['ldw_watermark_lite_checkbox_field_2'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_3_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_3]' <?php checked( $options['ldw_watermark_lite_checkbox_field_3'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_4_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_4]' <?php checked( $options['ldw_watermark_lite_checkbox_field_4'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_5_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_5]' <?php checked( $options['ldw_watermark_lite_checkbox_field_5'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_6_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_6]' <?php checked( $options['ldw_watermark_lite_checkbox_field_6'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}
function ldw_watermark_lite_checkbox_field_7_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_7]' <?php checked( $options['ldw_watermark_lite_checkbox_field_7'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_8_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_8]' <?php checked( $options['ldw_watermark_lite_checkbox_field_8'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_checkbox_field_9_render( $args ) { 
	$options = get_option( 'ldw_watermark_lite_sizes' );
	?>
	<input type='checkbox' name='ldw_watermark_lite_sizes[ldw_watermark_lite_checkbox_field_9]' <?php checked( $options['ldw_watermark_lite_checkbox_field_9'], $args ); ?> value='<?php echo $args; ?>'>
	<?php
}

function ldw_watermark_lite_text_field_0_render(  ) { 
	$options = get_option( 'ldw_watermark_lite_label' );
	?>
	<input type="text" name='ldw_watermark_lite_label[ldw_watermark_lite_text_field_0]' value='<?php echo $options['ldw_watermark_lite_text_field_0'] ?>' size="40" pattern="^[a-zA-Z0-9-.?*_!£$%^&()+={};@'~#,\[\]]+$" />
	<?php
}

function ldw_watermark_lite_file_field_0_render(  ) { 
	$plugin_path = plugin_dir_path( __FILE__ );
	$plugin_dir = basename($plugin_path);
	$path = wp_upload_dir();
	$upload_dir = trailingslashit($path['basedir']);
	$upload_url = trailingslashit($path['baseurl']);
	$watermark_name = get_option( 'ldw_watermark_lite_image' );
	if ($watermark_name) {
		list($width, $height, $image_type) = getimagesize($upload_dir . $plugin_dir . '/watermarks/' . $watermark_name);
		if ($image_type == IMAGETYPE_PNG) {
			if ($width > 250) {
				$width = 250;
			}
			?>
			<div style="text-align: center; font-weight: bold; width: 250px;">
				<img src="<?php echo $upload_url . $plugin_dir . '/watermarks/' . $watermark_name . "?" . time(); ?>" width="<?php echo $width; ?>">
				<br />
				<?php
				echo $watermark_name;
				?>
			<br /><br />
			</div>
			<?php
		}
	}
}

function ldw_watermark_lite_sizes_section_callback(  ) { 
	echo __( '', 'ldw-watermark-lite' );
}

function ldw_watermark_lite_label_section_callback(  ) { 
	echo __( '', 'ldw-watermark-lite' );
}

function ldw_watermark_lite_get_image_sizes( $size = '' ) {
	global $_wp_additional_image_sizes;
	$sizes = array();
	$get_intermediate_image_sizes = get_intermediate_image_sizes();
	foreach( $get_intermediate_image_sizes as $_size ) {
		if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
			$sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
			$sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
			$sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array( 
				'width' => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
			);
		}
	}
	if ( $size ) {
		if( isset( $sizes[ $size ] ) ) {
			return $sizes[ $size ];
		} else {
			return false;
		}
	}
	return $sizes;
}

function ldw_watermark_lite_calculate_text_box($text, $font, $font_size, $font_angle) { 
    $rect = imagettfbbox($font_size, $font_angle, $font, $text); 
    $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6])); 
    $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6])); 
    $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7])); 
    $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7])); 
    return array( "left" => abs($minX) - 1, "top" => abs($minY) - 1, "width" => $maxX - $minX, "height" => $maxY - $minY ); 
} 

function ldw_watermark_lite_options_page(  ) { 
	?><h1>LDW Watermark Lite</h1><?php
	$plugin_path = plugin_dir_path( __FILE__ );
	$plugin_dir = basename($plugin_path);
	$path = wp_upload_dir();
	$upload_dir = trailingslashit($path['basedir']);
	if (!wp_is_writable($upload_dir)) {
		echo $upload_dir . " is not writable. Please read the documentation for assistance.<br /><br />";
		exit;
	}
	if (file_exists($upload_dir . ".htaccess")) {
		if (!wp_is_writable($upload_dir . ".htaccess")) {
			echo ".htaccess is not writable. Please read the documentation for assistance.<br /><br />";
			exit;
		}
	}
	if (!file_exists($upload_dir . $plugin_dir . '/watermarks/')) {
		mkdir($upload_dir . $plugin_dir . '/watermarks/', 0755, true);
	}
	if(!in_array('mod_rewrite', apache_get_modules())) {
		echo "mod_rewrite is not installed. Please read the documentation for assistance.<br /><br />";
		exit;
	}
	if (!extension_loaded('gd')) {
		echo "GD is not installed. Please read the documentation for assistance.<br /><br />";
		exit;
	}
	$checked_sizes = get_option( 'ldw_watermark_lite_sizes' );
	if ($checked_sizes) {
		foreach ($checked_sizes as $key => $value) {
			$htaccess_sizes .= $value . ':';
		}
		$htaccess_sizes = substr($htaccess_sizes, 0, -1); 
	}
	$file_watermark = get_option( 'ldw_watermark_lite_image' );
	if ($file_watermark) {
		if (file_exists($upload_dir . $plugin_dir . '/watermarks/' . $file_watermark)) {
			$htaccess_wm_image = $file_watermark;
		}
	}
	if ($htaccess_wm_image && $htaccess_sizes) {
		$file_contents = 'RewriteEngine on
RewriteRule ^(.*\.(jpe?g))$ ' . $plugin_path . 'watermark-lite.php?image=' . $upload_dir . '$1&watermark=' . $upload_dir . $plugin_dir . '/watermarks/' . $htaccess_wm_image . '&data=' . $upload_dir . $plugin_dir . '&sizes=' . $htaccess_sizes . ' [NC]
';
		file_put_contents($upload_dir . ".htaccess", $file_contents);
	} else {
		unlink($upload_dir . ".htaccess");
	}
	?>
	<table style="width: 100%;">
		<tr>
			<td style="width: 50%;">
				<form action='options.php' method='post'>
					<?php
					settings_fields( 'ldw_watermark_lite_plugin_page' );
					do_settings_sections( 'ldw_watermark_lite_plugin_page' );
					submit_button();
					?>
				</form>
			</td>
			<td>
				<div style="text-align: center;">
					<h4>Thanks for using LDW Watermark Lite :)</h4>
				</div>
			</td>
		</tr>
	</table>
	<?php
}
?>
