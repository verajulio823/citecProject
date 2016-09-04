
function scroll_to_class(element_class, removed_height) {
	var scroll_to = $(element_class).offset().top - removed_height;
	if($(window).scrollTop() != scroll_to) {
		$('html, body').stop().animate({scrollTop: scroll_to}, 0);
	}
}

function bar_progress(progress_line_object, direction) {
	var number_of_steps = progress_line_object.data('number-of-steps');
	var now_value = progress_line_object.data('now-value');
	var new_value = 0;
	if(direction == 'right') {
		new_value = now_value + ( 100 / number_of_steps );
	}
	else if(direction == 'left') {
		new_value = now_value - ( 100 / number_of_steps );
	}
	progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);
}

jQuery(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch("assets/img/backgrounds/1.jpg");

    $('#top-navbar-1').on('shown.bs.collapse', function(){
    	$.backstretch("resize");
    });
    $('#top-navbar-1').on('hidden.bs.collapse', function(){
    	$.backstretch("resize");
    });

    /*
        Form
    */
    $('.f1 fieldset:first').fadeIn('slow');

    $('.f1 input[type="text"], .f1 input[type="password"], .f1 textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });

    // next step
    $('.f1 .btn-next').on('click', function() {
    	var parent_fieldset = $(this).parents('fieldset');
    	var next_step = true;
    	// navigation steps / progress steps
    	var current_active_step = $(this).parents('.f1').find('.f1-step.active');
    	var progress_line = $(this).parents('.f1').find('.f1-progress-line');

    	// fields validation
    	parent_fieldset.find('input[type="text"], input[type="password"], textarea, input[type="email"]').each(function() {
    		//visible para el tema de los papers
            if( $(this).val() == "" && this.id!="fax" && this.id!="codigoPostal" && this.id!="departamento" && $(this).is(':visible')) {
    			$(this).addClass('input-error');
    			next_step = false;
            }
    		else {
                //validación del mail (no se usa input email debido a que lo revisa en el submit, y me interesa en el boton "siguiente")
                if(this.id=="email"){
                    x= $(this).val();
                    atpos = x.indexOf("@");
                    dotpos = x.lastIndexOf(".");
                    if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length) {
                        $(this).addClass('input-error');
    			         next_step = false;
                    }
                }
    			else $(this).removeClass('input-error');
    		}
    	});

        parent_fieldset.find('input[type="file"]').each(function() {
    		if( $(this).val() == "" && $(this).is(':visible')) {
    			$(this).addClass('inputFile-error');
    			next_step = false;
    		}
    		else {
    			$(this).removeClass('inputFile-error');
    		}
    	});

        parent_fieldset.find('input[type="number"]').each(function() {
    		if( $(this).val() == "") {
    			$(this).addClass('input-error');
    			next_step = false;
    		}
    		else {
                if($(this).val()<0  && this.id!="cantPaper"){
                    alert("Ingrese valores positivos");
                    $(this).addClass('input-error');
    			     next_step = false;
                }
    			$(this).removeClass('input-error');
    		}
    	});

        parent_fieldset.find('select').each(function() {
            //visible para el tema de los papers
    		if( $(this).val() == "0" && $(this).is(':visible')) {
    			$(this).addClass('select-error');
    			next_step = false;
    		}
    		else {
    			$(this).removeClass('select-error');
    		}
    	});
    	// fields validation

    	if( next_step ) {
    		parent_fieldset.fadeOut(400, function() {
    			// change icons
    			current_active_step.removeClass('active').addClass('activated').next().addClass('active');
    			// progress bar
    			bar_progress(progress_line, 'right');
    			// show next step
	    		$(this).next().fadeIn();
	    		// scroll window to beginning of the form
    			scroll_to_class( $('.f1'), 20 );
	    	});
    	}
        else{
            alert("Faltan campos por completar.");
        }

    });

    // previous step
    $('.f1 .btn-previous').on('click', function() {
    	// navigation steps / progress steps
    	var current_active_step = $(this).parents('.f1').find('.f1-step.active');
    	var progress_line = $(this).parents('.f1').find('.f1-progress-line');

    	$(this).parents('fieldset').fadeOut(400, function() {
    		// change icons
    		current_active_step.removeClass('active').prev().removeClass('activated').addClass('active');
    		// progress bar
    		bar_progress(progress_line, 'left');
    		// show previous step
    		$(this).prev().fadeIn();
    		// scroll window to beginning of the form
			scroll_to_class( $('.f1'), 20 );
    	});
    });

    // submit
    $('.f1').on('submit', function(e) {

    	// fields validation
    	$(this).find('input[type="text"], input[type="password"], textarea').each(function() {
    		if( $(this).val() == "" && this.id!="fax" && this.id!="codigoPostal" && this.id!="departamento" && $(this).is(':visible') ) {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    	// fields validation

    });


});
