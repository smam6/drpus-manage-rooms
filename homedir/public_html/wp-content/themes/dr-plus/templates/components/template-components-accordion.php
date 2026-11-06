<?php

use DrPlus\Utils;
use DrPlus\Utils\Elementor;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'items'			=> [],
	'open_icon'		=> 'drplus-icon-bottom',
	'close_icon'	=> 'drplus-icon-top',
	'title_tag'		=> 'div',
	'faq_schema'	=> false,
	'item_style'	=> 'style-1'
], ['open_icon', 'close_icon'] );

$default_item_args = [
	'title'			=> '',
	'text'			=> '',
	'show_bg_icon'	=> true, // Only for style-2
	'bg_icon'		=> 'drplus-icon-dr-plus-1'
];
$items = [];
foreach( $args['items'] as $item ) {
	$item = Utils::check_default( $item, $default_item_args, ['bg_icon'] );
	$items[] = [
		'title'			=> wp_kses_post( $item['title'] ),
		'text'			=> wp_kses_post( $item['text'] ),
		'bg_icon'		=> Sanitizers::icon( $item['bg_icon'], 'accordion-item-bg-icon' ),
		'show_bg_icon'	=> $item['show_bg_icon'] && $args['item_style'] == 'style-2'
	];
}

$open_icon = Sanitizers::icon( $args['open_icon'], 'accordion-item-icon-open' );
$close_icon = Sanitizers::icon( $args['close_icon'], 'accordion-item-icon-close' );
$title_tag = Sanitizers::tag( $args['title_tag'] );

$default_item = 0;
?>
<div class="accordion-items accordion-<?php echo $args['item_style'] ?>">
	<?php foreach ( $items as $index => $item ) { 
		$is_active = $default_item === $index;
		$item_id = 'accordion-item-' . wp_unique_id();
		$panel_id = $item_id . '-panel';
	?>
		<div class="accordion-item<?php echo $is_active ? ' accordion-item-default accordion-item-active' : '' ?>">
			<div class="accordion-item-head">
				<?php if( $args['item_style'] == 'style-2' ) { ?>
					<span class="accordion-item-head-line"></span>
				<?php } ?>
				<<?php echo tag_escape( $title_tag ) ?>
					class="accordion-item-title" aria-expanded="<?php echo $is_active ? 'true' : 'false' ?>"
					aria-controls="<?php echo esc_attr( $panel_id ) ?>"
					role="button"
				>
					<?php echo $item['title'] ?>
				</<?php echo tag_escape( $title_tag ) ?>>

				<div class="accordion-item-icon">
					<?php echo $open_icon ?>
					<?php echo $close_icon ?>
				</div>
			</div>

			<div
				id="<?php echo esc_attr( $panel_id ) ?>"
				class="accordion-item-content"
				role="region"
				aria-labelledby="<?php echo esc_attr( $item_id ) ?>"
				<?php echo $is_active ? '' : 'hidden' ?>
			>
				<div class="accordion-item-content-text"><?php echo Elementor::parse_text_editor( $item['text'] ) ?></div>
				<?php if( $item['show_bg_icon'] ) { echo $item['bg_icon']; } ?>
			</div>
		</div>
	<?php } ?>
	
	<?php
	if( $args['faq_schema'] ) {
		$json = [
			'@context' => 'https://schema.org',
			'@type' => 'FAQPage',
			'mainEntity' => [],
		];

		foreach( $items as $item ) {
			$json['mainEntity'][] = [
				'@type' => 'Question',
				'name' => wp_strip_all_tags( $item['title'] ),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text' => Elementor::parse_text_editor( $item['text'] ),
				],
			];
		}
		?>
		<script type="application/ld+json"><?php echo wp_json_encode( $json ); ?></script>
	<?php } ?>	
</div>