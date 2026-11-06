(function($) {
	$(document).ready(function(){
		$('.changelogs-sidebar-version').on('click', function() {
			if($(this).hasClass('active')) return;
			$(this).addClass('active').siblings('.active').removeClass('active');
			
			let version = $(this).attr('data-version'),
				changelog = changelogItems[version];
			$('.changelogs-item').remove();
			$('#changelogs-version').text(version);
			$('#changelogs-time').text($(this).find('.changelogs-sidebar-version-time').text());
			changelog.log.forEach( function(item) {
				$(`<div class="changelogs-item">${item}</div>`).appendTo('#changelogs-items');
			} )
		})
	});
})(jQuery);