<?php


//create admin menu
add_action('admin_menu', 'wf_admin_menu');

function wf_admin_menu(){
   $menu= add_menu_page( 'LH WordSearch', 'LH WordSearch', 'manage_options', 'wf_dashboard', 'wf_menu_init','dashicons-grid-view' );
    add_submenu_page('wf_dashboard', '', '', 'manage_options','wf_dashboard', 'wf_menu_init');
	add_submenu_page('wf_dashboard', 'Generate puzzle', 'Generate puzzle', 'manage_options','wf_dashboard/generate', 'wf_sub_generate');
	add_submenu_page('wf_dashboard', 'View puzzles', 'View puzzles', 'manage_options','wf_dashboard/view', 'wf_sub_view');
	add_submenu_page('wf_dashboard', 'Settings', 'Settings', 'manage_options','wf_dashboard/settings', 'wf_sub_settings');

	
}

function wf_menu_init(){
     ?>  
<div class="wrap nosubsub">
	<div id="welcome-panel" class="welcome-panel dus-panel">
		
		<div class="welcome-panel-content">
			<h2>Wordfind game for WordPress</h2>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<div class="card">
						<a href="admin.php?page=wf_dashboard/generate" class="card-link">
							<i></i>
							<h3>Generate puzzle</h3>
						</a>
					</div>
				</div>
				<div class="welcome-panel-column">
					<div class="card ">
						<a href="admin.php?page=wf_dashboard/view" class="card-link">
							<i></i>
							<h3>View puzzles</h3>
						</a>
					</div>
				</div>
				<div class="welcome-panel-column">
					<div class="card ">
						<a href="admin.php?page=wf_dashboard/settings" class="card-link">
							<i></i>
							<h3>Settings</h3>
						</a>
					</div>
				</div>
			</div>
				
			
		</div>
	</div>
</div>
     <?php   
     }


function wf_sub_generate(){
	 $exceptions=get_option('wf_lovercase_letters','');
	if(is_array($exceptions)){
		$exc_str=implode(',',array_filter($exceptions));
	}
	else $exc_str=$exceptions;
   ?>
   <div class='wrap'>
    <h2>Generate puzzle</h2>
	  <div id="main" role="main">
       <div class="wf_panel_wrapper">
		   <?php wf_generate_grid();?>
	 
		   </div>
		  
			<div class="" id="ajax-response"></div> 
    </div>
  </div>
   <?php
  	wp_enqueue_style('wf_wordfindcss');
 }




function wf_sub_view(){
   ?>
   <div class='wrap'>
    <h2>View puzzles</h2>
	   <div class="col-wrap">
				
	   <?php
	   $puzzles=wf_fetch_puzzles();
	     $all_items=0;
	
		
    	     
	   if(count($puzzles)>0){
		   $all_items=count($puzzles);
		   $i=0;
	       echo '<div class="wf_puzzles_wrapper">';
	      foreach($puzzles as $puzzle) 
	        {
			  $options=json_decode( html_entity_decode( stripslashes ($puzzle['options']) ) );
				$quiz=$options->quiz;
			
    	          echo '<div class="wf_puzzle_item puzzle-'.$puzzle['id'].'  '.( ($i<6) ? "active" :"inactive").' ">';
			 		echo '<div class="puzle_item_id">'.$puzzle['id'].'.</div>';
    	            echo '<h3>'. ( $puzzle['name']!='' ? $puzzle['name'] : "Puzzle #".$puzzle['id'] ) .'</h3>';
    	            echo wf_draw_puzzle($puzzle['puzzle']);
			  		
    	            echo '<ol id="words" class="disabled '.($quiz ? 'show-hints' : '').'">'.wf_list_words($puzzle['words'],$puzzle['hints'],$puzzle['options']).'</ol>';
    	            echo wf_display_orientations($puzzle['options']);
    	          	echo '<button class="wf_generate_pdf button-primary button" data-attr-puzzle="'.$puzzle['id'].'">Save as PDF</button> <a class="pdf_url" id="pdf_url-'.$puzzle['id'].'" href="#" download="">Download</a>';
			  		echo '<a href="'.admin_url('admin.php?page=').'wf_dashboard/generate/&grid_id='.$puzzle['id'].'" class="button-secondary button" >Edit puzzle</a> ';
			  		echo '<button class="wf_delete_puzzle button-delete button" data-attr-puzzle="'.$puzzle['id'].'">Delete puzzle</button> ';
			  
			  		
			  		echo '<p><strong> Shortcode: </strong><i>[wf_puzzle id="'.$puzzle['id'].'"]</i></p>';
			  		echo '<p class="small">If you want to customize the messsage displayed upon solving the puzzle include the message in the shortcode like: <i>[wf_puzzle id="'.$puzzle['id'].'" message="Your custom message"]</i></p>';
    	             
    	          echo '</div><!--end puzzle-item-->';
			  $i++;
    	      }
		   
		   			
    	    echo '</div><!--end puzzle-wrapper-->';
		   echo '<div class="wf_puzzle_pagination">';
		  
	 						for( $j=1; $j<=ceil($all_items/6); $j++){
						echo '<span class="wf_pagination  '.( ($j==1) ? "active" :"").' " data-page="'.$j. '" data-view="6"> '.$j.'</span>';
						}
	 				echo '</div>';
	      }
	else{
		echo '<p> There\'s nothing to display yet</p>';
	}
    	echo     '<div class="" id="ajax-response"></div>' ;
	   
	   
	   ?>
	  

	  
    </div>
  </div>
   <?php
 }

function wf_sub_settings(){

	?>
	<div class='wrap'>
		<h2>Settings</h2>
		<div class="wf_panel_wrapper">
			<h3>Alphabets</h3>
			<div id="saved_alphabets">
				<?php
				echo wf_display_alphabets('list','Please select');
				?>
			</div>
			<p><strong>Define new alphabet </strong><span class="small">(only uppercase letters, unless the letter doesn't have an uppercase version)</span></p>
			<label for="alphabet-name">Name:
				<input id="alphabet-name" type="text" placeholder="Alphabet name" value=""/>
			</label>
			
			<label for="alphabet">New alphabet:
				<input id="alphabet" type="text" placeholder="Alphabet characters" value=""/>
			</label>
			
			<button id="save_alphabet" class="button button-primary">Save alphabet</button>
			<hr/>
			<label for="exceptions">Letters with no uppercase version (comma separated list):
				<?php $exceptions=get_option('wf_lovercase_letters','');
						if(is_array($exceptions)){
							$exc_str=implode(',',array_filter($exceptions));
						}
						else $exc_str=$exceptions;?>
				<input id="exceptions" type="text" placeholder="" value="<?php echo $exc_str; ?>"/>
			</label>
			<button id="save_exceptions" class="button button-primary">Update</button>
			
			
		</div>
		
		<div class="wf_panel_wrapper">
			<h3>Colors:</h3>
			<?php 
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
	
			?><div class="wf_colors_wrapper">
					<div class="wf_color_item">
						<p><strong>Grid background color</strong></p>
						<input type="text" value="<?php echo $colors['grid_bg']; ?>" class="wf-grid-color" id="wf_grid_bg" data-default-color="<?php echo $colors['grid_bg']; ?>" />
					</div>
					<div class="wf_color_item">
						<p><strong>Grid text color</strong></p>
						<input type="text" value="<?php echo $colors['grid_fg']; ?>" class="wf-grid-color" id="wf_grid_fg" data-default-color="<?php echo $colors['grid_fg']; ?>" />
					</div>
					<div class="wf_color_item">
						<p><strong>Selection color (while selecting letters)</strong></p>
						<input type="text" value="<?php echo $colors['sel_bg']; ?>" class="wf-grid-color" id="wf_sel_bg" data-default-color="<?php echo $colors['sel_bg']; ?>" />
					</div>
					<div class="wf_color_item">
						<p><strong>Solved word background color</strong></p>
						<input type="text" value="<?php echo $colors['word_bg']; ?>"class="wf-grid-color"  id="wf_word_bg" data-default-color="<?php echo $colors['word_bg']; ?>" />
					</div>
					<div class="wf_color_item">
						<p><strong>Solved grid background color</strong></p>
						<input type="text" value="<?php echo $colors['complete_bg']; ?>" class="wf-grid-color" id="wf_complete_bg" data-default-color="<?php echo $colors['complete_bg']; ?>" />
					</div>
			
			</div>
			<button id="save_colors" class="button button-primary">Save colors</button>
			
			
		</div>
		
		<div class="wf_panel_wrapper">
			<h3>Congratulation popup</h3>
			<?php 
			$message=get_option('wf_grid_message','Lorem ipsum dolor');
			
			?>
			<textarea id="wf_message" class="large-text"><?php echo $message;?></textarea><br>
			<button id="update_message" class="button button-primary">Update message</button>
		</div>
		<div class="" id="ajax-response"></div>
	</div>
	<?php
}