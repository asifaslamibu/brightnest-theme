<?php
/**
 * Template Name: Guide (long article)
 *
 * Hero (heading, intro, reading time, booking buttons | photo) → article laid out in cards
 * (assets/js/art.js) with "On this page" and a booking card beside it → homepage FAQs → booking band.
 * Made once from the homepage's long text (inc/guide.php).
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id   = get_the_ID();
	$bn_html = brightnest_guide_sections( apply_filters( 'the_content', get_the_content() ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals -- core hook
	$bn_secs = $bn_html[1];
	$bn_lead = has_excerpt() ? array( get_the_excerpt() ) : array(); // The first paragraph already opens the article.
	$bn_img  = has_post_thumbnail() ? get_the_post_thumbnail_url( $bn_id, 'large' ) : BRIGHTNEST_URI . '/assets/img/site/about-1.jpg';
	$bn_wa   = brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) );
	?>
	<div class="bn-read-progress" aria-hidden="true"><span data-read-progress></span></div>
	<article class="bn-post bn-guide">
		<header class="bn-guide-hero">
			<div class="bn-container bn-post-wide">
				<?php brightnest_breadcrumbs(); ?>
				<div class="bn-guide-hero-grid">
					<div>
						<span class="bn-blog-kicker"><?php esc_html_e( 'Cleaning guide', 'brightnest' ); ?></span>
						<h1><?php echo esc_html( brightnest_page_h1() ); ?></h1>
						<?php if ( $bn_lead ) : ?><p class="bn-guide-lead"><?php echo esc_html( $bn_lead[0] ); ?></p><?php endif; ?>
						<p class="bn-post-meta">
							<span><?php echo brightnest_icon( 'clock', 16 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_reading_time( $bn_id ) ); ?></span>
							<span><?php echo brightnest_icon( 'calendar', 16 ); // phpcs:ignore ?> <?php echo esc_html( sprintf( /* translators: %s date */ __( 'Updated %s', 'brightnest' ), get_the_modified_date() ) ); ?></span>
						</p>
						<div class="bn-guide-btns">
							<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
							<?php if ( $bn_wa ) : ?>
								<a class="bn-btn bn-btn-wa bn-btn-lg" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
							<?php endif; ?>
						</div>
					</div>
					<div class="bn-guide-hero-art"><img src="<?php echo esc_url( $bn_img ); ?>" alt="" width="640" height="480" fetchpriority="high"></div>
				</div>
			</div>
		</header>

		<div class="bn-container bn-post-layout bn-guide-layout">
			<div class="bn-post-main">
				<div class="bn-prose bn-art" data-art data-post-body><?php echo $bn_html[0]; // phpcs:ignore WordPress.Security.EscapeOutput -- page content ?></div>
			</div>
			<aside class="bn-post-side">
				<?php if ( count( $bn_secs ) > 1 ) : ?>
					<details class="bn-toc" data-toc open>
						<summary><?php esc_html_e( 'On this page', 'brightnest' ); ?> <?php echo brightnest_icon( 'chevron-down', 18 ); // phpcs:ignore ?></summary>
						<nav aria-label="<?php esc_attr_e( 'On this page', 'brightnest' ); ?>"><ol data-toc-list>
							<?php foreach ( $bn_secs as $bn_s ) : ?>
								<li class="is-h<?php echo (int) $bn_s[2]; ?>"><a href="#<?php echo esc_attr( $bn_s[0] ); ?>"><?php echo esc_html( $bn_s[1] ); ?></a></li>
							<?php endforeach; ?>
						</ol></nav>
					</details>
				<?php endif; ?>
				<div class="bn-post-book">
					<strong><?php esc_html_e( 'Need help at home?', 'brightnest' ); ?></strong>
					<p><?php esc_html_e( 'Vetted professionals, clear prices and secure online payment.', 'brightnest' ); ?></p>
					<a class="bn-btn bn-btn-primary bn-btn-block" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
					<?php if ( $bn_wa ) : ?>
						<a class="bn-btn bn-btn-wa bn-btn-block" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</article>

	<?php $bn_faqs = brightnest_lines( 'home_faqs' ); ?>
	<?php if ( $bn_faqs ) : ?>
		<section class="bn-guide-faq">
			<div class="bn-container bn-post-narrow">
				<h2><?php echo brightnest_accent( brightnest_opt( 'home_faq_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<div class="bn-faq">
					<?php foreach ( $bn_faqs as $bn_i => $bn_fq ) : ?>
						<details class="bn-faq-item" <?php echo 0 === $bn_i ? 'open' : ''; ?>>
							<summary><?php echo esc_html( $bn_fq[0] ); ?> <?php echo brightnest_icon( 'chevron-down', 20 ); // phpcs:ignore ?></summary>
							<p><?php echo esc_html( $bn_fq[1] ); ?></p>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php
endwhile;

get_template_part( 'template-parts/blog-cta' );

get_footer();
