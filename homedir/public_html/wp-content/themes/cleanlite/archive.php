<?php
/**
 * The template for displaying archive pages
 */

get_header();
?>

<main id="content" class="site-main">
<?php if ( have_posts() ) : ?>

	<header class="page-header">
		<?php
		the_archive_title( '<h1 class="page-title">', '</h1>' );
		the_archive_description( '<div class="archive-description">', '</div>' );
		?>
	</header>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="post-thumbnail">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'medium' ); ?>
					</a>
				</div>
			<?php endif; ?>

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

			<div class="entry-tags">
				<?php the_tags( '<span class="tag-links">', ', ', '</span>' ); ?>
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
	<p><?php esc_html_e( 'No posts found.', 'cleanlite' ); ?></p>
	<?php
endif;
?>
</main>

<?php
get_sidebar();
get_footer();