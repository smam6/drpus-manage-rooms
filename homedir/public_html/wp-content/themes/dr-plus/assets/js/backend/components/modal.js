function drplusModalOpen(id) {
	if(typeof id == 'string' && id.substring(0,1) != '#') {
		id = `#${id}`;
	}
	jQuery(id).fadeIn();
	jQuery(id).next().fadeIn();
}
function drplusModalClose(id) {
	if(typeof id == 'string' && id.substring(0,1) != '#') {
		id = `#${id}`;
	}
	jQuery(id).fadeOut();
	jQuery(id).next().fadeOut();
}
(function($) {
	$(document).ready(function(){
		$('.drplus-modal-overlay').on('click', function() {
			drplusModalClose($(this).prev());
		})
		$('.drplus-modal-close').on('click', function(e) {
			e.preventDefault();
			drplusModalClose($(this).closest('.drplus-modal'))
		})
	});
})(jQuery);