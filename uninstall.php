<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

$option_name = 'ldw_watermark_lite_sizes';
delete_option( $option_name );

$option_name = 'ldw_watermark_lite_label';
delete_option( $option_name );

$option_name = 'ldw_watermark_lite_image';
delete_option( $option_name );
?>
