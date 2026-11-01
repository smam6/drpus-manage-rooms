/**
 *  showcase jquery
 */
(jQuery)(document).ready(function ($){
    $('.card__product').hover(
        function () {
            let title       = $(this).find('.card__product__body__title');
            let lineHeight  = parseFloat(title.css('line-height'));
            let titleHeight = title.height();

            if (titleHeight > lineHeight) {
                title.addClass('card__product__body__lineClamp');
            }
        },
        function () {
            let title = $(this).find('.card__product__body__title');
            title.removeClass('card__product__body__lineClamp');
        }
    );
})