(function () {
	let $ = jQuery;
	const prefix = 'drplus_hospital_';

	// Gallery
	document.getElementById(`${prefix}gallery-wrap`).addEventListener('click', function() {
		let selectedFileInput = document.getElementById(`${prefix}gallery`);
		var fileFrame = wp.media({
			frame: 'select',
			editing : true,
			multiple : true,
			library: {
				type: 'image'
			},
			selection : ""
		});

		fileFrame.on('open', function() {
			var selection = fileFrame.state().get('selection');
			let ids = selectedFileInput.value ? selectedFileInput.value.split(',') : [];
			ids.forEach( function( id ) {
				let attachment = wp.media.attachment( id );
				selection.add( attachment ? [ attachment ] : []);
			});
		});

		fileFrame.on('select', function() {
			$(`#${prefix}gallery-wrap img`).remove();
			let ids = [];
			fileFrame.state().get('selection').map((attachment) => {
				attachment = attachment.toJSON();
				$(`<img src="${attachment.url}" alt="">`).insertBefore(`#${prefix}gallery-add`);
				ids.push(attachment.id);
			});
			selectedFileInput.value = ids.join(',');
		});

		fileFrame.open();
	})

	function updateIndexes(itemsClass) {
		$(`.${itemsClass}`).each(function(index) {
			$(this).find('input').each(function() {
				$(this).attr('name', $(this).attr('name').replace(/\d+/g, index));
			})
		});
	}

	let repeaterSections = {
		service: {
			swapy: {},
			wrap: 'services',
			item: 'service',
			variables: {
				index: 0,
				title: '',
				description: '',
			}
		},
		phone: {
			swapy: {},
			wrap: 'phones',
			item: 'phone',
			variables: {
				index: 0,
				title: '',
				phone: '',
			}
		},
		email: {
			swapy: {},
			wrap: 'emails',
			item: 'email',
			variables: {
				index: 0,
				title: '',
				email: '',
			}
		},
		social: {
			swapy: {},
			wrap: 'socials',
			item: 'social',
			variables: {
				index: 0,
				title: '',
				icon: '',
				link: '',
			}
		}
	};
	for(const [key, details] of Object.entries(repeaterSections)) {
		details.swapy = Swapy.createSwapy(document.getElementById(prefix + details.wrap));
		details.variables.index = document.getElementsByClassName(prefix + details.item).length;

		// Add
		document.getElementById(`${prefix}${details.item}-add`).addEventListener('click', function() {
			let template = wp.template(`drplus-hospital-${details.item}`);
			html = template( details.variables );
			details.variables.index++;
	
			$(html).insertBefore(this);
			details.swapy.update();
		});

		// Remove
		$(document).on('click', `.${prefix}${details.item}-remove`,function() {
			$(this).closest(`.${prefix}${details.item}-slot`).remove();
			details.swapy.update();
			updateIndexes(prefix + details.item);
		})
		details.swapy.onSwapEnd(function() {
			updateIndexes(prefix + details.item);
		});
	}
})()