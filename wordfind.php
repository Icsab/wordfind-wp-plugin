<?php
/*
Plugin Name:  WordSearch
Version: 0.1.1
Description: Generates wordsearch games
*/

if(!defined('ABSPATH')){
    exit; //Exit if accessed directly
}


define( 'WF_VERSION', '0.1.1' );
//define( 'WF_VERSION', date("YmdHis") );

global $wf_db_version;
$wf_db_version = 1.0;

register_activation_hook( __FILE__, 'wf_activate' );
register_deactivation_hook( __FILE__, 'wf_deactivate' ); 
register_uninstall_hook( __FILE__, 'wf_uninstall' ); 



require_once (plugin_dir_path( __FILE__ ) .'/inc/wf_functions.php');
require_once (plugin_dir_path( __FILE__ ) .'/inc/wf_ajax_functions.php');
require_once (plugin_dir_path( __FILE__ ) .'/inc/admin/wf_admin_menu.php');
require_once (plugin_dir_path( __FILE__ ) .'/inc/wf_generate_pdf.php');
require_once (plugin_dir_path( __FILE__ ) .'/inc/public/wf_shortcode.php');



