<?php
/**
 * Comments under a blog post: the approved comments, then the reply form, in one card.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section class="bn-comments" id="comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="bn-comments-title">
			<?php
			/* translators: %s number of comments */
			echo esc_html( sprintf( _n( '%s comment', '%s comments', get_comments_number(), 'brightnest' ), number_format_i18n( get_comments_number() ) ) );
			?>
		</h2>
		<ol class="comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'avatar_size' => 40, 'short_ping' => true ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'        => __( 'Leave a comment', 'brightnest' ),
			'title_reply_before' => '<h2 id="reply-title" class="bn-comments-title">',
			'title_reply_after'  => '</h2>',
			'comment_notes_before' => '<p class="bn-comments-note">' . esc_html__( 'Your email address will not be published.', 'brightnest' ) . '</p>',
			'label_submit'       => __( 'Post comment', 'brightnest' ),
			'class_submit'       => 'bn-btn bn-btn-primary',
		)
	);
	?>
</section>
