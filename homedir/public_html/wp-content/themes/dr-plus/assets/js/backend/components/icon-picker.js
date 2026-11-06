(function($) {
	$(document).ready(function(){
		let activeClass = 'icon-picker-selected';
		function openModal(modal, selectedIcon, fieldID) {			
			if(selectedIcon) {
				modal.find(`.${activeClass}`).removeClass(activeClass);
				selectedIcon = "." + selectedIcon.replaceAll(" ", ".");
				modal.find(`${selectedIcon}`).parent().addClass(activeClass);
			}
			modal.attr('data-field', fieldID);
			drplusModalOpen(modal);
			modal.find('.icon-picker-search').focus();
		}
		// Get HTMl from AJAX and open
		$(document).on('click', '.icon-picker-select', function(e) {
			e.preventDefault();			
			let form = $(this).closest('.icon-picker-form'),
				field = form.find('.icon-picker-field'),
				selectedIcon = field.val(),
				modal = $(`#${form.attr('data-modal')}`),
				modalContent = modal.find('.drplus-modal-content');
			if(!modalContent.html()) {
				$.ajax({
					url: drplusIconPicker.ajaxUrl,
					type: 'POST',
					data: {
						action: 'drplus_icon_picker',
						nonce: drplusIconPicker.nonce
					},
					success: function(res) {
						modalContent.html(res)
						openModal(modal, selectedIcon, field.attr('id'));
					}
				});
			} else {
				openModal(modal, selectedIcon, field.attr('id'));
			}
		});
		// Change pack
		$(document).on('click', `.icon-picker-pack-selector:not(.selected)`, function() {
			$(`.icon-picker-pack-selector`).removeClass('selected');
			$(this).addClass('selected');
			let pack = $(this).attr('data-pack');
			if(pack) {
				$(`.icon-picker-pack-content:not([data-pack="${pack}"])`).hide();
				$(`.icon-picker-pack-content[data-pack="${pack}"]`).show();
			} else {
				$('.icon-picker-pack-content').show();
			}
		});
		// Change selected icon
		$(document).on('click', `.icon-picker-icon:not(.${activeClass})`, function() {
			$(`.${activeClass}`).removeClass(activeClass);
			$(this).addClass(activeClass)
		})
		// Search
		$(document).on('keyup', '.icon-picker-search', function() {
			let text = $(this).val();
			if(text) {
				$('.icon-picker-icon-icon').each(function() {
					if($(this).attr('class').includes(text) || ( $(this).attr('src') != undefined && $(this).attr('src').includes(text) ) || $(this).siblings('.icon-picker-icon-name').text().toLowerCase().includes(text)) {
						$(this).parent().show();
					} else {
						$(this).parent().hide();
					}
	
				});
			} else {
				$('.icon-picker-icon').show();
			}
		});
		// Apply
		$('.drplus-modal .drplus-modal-submit-btn').on('click', function(e) {
			e.preventDefault();
			let iconClass;
			let source;
			if( $('.icon-picker-selected i').length ) {
				iconClass = $('.icon-picker-selected i').attr('class');
				source = iconClass;
			} else {
				iconClass = $('.icon-picker-selected img').attr('src');
				source = iconClass;
				iconClass = iconClass.split('/');
				iconClass = iconClass[iconClass.length - 1].replace('.svg', '');
			}			
			iconClass = iconClass.replace('icon-picker-icon-icon ', '')
			$(`#${$(this).closest('.drplus-modal').attr('data-field')}`).val(iconClass).attr('data-source', source).trigger('change');
		})
		// Change icon
		$(document).on('change', '.icon-picker-field', function() {
			let source = $(this).val();
			let type = $(this).attr('data-source').includes('.svg') ? 'svg' : 'icon';
			if(type == 'icon') {
				$(this).closest('.icon-picker-form').find('img.icon-picker-select-icon').hide();
				$(this).closest('.icon-picker-form').find('i.icon-picker-select-icon').attr('class', 'icon-picker-select icon-picker-select-icon ' + source).show();
			} else {
				$(this).closest('.icon-picker-form').find('i.icon-picker-select-icon').hide();
				$(this).closest('.icon-picker-form').find('img.icon-picker-select-icon').attr('src', $(this).attr('data-source')).show();
			}
		})
	});
})(jQuery);