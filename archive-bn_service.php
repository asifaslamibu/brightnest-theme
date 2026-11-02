<?php
/**
 * Booking page (/booking/): a hero with the visitor's area and trust points, category chips that
 * stay on screen and filter one grid of every service card (photo, rating, from-price, duration,
 * Book now). Cards open the checkout, so only services that book online show here
 * ("Info page only" services stay off this page).
 * Also used for service category pages (/booking/cleaning/) and service search results.
 *
 * @package BrightNest
 */

get_header();

$bn_cats    = array_values( array_filter( brightnest_categories(), function ( $t ) { return $t->count > 0; } ) );
$bn_current = is_tax( 'bn_service_cat' ) ? get_queried_object() : null;
$bn_search  = is_search() ? get_search_query() : '';
$bn_hub     = get_post_type_archive_link( 'bn_service' );

// Category picked in a search without words: treat it like the category page.
if ( ! $bn_current && is_search() && '' === $bn_search && get_query_var( 'bn_service_cat' ) ) {
	$bn_term = get_term_by( 'slug', get_query_var( 'bn_service_cat' ), 'bn_service_cat' );
	if ( $bn_term ) {
		$bn_current = $bn_term;
	}
}
$bn_mode = $bn_search ? 'search' : ( $bn_current ? 'category' : 'hub' );

// Only services booked online at the checkout.
$bn_bookable = function ( $ids ) {
	return array_values( array_filter( $ids, function ( $id ) { return ! brightnest_info_only( $id ); } ) );
};
$bn_services_of = function ( $term_id ) use ( $bn_bookable ) {
	return $bn_bookable( get_posts(
		array(
			'post_type'      => 'bn_service',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	) );
};
// Categories with no bookable service are left out of the chips and the sections.
$bn_cats  = array_values( array_filter( $bn_cats, function ( $t ) use ( $bn_services_of ) { return (bool) $bn_services_of( $t->term_id ); } ) );
$bn_cur   = brightnest_opt( 'currency' );

$bn_card = function ( $id, $label = false ) use ( $bn_cur ) {
	$terms  = get_the_terms( $id, 'bn_service_cat' );
	$areas  = array( 'c' => array(), 'p' => array() );
	foreach ( brightnest_service_areas( $id ) as $c => $r ) {
		$areas['c'][ $c ] = array( (int) (bool) $r['on'], (float) $r['adj'] );
	}
	foreach ( brightnest_service_places( $id ) as $k => $r ) {
		$areas['p'][ $k ] = array( (int) (bool) $r['on'], (float) $r['adj'] );
	}
	$terms  = $terms && ! is_wp_error( $terms ) ? $terms : array();
	$from   = brightnest_service_from( $id );
	$rating = get_post_meta( $id, '_bn_rating', true );
	$dur    = get_post_meta( $id, '_bn_duration', true );
	$text   = has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
	?>
	<a class="bn-bk-card" href="<?php echo esc_url( brightnest_checkout_url( $id ) ); ?>" data-bk-areas="<?php echo esc_attr( wp_json_encode( $areas ) ); ?>" data-hub-card data-cats="<?php echo esc_attr( implode( ' ', wp_list_pluck( $terms, 'slug' ) ) ); ?>" data-name="<?php echo esc_attr( strtolower( wp_strip_all_tags( html_entity_decode( get_the_title( $id ) ) . ' ' . $text ) ) ); ?>">
		<span class="bn-bk-img">
			<?php if ( has_post_thumbnail( $id ) ) : ?>
				<?php echo get_the_post_thumbnail( $id, 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			<?php else : ?>
				<span class="bn-bk-icon"><?php echo brightnest_service_icon_html( $id, 46 ); // phpcs:ignore ?></span>
			<?php endif; ?>
			<?php if ( '1' === get_post_meta( $id, '_bn_featured', true ) ) : ?><span class="bn-bk-badge"><?php echo brightnest_icon( 'star', 12 ); // phpcs:ignore ?> <?php esc_html_e( 'Popular', 'brightnest' ); ?></span><?php endif; ?>
		</span>
		<span class="bn-bk-body">
			<?php if ( $label && $terms ) : ?><span class="bn-bk-cat"><?php echo esc_html( $terms[0]->name ); ?></span><?php endif; ?>
			<span class="bn-bk-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
			<?php if ( $text ) : ?><span class="bn-bk-text"><?php echo esc_html( wp_trim_words( $text, 14, '…' ) ); ?></span><?php endif; ?>
			<span class="bn-bk-meta">
				<?php if ( '' !== (string) $rating ) : ?><span class="bn-bk-rate"><?php echo brightnest_icon( 'star', 13 ); // phpcs:ignore ?> <?php echo esc_html( number_format_i18n( (float) $rating, 1 ) ); ?></span><?php endif; ?>
				<?php if ( $dur ) : ?><span><?php echo brightnest_icon( 'clock', 13 ); // phpcs:ignore ?> <?php echo esc_html( $dur ); ?></span><?php endif; ?>
			</span>
			<span class="bn-bk-foot">
				<span class="bn-bk-price">
					<?php if ( '' !== $from[0] ) : ?>
						<small><?php esc_html_e( 'From', 'brightnest' ); ?></small>
						<strong data-bk-from="<?php echo esc_attr( (float) $from[0] ); ?>"<?php echo $from[1] ? ' data-bk-unit' : ''; ?>><?php echo esc_html( $bn_cur . ' ' . number_format_i18n( (float) $from[0] ) ); ?></strong><?php if ( $from[1] ) : ?><em><?php echo esc_html( $from[1] ); ?></em><?php endif; ?>
					<?php endif; ?>
				</span>
				<span class="bn-bk-off" data-bk-off hidden><?php esc_html_e( 'Not available in your area', 'brightnest' ); ?></span>
				<span class="bn-bk-go"><?php esc_html_e( 'Book now', 'brightnest' ); ?> <?php echo brightnest_icon( 'arrow-right', 15 ); // phpcs:ignore ?></span>
			</span>
		</span>
	</a>
	<?php
};

if ( 'search' === $bn_mode ) {
	/* translators: %s search term */
	$bn_h1  = sprintf( __( 'Results for “%s”', 'brightnest' ), $bn_search );
	$bn_sub = __( 'Pick a service to see the price for your area and book online.', 'brightnest' );
} elseif ( 'category' === $bn_mode ) {
	$bn_h1  = $bn_current->name;
	$bn_sub = $bn_current->description ? wp_strip_all_tags( $bn_current->description ) : __( 'Pick a service to see the price for your area and book online in a minute.', 'brightnest' );
} else {
	$bn_h1  = __( 'Book a service', 'brightnest' );
	$bn_sub = __( 'Vetted professionals at your door. Pick a service, choose a time and pay securely online.', 'brightnest' );
}
?>

<div class="bn-hub bn-bk" data-hub>
	<section class="bn-bk-hero">
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<div class="bn-bk-hero-grid">
				<div>
					<h1><?php echo esc_html( $bn_h1 ); ?></h1>
					<p class="bn-bk-sub"><?php echo esc_html( $bn_sub ); ?></p>
					<p class="bn-bk-area" data-bk-area hidden><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?> <span><?php esc_html_e( 'Prices for', 'brightnest' ); ?> <strong data-bk-area-name></strong></span></p>
				</div>
				<ul class="bn-bk-trust">
					<li><?php echo brightnest_icon( 'shield', 20 ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Vetted professionals', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Trained, background-checked team', 'brightnest' ); ?></small></span></li>
					<li><?php echo brightnest_icon( 'tag', 20 ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Clear prices', 'brightnest' ); ?></strong><small><?php esc_html_e( 'The price you see is the price you pay', 'brightnest' ); ?></small></span></li>
					<li><?php echo brightnest_icon( 'calendar', 20 ); // phpcs:ignore ?><span><strong><?php esc_html_e( 'Book in a minute', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Secure online payment', 'brightnest' ); ?></small></span></li>
				</ul>
			</div>
		</div>
	</section>

	<?php if ( $bn_cats ) : ?>
		<nav class="bn-bk-cats" aria-label="<?php esc_attr_e( 'Service categories', 'brightnest' ); ?>">
			<div class="bn-container bn-bk-cats-wrap" data-bk-scroller>
				<button type="button" class="bn-bk-arrow is-prev" data-bk-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>" hidden><?php echo brightnest_icon( 'arrow-left', 16 ); // phpcs:ignore ?></button>
				<div class="bn-bk-cats-track" data-bk-track>
					<a class="<?php echo 'hub' === $bn_mode ? 'is-active' : ''; ?>" href="<?php echo esc_url( $bn_hub ); ?>" data-bk-all><?php esc_html_e( 'All', 'brightnest' ); ?></a>
					<?php foreach ( $bn_cats as $bn_t ) : ?>
						<?php $bn_url = get_term_link( $bn_t ); // On the hub the script filters the grid instead. ?>
						<a class="<?php echo ( $bn_current && $bn_current->term_id === $bn_t->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( $bn_url ); ?>" data-bk-chip="<?php echo esc_attr( $bn_t->slug ); ?>" data-bk-name="<?php echo esc_attr( $bn_t->name ); ?>">
							<span class="bn-bk-chip-ic"><?php echo brightnest_cat_icon_html( $bn_t->term_id, 18 ); // phpcs:ignore ?></span>
							<?php echo esc_html( $bn_t->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
				<button type="button" class="bn-bk-arrow is-next" data-bk-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>" hidden><?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></button>
			</div>
		</nav>
	<?php endif; ?>

	<div class="bn-container bn-bk-main">
		<?php if ( 'hub' === $bn_mode ) : ?>
			<?php
			$bn_all_ids = array();
			foreach ( $bn_cats as $bn_t ) {
				$bn_all_ids = array_merge( $bn_all_ids, $bn_services_of( $bn_t->term_id ) );
			}
			$bn_all_ids = array_values( array_unique( $bn_all_ids ) );
			// Popular first, then the order above.
			usort( $bn_all_ids, function ( $x, $y ) use ( $bn_all_ids ) {
				$px = '1' === get_post_meta( $x, '_bn_featured', true );
				$py = '1' === get_post_meta( $y, '_bn_featured', true );
				return $px === $py ? array_search( $x, $bn_all_ids, true ) - array_search( $y, $bn_all_ids, true ) : ( $px ? -1 : 1 );
			} );
			?>
			<section class="bn-bk-sec" data-hub-row>
				<div class="bn-bk-sec-head">
					<h2 data-bk-title><?php esc_html_e( 'All services', 'brightnest' ); ?> <small data-bk-count><?php echo esc_html( sprintf( /* translators: %d number of services */ _n( '%d service', '%d services', count( $bn_all_ids ), 'brightnest' ), count( $bn_all_ids ) ) ); ?></small></h2>
				</div>
				<div class="bn-bk-grid" data-bk-grid>
					<?php foreach ( $bn_all_ids as $bn_id ) { $bn_card( $bn_id, true ); } ?>
				</div>
			</section>
			<p class="bn-hub-empty bn-bk-empty" data-hub-empty hidden><?php esc_html_e( 'No services match your search.', 'brightnest' ); ?></p>
		<?php else : ?>
			<?php $bn_ids = 'category' === $bn_mode ? $bn_services_of( $bn_current->term_id ) : $bn_bookable( wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) ); ?>
			<section class="bn-bk-sec" data-hub-row>
				<?php if ( $bn_ids ) : ?>
					<div class="bn-bk-grid"><?php foreach ( $bn_ids as $bn_id ) { $bn_card( $bn_id ); } ?></div>
				<?php else : ?>
					<div class="bn-bk-none">
						<p><?php esc_html_e( 'No services found. Try another word, or message us and we will help.', 'brightnest' ); ?></p>
						<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_hub ); ?>"><?php esc_html_e( 'Browse all services', 'brightnest' ); ?></a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>

	<?php get_template_part( 'template-parts/blog-cta' ); ?>
</div>

<script>
/* Booking page: arrows that scroll the category chips (shown only when they do not fit). */
(function () {
	var wrap = document.querySelector('[data-bk-scroller]');
	if (!wrap) { return; }
	var track = wrap.querySelector('[data-bk-track]');
	var prev = wrap.querySelector('[data-bk-prev]');
	var next = wrap.querySelector('[data-bk-next]');
	var dir = getComputedStyle(track).direction === 'rtl' ? -1 : 1;
	function update() {
		var max = track.scrollWidth - track.clientWidth;
		var pos = Math.abs(track.scrollLeft);
		prev.hidden = pos <= 2;
		next.hidden = pos >= max - 2;
		wrap.classList.toggle('has-prev', !prev.hidden);
		wrap.classList.toggle('has-next', !next.hidden);
	}
	prev.addEventListener('click', function () { track.scrollBy({ left: -track.clientWidth * 0.7 * dir, behavior: 'smooth' }); });
	next.addEventListener('click', function () { track.scrollBy({ left: track.clientWidth * 0.7 * dir, behavior: 'smooth' }); });
	track.addEventListener('scroll', update, { passive: true });
	window.addEventListener('resize', update);
	update();
})();

/* Booking page: the visitor's area in the hero; the category chips filter the grid. */
(function () {
	var cur = <?php echo wp_json_encode( brightnest_opt( 'currency' ) ); ?>;
	var fees = <?php echo wp_json_encode( (object) array_merge( array_combine( brightnest_cities(), array_map( 'brightnest_area_fee', brightnest_cities() ) ) ?: array(), brightnest_place_fees() ) ); ?>;
	var money = function (n) { return cur + ' ' + Number(n).toLocaleString('en-US', { maximumFractionDigits: 2 }); };
	function area() {
		var loc = null;
		try { loc = JSON.parse(localStorage.getItem('bn_location') || 'null'); } catch (e) { /* ignore */ }
		var box = document.querySelector('[data-bk-area]');
		if (box) {
			box.hidden = !(loc && loc.label);
			if (loc && loc.label) { box.querySelector('[data-bk-area-name]').textContent = loc.label; }
		}
		var city = loc && loc.area ? loc.area : '';
		var key = city && loc.street ? city + '|' + loc.street : '';
		// The area's charge per booking, as at the checkout (its own, else its city's).
		var fee = key && fees[key] !== undefined ? +fees[key] : (+fees[city] || 0);
		document.querySelectorAll('[data-bk-areas]').forEach(function (card) {
			var A = JSON.parse(card.getAttribute('data-bk-areas'));
			var row = city && A.c[city] ? A.c[city] : [1, 0];
			if (row[0] && key && A.p[key]) { row = A.p[key]; }
			var price = card.querySelector('[data-bk-from]');
			if (price) {
				var unit = Math.round(+price.getAttribute('data-bk-from') * (1 + row[1] / 100) * 100) / 100;
				price.textContent = money(price.hasAttribute('data-bk-unit') ? unit : unit + fee); // Hourly rates stay per hour.
			}
			var off = !row[0];
			card.classList.toggle('is-off', off);
			card.querySelector('[data-bk-off]').hidden = !off;
		});
	}
	area();
	document.addEventListener('bn:location', area); // Area changed in the header picker.
	// Hub: the chips filter the one grid (no page change); category and search pages keep their links.
	var grid = document.querySelector('[data-bk-grid]');
	if (!grid) { return; }
	var chips = document.querySelectorAll('[data-bk-chip], [data-bk-all]');
	var title = document.querySelector('[data-bk-title]');
	var count = document.querySelector('[data-bk-count]');
	var allTitle = title ? title.firstChild.textContent.trim() : '';
	var one = <?php echo wp_json_encode( __( '%d service', 'brightnest' ) ); ?>;
	var many = <?php echo wp_json_encode( __( '%d services', 'brightnest' ) ); ?>;
	function show(slug, name) {
		var n = 0;
		grid.querySelectorAll('[data-hub-card]').forEach(function (c) {
			var hit = !slug || (' ' + c.getAttribute('data-cats') + ' ').indexOf(' ' + slug + ' ') !== -1;
			c.hidden = !hit;
			if (hit) { n++; }
		});
		chips.forEach(function (c) { c.classList.toggle('is-active', slug ? c.getAttribute('data-bk-chip') === slug : c.hasAttribute('data-bk-all')); });
		if (title) { title.firstChild.textContent = (name || allTitle) + ' '; }
		if (count) { count.textContent = (n === 1 ? one : many).replace('%d', n); }
	}
	chips.forEach(function (c) {
		c.addEventListener('click', function (e) {
			e.preventDefault();
			show(c.getAttribute('data-bk-chip') || '', c.getAttribute('data-bk-name') || '');
		});
	});
})();
</script>

<?php
get_footer();
