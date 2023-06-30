    /* Example words setup */
   /* [        
		'addword'
    ].map(word => WordFindGame.insertWordBefore(jQuery('#add-word').parent(), word,'Add your first word'));
 */

    /* Init */
	var puzzle, wordList,quizList,orientations;
    
  


function generate_puzzle(){
	if(validate_words()){
		
		 jQuery('#result-message').removeClass();
				var fillBlanks, options;
				if (jQuery('#extra-letters').val() === 'none') {
					fillBlanks = false;
				} else if (jQuery('#extra-letters').val().startsWith('secret-word')) {
					fillBlanks = jQuery('#secret-word').val();
				}
			wordList =getWordsAdmin();
			
			//console.log(wordList);
			orientations=jQuery('#allowed-orientations').val()
			 try {

				let options={
					height: jQuery('#grid-size').val() ==''? 4 : jQuery('#grid-size').val(),
					width:  jQuery('#grid-size').val() ==''? 4 : jQuery('#grid-size').val(),
					allowedMissingWords: +jQuery('#allowed-missing-words').val(),
					maxGridGrowth: +jQuery('#max-grid-growth').val(),
					fillBlanks: fillBlanks,
					allowExtraBlanks: ['none', 'secret-word-plus-blanks'].includes(jQuery('#extra-letters').val()),
					orientations: orientations,
					maxAttempts: 100,
					quiz: jQuery('#puzzle-type').val()=='quiz',
					alphabetName: jQuery('#alphabet-dropdown option:selected' ).text(),
					alphabet: jQuery('#alphabet').val(),
					}

				
			puzzle = wordfind.newPuzzleLax(wordList.sort(), options);
			 drawPuzzleAdmin('#puzzle',puzzle);
			jQuery('.wf_grid_buttons').addClass('active');
				
			
			 }
			catch (error) {
					jQuery('#result-message').text(`😞 ${error}, try to specify less ones`).css({color: 'red'});
					return;
				}
	}
	else{
		//jQuery('#result-message').html('😞 Please fix the errors!').css({color:'red'});
		
	}
}



 var getWordsAdmin = function () {
	  return     jQuery('input.word').toArray().map(wordEl => wordEl.value).filter(word => word);
	 
   };

 var getQuizList = function () {
	
	 if(jQuery('#puzzle-type').val()=='quiz'){
		 let q={};
		 jQuery('.wf_floating.hint').each(function(){
			// q.push(jQuery(this).val());
			let word=""+jQuery(this).next('.wf_floating').find('input.word').val();
			q[word]=jQuery(this).find('input.hint').val();
		 })
	 return    q; 
		}
   };


 var drawPuzzleAdmin = function (el, puzzle) {
      var output = '';
      // for each row in the puzzle
      for (var i = 0, height = puzzle.length; i < height; i++) {
        // append a div to represent a row in the puzzle
        var row = puzzle[i];
		  var no_uppercase=jQuery('#exceptions').val().split(',');
		  let letter='';
        output += '<div>';
        // for each element in that row
        for (var j = 0, width = row.length; j < width; j++) {
            // append our button with the appropriate class
            output += '<button class="puzzleSquare" x="' + j + '" y="' + i + '">';
			if(no_uppercase.includes(row[j])){
				letter=row[j];
				
			}
			else{
				
				letter=row[j].toUpperCase();
			}
            output += letter || '&nbsp;';
            output += '</button>';
        }
        // close our div that represents a row
        output += '</div>';
      }

      jQuery(el).html(output);
    };

    /* Event listeners */
   
	 	
		jQuery('body').on('change','#extra-letters',function(evt){
			if( evt.target.value.startsWith('secret-word')){
				jQuery('label[for=secret-word]').addClass('active');
			}
			else{
				jQuery('label[for=secret-word]').removeClass('active');
			}
		} );
		jQuery('body').on('change','#puzzle-type',function(evt){
			if( evt.target.value=='quiz'){
				jQuery('#words').addClass('show-hints');
			}
			else{
				jQuery('#words').removeClass('show-hints');
			}
		} );

		jQuery('body').on('change','#alphabet-dropdown',function(evt){
				console.log(evt.target.value);
				jQuery('#alphabet').val(evt.target.value);
			
		} );


	
		jQuery('body').on('change','#allowed-orientations',function(evt)  {display_orientations();})
						  
		jQuery('body').on('click','.orientation-item .close',function(){ 
		 
		 var target= jQuery(this).attr('data-target');
		 jQuery('#allowed-orientations option[value="' + target + '"]').removeAttr('selected');
		jQuery(this).parent('.orientation-item').remove();
	  });
	 jQuery('body').on('click','.wf_pagination',function(){
        paginate(jQuery(this).attr('data-view'),jQuery(this).attr('data-page'));
       jQuery('.wf_pagination').removeClass('active');
		jQuery(this).addClass('active');
		 return false;
		
    })
 
	


	jQuery('body').on('keyup','input.word',function() {
	   var no_uppercase=jQuery('#exceptions').val().split(',');
	  
	 //  if(no_uppercase.includes(row[j])){}
	 
	     let upper = this.value.toLocaleUpperCase();
	   for(i=0;i<no_uppercase.length;i++){
		   upper=upper.replace(no_uppercase[i].toLocaleUpperCase(),no_uppercase[i]);
	   }
        this.value = upper;
    });


    jQuery('body').on('click','#add-word',function() {WordFindGame.insertWordBefore(jQuery('#add-word').parent())});

    jQuery('#create-grid').click(function(){generate_puzzle(); } );
 	
	
    
	jQuery('body').on('click','#wf_save',function(){
		if( jQuery(this).text()=='Saved'){
		jQuery('#ajax-response').addClass('notice inline notice-warning ').html('Already saved!');
		setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-warning ').html(''); }, 5000);
		return;
	}else{ 
		let wList =getWordsAdmin();
		let qList=getQuizList();
		let selectedOrientations=jQuery('#allowed-orientations').val();
		console.log(qList);
		save_puzzle(puzzle,wList,qList,selectedOrientations,jQuery(this).attr('data_target')); 
	}
	});
 	jQuery('body').on('click','.wf_delete_puzzle',function(){
		delete_puzzle(jQuery(this).attr('data-attr-puzzle')); 
	} );
	jQuery('body').on('click','.wf_edit_puzzle',function(){
			edit_puzzle(jQuery(this).attr('data-attr-puzzle')); 
		} );

	jQuery('body').on('click','.wf_generate_pdf',function(){
		generate_pdf_by_id(jQuery(this).attr('data-attr-puzzle')); 
	} );
	jQuery('body').on('click','#wf_to_pdf',function(){generate_pdf(puzzle,wordList,name);});


	  
	 jQuery('body').on('click','#save_alphabet',function(){
       
      if( ( jQuery('#alphabet').val()!='')&&jQuery('#alphabet-name').val()!=''){
		  save_alphabet(jQuery('#alphabet').val(), jQuery('#alphabet-name').val());
	  }
    })

	 jQuery('body').on('click','.delete_alphabet',function(){
 	let name=jQuery(this).attr('data-target');
		  delete_alphabet(name);
	  
    })
	jQuery('body').on('click','#save_exceptions',function(){
        save_exceptions(jQuery('#exceptions').val());
	  
    })
	jQuery('#save_colors').click(function(){
		jQuery.ajax( {
			url: wf_frontendajax.ajax_url,
			method: 'POST',
			data:{
			'action': 'wf_save_colors_action',
			'grid_bg':jQuery('#wf_grid_bg').val(),
			'grid_fg':jQuery('#wf_grid_fg').val(),
			'sel_bg':jQuery('#wf_sel_bg').val(),
			'word_bg':jQuery('#wf_word_bg').val(),
			'complete_bg':jQuery('#wf_complete_bg').val(),
		},
		success:function(response) {
					
			jQuery('#ajax-response').addClass('notice inline notice-success ').html('Saved!');
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-success ').html(''); }, 5000);
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			
		}	
	});
		
	});

jQuery('#update_message').click(function(){
		
		
		jQuery.ajax( {
			url: wf_frontendajax.ajax_url,
			method: 'POST',
			data:{
			'action': 'wf_save_message_action',
			'message':jQuery('#wf_message').val(),
			
		},
		success:function(response) {
					
			jQuery('#ajax-response').addClass('notice inline notice-success ').html('Saved!');
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-success ').html(''); }, 5000);
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
		}	
	});
		
	});


function paginate($c,$p){
	var $total=jQuery('.wf_puzzle_item').length;

	  jQuery('.wf_puzzle_item').removeClass('active').addClass('inactive');
    jQuery('.wf_puzzle_item').slice( ($p-1)*$c, $p*$c ).removeClass('inactive').addClass('active');
	
}



function display_orientations(){
	var selectedValues = jQuery('#allowed-orientations').val();
	var output="";
	
	jQuery("#allowed-orientations :selected").map(function(i, el) {
  		output+='<span class="orientation-item" >'+jQuery(el).text()+'<span class="close" data-target="'+jQuery(el).val()+'">x</span></span>';
}).get();

	jQuery('#selected-orientations').html(output);
}

jQuery(document).bind('ready ajaxComplete', function(){
	display_orientations();
	
 })
 

function delete_puzzle(id){
	jQuery.ajax( {
		url: wf_frontendajax.ajax_url,
		method: 'POST',
		data:{
			'action': 'wf_delete_puzzle_action',
			'id':id,
		},
		success:function(response) {
			jQuery('.wf_puzzle_item.puzzle-'+id).remove();
			jQuery('.wf_pagination.active').click();
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			
		}	
	});
	
}


function save_puzzle(puzzle,wordlist,quizList,orientations,id){
	//console.log(puzzle);
	let fillBlanks;
	
	//console.log(quizList);
	if (jQuery('#extra-letters').val() === 'none') {
					fillBlanks = false;
				} else if (jQuery('#extra-letters').val().startsWith('secret-word')) {
					fillBlanks = jQuery('#secret-word').val();
				}
	let options={
					 height: jQuery('#grid-size').val() ==''? 4 : jQuery('#grid-size').val(),
					 width:  jQuery('#grid-size').val() ==''? 4 : jQuery('#grid-size').val(),
					 allowedMissingWords: +jQuery('#allowed-missing-words').val(),
              		 maxGridGrowth: +jQuery('#max-grid-growth').val(),
					 fillBlanks: fillBlanks,
					 allowExtraBlanks: ['none', 'secret-word-plus-blanks'].includes(jQuery('#extra-letters').val()),
					 orientations: orientations,
					 maxAttempts: 100,
					 quiz: jQuery('#puzzle-type').val()=='quiz',
					 alphabetName: jQuery('#alphabet-dropdown option:selected' ).text(),
					 alphabet: jQuery('#alphabet').val(),
					}
	
		jQuery.ajax( {
		
		url: wf_frontendajax.ajax_url,
		
		method: 'POST',
		/*beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', dus_frontendajax.nonce );
		},*/
		data:{
			'action': 'wf_save_puzzle_action',
			'name':jQuery('#puzzle-name').val(),
			'puzzle':JSON.stringify(puzzle),
			'wordlist':JSON.stringify(wordlist),
			'quizlist':JSON.stringify(quizList),
			'options':JSON.stringify(options),
			'id':id,
			
		},
		success:function(response) {
			
			  	jQuery('#wf_save').text('Saved'); 
				jQuery('#ajax-response').addClass('notice inline notice-success ').html('Saved!');
				setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-success ').html(''); }, 5000);
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			
		}	
	});
	
}

function edit_puzzle(id){
	jQuery.ajax( {
		url: wf_frontendajax.ajax_url,
		method: 'POST',
		data:{
			'action': 'wf_edit_puzzle_action',
			'id':id,
		},
		success:function(response) {
		
			jQuery('#ajax-response').addClass('wf_popup').html(response); 
			//setTimeout(function() { jQuery("#ajax-response").removeClass('wf_popup ').html(''); }, 5000);
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			
		}	
	});
	
}
function generate_pdf(puzzle,wordlist,orientations){
	//console.log(puzzle);
		jQuery.ajax( {
		
		url: wf_frontendajax.ajax_url,
		
		method: 'POST',
		
		data:{
			'action': 'wf_generate_pdf_action',
			'name':jQuery('#puzzle-name').val(),
			'puzzle':JSON.stringify(puzzle),
			'wordlist':JSON.stringify(wordlist),
			
			
		},
		success:function(response) {
			
			  jQuery('#wf_to_pdf').text('Done'); 
			 jQuery('#pdf_url').attr('href',response);
			jQuery('#pdf_url').get(0).click();
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			jQuery('#ajax-response').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			
		}	
	});
	
}
function generate_pdf_by_id(id){
	//console.log(puzzle);
		jQuery.ajax( {
		
		url: wf_frontendajax.ajax_url,
		
		method: 'POST',
		
		data:{
			'action': 'wf_generate_pdf_by_id_action',
			'id':id,
			
		},
		success:function(response) {
			
			
			jQuery('#pdf_url-'+id).attr('href',response);
			jQuery('#pdf_url-'+id).get(0).click();
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			
			console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			jQuery('#ajax-response').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			
		}	
	});
	
}


function validate_words(){
	let error='';
	let valid=true;
	jQuery('.word').removeClass('error');
	 jQuery('#puzzle-name').removeClass('error');
	jQuery('#result-message').html('');
	if(jQuery('#puzzle-name').val()==''){
		valid=false;
		 jQuery('#puzzle-name').addClass('error');
		error +=  'Missing title.'+"<br/>";
	}
	let quant='+';
	if(jQuery('#grid-size').val()!=''){
		quant='{2,'+jQuery('#grid-size').val()+'}';
		
	}
	jQuery("input.word" ).prop("pattern", '['+jQuery('#alphabet').val()+']'+quant);
	
	jQuery('input.word').each(function(){
		let input =jQuery(this)[0];
		
		 if (!input.checkValidity()){
			 jQuery(this).addClass('error');
			error += (input.validationMessage || 'Invalid character.')+"<br/>";
			 valid=false;
		 }
	  	 
	})
	
	
	jQuery('#result-message').html('😞 '+error).css({color:'red'});
	return valid;	
	  }


function save_alphabet(alphabet,name){
	jQuery.ajax( {
		
		url: wf_frontendajax.ajax_url,
		
		method: 'POST',
		
		data:{
			'action': 'wf_save_alphabet_action',
			'alphabet':alphabet,
			'name':name,
			
			
		},
		success:function(response) {
			if(response.success==false){
				/*jQuery('<p class="wf_ajax_error">'+response.data+'</p>').insertAfter('#save_alphabet');
				setTimeout(function() { jQuery(".wf_ajax_error").remove(); }, 5000);*/
			jQuery('#ajax-response').addClass('notice inline notice-error ').html(response.data);
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			}
			else{
				jQuery('#saved_alphabets').html(response);
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			console.log(response);
			//console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			
			//jQuery('#saved_alphabets').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>'); 
			jQuery('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>').appendTo('#save_alphabet');
		}	
	});
	
}
function delete_alphabet(name){
	if(confirm('Are you sure?')){
		jQuery.ajax( {

			url: wf_frontendajax.ajax_url,

			method: 'POST',

			data:{
				'action': 'wf_delete_alphabet_action',
				'name':name,


			},
			success:function(response) {


				jQuery('#saved_alphabets').html(response);
			},
			error: function (xhr, ajaxOptions, thrownError,response) {

				//console.log(thrownError);
				var err = eval("(" + xhr.responseText + ")");

				jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>');
				setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);

			}	
		});
	}
}
function save_exceptions(exceptions){
	jQuery.ajax( {
		
		url: wf_frontendajax.ajax_url,
		
		method: 'POST',
		
		data:{
			'action': 'wf_save_exceptions_action',
			'exceptions':exceptions,
			
			
			
		},
		success:function(response) {
			if(response.success==false){
				jQuery('#ajax-response').addClass('notice inline notice-error ').html(response.data);
				setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
				
			}
			else{
				
				jQuery('#ajax-response').addClass('notice inline notice-success ').html('Saved');
				setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-success ').html(''); }, 5000);
			}
			
		},
		error: function (xhr, ajaxOptions, thrownError,response) {
			console.log(response);
			//console.log(thrownError);
			var err = eval("(" + xhr.responseText + ")");
			jQuery('#ajax-response').addClass('notice inline notice-error ').html('<h3>Error '+ xhr.status + ' </h3><p>'+err.message+'</p>');
			setTimeout(function() { jQuery("#ajax-response").removeClass('notice inline notice-error ').html(''); }, 5000);
			
			
		}	
	});
	
}
