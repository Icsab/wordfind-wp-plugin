<?php

function wf_load_dependencies(){
	wp_register_script('wf_wordfindgame', plugins_url('/wordfind/assets/js/wordfindgame.js?'.WF_VERSION),array('jquery'),WF_VERSION, true);
 	wp_register_style('wf_wordfindcss', plugins_url('/wordfind/assets/css/wordfind.css?'.WF_VERSION));
	wp_register_script('wf_wordfind', plugins_url('/wordfind/assets/js/wordfind.js?'.WF_VERSION),array('jquery'),WF_VERSION, true);
	wp_register_script('wf_generate', plugins_url('/wordfind/assets/js/wf_generate.js?'.WF_VERSION),array('jquery','wf_wordfind','wf_wordfindgame'),WF_VERSION, true);
	
	// wp_enqueue_script('wf_admin_js', plugins_url('/wordfind/assets/js/wf_script.js?'.WF_VERSION),array('jquery'),WF_VERSION, true);
		 
	 wp_enqueue_style('wf_admin_css', plugins_url('/wordfind/assets/css/wf_style.css?'.WF_VERSION));
	wp_localize_script( 'wf_generate', 'wf_frontendajax',
            array( 
				'ajax_url' => admin_url( 'admin-ajax.php' ) ,
				'root' => esc_url_raw( rest_url() ),
   				'nonce' => wp_create_nonce( 'wp_rest' ),
			) );
	$shortcode_found = false;
	
	if(!is_admin()){
		 global $post, $wpdb;
	 // determine whether this page contains "wf_puzzle" shortcode
       if (!empty($post) && has_shortcode($post->post_content, 'wf_puzzle') ) {
          $shortcode_found = true;
       } else if ( isset($post->ID) ) {
          $result = $wpdb->get_var( $wpdb->prepare(
            "SELECT count(*) FROM $wpdb->postmeta " .
            "WHERE post_id = %d and meta_value LIKE '%%wf_puzzle%%'", $post->ID ) );
          $shortcode_found = ! empty( $result );
       }
	}
	else{
	 wp_enqueue_script('wf_wordfind');
    wp_enqueue_script('wf_wordfindgame');
    wp_enqueue_script('wf_generate');
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script('wf_script', plugins_url('/wordfind/assets/js/wf_script.js?'.WF_VERSION),array('jquery','wp-color-picker'),false, true);
	}
    if ( $shortcode_found ) {
        wp_enqueue_script('wf_wordfind');
		wp_enqueue_script('wf_wordfindgame');
    	wp_enqueue_style('wf_wordfindcss');
		
		//add the styles needed to use the colors saved
		wp_register_style( 'grid-custom-css', false );
		wp_enqueue_style( 'grid-custom-css' );
		if(!get_option('wf_grid_colors')){
					$colors=array(
						'grid_bg'=>'#bada55',
						'grid_fg'=>'#ffffff',
						'sel_bg'=>'#38915f',
						'word_bg'=>'#556CDA',
						'complete_bg'=>'#A63162',
						
					);
				}
	 			else{
	        $colors=get_option( 'wf_grid_colors');
				}
	
		$grid_custom_css = "#puzzle button.puzzleSquare{ background-color: ".$colors['grid_bg'].";color:".$colors['grid_fg']." ;}
							#puzzle button.puzzleSquare.selected{ background-color: ".$colors['sel_bg']."; }
							#puzzle button.puzzleSquare.found{ background: ".$colors['word_bg']."; }
							#puzzle button.puzzleSquare.solved{ background: #cd2653; }";
							#puzzle button.puzzleSquare.complete{ background: ".$colors['complete_bg']."; }";
        wp_add_inline_style( 'grid-custom-css', $grid_custom_css );
      }
}

add_action( 'admin_enqueue_scripts', 'wf_load_dependencies' );
add_action('wp_enqueue_scripts', 'wf_load_dependencies');



function wf_activate(){

	//check db version
	wf_update_db_check();

}


function wf_deactivate(){
	
}

function wf_uninstall(){
	
	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
		die;
	}

	$wf_options = ['wf_grid_colors','wf_db_version','wf_lovercase_letters','wf_grid_message'];
	foreach($wf_options as $option_name){
		delete_option($option_name);	
	}

	// drop a custom database table
	global $wpdb;
	$table_name = $wpdb->prefix . 'wf_puzzles';
	$wpdb->query("DROP TABLE IF EXISTS $table_name");
	

}

function wf_update_db_check() {
    global $wf_db_version;
    if ( get_site_option( 'wf_db_version' ) != $wf_db_version ) {
        wf_db();
    }
}
add_action( 'plugins_loaded', 'wf_update_db_check' );


function wf_db() {
	global $wpdb;
	global $wf_db_version;
	$installed_ver = get_option( "wf_db_version" ,'');
	
	$table_name = $wpdb->prefix . 'wf_puzzles';
	
	$charset_collate = $wpdb->get_charset_collate();

	if ( $installed_ver != $wf_db_version ) {	

	$sql="	CREATE TABLE  $table_name (
			puzzle_id bigint(20)  NOT NULL AUTO_INCREMENT,
			name text DEFAULT NULL,
			timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			puzzle longtext NOT NULL,
			wordlist longtext NOT NULL,
			hints text NOT NULL,
			options longtext NOT NULL,
			pdf_url text DEFAULT NULL,
		 	PRIMARY KEY  (puzzle_id)
		)  $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'wf_db_version', $wf_db_version );
	}
	
}



function wf_save_puzzle($name,$puzzle,$words,$hints,$options,$id){
	global $wpdb;
		
	$table_name = $wpdb->prefix . 'wf_puzzles';
	$data=array(
		
		'name'=>$name,
		'puzzle'=>$puzzle,
		'wordlist'=>$words,
		'options'=>$options,
		'hints'=>$hints,
	);
	if($id=='all'){
		$wpdb->insert($table_name, $data);
	}
	else{
		//$data['id']=$id;	
		$wpdb->update($table_name, $data,array('puzzle_id'=>$id));
	}
	
	
}


function wf_fetch_puzzles(){
	global $wpdb;

	$table_name = $wpdb->prefix . 'wf_puzzles';


	$sql="	SELECT * FROM $table_name;";
	global $wf_puzzles;
	$wf_puzzles=array();
	
	$result = $wpdb->get_results( $sql );

	foreach ( $result as $key=>$row )
	{
		$wf_puzzles[$key]['id']=$row->puzzle_id;
		$wf_puzzles[$key]['name']=$row->name;
		$wf_puzzles[$key]['puzzle']=$row->puzzle;
		$wf_puzzles[$key]['words']=$row->wordlist;
		$wf_puzzles[$key]['hints']=$row->hints;
		$wf_puzzles[$key]['options']=$row->options;
		$wf_puzzles[$key]['pdf_url']=$row->pdf_url;
	
	}
    return $wf_puzzles	;
}

function wf_fetch_puzzle($id){
	global $wpdb;

	$table_name = $wpdb->prefix . 'wf_puzzles';


	$sql="SELECT * FROM $table_name WHERE `puzzle_id`=$id";
	global $wf_puzzles;
	$wf_puzzles=array();
	$result = $wpdb->get_results( $sql );
	/*$rowcount = $result->num_rows;
    if($rowcount==0) return false;*/
	
	if(empty($result)){return ;}
		foreach ( $result as $key=>$row )
		{

			$wf_puzzles['id']=$row->puzzle_id;
			$wf_puzzles['name']=$row->name;
			$wf_puzzles['puzzle']=$row->puzzle;
			$wf_puzzles['words']=$row->wordlist;
			$wf_puzzles['hints']=$row->hints;
			$wf_puzzles['options']=$row->options;
			$wf_puzzles['pdf_url']=$row->pdf_url;

		}
	
    return $wf_puzzles;
}
//remove puzzle data from db
function wf_delete_puzzle($id){
	global $wpdb;
		$table_name = $wpdb->prefix . 'wf_puzzles';
		
			$wpdb->delete( 
			$table_name, 
			['puzzle_id' => $id]
			) ;
		
	
}

function wf_draw_puzzle($p_string){
    //p_arr=json_decode($p_string);
     $p_arr=json_decode( html_entity_decode( stripslashes ($p_string ) ) );
    
    $output='<div class="puzzle_table">';
	if(empty($p_arr)){return;}
	foreach($p_arr as $i=>$row){
        $output.='<div class="puzzle_row">';
        foreach($row as $j=>$el)
        {
            $output.='<button class="puzzleSquare" x="'.$j.'" y="'.$i.'">';
			
            $output.=!empty($el)? $el : '&nbsp;';
            $output.='</button>';
            
       
        }
        $output .='</div>';
	
    }
	 $output .='</div>';
    return $output;
}

function wf_list_words($w_string,$q_string,$o_string){
	
	$options=json_decode( html_entity_decode( stripslashes ($o_string ) ) );
	$quiz=$options->quiz;
	$w_arr=json_decode( html_entity_decode( stripslashes ($w_string ) ) );
	$q_arr=json_decode( html_entity_decode( stripslashes ($q_string ) ) );
	$output='';
	 
    foreach($w_arr as  $key=>$row){
            
      $output.=!empty($row) ? '<li class="word"><label for="hint-'.($key+1).'" class="wf_floating hint"><span>Question #.'.($key+1).'</span><input class="hint"  id="hint-'.($key+1).'" value="'.( $quiz && !empty($q_arr->$row) ? $q_arr->$row : '' ).'" /></label><label class="wf_floating"for="word-'.($key+1).'"><span>Word #.'.($key+1).'</span><input class="word '.($quiz ? 'quiz' : '').'  id="word-'.($key+1).'"  value="'.$row.'"/></label> </li>':'';
       
        }
      
   
    return $output;

}

function wf_display_orientations($option_string){
	
    $options=json_decode( html_entity_decode( stripslashes ($option_string ) ) );
	if(empty($options)){
		return;
	}
	
	$o_arr=$options->orientations;
	if(empty($o_arr)){
		return;
	}
   
   echo '<div class="orientation_table">';
    
        echo '<div class="orientation_arrow">';
            if(in_array('diagonalUpBack',$o_arr)){echo '&nwarr;';}
            else{echo '&nbsp;';};
        echo '</div>';
         echo '<div class="orientation_arrow">';
            if(in_array('verticalUp',$o_arr)){echo '&uarr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('diagonalUp',$o_arr)){echo '&nearr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('horizontalBack',$o_arr)){echo '&larr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
          echo '&nbsp;';
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('horizontal',$o_arr)){echo '&rarr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('diagonalBack',$o_arr)){echo '&swarr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('vertical',$o_arr)){echo '&darr;';}
            else{echo '&nbsp;';};
        echo '</div>';
        echo '<div class="orientation_arrow">';
            if(in_array('diagonal',$o_arr)){echo '&searr;';}
            else{echo '&nbsp;';};
        echo '</div>';
      echo '</div>';  
    
}


function generate_pdf($data,$word,$hints,$name){
	    $p_arr=json_decode( html_entity_decode( stripslashes ($data ) ) );
		$w_arr=json_decode( html_entity_decode( stripslashes ($word ) ) );
		$h_arr=json_decode( html_entity_decode( stripslashes ($hints ) ) );
		//$p_arr=json_decode( html_entity_decode( stripslashes ($p_string ) ) );
$pdf = new PDF();

// Add a Unicode font (uses UTF-8)
// This generates 2 php files ( wp-content/plugins/wordfind/inc/fpdf/font/unifont/dejavusanscondensed.mtx.php and wp-content/plugins/wordfind/inc/fpdf/font/unifont/fdejavusanscondensed.cw127.php) with hardcoded path to the font file, so before server transfer these should be removed 
		$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
		$pdf->SetFont('DejaVu','',14);
		$pdf->AddPage();
		$pdf->puzzle($p_arr);
		//	$pdf->AddPage();
		$pdf->word($w_arr,$h_arr);
	//create folder for pdf's
	$pdf_folder=$_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/pdf/";
	if (!file_exists($pdf_folder)) {
    mkdir($pdf_folder, 0777, true);
	}
		$pdf->Output('F',	$pdf_folder.$name.'.pdf');
		//	$pdf->Output('D',$name.'.pdf',false);

return(get_site_url()."/wp-content/uploads/pdf/".$name.'.pdf');	
}


function wf_display_alphabets($mode,$name){
	$json_url = plugin_dir_path( __FILE__ ).'admin/alphabets.json';
	if(file_exists($json_url)){	
		$json = file_get_contents($json_url);
		$alphabets = json_decode($json, TRUE);
	}
	else return 'There\'s nothing to display yet!';
	if (empty($alphabets)) return 'There\'s nothing to display yet!';
	$output='';
	if($mode=='list'){
		$output.= '<ol>';
		foreach($alphabets as $alphabet){
		$output.='<li><strong>' .$alphabet['name'].': </strong><span class="alphabet">'.$alphabet['alphabet'].'</span><span class="delete_alphabet" data-target="'.$alphabet['name'].'">Delete alphabet</span></li>';
		}
		$output.= '</ol>';
	}
	else if($mode=='dropdown'){
		$output.="<label for 'alphabet-dropdown' >Select an alphabet or create a new one";
		$output.= '<select id="alphabet-dropdown">';
		$output.= '<option value="">Please select:</option>';
		foreach($alphabets as $alphabet){
		$output.='<option value="'.$alphabet['alphabet'].'"  '.($name==$alphabet['name'] ? 'selected' : '' ).'>'. $alphabet['name'].'</option>';
		}
		$output.= '</select>';
		$output.= '</label>';
	}
	
	return $output;
}

function wf_delete_alphabet($name){
	$json_url = plugin_dir_path( __FILE__ ).'admin/alphabets.json';
	if(file_exists($json_url)){	
		$json = file_get_contents($json_url);
		$alphabets = json_decode($json, TRUE);
		foreach ($alphabets as $key=>$alphabet){
			if (isset($alphabet['name']) && $alphabet['name'] == $name)
				{unset($alphabets[$key]);}
		}
		file_put_contents($json_url,json_encode($alphabets));
		
	}
	
	echo wf_display_alphabets('list');
	
}
function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}



function wf_generate_grid(){

	if (isset($_REQUEST['grid_id'] )){
		$id=$_REQUEST['grid_id'];
		$grid=wf_fetch_puzzle($id);
		$o_str=$grid['options'];
		$options=json_decode( html_entity_decode( stripslashes ($o_str ) ) );
		$orientations=$options->orientations;
	}
	else{
		$id="all";
	}
	
 $exceptions=get_option('wf_lovercase_letters','');
	if(is_array($exceptions)){
		$exc_str=implode(',',array_filter($exceptions));
	}
	else $exc_str=$exceptions;
	
   ?>
  
       
		  <fieldset id="controls">
			  <div class="wf_settings">
				  <h3>Grid settings</h3>
				  <input type="hidden" id="exceptions" value="<?php echo $exc_str; ?>"/>
				  <label for="puzzle-name">Puzzle name :
						<input id="puzzle-name" type="text" required value="<?php echo  isset($grid['name']) ? $grid['name']:''  ;?>"/>
					</label>
				  <label for="grid-size">Grid size :
						<select id="grid-size">
							<option value="" selected>Please select</option>
							<?php for($i=3; $i<=25;$i++){
	   							echo '<option value="'.$i.'" '.( (isset($options->height) && $i==$options->height) ? 'selected' : '').' >'.$i.'x'.$i.'</option>';
 							  }	?>
						</select>
					</label>
				  <label for="puzzle-type">Game type :
						<select id='puzzle-type'>
							<option value='word' <?php echo (isset($options->quiz)&& !$options->quiz) ?  'selected' : ''; ?> >Words (easier)</option>
							<option value='quiz' <?php echo  (isset($options->quiz)&& $options->quiz) ?  'selected' : ''; ?>>Quiz (harder)</option>
					  </select>
				  </label>
				  	<label for="allowed-orientations">Allowed orientations :
						<select id='allowed-orientations' multiple>
							<option value='horizontal' <?php echo ( isset($orientations) && !in_array('horizontal',$orientations) ) ? '':'selected'; ?> >Horizontal</option>
							<option value='horizontalBack' <?php echo  ( isset($orientations) && !in_array('horizontalBack',$orientations) ) ? '':'selected'; ?> >Horizontal Back</option>
							<option value='vertical'<?php echo ( isset($orientations) && !in_array('vertical',$orientations) ) ? '':'selected'; ?> >Vertical</option>
							<option value='verticalUp' <?php echo ( isset($orientations) && !in_array('verticalUp',$orientations) ) ? '':'selected'; ?> >Vertical Up</option>
							<option value='diagonal' <?php echo  ( isset($orientations) && !in_array('diagonal',$orientations) ) ? '':'selected'; ?> >Diagonal</option>
							<option value='diagonalBack' <?php echo  ( isset($orientations) && !in_array('diagonalBack',$orientations) ) ? '':'selected'; ?> >Diagonal Back</option>
							<option value='diagonalUp' <?php echo  ( isset($orientations) && !in_array('diagonalUp',$orientations) ) ? '':'selected'; ?> >Diagonal Up</option>
							<option value='diagonalUpBack' <?php echo ( isset($orientations) &&  !in_array('diagonalUpBack',$orientations) ) ? '':'selected'; ?> >Diagonal Up Back</option>
					  </select>
				  </label>
				  <span id="selected-orientations"></span>
				  
				  <?php	$alphabetName=isset($options->alphabetName) ? $options->alphabetName : 'Please select';
				echo wf_display_alphabets('dropdown',$alphabetName);?>
				  <label for="alphabet">Allowed characters <span class="small">(please only add uppercase letters, unless the letter doesn't have an uppercase version)</span>:<br/>
						<input id="alphabet" type="text"  class="large-text" value="<?php echo isset($options->alphabet) ?  $options->alphabet : '' ;?>" />
					</label>
					<label for="allowed-missing-words">Allowed missing words :
						<input id="allowed-missing-words" type="number" min="0" max="5" step="1" value="<?php echo isset($options->allowedMissingWords) ? $options->allowedMissingWords : 0;?>" >
					</label>
					
					<label for="max-grid-growth">Max grid growth :
						<input id="max-grid-growth" type="number" min="0" max="5" step="1" value="<?php echo isset($options->maxGridGrowth) ?  $options->maxGridGrowth : 0 ;?>" >
					</label>
				 
					<label for="extra-letters">Extra letters :
						<select id="extra-letters">
							<option value="secret-word" selected>form a secret word</option>
							<option value="none">none, allow blanks</option>
							<option value="secret-word-plus-blanks">form a secret word but allow for extra blanks</option>
							<option value="random" selected>random</option>
						</select>
					</label>
			  </div>
					<label for="secret-word">Secret word :
						<input id="secret-word">
					</label>	
			  
			  	
					<ul id="words" class="<?php echo isset( $options->quiz) && $options->quiz  ? 'show-hints' : '';?>">
						<?php
					if( isset($grid['words']) && isset($grid['hints']) && isset($o_str))
					echo wf_list_words($grid['words'],$grid['hints'],$o_str);
	
					?>
					<li><button id="add-word" class="button button-secondary">Add word</button></li>
					</ul>
					<button id="create-grid" class="button button-primary">Create grid</button>
					<p id="result-message"></p>
			</fieldset>
		
	
		   <div class="wf_puzzle_container <?php echo $id=='all' ? 'wf_grid_create' : 'wf_grid_edit';?>">
				 <div id="puzzle" >
			   <?php if($id!='all'){  echo wf_draw_puzzle($grid['puzzle']);  }?>
			   </div>
			   <div class="wf_grid_buttons">
				<button id="wf_save" class="button button-secondary" data_target="<?php echo $id;?>">Save Puzzle</button> 
				<button id="wf_to_pdf" class="button button-secondary">Save as PDF</button> 
			   <a id="pdf_url" href="#"download>Download</a>
			   </div>
		   </div> 
		  
		<!--<div class="" id="ajax-response"></div> -->
   
  
   <?php
  	wp_enqueue_style('wf_wordfindcss');
	
}