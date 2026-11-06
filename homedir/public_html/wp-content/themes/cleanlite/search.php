<?php
/**
 * The template for displaying search results pages
 */

get_header();
?>

<main id="content" class="site-main">

<?php if ( have_posts() ) : ?>

	<header class="page-header">
		<h1 class="page-title">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Search Results for: %s', 'cleanlite' ), '<span>' . get_search_query() . '</span>' );
			?>
		</h1>
	</header>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<header class="entry-header">
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="entry-meta">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</div>
			</header>

			<div class="entry-content">
				<?php the_excerpt(); ?>
			</div>

		</article>
		<?php
	endwhile;

	the_posts_pagination(
		array(
			'prev_text' => __( '&laquo; Previous', 'cleanlite' ),
			'next_text' => __( 'Next &raquo;', 'cleanlite' ),
		)
	);

else :
	?>

	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'cleanlite' ); ?></h1>
	</header>

	<div class="page-content">
		<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'cleanlite' ); ?></p>
		<?php get_search_form(); ?>
	</div>

	<?php
endif;
?>
</main>

<?php
get_sidebar();
get_footer();