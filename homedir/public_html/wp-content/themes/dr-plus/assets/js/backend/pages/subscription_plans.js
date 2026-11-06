(function($) {
	$(document).ready(function(){
		const prefix = 'drplus_plans_';
		let plansIndex = $(`.${prefix}plan_item`).length;

		$(`#${prefix}add_plan_btn`).on('click', function(e) {
			e.preventDefault();
			let template = wp.template(`drplus-plan-item`);
			let html = template({
				index: plansIndex
			});
			$(html).appendTo(`.${prefix}plans_wrap`);
			
			$(`.${prefix}plan_item:last-child .${prefix}plan_item_head`).addClass('active');
			$(`.${prefix}plan_item:last-child .${prefix}plan_item_inner`).slideDown();
			plansIndex++;
		});

		$(document).on('click', `.${prefix}plan_item_remove`, function() {
			$item = $(this).closest(`.${prefix}plan_item`);
			$item.slideUp( {
				complete: function() {
					$item.remove();
					resetPlanIndexes();	
				}
			} );
		});

		$(document).on('keyup', `.${prefix}plan_title`, function() {
			$(this).closest(`.${prefix}plan_item`).find(`.${prefix}plan_item_name`).text($(this).val());
		});

		$(document).on('click', `.${prefix}plan_item_head`, function() {
			$(this).closest(`.${prefix}plan_item`).find(`.${prefix}plan_item_inner`).slideToggle();
			$(this).toggleClass('active');
		});

		function resetPlanIndexes() {
			$(`.${prefix}plan_item`).each(function(index) {
				$(this).find('input, select, textarea, label').each(function() {
					let name = $(this).attr('name');
					let id = $(this).attr('id');
					let forAttr = $(this).attr('for');

					if (name) {
						let newName = name.replace(/\d+/, index);
						$(this).attr('name', newName);
					}
					if (id) {
						let newId = id.replace(/\d+/, index);
						$(this).attr('id', newId);
					}
					if (forAttr) {
						let newFor = forAttr.replace(/\d+/, index);
						$(this).attr('for', newFor);
					}
				});
			});
			plansIndex = $(`.${prefix}plan_item`).length;
		}
	});
})(jQuery);