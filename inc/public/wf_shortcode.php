<?php

add_shortcode("wf_puzzle", "wf_display_puzzle_shortcode");


function wf_display_puzzle_shortcode($atts){
	$a = shortcode_atts( array(
		'id' => 1,
		'message'=>'',
	), $atts );
	
	
	ob_start();
	$puzzle_obj=wf_fetch_puzzle($a['id']);
	if($a['message']==''){
		$message=$message=get_option('wf_grid_message','Congratulations');
	}
	else{
		$message=$a['message'];
	}
	
	
	if(!empty($puzzle_obj)){
		//$puzzle=$puzzle_obj['puzzle'];
		 $puzzle=html_entity_decode( stripslashes ($puzzle_obj['puzzle']) ) ;
		 $word=html_entity_decode( stripslashes ($puzzle_obj['words']) ) ;
		 $hints=html_entity_decode( stripslashes ($puzzle_obj['hints']) ) ;
		 $options= html_entity_decode( stripslashes ($puzzle_obj['options']) ) ;
		
		echo '<div class="wf_puzzle_wrapper">
					<div id="puzzle"></div>
					<div class="puzzle_instr">
						<ol id="wordlist"></ol>
						<!-- <button id="reset-grid">Reset grid</button>-->
						 <button id="shuffle-grid">Shuffle letters</button>
						 <button id="wf-solve">Solve Puzzle</button>
           				 <p id="result-message"></p>
           				 
					</div>
				</div>
				<div id="solve_popup">'.$message.'<span class="popup-close"></div>';
		echo '<script>
		
		function recreate(p,w,o,h) {
		         try {
		
            game = new WordFindGame("#puzzle",o,p,w,h);
			
        } catch (error) {
            jQuery("#result-message").text(`😞 ${error}`).css({color: \'red\'});
            return;
        }
		 wordfind.print(game);
        if (window.game) {
            var emptySquaresCount = WordFindGame.emptySquaresCount();
            jQuery("#result-message").text(` ${emptySquaresCount ? \'but there are empty squares\' : \'\'}`).css({color: \'\'});
        }
        window.game = game;
    }
	jQuery("document").ready(function(){
	/*this generates the same grid every time
	 recreate('.$puzzle.','.$word.','.$options.','.$hints.');*/
	 //This generates a new grid with the same options
	 recreate("",'.$word.','.$options.','.$hints.');
	 
	  //jQuery("#reset-grid").click( function(){ recreate('.$puzzle.','.$word.','.$options.','.$hints.'); jQuery("#solve_popup").removeClass("active");});
	  jQuery("#shuffle-grid").click( function(){ recreate("",'.$word.','.$options.','.$hints.'); jQuery("#solve_popup").removeClass("active");});

   	 jQuery("#wf-solve").click(() => game.solve());
	 jQuery("body").on("click",".show-word",function(){
		 let w=jQuery(this).attr("data-word");
		 game.solveWord(w);
	 })
	 jQuery("body").on("click",".show-hint",function(){
		 jQuery(this).prev(".hidden-hint").removeClass("hidden-hint");
	 })
	 jQuery("#solve_popup .popup-close").click(function(){
		jQuery("#solve_popup").removeClass("active");
	});
	})
   
	</script>';
		//echo wf_draw_puzzle($puzzle_obj['puzzle']);
    	// echo wf_list_words($puzzle_obj['words']);
		
	}
	

	$output = ob_get_clean();
	return $output;
}