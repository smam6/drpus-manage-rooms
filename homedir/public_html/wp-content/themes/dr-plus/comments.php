<?php
/**
 * The template for displaying comments
 *
 * The area of the page that contains both current comments
 * and the comment form.
 */

use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Sanitizers;

if( !defined( 'ABSPATH' ) ) exit;
if( post_password_required() || !post_type_supports( get_post_type(), 'comments' ) || !comments_open() ) {
	return;
}

$args = [
	'reviews_section_tag'	=> 'h3',
	'reviews_section_title'	=> apply_filters( 'drplus/comments/title', esc_html__( 'Comments', 'drplus' ) ),
	'reviews_section_icon'	=> 'drplus-icon-chat-fill',
	'form_section_tag'		=> 'h3',
	'form_section_title'	=> apply_filters( 'drplus/comments/reply/title', esc_html__( 'Add reply', 'drplus' ) ),
	'form_section_icon'		=> 'drplus-icon-diamond',
];
$query_args = get_query_var( 'drplus_comments_args' );
if( is_array( $query_args ) ) {
	foreach( $query_args as $key => $query_arg ) {
		if( $key == 'reviews_section_icon' || $key == 'form_section_icon' ) {
			foreach( $query_arg as $sub_key => $sub_part ) {
				$query_arg[$sub_key] = Utils::convert_chars( $sub_part );
			}
		} else {
			$query_args[$key] = Utils::convert_chars( $query_arg );
		}
	}
	$args = Utils::check_default( $query_args, $args, ['reviews_section_icon', 'form_section_icon'] );
}
?>
<div id="comments" class="row">
	<div class="comments-area col-12">
		<?php if( have_comments() ) { ?>
			<?php
			SectionTitle::view( [
				'icon'			=> $args['reviews_section_icon'],
				'tag'			=> $args['reviews_section_tag'],
				'title'			=> $args['reviews_section_title'],
				'aria-label'	=> $args['reviews_section_title'],
				'classes'		=> ['comments-title'],
			] );
			?>
			<ol class="comment-list">
				<?php
					wp_list_comments(
						[
							'style'			=> 'ol',
							'avatar_size'	=> 75,
							'callback'		=> 'drplus_comment_walker',
						]
					);
				?>
			</ol><!-- .comment-list -->
		<?php } ?>

		<div id="comments-form-wrap">
			<?php
			ob_start();
			get_template_part( "templates/components/template-components-button", null, [
				'type'			=> 'primary',
				'text'			=> 'button-text',
				'icon'			=> is_rtl() ? 'drplus-icon-arrow-up-left-square' : 'drplus-icon-arrow-up-right-square',
				'icon_align'	=> 'end',
				'align'			=> 'end',
				'small'			=> true,
				'classes'		=> ['button-class'],
				'id'			=> 'button-id',
				'atts'			=> [
					'type'	=> 'submit',
					'name'	=> 'button-name'
				],
			] );
			$submit_button = ob_get_clean();
			$submit_button = str_replace( 'button-name', '%1$s', $submit_button );
			$submit_button = str_replace( 'button-id', '%2$s', $submit_button );
			$submit_button = str_replace( 'button-class', '%3$s', $submit_button );
			$submit_button = str_replace( 'button-text', '%4$s', $submit_button );

			$title_icon = Sanitizers::icon( $args['form_section_icon'], "drplus-simple-icon section-title-icon" );
			$title_tag = tag_escape( $args['form_section_tag'] );
			?>
			<?php comment_form(
				[
					'logged_in_as'			=> '',
					'comment_notes_before'	=> '',
					'title_reply'			=> $args['form_section_title'],
					'title_reply_before'  => '<'. $title_tag .' class="section-title"><div class="section-title-inner">
						<span class="drplus-simple-icon-wrap icon-has-bg section-title-icon-wrap">
						'. $title_icon .'</span>	
						<span class="section-title-title">',
					'title_reply_after'   => '</span></div></'. $title_tag .'>',
					'submit_button'			=> $submit_button
				]
			); ?>
		</div>
	</div>
</div>