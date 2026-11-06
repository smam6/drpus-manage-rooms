<?php
defined( 'ABSPATH' ) || exit;

$help_url = 'https://amin-noorani.ir/releases/drplus/Help.pdf';

$help_btn_html = '<a href="' . $help_url . '" target="_blank" class="button button-primary" style="margin-bottom:16px">' . esc_html__( "View PDF", 'drplus' ) . '</a>';
$help_btn_html .= '<a href="' . $help_url . '" target="_blank" class="button" download style="margin-bottom:16px;margin-inline-start:8px">' . esc_html__( "Download PDF", 'drplus' ) . '</a>';
$help_pdf_html = '<object data="' . $help_url . '" type="application/pdf" width="100%" height="800px"><iframe src="' . $help_url . '" width="100%" height="800px" style="border: none;"></iframe></object>';

$result = $help_btn_html . $help_pdf_html;

Redux::set_section( // Help
	$opt_name,
	array(
		'title'			=> esc_html__( 'Help', 'drplus' ),
		'id'			=> 'help-section',
		'subsection'	=> true,
		'fields'		=> array(
			[ // help
				'id'			=> 'help',
				'type'			=> 'raw',
				'full_width'	=> true,
				'content'		=> $result,
			],
		),
	)
);