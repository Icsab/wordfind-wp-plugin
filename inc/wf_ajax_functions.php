<?php

add_action("wp_ajax_wf_save_puzzle_action", "wf_ajax_save_puzzle_callback");
add_action("wp_ajax_nopriv_wf_save_puzzle_action", "wf_ajax_save_puzzle_callback");


function wf_ajax_save_puzzle_callback(){
    if(isset($_POST["puzzle"])){$puzzle=$_POST["puzzle"];}/*else  return*/;
    if(isset($_POST["wordlist"])){$words=$_POST["wordlist"];}else  return;
    if(isset($_POST["options"])){$options=$_POST["options"];}else  return;
	if(isset($_POST["quizlist"])){$hints=$_POST["quizlist"];}else  $hints="";
	if(isset($_POST["name"])){$name=$_POST["name"];}else  return;
	if(isset($_POST["id"])){$id=$_POST["id"];}else  $id='all';
  
   wf_save_puzzle($name,$puzzle,$words,$hints,$options,$id);
    wp_die();
}

add_action("wp_ajax_wf_edit_puzzle_action", "wf_ajax_edit_puzzle_callback");
add_action("wp_ajax_nopriv_wf_edit_puzzle_action", "wf_ajax_edit_puzzle_callback");


function wf_ajax_edit_puzzle_callback(){
    if(isset($_POST["id"])){$id=$_POST["id"];}else{
		wp_send_json_error("Error: Empty string");
		return;
	} 
   wf_generate_grid($id);
    wp_die();
}


add_action("wp_ajax_wf_generate_pdf_action", "wf_ajax_generate_pdf_callback");
add_action("wp_ajax_nopriv_wf_generate_pdf_action", "wf_ajax_generate_pdf_callback");


function wf_ajax_generate_pdf_callback(){
    if(isset($_POST["puzzle"])){$puzzle=$_POST["puzzle"];}else  return;
    if(isset($_POST["wordlist"])){$words=$_POST["wordlist"];}else  return;
	if(isset($_POST["quizlist"])){$hints=$_POST["quizlist"];}else  $hints='';
  	if(isset($_POST["name"]) &&$_POST["name"]!=''){$name=$_POST["name"];}else $name="temp";
	
  echo generate_pdf($puzzle,$words,$hints,$name);
	
    wp_die();
}


add_action("wp_ajax_wf_generate_pdf_by_id_action", "wf_ajax_generate_pdf_by_id_callback");
add_action("wp_ajax_nopriv_wf_generate_pdf_by_id_action", "wf_ajax_generate_pdf_by_id_callback");


function wf_ajax_generate_pdf_by_id_callback(){
    if(isset($_POST["id"])){$id=$_POST["id"];}else  return;
   
	$game=wf_fetch_puzzle($id);
		
  echo generate_pdf($game['puzzle'],$game['words'],$game['hints'],$game['name']);
	
    wp_die();
}

add_action("wp_ajax_wf_delete_puzzle_action", "wf_ajax_delete_puzzle_callback");
add_action("wp_ajax_nopriv_wf_delete_puzzle_action", "wf_ajax_delete_puzzle_callback");


function wf_ajax_delete_puzzle_callback(){
    if(isset($_POST["id"])){$id=$_POST["id"];}else  return;
   
   wf_delete_puzzle($id);
    wp_die();
}


add_action("wp_ajax_wf_save_alphabet_action", "wf_ajax_save_alphabet_callback");
add_action("wp_ajax_nopriv_wf_save_alphabet_action", "wf_ajax_save_alphabet_callback");


function wf_ajax_save_alphabet_callback(){
    if(isset($_POST["alphabet"])){$alphabet=$_POST["alphabet"];}else  return;
	if(isset($_POST["name"])){$name=$_POST["name"];}else  return;
   
   //wf_save_alphabet($alphabet,$name);
	
	//get non-uppercase letters
	$no_capitals=get_option('wf_lovercase_letters',[]);
	$alphabet_arr=str_split_unicode($alphabet);
	$upper_alphabet='';
	foreach($alphabet_arr as $letter){
		if(!in_array($letter,$no_capitals))
			$letter=mb_strtoupper($letter,'UTF-8');
		$upper_alphabet.=$letter;
	}
	
	$json_url = plugin_dir_path( __FILE__ ).'admin/alphabets.json';
	if(file_exists($json_url)){	
		$json = file_get_contents($json_url);
		$alphabets = json_decode($json, TRUE);
		foreach ($alphabets as $a){
			if (isset($a['name']) && $a['name'] == $name)
			{wp_send_json_error("Error: The alphabet name is already in use");}
			}
		$alphabets[]=array(
			'name'=>$name,
			'alphabet'=>$upper_alphabet,
		);
		}
		else{
			$alphabets[]=array (
				'name'=>$name,
				'alphabet'=>$upper_alphabet,
			);
		}
		
	file_put_contents($json_url,json_encode($alphabets));
	echo wf_display_alphabets('list');
	
	
    wp_die();
}

add_action("wp_ajax_wf_delete_alphabet_action", "wf_ajax_delete_alphabet_callback");
add_action("wp_ajax_nopriv_wf_delete_alphabet_action", "wf_ajax_delete_alphabet_callback");


function wf_ajax_delete_alphabet_callback(){
  if(isset($_POST["name"])){$name=$_POST["name"];}else  return;
   
   wf_delete_alphabet($name);
    wp_die();
}

add_action("wp_ajax_wf_save_colors_action", "wf_ajax_save_colors_callback");
add_action("wp_ajax_nopriv_wf_save_colors_action", "wf_ajax_save_colors_callback");


function wf_ajax_save_colors_callback(){
    if(isset($_POST["grid_bg"])){$grid_bg=$_POST["grid_bg"];}else  return;
    if(isset($_POST["grid_fg"])){$grid_fg=$_POST["grid_fg"];}else  return;
	if(isset($_POST["sel_bg"])){$sel_bg=$_POST["sel_bg"];}else  return;
	if(isset($_POST["word_bg"])){$word_bg=$_POST["word_bg"];}else  return;
	if(isset($_POST["complete_bg"])){$complete_bg=$_POST["complete_bg"];}else  return;
	$colors=array(
						'grid_bg'=>$grid_bg,
						'grid_fg'=>$grid_fg,
						'sel_bg'=>$sel_bg,
						'word_bg'=>$word_bg,
						'complete_bg'=>$complete_bg,
						
					);
 	if(!get_option('wf_grid_colors')){
		add_option('wf_grid_colors',$colors);
	}
	else{
		update_option('wf_grid_colors',$colors);
	}
    wp_die();
}

add_action("wp_ajax_wf_save_message_action", "wf_ajax_save_message_callback");
add_action("wp_ajax_nopriv_wf_save_message_action", "wf_ajax_save_message_callback");


function wf_ajax_save_message_callback(){
    if(isset($_POST["message"])){$message=$_POST["message"];}else  return;
    
	
 	if(!get_option('wf_grid_message')){
		add_option('wf_grid_message',$message);
	}
	else{
		update_option('wf_grid_message',$message);
	}
    wp_die();
}


add_action("wp_ajax_wf_save_exceptions_action", "wf_ajax_save_exceptions_callback");
add_action("wp_ajax_nopriv_wf_save_exceptions_action", "wf_ajax_save_exceptions_callback");


function wf_ajax_save_exceptions_callback(){
    if(isset($_POST["exceptions"])){$exceptions=$_POST["exceptions"];}else {
		wp_send_json_error("Error: Empty string");
		return;
	} 
	//remove whitespaces from string
	$exceptions = preg_replace('/\s+/', '', $exceptions);
   $exc_arr=explode(',',$exceptions);
	if(!get_option('wf_lovercase_letters')){
		return add_option('wf_lovercase_letters',$exc_arr);
	}
	else{
		return update_option('wf_lovercase_letters',$exc_arr);
	}
	
  
    wp_die();
}