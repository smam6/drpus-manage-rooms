<?php

use DrPlus\Utils\UI;

if( !defined( 'ABSPATH' ) ) exit;

$faqs = $specialist->meta['faqs'];
if( empty( $faqs ) ) {
	$faqs[] = [
		'question'	=> '',
		'answer'	=> '',
	];
}

UI::dropzone( [], true );
$repeater_rows = [];
foreach( $faqs as $index => $faq ) {
	$repeater_rows[$index] = [
		[
			'type'	=> 'full',
			'field'	=> [
				'type'			=> 'text',
				'placeholder'	=> __( "Question", 'drplus' ),
				'name'			=> "specialist_meta[faqs][%index%][question]",
				'classes'		=> ['input-secondary', 'faq-question'],
				'value'			=> $faq['question'],
			],
		],
		[
			'type'	=> 'full',
			'field'	=> [
				'type'			=> 'textarea',
				'placeholder'	=> __( "Answer", 'drplus' ),
				'name'			=> "specialist_meta[faqs][%index%][answer]",
				'classes'		=> ['input-secondary', 'faq-answer'],
				'value'			=> $faq['answer'],
			],
		]
	];
}
?>
<div class="drplus-specialist-form-body drplus-specialist-form-faqs">
	<?php
	UI::repeater( [
		'template_id'	=> 'specialist-faq',
		'style'			=> 'grid',
		'flow'			=> 'row',
		'slot_attrs'	=> [
			'data-swapy-slot'	=> 'specialist-faq-%index%-slot',
		],
		'item_attrs'	=> [
			'data-swapy-item'	=> 'specialist-faq-%index%-item',
		],
		'rows'	=> $repeater_rows
	] );
	?>
</div>