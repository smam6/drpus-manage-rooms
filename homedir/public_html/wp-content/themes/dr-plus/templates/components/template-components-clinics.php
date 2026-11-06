<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'title_tag'		=> 'div',
	'desktop_cols'	=> 6,
	'tablet_cols'	=> 4,
	'mobile_cols'	=> 3,
] );

$args['title_tag'] = Sanitizers::tag( $args['title_tag'] );

$wrap_attrs = [
	'class'	=> ['clinics', "desktop-columns-{$args['desktop_cols']}", "tablet-columns-{$args['tablet_cols']}", "mobile-columns-{$args['mobile_cols']}"],
	'style'	=> [
		'--desktop-cols'	=> $args['desktop_cols'],
		'--tablet-cols'		=> $args['tablet_cols'],
		'--mobile-cols'		=> $args['mobile_cols'],
	],
	'data-desktop-cols'	=> $args['desktop_cols'],
	'data-tablet-cols'	=> $args['tablet_cols'],
	'data-mobile-cols'	=> $args['mobile_cols'],
];
?>
<div <?php echo Utils::get_html_attributes( $wrap_attrs ) ?>>
	<?php
	foreach( $args['items'] as $item ) {
		$has_link = !empty( $item['link'] ) && !empty( $item['link']['url'] );
		?>
		<?php if( $has_link ) { ?>
			<a <?php echo Utils::get_html_attributes( Elementor::get_link_attributes( $item['link'] ) ) ?> class="clinic" title="<?php echo esc_attr( $item['title'] ) ?>">
		<?php } else { ?>
			<div class="clinic">
		<?php } ?>
			<div class="clinic-inner">
				<div class="clinic-icon-wrap">
					<?php
					if( $item['icon_type'] == 'icon' ) {
						echo Sanitizers::icon( $item['icon'], 'clinic-icon' );
					} else {
						if( !empty( $item['img'] ) ) {
							echo !empty( $item['img']['id'] ) ? wp_get_attachment_image( $item['img']['id'], [42, 42], false, ['class' => 'clinic-icon'] ) : '<img src="' . $item['img']['url'] . '" alt="' . esc_attr( $item['title'] ) . '" class="clinic-icon">';
						}
					}
					?>
				</div>

				<<?php echo tag_escape( $args['title_tag'] ) ?> class="clinic-title line-clamp line-clamp-1"><?php echo esc_html( $item['title'] ) ?></<?php echo tag_escape( $args['title_tag'] ) ?>>

			</div>
			<?php
			get_template_part( "templates/components/template-components-simple_icon", null, [
				'icon'		=> 'drplus-icon-diamond',
				'classes'	=> ['clinic-separator'],
			] );
			get_template_part( "templates/components/template-components-simple_icon", null, [
				'icon'		=> 'drplus-icon-diamond',
				'classes'	=> ['clinic-separator', 'clinic-separator-white'],
			] );
			?>

			<div class="clinic-popover"><?php echo esc_html( $item['title'] ) ?></div>

		<?php if( $has_link ) { ?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>
		<div class="clinic clinic-empty"></div>
		<div class="clinic clinic-empty"></div>
	<?php } ?>
</div>