<?php
/**
 * Displays the content and meta information for a post object.
 *
 * @package bporg-developer
 * @since 1.0.0
 */
?>

<?php if ( ! has_shortcode( get_the_content(), 'restsplain' ) ) : ?>
    <h1><?php the_title(); ?></h1>
<?php endif; ?>

<?php
/*
 * Content
 */
?>

<?php the_content( __( '(More ...)' , 'bporg-developer' ) ); ?>

<?php edit_post_link( __( 'Edit', 'bporg-developer' ), '<span class="edit-link">', '</span>' ); ?>

<div class="bottom-of-entry">&nbsp;</div>
