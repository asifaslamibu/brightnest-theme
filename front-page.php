<?php
/**
 * Homepage: split hero (text, area picker, numbers | photo) → service tiles → trusted partner →
 * cleaning list → maintenance list → easy steps → numbers → why Bright Nest → reviews → blog →
 * cleaning guide teaser (its own page, inc/guide.php) → FAQ → closing card → book on WhatsApp.
 * All text comes from BrightNest → Settings → Homepage (sections can be switched off there).
 * Long text folds behind "Read more"; long lists show a few items and a "Load more" button.
 *
 * @package BrightNest
 */

get_header();

$bn_all = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );

$bn_on = function ( $key ) {
	return '0' !== (string) brightnest_opt( 'show_' . $key );
};

// Top tiles: popular services first, then the rest in display order (max 10, like 5 × 2).
$bn_popular = array_values( array_filter( $bn_all, function ( $id ) { return '1' === get_post_meta( $id, '_bn_featured', true ); } ) );
$bn_tiles   = array_slice( array_values( array_unique( array_merge( $bn_popular, $bn_all ) ) ), 0, 10 );
$bn_reasons = array_slice( brightnest_lines( 'love_points' ), 0, 4 );

// Big illustrations for the four "reasons" cards (brand navy + orange).
$bn_art = array(
	'<svg viewBox="0 0 120 100" aria-hidden="true"><path d="M60 8l14.7 29.8 32.9 4.8-23.8 23.2 5.6 32.7L60 83 30.6 98.5l5.6-32.7L12.4 42.6l32.9-4.8z" fill="#f25c05"/><path d="M60 22l9.6 19.4 21.4 3.1-15.5 15.1 3.7 21.3L60 70.8 40.8 80.9l3.7-21.3L29 44.5l21.4-3.1z" fill="#ff8a2b"/></svg>',
	'<svg viewBox="0 0 120 100" aria-hidden="true"><rect x="10" y="12" width="100" height="80" rx="8" fill="#eef0f7"/><path d="M10 20a8 8 0 0 1 8-8h84a8 8 0 0 1 8 8v12H10z" fill="#262d72"/><circle cx="32" cy="12" r="4" fill="#f25c05"/><circle cx="88" cy="12" r="4" fill="#f25c05"/><g fill="none" stroke="#f25c05" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 50l5 5 8-9M45 50l5 5 8-9M68 50l5 5 8-9M22 72l5 5 8-9M45 72l5 5 8-9"/></g><g fill="#fff"><rect x="68" y="68" width="14" height="11" rx="2"/><rect x="90" y="46" width="12" height="11" rx="2"/><rect x="90" y="68" width="12" height="11" rx="2"/></g></svg>',
	'<svg viewBox="0 0 120 100" aria-hidden="true"><rect x="20" y="58" width="18" height="32" rx="3" fill="#ffc3a0"/><rect x="47" y="40" width="18" height="50" rx="3" fill="#ff8a2b"/><rect x="74" y="22" width="18" height="68" rx="3" fill="#f25c05"/><path d="M83 2l3.6 7.3 8 1.2-5.8 5.6 1.4 8L83 20.3 75.8 24l1.4-8-5.8-5.6 8-1.2z" fill="#262d72"/><rect x="12" y="90" width="90" height="3" rx="1.5" fill="#cfd3e4"/></svg>',
	'<svg viewBox="0 0 120 100" aria-hidden="true"><rect x="38" y="4" width="46" height="92" rx="9" fill="#eef0f7"/><g fill="#262d72"><rect x="28" y="12" width="22" height="22" rx="5"/><rect x="58" y="16" width="17" height="17" rx="4"/><rect x="45" y="42" width="17" height="17" rx="4"/><rect x="66" y="48" width="22" height="22" rx="5"/><rect x="28" y="64" width="17" height="17" rx="4"/></g><rect x="54" y="78" width="11" height="11" rx="3" fill="#f25c05"/><path d="M98 20l1.6 3.2 3.5.5-2.5 2.5.6 3.5-3.2-1.7-3.2 1.7.6-3.5-2.5-2.5 3.5-.5z" fill="#f25c05"/><path d="M20 44l1.3 2.6 2.8.4-2 2 .5 2.8-2.6-1.4-2.6 1.4.5-2.8-2-2 2.8-.4z" fill="#f25c05"/><path d="M100 76l1.3 2.6 2.8.4-2 2 .5 2.8-2.6-1.4-2.6 1.4.5-2.8-2-2 2.8-.4z" fill="#ff8a2b"/></svg>',
);

// "Text\n\nMore text" → paragraphs.
$bn_paras = function ( $text ) {
	$out = '';
	foreach ( preg_split( '/\n\s*\n/', trim( (string) $text ) ) as $p ) {
		if ( '' !== trim( $p ) ) {
			$out .= '<p>' . nl2br( esc_html( trim( $p ) ) ) . '</p>';
		}
	}
	return $out;
};

// "Title|Description|link|image" lines → cards. Link and image are optional; without an image a theme photo is picked by the title.
$bn_cards = function ( $key ) {
	$photos = array(
		'maid'       => 'about-2.jpg',
		'house'      => 'house.jpg',
		'deep'       => 'bg-appointment.jpg',
		'office'     => 'office.jpg',
		'restaurant' => 'about-1.jpg',
		'industrial' => 'office.jpg',
		'move'       => 'move.jpg',
		'pest'       => 'pest.jpg',
		'ac '        => 'ac.jpg',
		'electric'   => 'electrical.jpg',
		'plumb'      => 'plumbing.jpg',
		'tile'       => 'deep.jpg',
		'paint'      => 'painting.jpg',
		'handyman'   => 'deep.jpg',
		'interior'   => 'handyman.jpg',
	);
	$rows   = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( $key ) ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( '' === $p[0] ) {
			continue;
		}
		$link = isset( $p[2] ) ? $p[2] : '';
		$img  = isset( $p[3] ) ? $p[3] : '';
		if ( '' === $img ) {
			foreach ( $photos as $word => $file ) {
				if ( false !== stripos( $p[0] . ' ', $word ) ) {
					$img = BRIGHTNEST_URI . '/assets/img/site/' . $file;
					break;
				}
			}
		}
		$rows[] = array(
			'title' => $p[0],
			'text'  => isset( $p[1] ) ? $p[1] : '',
			'link'  => '' === $link ? '' : ( 0 === strpos( $link, '/' ) ? home_url( $link ) : $link ),
			'img'   => $img,
		);
	}
	return $rows;
};

// A slider of service cards that moves on by itself (assets/js/main.js, data-autoplay).
$bn_card_section = function ( $key ) use ( $bn_cards ) {
	$cards = $bn_cards( $key . '_list' );
	if ( ! $cards ) {
		return;
	}
	?>
	<section class="bn-h2-cards bn-h2-cards-<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
		<div class="bn-container">
			<div class="bn-jx-head">
				<h2><?php echo brightnest_accent( brightnest_opt( $key . '_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
				<?php if ( brightnest_opt( $key . '_sub' ) ) : ?><p><?php echo esc_html( brightnest_opt( $key . '_sub' ) ); ?></p><?php endif; ?>
			</div>
			<div class="bn-h2-slider" data-carousel data-autoplay="4000">
			<ul class="bn-h2-card-grid bn-carousel-track" data-track>
				<?php foreach ( $cards as $c ) : ?>
					<li class="bn-h2-card bn-carousel-item">
						<?php if ( $c['img'] ) : ?>
							<span class="bn-h2-card-img"><img src="<?php echo esc_url( $c['img'] ); ?>" alt="" loading="lazy" width="400" height="250"></span>
						<?php endif; ?>
						<div class="bn-h2-card-body">
							<h3><?php echo esc_html( $c['title'] ); ?></h3>
							<div class="bn-more bn-more-sm" data-more>
								<div class="bn-more-body"><p><?php echo esc_html( $c['text'] ); ?></p></div>
								<button type="button" class="bn-more-btn" data-more-btn hidden><?php esc_html_e( 'Read more', 'brightnest' ); ?></button>
							</div>
							<?php if ( $c['link'] ) : ?>
								<a class="bn-h2-card-link" href="<?php echo esc_url( $c['link'] ); ?>"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
							<?php elseif ( brightnest_whatsapp_url() ) : ?>
								<a class="bn-h2-card-link" href="<?php echo esc_url( brightnest_whatsapp_url( sprintf( /* translators: %s service */ __( 'Hi, I would like a quote for %s.', 'brightnest' ), $c['title'] ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get a quote', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="bn-h2-slider-nav">
				<button type="button" class="bn-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 18 ); // phpcs:ignore ?></button>
				<span class="bn-h2-dots" data-dots></span>
				<button type="button" class="bn-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?></button>
			</div>
			</div>
		</div>
	</section>
	<?php
};
?>

<section class="bn-h2-hero">
	<div class="bn-container bn-h2-hero-grid">
		<div class="bn-h2-hero-copy">
			<?php if ( brightnest_opt( 'hero_badge' ) ) : ?>
				<span class="bn-h2-pill"><?php echo brightnest_icon( 'star', 14 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'hero_badge' ) ); ?></span>
			<?php endif; ?>
			<h1><?php echo brightnest_accent( brightnest_opt( 'hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<?php if ( brightnest_opt( 'hero_subtitle' ) ) : ?>
				<div class="bn-h2-lead">
					<p><?php echo esc_html( brightnest_opt( 'hero_subtitle' ) ); ?></p>
					<?php if ( brightnest_opt( 'hero_more' ) ) : ?>
						<div class="bn-h2-lead-more" id="bn-hero-more" hidden><?php echo $bn_paras( brightnest_opt( 'hero_more' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<button type="button" class="bn-more-btn" data-reveal="bn-hero-more" aria-expanded="false"><?php esc_html_e( 'Read more', 'brightnest' ); ?></button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="bn-h2-loc">
				<p class="bn-h2-locq"><?php echo esc_html( brightnest_opt( 'loc_question' ) ); ?></p>
				<?php get_template_part( 'template-parts/area-dropdown' ); ?>
			</div>
			<?php
			$bn_stats = array();
			foreach ( array( 1, 2, 3 ) as $bn_s ) {
				if ( brightnest_opt( 'stat_' . $bn_s . '_num' ) ) {
					$bn_stats[] = array( brightnest_opt( 'stat_' . $bn_s . '_num' ), brightnest_opt( 'stat_' . $bn_s . '_label' ) );
				}
			}
			?>
			<?php if ( $bn_stats ) : ?>
				<ul class="bn-h2-stats">
					<?php foreach ( $bn_stats as $bn_st ) : ?>
						<li><strong><?php echo esc_html( $bn_st[0] ); ?></strong><span><?php echo esc_html( $bn_st[1] ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="bn-h2-hero-art">
			<img src="<?php echo esc_url( brightnest_img_opt( 'hero_img_1', 'bg-stats.jpg' ) ); ?>" alt="" width="640" height="560" fetchpriority="high">
			<div class="bn-h2-float bn-h2-float-a"><?php echo brightnest_icon( 'shield', 20 ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Vetted professionals', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Satisfaction guarantee', 'brightnest' ); ?></small></span></div>
			<div class="bn-h2-float bn-h2-float-b"><?php echo brightnest_icon( 'tag', 20 ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Secure online payment', 'brightnest' ); ?></strong></span></div>
		</div>
	</div>
</section>

<?php if ( $bn_tiles && $bn_on( 'services' ) ) : ?>
<section class="bn-jx-todo bn-h2-todo" id="services">
	<div class="bn-container">
		<div class="bn-jx-head">
			<h2><?php echo esc_html( brightnest_opt( 'banner_title' ) ); ?></h2>
			<p><?php echo esc_html( brightnest_opt( 'top_title' ) ); ?></p>
		</div>
		<ul class="bn-jx-tiles">
			<?php foreach ( $bn_tiles as $bn_id ) : ?>
				<?php $bn_off = array_keys( array_filter( brightnest_service_areas( $bn_id ), function ( $a ) { return ! $a['on']; } ) ); ?>
				<li data-off-areas="<?php echo esc_attr( wp_json_encode( $bn_off ) ); ?>">
					<a href="<?php echo esc_url( get_permalink( $bn_id ) ); ?>">
						<span class="bn-jx-tile-box"><?php echo brightnest_service_icon_html( $bn_id, 46 ); // phpcs:ignore ?></span>
						<span class="bn-jx-tile-name"><?php echo esc_html( get_the_title( $bn_id ) ); ?></span>
						<span class="bn-jl-tile-off" hidden></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="bn-h2-all"><a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a></p>
	</div>
</section>
<?php endif; ?>

<?php if ( $bn_on( 'intro' ) && brightnest_opt( 'intro_title' ) ) : ?>
<section class="bn-h2-intro">
	<div class="bn-container bn-h2-intro-grid">
		<div class="bn-h2-intro-art"><img src="<?php echo esc_url( brightnest_img_opt( 'why_image', 'about-1.jpg' ) ); ?>" alt="" loading="lazy" width="600" height="520"></div>
		<div class="bn-h2-intro-copy">
			<h2><?php echo brightnest_accent( brightnest_opt( 'intro_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<div class="bn-more" data-more>
				<div class="bn-more-body"><?php echo $bn_paras( brightnest_opt( 'intro_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<button type="button" class="bn-more-btn" data-more-btn hidden><?php esc_html_e( 'Read more', 'brightnest' ); ?></button>
			</div>
			<?php $bn_ticks = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( 'intro_points' ) ) ) ); ?>
			<?php if ( $bn_ticks ) : ?>
				<ul class="bn-h2-ticks">
					<?php foreach ( $bn_ticks as $bn_tk ) : ?>
						<li><?php echo brightnest_icon( 'check-circle', 20 ); // phpcs:ignore ?> <?php echo esc_html( $bn_tk ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
if ( $bn_on( 'clean' ) ) {
	$bn_card_section( 'clean' );
}
if ( $bn_on( 'maint' ) ) {
	$bn_card_section( 'maint' );
}
?>

<?php $bn_steps = array_slice( brightnest_lines( 'process_steps' ), 0, 3 ); ?>
<?php if ( $bn_steps && $bn_on( 'process' ) ) : ?>
<section class="bn-h2-steps">
	<div class="bn-container">
		<div class="bn-jx-head">
			<h2><?php echo brightnest_accent( brightnest_opt( 'process_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
		</div>
		<ol class="bn-h2-step-list">
			<?php foreach ( $bn_steps as $bn_n => $bn_st ) : ?>
				<li><span class="bn-h2-step-n"><?php echo (int) $bn_n + 1; ?></span><h3><?php echo esc_html( $bn_st[0] ); ?></h3><p><?php echo esc_html( $bn_st[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php endif; ?>

<?php $bn_nums = brightnest_achievements(); ?>
<?php if ( $bn_nums && $bn_on( 'numbers' ) ) : ?>
<section class="bn-h2-numbers">
	<div class="bn-container"><div class="bn-h2-numbers-card">
		<div class="bn-h2-numbers-head">
			<h2><?php echo brightnest_accent( brightnest_opt( 'numbers_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php if ( brightnest_opt( 'numbers_sub' ) ) : ?><p><?php echo esc_html( brightnest_opt( 'numbers_sub' ) ); ?></p><?php endif; ?>
		</div>
		<ul class="bn-h2-num-list">
			<?php foreach ( $bn_nums as $bn_nm ) : ?>
				<li><strong><?php echo esc_html( $bn_nm[0] ); ?></strong><span><?php echo esc_html( $bn_nm[1] ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</div></div>
</section>
<?php endif; ?>

<?php if ( $bn_reasons && $bn_on( 'why' ) ) : ?>
<section class="bn-jx-reasons bn-h2-why">
	<div class="bn-container bn-h2-why-grid">
		<div class="bn-h2-why-head">
			<h2><?php echo brightnest_accent( brightnest_opt( 'love_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p><?php echo esc_html( brightnest_opt( 'love_sub' ) ); ?></p>
			<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
		</div>
		<ul class="bn-jx-reason-grid">
			<?php foreach ( $bn_reasons as $bn_n => $bn_pt ) : ?>
				<li>
					<span class="bn-jx-art"><?php echo $bn_art[ $bn_n ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG ?></span>
					<h3><?php echo esc_html( $bn_pt[0] ); ?></h3>
					<p><?php echo esc_html( $bn_pt[1] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php endif; ?>

<?php
if ( $bn_on( 'reviews' ) ) {
	get_template_part( 'template-parts/google-reviews', null, array( 'eyebrow' => brightnest_opt( 'reviews_eyebrow' ), 'title' => brightnest_opt( 'reviews_title' ) ) );
}
?>

<?php $bn_posts = $bn_on( 'blog' ) ? get_posts( array( 'posts_per_page' => 3, 'fields' => 'ids', 'ignore_sticky_posts' => true ) ) : array(); ?>
<?php if ( $bn_posts ) : ?>
<section class="bn-h2-blog">
	<div class="bn-container">
		<div class="bn-h2-blog-head">
			<div>
				<?php if ( brightnest_opt( 'blog_eyebrow' ) ) : ?><span class="bn-eyebrow"><?php echo esc_html( brightnest_opt( 'blog_eyebrow' ) ); ?></span><?php endif; ?>
				<h2><?php echo brightnest_accent( brightnest_opt( 'blog_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			</div>
			<?php $bn_blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' ); ?>
			<a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( $bn_blog_url ); ?>"><?php esc_html_e( 'All articles', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
		</div>
		<div class="bn-pc-grid">
			<?php foreach ( $bn_posts as $bn_pid ) { get_template_part( 'template-parts/post-card', null, array( 'id' => $bn_pid ) ); } ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
// Cleaning guide teaser: the guide page's first paragraphs, its headings as links, and a button to it.
$bn_guide = $bn_on( 'article' ) ? brightnest_guide_id() : 0;
?>
<?php if ( $bn_guide ) : ?>
	<?php
	$bn_g_url   = get_permalink( $bn_guide );
	$bn_g_intro = brightnest_guide_intro( $bn_guide );
	$bn_g_secs  = brightnest_guide_sections( (string) get_post_field( 'post_content', $bn_guide ) )[1];
	$bn_g_img   = has_post_thumbnail( $bn_guide ) ? get_the_post_thumbnail_url( $bn_guide, 'large' ) : BRIGHTNEST_URI . '/assets/img/site/about-2.jpg';
	?>
<section class="bn-h2-guide" id="guide">
	<div class="bn-container bn-h2-guide-grid">
		<div class="bn-h2-guide-copy">
			<span class="bn-eyebrow"><?php esc_html_e( 'Cleaning guide', 'brightnest' ); ?></span>
			<h2><?php echo esc_html( brightnest_page_h1( $bn_guide ) ); ?></h2>
			<?php foreach ( $bn_g_intro as $bn_p ) : ?>
				<p><?php echo esc_html( $bn_p ); ?></p>
			<?php endforeach; ?>
			<?php if ( $bn_g_secs ) : ?>
				<ul class="bn-h2-guide-chips">
					<?php foreach ( array_slice( $bn_g_secs, 0, 5 ) as $bn_s ) : ?>
						<li><a href="<?php echo esc_url( $bn_g_url . '#' . $bn_s[0] ); ?>"><?php echo brightnest_icon( 'check-circle', 15 ); // phpcs:ignore ?> <?php echo esc_html( $bn_s[1] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="bn-h2-guide-btns">
				<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( $bn_g_url ); ?>"><?php esc_html_e( 'Read the full guide', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
				<a class="bn-btn bn-btn-ghost bn-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
			</div>
		</div>
		<div class="bn-h2-guide-art">
			<img src="<?php echo esc_url( $bn_g_img ); ?>" alt="" width="600" height="510" loading="lazy">
			<div class="bn-h2-guide-badge"><?php echo brightnest_icon( 'clipboard', 20 ); // phpcs:ignore ?><span><strong><?php echo esc_html( brightnest_reading_time( $bn_guide ) ); ?></strong><small><?php echo esc_html( sprintf( /* translators: %d number of sections */ _n( '%d topic covered', '%d topics covered', count( $bn_g_secs ), 'brightnest' ), count( $bn_g_secs ) ) ); ?></small></span></div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php $bn_hfaq = $bn_on( 'home_faq' ) ? brightnest_lines( 'home_faqs' ) : array(); ?>
<?php if ( $bn_hfaq ) : ?>
<section class="bn-h2-faq" id="faq">
	<div class="bn-container bn-h2-faq-box">
		<div class="bn-jx-head"><h2><?php echo brightnest_accent( brightnest_opt( 'home_faq_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div>
		<div class="bn-faq" data-load-list>
			<?php foreach ( $bn_hfaq as $bn_i => $bn_fq ) : ?>
				<details class="bn-faq-item<?php echo $bn_i >= 5 ? ' is-later' : ''; ?>" <?php echo 0 === $bn_i ? 'open' : ''; ?>>
					<summary><?php echo esc_html( $bn_fq[0] ); ?> <?php echo brightnest_icon( 'chevron-down', 20 ); // phpcs:ignore ?></summary>
					<p><?php echo esc_html( $bn_fq[1] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
		<?php if ( count( $bn_hfaq ) > 5 ) : ?>
			<p class="bn-h2-all"><button type="button" class="bn-btn bn-btn-ghost" data-load-more="5"><?php esc_html_e( 'Load more', 'brightnest' ); ?> <?php echo brightnest_icon( 'chevron-down', 16 ); // phpcs:ignore ?></button></p>
		<?php endif; ?>
	</div>
	<script type="application/ld+json">
	<?php
	echo wp_json_encode(
		array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => array_map(
				function ( $f ) {
					return array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
				},
				$bn_hfaq
			),
		)
	);
	?>
	</script>
</section>
<?php endif; ?>

<?php if ( $bn_on( 'promise' ) ) : ?>
<section class="bn-h2-promise">
	<div class="bn-container"><div class="bn-jx-promise-inner bn-h2-promise-card">
		<span class="bn-jx-shield" aria-hidden="true">
			<svg viewBox="0 0 64 72"><path d="M32 2 4 12v22c0 18 12 30 28 36 16-6 28-18 28-36V12z" fill="#fff"/><path d="M22 36l7 7 14-15" fill="none" stroke="#f25c05" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</span>
		<div>
			<h2><?php echo esc_html( brightnest_opt( 'promise_title' ) ); ?></h2>
						<div class="bn-more bn-more-dark" data-more>
				<div class="bn-more-body"><?php echo $bn_paras( brightnest_opt( 'promise_text' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<button type="button" class="bn-more-btn" data-more-btn hidden><?php esc_html_e( 'Read more', 'brightnest' ); ?></button>
			</div>
			<?php if ( brightnest_opt( 'promise_close' ) ) : ?><p><strong><?php echo esc_html( brightnest_opt( 'promise_close' ) ); ?></strong></p><?php endif; ?>
			<div class="bn-h2-promise-btns">
				<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></a>
				<?php if ( brightnest_whatsapp_url() ) : ?>
					<a class="bn-btn bn-btn-wa bn-btn-lg" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
				<?php endif; ?>
				<?php if ( brightnest_opt( 'phone' ) ) : ?>
					<a class="bn-btn bn-btn-outline-light bn-btn-lg" href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo brightnest_icon( 'phone', 17 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_opt( 'phone' ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div></div>
</section>
<?php endif; ?>



<?php if ( $bn_on( 'app' ) && brightnest_whatsapp_url() ) : ?>
<section class="bn-jx-app">
	<div class="bn-container bn-jx-app-grid">
		<div class="bn-jx-app-copy">
			<span class="bn-jx-kicker"><?php esc_html_e( 'Book in a tap', 'brightnest' ); ?></span>
			<h2><?php echo esc_html( brightnest_opt( 'app_title' ) ); ?></h2>
			<p><?php echo esc_html( brightnest_opt( 'app_text' ) ); ?></p>
			<div class="bn-jx-badges">
				<a class="bn-jx-badge" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener">
					<?php echo brightnest_icon( 'whatsapp', 24 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'Chat with us on', 'brightnest' ); ?></small><strong>WhatsApp</strong></span>
				</a>
				<?php if ( brightnest_opt( 'phone' ) ) : ?>
					<a class="bn-jx-badge" href="<?php echo esc_url( brightnest_tel_url() ); ?>">
						<?php echo brightnest_icon( 'phone', 22 ); // phpcs:ignore ?><span><small><?php esc_html_e( 'Call us', 'brightnest' ); ?></small><strong><?php echo esc_html( brightnest_opt( 'phone' ) ); ?></strong></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="bn-jx-phones" aria-hidden="true">
			<div class="bn-jx-phone bn-jx-phone-a">
				<div class="bn-jx-screen">
					<span class="bn-jx-s-top"><?php echo brightnest_icon( 'pin', 11 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_city_label( brightnest_cities()[0] ) ); ?></span>
					<span class="bn-jx-s-hello"><?php esc_html_e( 'Good evening!', 'brightnest' ); ?></span>
					<span class="bn-jx-s-banner"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<span class="bn-jx-s-grid"><?php foreach ( array_slice( $bn_tiles, 0, 8 ) as $bn_id ) : ?><i><?php echo brightnest_service_icon_html( $bn_id, 14 ); // phpcs:ignore ?></i><?php endforeach; ?></span>
					<span class="bn-jx-s-card"></span>
				</div>
			</div>
			<div class="bn-jx-phone bn-jx-phone-b">
				<div class="bn-jx-screen">
					<span class="bn-jx-s-title"><?php esc_html_e( 'Date & Time', 'brightnest' ); ?></span>
					<span class="bn-jx-s-days"><i></i><i class="on"></i><i></i><i></i><i></i></span>
					<span class="bn-jx-s-slots"><i class="on"></i><i></i><i></i><i></i><i></i><i></i></span>
					<span class="bn-jx-s-btn"></span>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>



<script>
/* Homepage: "Read more" on long text, "Load more" on long lists. */
(function () {
	var more = <?php echo wp_json_encode( __( 'Read more', 'brightnest' ) ); ?>;
	var less = <?php echo wp_json_encode( __( 'Show less', 'brightnest' ) ); ?>;
	function check() {
		document.querySelectorAll('[data-more]').forEach(function (box) {
			var body = box.querySelector('.bn-more-body');
			var btn = box.querySelector('[data-more-btn]');
			if (!btn || box.classList.contains('is-open')) { return; }
			btn.hidden = body.scrollHeight <= body.clientHeight + 4;
			box.classList.toggle('is-folded', !btn.hidden);
		});
	}
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-more-btn]');
		if (btn) {
			var box = btn.closest('[data-more]');
			var open = box.classList.toggle('is-open');
			box.classList.toggle('is-folded', !open);
			btn.textContent = open ? less : more;
			if (!open && box.getBoundingClientRect().top < 0) { box.scrollIntoView({ block: 'start' }); }
			return;
		}
		var rv = e.target.closest('[data-reveal]');
		if (rv) {
			var el = document.getElementById(rv.getAttribute('data-reveal'));
			el.hidden = !el.hidden;
			rv.setAttribute('aria-expanded', String(!el.hidden));
			rv.textContent = el.hidden ? more : less;
			return;
		}
		var lm = e.target.closest('[data-load-more]');
		if (lm) {
			var list = lm.closest('.bn-container').querySelector('[data-load-list]');
			var later = list.querySelectorAll('.is-later');
			for (var i = 0; i < later.length && i < +lm.getAttribute('data-load-more'); i++) { later[i].classList.remove('is-later'); }
			if (!list.querySelector('.is-later')) { lm.parentNode.remove(); }
			check();
		}
	});
	check();
	window.addEventListener('load', check);
	window.addEventListener('resize', check);
})();
</script>

<?php
get_footer();
