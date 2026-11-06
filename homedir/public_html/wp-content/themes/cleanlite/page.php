<?php
get_header();
?>

<main id="content" class="site-main">
<?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content">
                <?php
                the_content();
                wp_link_pages( array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'cleanlite' ),
                    'after'  => '</div>',
                ) );
                ?>
            </div>

        </article>
        <?php
    endwhile;
endif;
?>
</main>

<?php
get_footer();

