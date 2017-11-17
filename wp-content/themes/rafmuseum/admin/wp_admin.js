(function($)
{   
	$( document ).ready(function() {
		var wp_admin = { 
		    initialize: function() { 
		        this.searchButtonInterview = $('#elastic_search_interview');
		        this.searchButtonStory = $('#elastic_search_story');
 
		        this.searchInputInterview = $('#elastic_search_interview_input');
		        this.searchInputStory = $('#elastic_search_story_input');

		        if(this.searchButtonInterview.length){

		        	this.searchButtonInterview.on('click', function(event) {
						wp_admin.search('elastic_search_interview', wp_admin.searchInputInterview.val()); 
			            event.preventDefault();   
			            return false; 
		        	});  
		        } 

				if(this.searchButtonStory.length){ 
		        	this.searchButtonStory.on('click', function(event) { 
						wp_admin.search('elastic_search_story', wp_admin.searchInputStory.val()); 
			            event.preventDefault();   
			            return false; 
		        	});  
		        } 
		    },
		    search: function(param, val){ 

				var USERNAME = 'raf';
				var PASSWORD = 'R4f!Mu$';  

				$.ajax ({
					type: "GET",
					url: "?" + param + "=" + val,
					dataType: 'json',
					async: false,
					beforeSend: function (xhr) {
						// xhr.setRequestHeader('Authorization', wp_admin.make_base_auth(USERNAME, PASSWORD));
					}
					
				}).done(function(data) {
					$('#elastic_results').html(data.html); 
				}); 
		    },		 
			make_base_auth: function(user, password) {
				var tok = user + ':' + password;
				var hash = btoa(tok);
				return 'Basic ' + hash;
			}

		};
		
	    wp_admin.initialize(); 
	});  
})(jQuery);
