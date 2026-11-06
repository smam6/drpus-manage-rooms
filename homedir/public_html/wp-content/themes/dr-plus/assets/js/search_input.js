var drplusSearchInput = {
	init: function() {
		this.inputs = document.querySelectorAll('.drplus-search-input');
		this.inputPopover = document.querySelector('.drplus-search-input-popover');
		this.inputPopoverList = document.querySelector('.drplus-search-input-popover-list');

		this.inputs.forEach(input => {
			// check if input has a '.drplus-search-with-ajax' first
			if (input.classList.contains('drplus-search-with-ajax')) {
				input.addEventListener('blur', () => {
					if(!input.value) {
						input.value = input.dataset.value;
					}
				});
			}
		});

		document.addEventListener('click', (event) => {
			if (!this.inputPopover.contains(event.target) && !this.activeInput?.contains(event.target)) {
				this.hidePopover();
			}
		});
		document.addEventListener('click', (event) => {
			if( this.activeInput && event.target.closest('.drplus-search-input-popover-item') ) {
				let wrap = this.activeInput.parentElement,
					item = event.target.closest('.drplus-search-input-popover-item'),
					itemText = item.querySelector('.drplus-search-input-popover-item-title').textContent;
				if(item.nodeName != 'A') {
					wrap.querySelector('.drplus-search-input-alt').value = item.dataset.value;
					this.activeInput.value = itemText;
					this.activeInput.dataset.value = itemText;
					this.activeInput.dispatchEvent(new Event('change'));
	
					wrap.classList.add('filled');
	
					this.hidePopover();
				}
			}
		});
		window.addEventListener('resize', this.setPopoverPosition);
	},
	showPopover: function(loading = 'show') {
		if (!this.activeInput) return;
		this.setPopoverPosition();
		this.inputPopover.classList.add('show');
		if(loading == 'show') {
			this.inputPopover.classList.add('loading');
		} else {
			this.inputPopover.classList.remove('loading');
		}
	},
	hidePopover: function() {
		this.inputPopover.classList.remove('show');
		if (this.activeInput) {
			this.activeInput.parentElement.classList.remove('active');
			this.activeInput = null;
		}
	},
	setPopoverPosition: function() {
		if (!this.activeInput) return;
		const wrapRect = this.activeInput.parentElement.getBoundingClientRect();
		this.inputPopover.style.cssText = `
			top: ${wrapRect.bottom + window.scrollY - parseInt(window.getComputedStyle(document.documentElement).marginTop)}px;
			left: ${wrapRect.left + window.scrollX}px;
			width: ${wrapRect.width}px;
		`;
	},
	clearPopoverItems: function() {
		this.inputPopoverList.innerHTML = '';
	},
	reset: function() {
		this.clearPopoverItems();
		this.inputPopover.classList.add('loading');
		this.inputPopover.classList.remove('empty');
	},
	addPopoverItem: function(item) {
		if (!this.activeInput) return;

		let fragment = document.createDocumentFragment();
		let wrap;
		if( typeof item.link != 'undefined' ) {
			wrap = document.createElement('a');
			wrap.href = item.link;
		} else {
			wrap = document.createElement('div');
		}
		wrap.className = 'drplus-search-input-popover-item';
		wrap.dataset.value = item.value;
		wrap.title = item.text;

		let iconWrap = document.createElement('div');
		iconWrap.className = 'drplus-search-input-popover-item-icon-wrap';
		if( typeof item.icon != 'undefined' && item.icon !== '' ) {
			let icon = document.createElement('i');
			icon.className = item.icon;
			iconWrap.appendChild(icon);
		} else if( item.img !== '' ) {
			let img = document.createElement('img');
			img.src = item.img;
			img.alt = item.text;
			iconWrap.appendChild(img);
		}
		wrap.appendChild(iconWrap);

		let texts = document.createElement('div');
		texts.className = 'drplus-search-input-popover-item-text';

		let title = document.createElement('span');
		title.className = 'drplus-search-input-popover-item-title';
		title.textContent = item.text;
		texts.appendChild(title);
		let subtitle = document.createElement('span');
		subtitle.className = 'drplus-search-input-popover-item-subtitle';
		subtitle.innerHTML = item.sub;
		texts.appendChild(subtitle);

		wrap.appendChild(texts);

		fragment.appendChild(wrap);
		this.inputPopoverList.appendChild(fragment);
	},
	showEmpty: function() {
		this.inputPopover.classList.add('empty');
	},
};
document.addEventListener('DOMContentLoaded', () => {
	drplusSearchInput.init()
});