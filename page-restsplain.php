<?php
/**
 * The template for displaying the Restsplain shortcode nicer.
 *
 * Template Name: Restsplain
 *
 * @package bporg-developer
 * @since 1.0.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
            <div class="restsplain-wrapper">
                <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-content">
                        <?php the_content(); ?>
                        <?php
                            wp_link_pages( array(
                                'before' => '<div class="page-links">' . __( 'Pages:', 'wporg' ),
                                'after'  => '</div>',
                            ) );
                        ?>
                    </div><!-- .entry-content -->
                    <?php edit_post_link( __( 'Edit', 'wporg' ), '<footer class="entry-meta"><span class="edit-link">', '</span></footer>' ); ?>
                </article><!-- #post-## -->

                <?php
                    // If comments are open or we have at least one comment, load up the comment template
                    if ( comments_open() || '0' != get_comments_number() ) :
                        comments_template();
                    endif;
                ?>

                <?php endwhile; // end of the loop. ?>
            </div><!-- .restsplain-wrapper -->
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
