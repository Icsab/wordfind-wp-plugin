<?php
$wf_options = ['wf_grid_colors','wf_db_version','wf_lovercase_letters','wf_grid_message'];
	foreach($wf_options as $option_name){
		delete_option($option_name);	
	}

	// drop a custom database table
	global $wpdb;
	$table_name = $wpdb->prefix . 'wf_puzzles';
	$wpdb->query("DROP TABLE IF EXISTS $table_name");