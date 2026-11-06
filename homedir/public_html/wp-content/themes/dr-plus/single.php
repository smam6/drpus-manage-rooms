<?php

use DrPlus\Components\Button;
use DrPlus\Components\SectionTitle;
use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

get_header();

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'single-specialist' ) ) {
	get_template_part( 'single-specialist' );
} else {

	$primary_classes = ['content-area', 'site-content'];
	$show_sidebar = is_active_sidebar( 'single' );
	if( $show_sidebar ) {
		$primary_classes[] = 'content-area-with-sidebar';
	}

	$options = Options::get_options( [
		'post-title-tag'					=> 'h1',
		'single_post_images_style'			=> true,
		'single_post_show_thumbnail'		=> true,
		'single_post_show_breadcrumb'		=> true,
		'single_post_show_time'				=> true,
		'single_post_show_comment_count'	=> true,
		'single_post_show_view_count'		=> true,
		'single_post_show_author'			=> true,
		'single_post_show_share'			=> true,
		'single_post_show_end_posts'		=> true,
		'single_post_end_posts_title'		=> esc_html__( 'Health Magazine', 'drplus' ),
		'single_post_end_posts_title_icon'	=> 'drplus-icon-diamond',
		'single_post_end_posts_title_tag'	=> 'h3',
		'single_post_time_type'				=> 'date',
		'single_post_end_posts_ppp'			=> 4,
		'single_post_end_posts_type'		=> 'related', // related | latests
		'single_post_end_posts_term_type'	=> 'category', // category | tag
	] );

	$show_post_meta = in_array( true, [
		$options['single_post_show_time'],
		$options['single_post_show_comment_count'],
		$options['single_post_show_view_count'],
		$options['single_post_show_author'],
	] );

	while ( have_posts() ) :
		the_post();

		$shares = drplus_single_share();

		$categories = get_the_category();
		$tags = get_the_tag_list( '', ' ' );

		$post_classes = ['entry-content'];
		if( !Utils::to_bool( $options['single_post_images_style'] ) ) {
			$post_classes[] = 'disable-image-style';
		}

		$time = '';
		if( $options['single_post_time_type'] == 'difference' ) {
			$time = sprintf( esc_html__( '%s ago', 'drplus' ), human_time_diff( get_the_date( "U" ), Utils::convert_chars( date_i18n( "U" ) ) ) );
		} else {
			$time = get_the_date();
		}
		?>
		<div id="page-body" class="page-width">
			<main id="page-main">
				
				<?php if( $options['single_post_show_breadcrumb'] ) {
					drplus_breadcrumb();
				} ?>

				<header id="page-header" aria-labelledby="post-title">
					<<?php echo $options['post-title-tag'] ?> id="post-title" class="line-clamp line-clamp-2"><?php echo drplus_get_post_title() ?></<?php echo $options['post-title-tag'] ?>>

					<?php if( $show_post_meta ) { ?>
						<div class="post-meta-wrap">
							<?php if( $options['single_post_show_time'] ) { ?>
								<div class="post-meta post-date">
									<i class="drplus-icon-calendar post-meta-icon"></i>
									<time class="post-meta-value" datetime="<?php echo get_the_date( 'Y-m-d' ) ?>"><a href="<?php echo esc_url( get_day_link( get_the_date( 'Y' ), get_the_date( 'm' ), get_the_date( 'd' ) ) ) ?>"><?php echo $time ?></a></time>
								</div>
							<?php } ?>
		
							<?php if( comments_open() && $options['single_post_show_comment_count'] ) { ?>
								<div class="post-meta post-comments-count">
									<i class="drplus-icon-message-square-dots post-meta-icon"></i>
									<a href="#respond" class="post-meta-value"><?php echo comments_number( __( "0 comment", 'drplus' ), __( "1 comment", 'drplus' ), __( "% comment", 'drplus' ) ) ?></a>
								</div>
							<?php } ?>
		
							<?php if( $options['single_post_show_view_count'] ) { ?>
								<?php if( $view = do_shortcode( "[drplus_post_views wrap=\"false\"]" ) ) { ?>
									<div class="post-meta post-views">
										<?php echo $view ?>
									</div>
								<?php } ?>
							<?php } ?>
		
							<?php if( $options['single_post_show_author'] ) { ?>							
								<div class="post-meta post-author">
									<i class="drplus-icon-author post-meta-icon"></i>
									<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="post-meta-value" rel="author"><?php the_author() ?></a>
								</div>
							<?php } ?>
						</div>
					<?php } ?>

					<?php if( $options['single_post_show_share'] ) { ?>
						<div id="post-share-wrap">
							<div id="post-share-btn"><i class="drplus-icon-share"></i><span class="screen-reader-text"><?php esc_html_e( 'Share', 'drplus' ) ?></span></div>
							<div id="post-share-body">
								<div id="post-share-items">
									<?php
									foreach( $shares as $name => $details ) {
										$url = add_query_arg( $details['args'], $details['url'] );
										?>
										<a href="<?php echo $url ?>" class="post-share-item" target="_blank" rel="noopener noreferrer"><i class="<?php echo $details['icon'] ?>" aria-hidden="true"></i><span class="screen-reader-text"><?php echo $details['srt'] ?></span></a>
									<?php } ?>
								</div>
		
								<div id="post-share-shortlink-wrap">
									<input type="url" id="post-share-shortlink" value="<?php echo wp_get_shortlink() ?>" readonly>
									<div id="post-share-copy"><i class="drplus-icon-copy" id="post-share-copy-icon"></i><i class="drplus-icon-tick" id="post-share-copy-tick"></i></div>
								</div>
							</div>
						</div>
					<?php } ?>
				</header>

				<div id="primary" <?php echo Utils::prepare_html_classes( $primary_classes, true ) ?>>
					<div id="post-content" class="row">
						<div class="entry-container<?php echo $show_sidebar ? ' col-md-9 col-sm-12' : ' col-12' ?>">
							<div id="page-content" class="site-content single" role="main">
								<article id="post-<?php the_ID(); ?>" <?php post_class( $post_classes ); ?>>
									<?php if( $options['single_post_show_thumbnail'] ) {
										drplus_post_thumbnail();
									} ?>
									
									<?php the_content(); ?>
									
									<div id="post-terms-wrap">
										<?php if( !empty( $categories ) ) { ?>
											<div id="post-categories" class="post-terms">
												<span class="post-term-title"><?php esc_html_e( 'Categories', 'drplus' ) ?>:</span>
												<?php foreach( $categories as $index => $category ) { ?>
													<a href="<?php echo esc_url( get_term_link( $category ), ['http', 'https'] ) ?>"><?php echo esc_html( $category->name ) ?></a>
													<?php if( $index !== array_key_last( $categories ) ) { ?>
														<span class="post-term-separator">, </span>
													<?php } ?>
												<?php } ?>
											</div>
										<?php } ?>

										<?php if( !empty( $tags ) && !is_wp_error( $tags ) ) { ?>
											<div id="post-tags" class="post-terms">
												<span class="post-term-title"><?php esc_html_e( 'Tags', 'drplus' ) ?>:</span>
												<?php echo $tags ?>
											</div>
										<?php } ?>
									</div>

									<?php wp_link_pages(); ?>
								</article>
							</div>
						</div>
						<?php
						if( $show_sidebar ) {
							get_sidebar( 'single' );
						}
						?>
					</div>

					<?php comments_template(); ?>

					<?php
					if( Utils::to_bool( $options['single_post_show_end_posts'] ) ) {
						$post_type = get_post_type();
						$query_args = [
							'post_type'			=> $post_type,
							'ppp'				=> $options['single_post_end_posts_ppp'],
							'show_pagination'	=> false,
							'query_exclude_ids'	=> [get_the_ID()],
						];
						if( $options['single_post_end_posts_type'] == 'related' ) {
							if( $options['single_post_end_posts_term_type'] == 'category' ) {
								$query_args['query_include_category'] = !empty( $categories ) ? wp_list_pluck( $categories, 'term_id' ) : [];
							} else {
								$tags = get_the_tags();
								$query_args['query_include_tag'] = !empty( $tags ) ? wp_list_pluck( $tags, 'term_id' ) : [];
							}
						} else {
							$query_args['query_type'] = 'latest';
						}

						$display_args = [
							'desktop_slider'	=> false,
							'desktop_cols'		=> 4,
							'desktop_gap'		=> 24,
							
							'tablet_slider'	=> false,
							'tablet_cols'	=> 2,
							'tablet_gap'	=> 16,

							'mobile_slider'	=> false,
							'mobile_cols'	=> 1,
							'mobile_gap'	=> 16,
						];

						$end_posts = Archive::posts( $query_args+$display_args, '', [], 'array' );
						if( !empty( $end_posts ) && !$end_posts['is_empty'] ) {
							$view_all_link = '';

							if( $post_type === 'post' ) {
								if( $options['single_post_end_posts_type'] == 'latests' ) {
									$view_all_link = get_post_type_archive_link( $post_type );
								} else {
									if( $options['single_post_end_posts_term_type'] == 'category' ) {
										if( !empty( $categories ) ) {
											$view_all_link = get_term_link( $categories[0] );
										}
									} else {
										if( !empty( $tags ) ) {
											$view_all_link = get_term_link( $tags[0] );
										}
									}
								}
							} else {
								$view_all_link = get_post_type_archive_link( $post_type );
							}
							?>
							<div class="row">
								<div class="col-12 end-posts-wrap">
									<div class="end-posts-head">
										<?php
										SectionTitle::view( [
											'title'	=> $options['single_post_end_posts_title'],
											'icon'	=> $options['single_post_end_posts_title_icon'],
											'tag'	=> $options['single_post_end_posts_title_tag'],
										] );

										if( $view_all_link ) {
											Button::view( [
												'type'			=> 'gray',
												'small'			=> true,
												'icon'			=> is_rtl() ? 'drplus-icon-arrow-square-left' : 'drplus-icon-arrow-square-right',
												'text'			=> esc_html__( "Show all", 'drplus' ),
												'link'			=> $view_all_link,
												'icon_align'	=> 'end',
											] );
										}
										?>
									</div>

									<div class="end-posts">
										<?php echo $end_posts['html'] ?>
									</div>

									<?php if( $view_all_link ) { ?>
										<div class="end-posts-footer">
											<?php
											Button::view( [
												'type'			=> 'gray',
												'small'			=> true,
												'icon'			=> is_rtl() ? 'drplus-icon-arrow-square-left' : 'drplus-icon-arrow-square-right',
												'text'			=> esc_html__( "Show all", 'drplus' ),
												'link'			=> $view_all_link,
												'icon_align'	=> 'end',
												'align'			=> 'center',
											] );
											?>
										</div>
									<?php } ?>
								</div>
							</div>
							<?php
						}
					}	
					?>
				</div>
			</main>
		</div>
		<?php
	endwhile;
}

get_footer();