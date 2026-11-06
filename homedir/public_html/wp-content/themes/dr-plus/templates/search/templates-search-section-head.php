<?php

use DrPlus\Components\Button;
use DrPlus\Components\SectionTitle;

echo '<div class="search-row-head">';
	$show_all_link = '';
	if( !empty( $args['show_more'] ) ) {
		$show_all_link = add_query_arg( [
			's'			=> get_search_query(),
			'section'	=> $args['post_type']
		], home_url() );
	}
	SectionTitle::view( [
		'icon'		=> $args['icon'],
		'title'		=> "{$args['label']} ({$args['count']})",
		'link'		=> $show_all_link,
		'nav_btns'	=> !empty( $args['show_more'] ),
	] );

	if( !empty( $args['show_more'] ) && $args['count'] > 8 ) {
		Button::view( [
			'text'			=> __( "Show All", 'drplus' ),
			'small'			=> true,
			'link'			=> $show_all_link,
			'icon'			=> is_rtl() ? 'drplus-icon-arrow-square-left' : 'drplus-icon-arrow-square-right',
			'icon_align'	=> 'end',
		] );
	}
echo '</div>';