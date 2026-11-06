(function() {
	var container = document.getElementById('site-navigation');
	if (!container) return;

	var button = container.querySelector('.menu-toggle');
	var menu   = container.querySelector('#primary-menu');

	if (!button || !menu) return;

	button.setAttribute('aria-expanded', 'false');
	menu.setAttribute('aria-hidden', 'true');

	button.addEventListener('click', function() {
		var expanded = this.getAttribute('aria-expanded') === 'true';
		this.setAttribute('aria-expanded', expanded ? 'false' : 'true');

		menu.classList.toggle('toggled-on');

		if (!expanded) {
			menu.setAttribute('aria-hidden', 'false');
			var firstLink = menu.querySelector('a');
			if (firstLink) {
				firstLink.focus();
			}
		} else {
			menu.setAttribute('aria-hidden', 'true');
		}
	});

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			if (menu.classList.contains('toggled-on')) {
				menu.classList.remove('toggled-on');
				menu.setAttribute('aria-hidden', 'true');
				button.setAttribute('aria-expanded', 'false');
				button.focus();
			}
		}
	});
})();
