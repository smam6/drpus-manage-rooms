document.addEventListener('DOMContentLoaded', function () {
	const selectWraps = document.querySelectorAll('.drplus-custom-select-wrap');
	const selectPopover = document.querySelector('.drplus-custom-select-popover');
	const selectPopoverList = selectPopover.querySelector('.drplus-custom-select-popover-list');
	const searchInput = selectPopover.querySelector('input');
	let activeSelectWrap = null;

	// Event delegation for popover items
	selectPopoverList.addEventListener('click', function(event) {
		const item = event.target.closest('.drplus-custom-select-popover-item');
		if (!item || !activeSelectWrap) return;
		
		const selectElement = activeSelectWrap.querySelector('select');
		if (selectElement) {
			selectElement.value = item.dataset.value;
			selectElement.dispatchEvent(new Event('change'));
			activeSelectWrap.querySelector('.drplus-custom-select-placeholder').textContent = item.textContent;
			activeSelectWrap.classList.add('selected');
		}

		hidePopover();
	});

	// Optimized popover positioning
	function setPopoverPosition() {
		if( !activeSelectWrap ) return;
		const wrapRect = activeSelectWrap.getBoundingClientRect();
		selectPopover.style.cssText = `
			top: ${wrapRect.bottom + window.scrollY - parseInt(window.getComputedStyle(document.documentElement).marginTop)}px;
			left: ${wrapRect.left + window.scrollX}px;
			width: ${wrapRect.width}px;
		`;
	}

	function hidePopover() {
		selectPopover.classList.remove('show');
		if (activeSelectWrap) {
			activeSelectWrap.classList.remove('active');
			activeSelectWrap = null;
		}
	}

	function showPopover() {
		if (!activeSelectWrap) return;
		setPopoverPosition();
		selectPopover.classList.add('show');
	}

	// Select wrap handlers
	selectWraps.forEach(selectWrap => {
		selectWrap.addEventListener('click', function(event) {
			event.preventDefault();
			event.stopPropagation();

			if (activeSelectWrap === selectWrap) {
				hidePopover();
				return;
			}

			// Update popover content
			selectPopoverList.innerHTML = '';
			const fragment = document.createDocumentFragment();
			const options = selectWrap.querySelectorAll('select option');

			options.forEach(option => {
				if (!option.textContent.trim()) return;

				const div = document.createElement('div');
				div.className = 'drplus-custom-select-popover-item';
				div.dataset.value = option.value;
				div.title = option.text;
				
				const span = document.createElement('span');
				span.className = 'drplus-custom-select-popover-item-text';
				span.textContent = option.text;
				div.appendChild(span);

				if (option.selected) div.classList.add('selected');
				fragment.appendChild(div);
			});
			selectPopoverList.appendChild(fragment);

			// Update active state
			activeSelectWrap?.classList.remove('active');
			activeSelectWrap = selectWrap;
			activeSelectWrap.classList.add('active');
			
			searchInput.value = '';
			showPopover();
			searchInput.focus();
		});
	});

	// Search functionality
	searchInput.addEventListener('input', (event) => {
		const search = event.target.value.toLowerCase();
		selectPopoverList.querySelectorAll('.drplus-custom-select-popover-item').forEach(item => {
			item.style.display = item.textContent.toLowerCase().includes(search) ? 'flex' : 'none';
		});
	});

	// Close popover on outside click
	document.addEventListener('click', (event) => {
		if (!selectPopover.contains(event.target) && !activeSelectWrap?.contains(event.target)) {
			hidePopover();
		}
	});

	window.addEventListener('resize', setPopoverPosition);
});