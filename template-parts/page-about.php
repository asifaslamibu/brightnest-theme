<?php
/**
 * About Us layout (inc/page-layouts.php), from the page text:
 * the section with a list becomes the intro (photo, checklist, buttons), then the numbers from
 * Settings → Homepage, sections with quotes become review cards, the other sections feature cards,
 * and text after the reviews a closing call to action.
 *
 * @package BrightNest
 */

$bn_sections = brightnest_page_sections( get_post()->post_content );
$bn_intro    = null;
$bn_features = array();
$bn_reviews  = array();
$bn_closing  = array();
foreach ( $bn_sections as $bn_sec ) {
	$bn_quotes = brightnest_blocks_of( $bn_sec['blocks'], 'core/quote' );
	if ( $bn_quotes ) {
		$bn_reviews = array( 'title' => $bn_sec['title'], 'quotes' => $bn_quotes );
		$bn_after   = false;
		foreach ( $bn_sec['blocks'] as $bn_b ) {
			if ( 'core/quote' === $bn_b['blockName'] ) {
				$bn_after = true;
			} elseif ( $bn_after ) {
				$bn_closing[] = $bn_b; // "Have a question? Contact us…" after the reviews.
			}
		}
	} elseif ( ! $bn_intro && ( brightnest_blocks_of( $bn_sec['blocks'], 'core/list' ) || '' === $bn_sec['title'] ) ) {
		$bn_intro = $bn_sec;
	} else {
		$bn_features[] = $bn_sec;
	}
}
$bn_stats = array();
foreach ( array( 1, 2, 3 ) as $bn_s ) {
	if ( brightnest_opt( 'stat_' . $bn_s . '_num' ) ) {
		$bn_stats[] = array( brightnest_opt( 'stat_' . $bn_s . '_num' ), brightnest_opt( 'stat_' . $bn_s . '_label' ) );
	}
}
$bn_icons   = array( 'star', 'check-circle', 'shield', 'users', 'home', 'headset' );
$bn_contact = brightnest_core_page( 'contact' );
?>
<?php if ( $bn_intro ) : ?>
	<section class="bn-ab-intro">
		<div class="bn-container bn-ab-intro-grid">
			<div class="bn-ab-intro-text">
				<span class="bn-eyebrow"><?php esc_html_e( 'About Bright Nest', 'brightnest' ); ?></span>
				<?php if ( $bn_intro['title'] ) : ?>
					<h2 class="bn-ab-h2"><?php echo esc_html( $bn_intro['title'] ); ?></h2>
				<?php endif; ?>
				<div class="bn-ab-lead"><?php echo wp_kses_post( brightnest_render_blocks( array_filter( $bn_intro['blocks'], function ( $b ) { return 'core/list' !== $b['blockName']; } ) ) ); ?></div>
				<?php foreach ( brightnest_blocks_of( $bn_intro['blocks'], 'core/list' ) as $bn_list ) : ?>
					<ul class="bn-ab-checks">
						<?php foreach ( brightnest_list_items( $bn_list ) as $bn_li ) : ?>
							<li><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?><span><?php echo wp_kses_post( $bn_li ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
				<div class="bn-ab-btns">
					<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
					<a class="bn-btn bn-btn-outline-navy" href="<?php echo esc_url( get_post_type_archive_link( 'bn_info' ) ); ?>"><?php esc_html_e( 'Our services', 'brightnest' ); ?></a>
				</div>
			</div>
			<div class="bn-ab-photo">
				<img src="<?php echo esc_url( BRIGHTNEST_URI . '/assets/img/pages/contact-hero-team.jpg' ); ?>" width="640" height="800" alt="<?php esc_attr_e( 'The Bright Nest cleaning team at work', 'brightnest' ); ?>" loading="lazy">
				<?php if ( $bn_stats ) : ?>
					<div class="bn-ab-badge"><strong><?php echo esc_html( $bn_stats[ min( 1, count( $bn_stats ) - 1 ) ][0] ); ?></strong><span><?php echo esc_html( $bn_stats[ min( 1, count( $bn_stats ) - 1 ) ][1] ); ?></span></div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $bn_stats ) : ?>
	<section class="bn-ab-stats">
		<div class="bn-container">
			<ul>
				<?php foreach ( $bn_stats as $bn_st ) : ?>
					<li><strong><?php echo esc_html( $bn_st[0] ); ?></strong><span><?php echo esc_html( $bn_st[1] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ( $bn_features ) : ?>
	<section class="bn-ab-features">
		<div class="bn-container">
			<div class="bn-ab-cards">
				<?php foreach ( $bn_features as $bn_i => $bn_f ) : ?>
					<article class="bn-ab-card">
						<span class="bn-ab-card-icon"><?php echo brightnest_icon( $bn_icons[ $bn_i % count( $bn_icons ) ], 24 ); // phpcs:ignore ?></span>
						<?php if ( $bn_f['title'] ) : ?>
							<h2><?php echo esc_html( $bn_f['title'] ); ?></h2>
						<?php endif; ?>
						<div class="bn-ab-card-text"><?php echo wp_kses_post( brightnest_render_blocks( $bn_f['blocks'] ) ); ?></div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $bn_reviews ) : ?>
	<section class="bn-ab-reviews">
		<div class="bn-container">
			<div class="bn-section-head">
				<span class="bn-eyebrow"><?php esc_html_e( 'Reviews', 'brightnest' ); ?></span>
				<?php if ( $bn_reviews['title'] ) : ?>
					<h2 class="bn-ab-h2"><?php echo esc_html( $bn_reviews['title'] ); ?></h2>
				<?php endif; ?>
			</div>
			<div class="bn-ab-quotes">
				<?php foreach ( $bn_reviews['quotes'] as $bn_q ) : ?>
					<?php list( $bn_qt, $bn_qn ) = brightnest_quote_parts( $bn_q ); ?>
					<figure class="bn-ab-quote">
						<div class="bn-ab-stars" aria-label="<?php esc_attr_e( '5 out of 5 stars', 'brightnest' ); ?>"><?php echo str_repeat( brightnest_icon( 'star', 15 ), 5 ); // phpcs:ignore ?></div>
						<blockquote><?php echo esc_html( $bn_qt ); ?></blockquote>
						<?php if ( $bn_qn ) : ?>
							<?php $bn_qp = array_map( 'trim', explode( ',', $bn_qn, 2 ) ); ?>
							<figcaption>
								<span class="bn-ab-avatar"><?php echo esc_html( mb_strtoupper( mb_substr( $bn_qp[0], 0, 1 ) ) ); ?></span>
								<span><strong><?php echo esc_html( $bn_qp[0] ); ?></strong><?php if ( ! empty( $bn_qp[1] ) ) : ?><small><?php echo esc_html( $bn_qp[1] ); ?></small><?php endif; ?></span>
							</figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php $GLOBALS['bn_cta_shown'] = true; // This page has its own call to action; no booking band in the footer. ?>
<section class="bn-ab-cta">
	<div class="bn-container">
		<div class="bn-ab-cta-box">
			<div>
				<h2><?php esc_html_e( 'Ready for a cleaner home or office?', 'brightnest' ); ?></h2>
				<?php if ( $bn_closing ) : ?>
					<div class="bn-ab-cta-text"><?php echo wp_kses_post( brightnest_render_blocks( $bn_closing ) ); ?></div>
				<?php else : ?>
					<p class="bn-ab-cta-text"><?php esc_html_e( 'Book online in a minute, or talk to our team about a custom quote.', 'brightnest' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="bn-ab-cta-btns">
				<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
				<?php if ( $bn_contact ) : ?>
					<a class="bn-btn bn-ab-cta-ghost" href="<?php echo esc_url( get_permalink( $bn_contact ) ); ?>"><?php esc_html_e( 'Contact us', 'brightnest' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
