<?php
/**
 * Homepage (Justlife-style layout):
 * full-width photo hero with location → "Leave your to-do list to us!" icon tiles →
 * reasons to love us → promise band → book on WhatsApp → service areas & locations.
 * All text comes from BrightNest → Settings → Homepage.
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
?>

<section class="bn-jx-hero" style="--bn-jx-img: url('<?php echo esc_url( brightnest_img_opt( 'hero_img_1', 'bg-stats.jpg' ) ); ?>');">
	<div class="bn-container bn-jx-hero-inner">
		<h1><?php echo brightnest_accent( brightnest_opt( 'hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<div class="bn-jx-loccard">
			<p class="bn-jx-locq"><?php echo esc_html( brightnest_opt( 'loc_question' ) ); ?></p>
			<div class="bn-jx-locbar">
				<button type="button" class="bn-jx-locfield" data-loc-open>
					<?php echo brightnest_icon( 'pin', 20 ); // phpcs:ignore ?>
					<span data-loc-label data-empty="<?php esc_attr_e( 'Search for area, street name, landmark…', 'brightnest' ); ?>"><?php esc_html_e( 'Search for area, street name, landmark…', 'brightnest' ); ?></span>
				</button>
				<button type="button" class="bn-jx-here" data-loc-here>
					<?php esc_html_e( 'Set my location', 'brightnest' ); ?>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
				</button>
			</div>
		</div>
	</div>
</section>

<?php if ( $bn_tiles && $bn_on( 'services' ) ) : ?>
<section class="bn-jx-todo" id="services">
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
	</div>
</section>
<?php endif; ?>

<?php if ( $bn_reasons && $bn_on( 'why' ) ) : ?>
<section class="bn-jx-reasons">
	<div class="bn-container">
		<div class="bn-jx-head">
			<h2><?php echo brightnest_accent( brightnest_opt( 'love_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<p><?php echo esc_html( brightnest_opt( 'love_sub' ) ); ?></p>
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

<?php if ( $bn_on( 'promise' ) ) : ?>
<section class="bn-jx-promise">
	<div class="bn-container bn-jx-promise-inner">
		<span class="bn-jx-shield" aria-hidden="true">
			<svg viewBox="0 0 64 72"><path d="M32 2 4 12v22c0 18 12 30 28 36 16-6 28-18 28-36V12z" fill="#fff"/><path d="M22 36l7 7 14-15" fill="none" stroke="#f25c05" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</span>
		<div>
			<h2><?php echo esc_html( brightnest_opt( 'promise_title' ) ); ?></h2>
			<p><?php echo esc_html( brightnest_opt( 'promise_text' ) ); ?><?php if ( brightnest_opt( 'promise_close' ) ) : ?> <strong><?php echo esc_html( brightnest_opt( 'promise_close' ) ); ?></strong><?php endif; ?></p>
		</div>
	</div>
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



<?php
get_footer();
