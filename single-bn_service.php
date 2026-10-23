<?php
/**
 * Single service page.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id       = get_the_ID();
	$bn_terms    = get_the_terms( $bn_id, 'bn_service_cat' );
	$bn_cat      = ( $bn_terms && ! is_wp_error( $bn_terms ) ) ? $bn_terms[0] : null;
	$bn_inc_ar   = brightnest_show_ar() ? brightnest_ar_meta( $bn_id, 'includes' ) : null; // Arabic list when filled in.
	$bn_includes = array_filter( array_map( 'trim', explode( "\n", (string) ( null !== $bn_inc_ar ? $bn_inc_ar : get_post_meta( $bn_id, '_bn_includes', true ) ) ) ) );
	$bn_duration = get_post_meta( $bn_id, '_bn_duration', true );
	list( $bn_price, $bn_unit ) = brightnest_service_from( $bn_id ); // Same "from" price as the cards and the hourly table.
	$bn_rating   = get_post_meta( $bn_id, '_bn_rating', true );
	$bn_reviews  = get_post_meta( $bn_id, '_bn_review_count', true );
	$bn_cur      = brightnest_opt( 'currency' );
	$bn_hourly   = brightnest_hourly_config( $bn_id );
	$bn_groups   = $bn_hourly ? array() : brightnest_service_options( $bn_id );
	$bn_has_opts = ! $bn_hourly && '' !== trim( (string) get_post_meta( $bn_id, '_bn_options', true ) );
	$bn_count    = $bn_has_opts ? count( brightnest_service_items( $bn_id ) ) : 0;
	$bn_book     = brightnest_checkout_url( $bn_id );
	$bn_title    = get_the_title();
	$bn_wa       = brightnest_whatsapp_url( sprintf( /* translators: %s service */ __( 'Hi, I would like to book %s.', 'brightnest' ), wp_strip_all_tags( html_entity_decode( $bn_title ) ) ) );
	// Settings → Service page: which sections show (the same on every service page).
	$bn_show     = array(
		'included' => '1' === (string) brightnest_opt( 'show_sv_included' ),
		'prices'   => '1' === (string) brightnest_opt( 'show_sv_prices' ),
		'faq'      => '1' === (string) brightnest_opt( 'show_sv_faq' ),
		'related'  => '1' === (string) brightnest_opt( 'show_sv_related' ),
	);
	$bn_show_wa  = '1' === (string) brightnest_opt( 'show_sv_whatsapp' );
	$bn_money    = function ( $n ) use ( $bn_cur ) {
		return $bn_cur . ' ' . number_format_i18n( $n, floor( $n ) == $n ? 0 : 2 );
	};
	// Base price in a span; the script at the bottom applies the area's price change (× $mult, e.g. hours).
	$bn_amt      = function ( $n, $mult = 1 ) use ( $bn_money ) {
		return '<span data-bn-amt="' . esc_attr( (float) $n ) . '" data-bn-mult="' . esc_attr( (float) $mult ) . '">' . esc_html( $bn_money( (float) $n * $mult ) ) . '</span>';
	};
	?>

	<?php // Simple service page: light hero, the service text, what's included, prices, FAQ. ?>
	<section class="bn-ss-hero">
		<div class="bn-container bn-ss-hero-grid">
			<div class="bn-ss-hero-copy">
				<?php brightnest_breadcrumbs(); ?>
				<h1><?php echo esc_html( brightnest_page_h1() ); ?></h1>
				<?php if ( '' !== $bn_rating ) : ?>
					<p class="bn-ss-rating">★ <strong><?php echo esc_html( number_format_i18n( (float) $bn_rating, 1 ) ); ?></strong><?php if ( $bn_reviews ) : ?> <span>(<?php echo esc_html( number_format_i18n( (int) $bn_reviews ) ); ?>)</span><?php endif; ?></p>
				<?php endif; ?>
				<?php if ( has_excerpt() ) : ?>
					<p class="bn-ss-lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $bn_price ) : ?>
					<p class="bn-ss-price"><small><?php echo esc_html( brightnest_sv_text( 'sv_from_label' ) ); ?></small> <strong><?php echo $bn_amt( (float) $bn_price ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong><?php if ( $bn_unit ) : ?> <span><?php echo esc_html( $bn_unit ); ?></span><?php endif; ?></p>
				<?php endif; ?>
				<?php // Filled by the script below: the chosen area, its extra charge, or "not offered here". ?>
				<p class="bn-ss-area" data-bn-area-note hidden></p>
				<div class="bn-ss-actions">
					<a class="bn-btn bn-btn-primary bn-btn-lg" href="<?php echo esc_url( $bn_book ); ?>"><?php echo esc_html( brightnest_sv_text( 'sv_book_label' ) ); ?></a>
					<?php if ( $bn_wa && $bn_show_wa ) : ?>
						<a class="bn-btn bn-btn-wa bn-btn-lg" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'WhatsApp', 'brightnest' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="bn-ss-hero-img">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'brightnest-hero', array( 'alt' => esc_attr( $bn_title ) ) ); ?>
				<?php else : ?>
					<span class="bn-ss-hero-icon"><?php echo brightnest_service_icon_html( $bn_id, 96 ); // phpcs:ignore ?></span>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="bn-container bn-sv-layout">
		<div class="bn-sv-main">

			<?php $bn_has_text = '' !== trim( wp_strip_all_tags( get_the_content() ) ); ?>
			<?php if ( $bn_has_text || has_excerpt() ) : ?>
				<section class="bn-sv-sec" id="overview">
					<h2 class="bn-sv-h2"><?php echo esc_html( str_replace( '{service}', wp_strip_all_tags( html_entity_decode( $bn_title ) ), brightnest_sv_text( 'sv_about_title' ) ) ); ?></h2>
					<div class="bn-prose"><?php if ( $bn_has_text ) : ?><?php the_content(); ?><?php else : ?><p><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?></div>
				</section>
			<?php endif; ?>

			<?php if ( $bn_includes && $bn_show['included'] ) : ?>
				<section class="bn-sv-sec" id="included">
					<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_included_title' ) ); ?></h2>
					<ul class="bn-sv-incl">
						<?php foreach ( $bn_includes as $bn_n => $bn_item ) : ?>
							<li><span><?php echo brightnest_icon( 'check', 16 ); // phpcs:ignore ?></span><?php echo esc_html( $bn_item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! $bn_show['prices'] ) : ?>
				<?php // Prices switched off in Settings → Service page. ?>
			<?php elseif ( $bn_hourly ) : ?>
				<section class="bn-sv-sec" id="pricing">
					<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_prices_title' ) ); ?></h2>
					<div class="bn-sv-rate">
						<div class="bn-sv-rate-main"><small><?php esc_html_e( 'Per professional, per hour', 'brightnest' ); ?></small><strong><?php echo $bn_amt( $bn_hourly['rate'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong>
							<?php if ( $bn_hourly['materials'] > 0 ) : ?><span><?php echo str_replace( '%%MAT%%', $bn_amt( $bn_hourly['materials'] ), esc_html( sprintf( /* translators: %s amount */ __( '+ %s / hour with cleaning materials', 'brightnest' ), '%%MAT%%' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?>
						</div>
						<table class="bn-sv-table">
							<thead><tr><th><?php esc_html_e( 'Duration', 'brightnest' ); ?></th><th><?php esc_html_e( '1 professional', 'brightnest' ); ?></th><th><?php esc_html_e( '2 professionals', 'brightnest' ); ?></th></tr></thead>
							<tbody>
								<?php foreach ( array( 2, 3, 4, 5 ) as $bn_h ) : ?>
									<tr><td><?php echo esc_html( sprintf( /* translators: %d hours */ __( '%d hours', 'brightnest' ), $bn_h ) ); ?></td><td><?php echo $bn_amt( $bn_hourly['rate'], $bn_h ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php echo $bn_amt( $bn_hourly['rate'], $bn_h * 2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td></tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</section>
			<?php elseif ( $bn_has_opts ) : ?>
				<section class="bn-sv-sec" id="pricing">
					<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_prices_title' ) ); ?></h2>
					<?php if ( count( $bn_groups ) > 1 ) : ?>
						<div class="bn-sv-tabs" role="tablist" data-price-tabs>
							<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
								<button type="button" role="tab" class="<?php echo 0 === $bn_gi ? 'is-active' : ''; ?>" aria-selected="<?php echo 0 === $bn_gi ? 'true' : 'false'; ?>" data-tab="<?php echo esc_attr( $bn_gi ); ?>"><?php echo esc_html( $bn_g['name'] ); ?> <em><?php echo esc_html( count( $bn_g['items'] ) ); ?></em></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
						<ul class="bn-sv-pkgs" data-panel="<?php echo esc_attr( $bn_gi ); ?>" <?php echo 0 === $bn_gi ? '' : 'hidden'; ?>>
							<?php foreach ( $bn_g['items'] as $bn_it ) : ?>
								<li>
									<div class="bn-sv-pkg-info">
										<strong><?php echo esc_html( $bn_it['name'] ); ?></strong>
										<?php if ( $bn_it['desc'] ) : ?><span><?php echo esc_html( $bn_it['desc'] ); ?></span><?php endif; ?>
									</div>
									<div class="bn-sv-pkg-price">
										<?php if ( $bn_it['old'] ) : ?><del><?php echo $bn_amt( $bn_it['old'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></del><?php endif; ?>
										<strong><?php echo $bn_amt( $bn_it['price'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong>
									</div>
									<a class="bn-btn bn-btn-navy bn-btn-sm" href="<?php echo esc_url( add_query_arg( 'add', $bn_it['id'], $bn_book ) ); ?>"><?php esc_html_e( 'Book', 'brightnest' ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endforeach; ?>
				</section>
			<?php elseif ( '' !== $bn_price ) : ?>
				<section class="bn-sv-sec" id="pricing">
					<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_prices_title' ) ); ?></h2>
					<ul class="bn-sv-pkgs">
						<li>
							<div class="bn-sv-pkg-info"><strong><?php echo esc_html( wp_strip_all_tags( html_entity_decode( $bn_title ) ) ); ?></strong><?php if ( $bn_unit ) : ?><span><?php echo esc_html( $bn_unit ); ?></span><?php endif; ?></div>
							<div class="bn-sv-pkg-price"><strong><?php echo $bn_amt( (float) $bn_price ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong></div>
							<a class="bn-btn bn-btn-navy bn-btn-sm" href="<?php echo esc_url( $bn_book ); ?>"><?php esc_html_e( 'Book', 'brightnest' ); ?></a>
						</li>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( $bn_show['faq'] ) : ?>
				<section class="bn-sv-sec" id="faq">
					<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_faq_title' ) ); ?></h2>
					<?php get_template_part( 'template-parts/faq' ); ?>
				</section>
			<?php endif; ?>
		</div>

		<aside class="bn-sv-side">
			<div class="bn-sv-book">
				<div class="bn-sv-book-head">
					<small><?php echo esc_html( brightnest_sv_text( 'sv_from_label' ) ); ?></small>
					<strong><?php echo '' !== $bn_price ? $bn_amt( (float) $bn_price ) : esc_html__( 'Get a quote', 'brightnest' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong>
					<?php if ( $bn_unit ) : ?><span><?php echo esc_html( $bn_unit ); ?></span><?php endif; ?>
				</div>
				<a class="bn-btn bn-btn-primary bn-btn-block bn-btn-lg" href="<?php echo esc_url( $bn_book ); ?>"><?php echo esc_html( brightnest_sv_text( 'sv_book_label' ) ); ?></a>
				<?php if ( $bn_wa && $bn_show_wa ) : ?>
					<a class="bn-btn bn-btn-wa bn-btn-block" href="<?php echo esc_url( $bn_wa ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Book on WhatsApp', 'brightnest' ); ?></a>
				<?php endif; ?>
				<?php if ( brightnest_opt( 'phone' ) ) : ?>
					<a class="bn-sv-call" href="<?php echo esc_url( brightnest_tel_url() ); ?>"><?php echo brightnest_icon( 'phone', 16 ); // phpcs:ignore ?> <?php echo esc_html( sprintf( /* translators: %s phone */ __( 'Or call %s', 'brightnest' ), brightnest_opt( 'phone' ) ) ); ?></a>
				<?php endif; ?>
			</div>
		</aside>
	</div>

	<?php
	$bn_related = $bn_cat ? get_posts(
		array(
			'post_type'      => 'bn_service',
			'posts_per_page' => 4,
			'fields'         => 'ids',
			'post__not_in'   => array( $bn_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
			'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $bn_cat->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	) : array();
	if ( count( $bn_related ) < 4 ) {
		$bn_related = array_merge(
			$bn_related,
			get_posts(
				array(
					'post_type'      => 'bn_service',
					'posts_per_page' => 4 - count( $bn_related ),
					'fields'         => 'ids',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'post__not_in'   => array_merge( array( $bn_id ), $bn_related ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
				)
			)
		);
	}
	if ( $bn_related && $bn_show['related'] ) :
		?>
		<section class="bn-section bn-sv-related">
			<div class="bn-container">
				<div class="bn-svcs-head">
						<h2 class="bn-sv-h2"><?php echo esc_html( brightnest_sv_text( 'sv_related_title' ) ); ?></h2>
					<a class="bn-btn bn-btn-navy" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?></a>
				</div>
				<div class="bn-sc-grid">
					<?php foreach ( $bn_related as $bn_rid ) { get_template_part( 'template-parts/service-card-photo', null, array( 'id' => $bn_rid ) ); } ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<div class="bn-sv-mbar">
		<div><small><?php esc_html_e( 'From', 'brightnest' ); ?></small><strong><?php echo '' !== $bn_price ? $bn_amt( (float) $bn_price ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></strong></div>
		<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_book ); ?>"><?php echo esc_html( brightnest_sv_text( 'sv_book_label' ) ); ?></a>
	</div>

	<?php
	// Prices for the customer's area (BrightNest → Cities & Areas, and the service's own on/off per city).
	$bn_area_data = array(
		'cur'    => $bn_cur,
		'cities' => array(),
		'places' => array(),
		'labels' => function_exists( 'brightnest_city_labels' ) ? brightnest_city_labels() : array(),
		'txt'    => array(
			'for'  => __( 'Prices for %s', 'brightnest' ),
			'off'  => __( 'Sorry, %1$s is not available in %2$s yet.', 'brightnest' ),
		),
	);
	foreach ( brightnest_service_areas( $bn_id ) as $bn_c => $bn_r ) {
		$bn_area_data['cities'][ $bn_c ] = array( 'on' => (bool) $bn_r['on'], 'adj' => (float) $bn_r['adj'] );
	}
	foreach ( brightnest_service_places( $bn_id ) as $bn_k => $bn_r ) {
		$bn_area_data['places'][ $bn_k ] = array( 'on' => (bool) $bn_r['on'], 'adj' => (float) $bn_r['adj'] );
	}
	?>
	<script>
	/* Service page: show prices for the area the customer picked (same rounding as the checkout). */
	(function () {
		var D = <?php echo wp_json_encode( $bn_area_data ); ?>;
		var title = <?php echo wp_json_encode( wp_strip_all_tags( html_entity_decode( $bn_title ) ) ); ?>;
		var money = function (n) { return D.cur + ' ' + Number(n).toLocaleString('en-US', { minimumFractionDigits: n % 1 ? 2 : 0, maximumFractionDigits: 2 }); };
		var fill = function (s) { var a = Array.prototype.slice.call(arguments, 1), i = 0; return s.replace(/%(\d)\$s|%s/g, function (m, n) { return n ? a[n - 1] : a[i++]; }); };
		function render() {
			var loc = null;
			try { loc = JSON.parse(localStorage.getItem('bn_location') || 'null'); } catch (e) { /* ignore */ }
			var city = loc && loc.area && D.cities[loc.area] ? loc.area : '';
			var row = city ? D.cities[city] : { on: true, adj: 0 };
			var key = city && loc.street ? city + '|' + loc.street : '';
			if (key && D.places[key]) { row = row.on ? D.places[key] : row; }
			var f = 1 + (row.adj || 0) / 100;
			document.querySelectorAll('[data-bn-amt]').forEach(function (el) {
				var unit = Math.round(+el.getAttribute('data-bn-amt') * f * 100) / 100;
				el.textContent = money(Math.round(unit * +el.getAttribute('data-bn-mult') * 100) / 100);
			});
			var note = document.querySelector('[data-bn-area-note]');
			if (!note) { return; }
			var place = city ? ((loc.street ? loc.street + ', ' : '') + (D.labels[city] || city)) : '';
			note.classList.toggle('is-off', !!city && !row.on);
			if (!city) { note.hidden = true; return; }
			note.hidden = false;
			note.textContent = !row.on ? fill(D.txt.off, title, place) : fill(D.txt.for, place); // An area charge is not named; it is in the checkout subtotal.
		}
		render();
		document.addEventListener('bn:location', render); // Area changed in the header picker.
	})();
	</script>
	<?php
	// Service schema for SEO.
	$bn_schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => wp_strip_all_tags( html_entity_decode( $bn_title ) ),
		'description' => wp_strip_all_tags( get_the_excerpt() ),
		'url'         => get_permalink(),
		'provider'    => array( '@type' => 'LocalBusiness', 'name' => get_bloginfo( 'name' ), 'telephone' => brightnest_opt( 'phone' ), 'address' => brightnest_opt( 'address' ) ),
		'areaServed'  => brightnest_cities(),
	);
	if ( '' !== $bn_price ) {
		$bn_schema['offers'] = array( '@type' => 'Offer', 'price' => $bn_price, 'priceCurrency' => $bn_cur );
	}
	if ( has_post_thumbnail() ) {
		$bn_schema['image'] = get_the_post_thumbnail_url( $bn_id, 'brightnest-hero' );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $bn_schema ) . '</script>';

endwhile;

get_footer();
