registerForm = function(container, nextContainer) {
	$('#registration').on('click', container + ' .registration-button', function(e){
		form = $('.form', container);
		nextForm = $('.form', nextContainer);
		if(form.is(':visible')) {
			form.hide();
		} else {
			nextForm.hide();
			form.show();
		}
	})
}

registerAjaxRegistration = function(container) {
	$(container).on('submit', 'form', function(e){
		form = this;
		e.preventDefault();
		action = this.action;
		$('body').css('cursor', 'wait');
		$.ajax({
			type: 'POST',
			url: action,
			data:  $( form ).serialize()
		}).done(function( data ){
			if(data.redirect) {
				$.ajax({
					type: "GET",
					url: data.redirect
				}).done(function(data){
					$('body').css('cursor', 'auto');
					container.html(data.html);
				});
			}
		})
	})
}

$(document).ready(function() {
	$('.tooltip').tooltipster();
	$('a').smoothScroll();
	registerForm('#speaker-regestration', '#user-regestration');
	registerForm('#user-regestration', '#speaker-regestration');
	registerAjaxRegistration($('#registration'));
});