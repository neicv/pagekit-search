Vue.ready(function () {
    jQuery('time').each(function() {
        new Vue({}).$mount(this);
    });
	
	var $ = jQuery;
	
	$('#search-form').on('submit', function(e) {

    e.preventDefault();
    e.stopImmediatePropagation();

        
	var form  = $(this);
    var action = form.attr('action');
    var data = form.serialize();

        
	// Process
    $.post(action, data, function(data) 
		{

        

		});
	});	
});
