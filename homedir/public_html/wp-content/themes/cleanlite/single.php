<?php
get_header();
?>

<main id="content" class="site-main">
<?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="post-thumbnail-single">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>

            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="entry-meta">
                    <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </time>
                </div>
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

            <?php if ( get_the_tags() ) : ?>
                <div class="entry-tags">
                    <?php the_tags( '<span class="tag-links">', ', ', '</span>' ); ?>
                </div>
            <?php endif; ?>

        </article>

        <?php
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
    endwhile;
endif;
?>
</main>

<?php
get_sidebar();
get_footer();

