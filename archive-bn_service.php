<?php
/**
 * Services hub (Justlife style): location + search bar, category strip,
 * one horizontal row of service cards per category. Cards open the booking directly.
 * Also used for service category pages and service search results (as a grid).
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

$bn_services_of = function ( $term_id ) {
	return get_posts(
		array(
			'post_type'      => 'bn_service',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'tax_query'      => array( array( 'taxonomy' => 'bn_service_cat', 'terms' => $term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
};
$bn_card = function ( $id ) {
	?>
	<a class="bn-hub-card bn-carousel-item" href="<?php echo esc_url( brightnest_checkout_url( $id ) ); ?>" data-hub-card data-name="<?php echo esc_attr( strtolower( wp_strip_all_tags( html_entity_decode( get_the_title( $id ) ) . ' ' . get_the_excerpt( $id ) ) ) ); ?>">
		<span class="bn-hub-img">
			<?php if ( has_post_thumbnail( $id ) ) : ?>
				<?php echo get_the_post_thumbnail( $id, 'brightnest-card', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
			<?php else : ?>
				<span class="bn-hub-icon bn-tone-<?php echo esc_attr( brightnest_tone( $id ) ); ?>"><?php echo brightnest_icon( brightnest_service_icon( $id ), 52 ); // phpcs:ignore ?></span>
			<?php endif; ?>
			<?php if ( '1' === get_post_meta( $id, '_bn_featured', true ) ) : ?><span class="bn-hub-badge"><?php esc_html_e( 'Popular', 'brightnest' ); ?></span><?php endif; ?>
		</span>
		<span class="bn-hub-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
	</a>
	<?php
};
$bn_first = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => 1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) );
?>

<div class="bn-hub" data-hub>
	<div class="bn-hub-bar">
		<div class="bn-container bn-hub-bar-inner">
			<button type="button" class="bn-hub-loc" data-loc-open>
				<?php echo brightnest_icon( 'pin', 18 ); // phpcs:ignore ?>
				<span data-loc-label data-empty="<?php esc_attr_e( 'Set your location', 'brightnest' ); ?>"><?php esc_html_e( 'Set your location', 'brightnest' ); ?></span>
				<svg class="bn-hub-gps" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
			</button>
			<form class="bn-hub-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="bn_service">
				<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
				<label class="screen-reader-text" for="bn-hub-q"><?php esc_html_e( 'Search services', 'brightnest' ); ?></label>
				<input id="bn-hub-q" type="search" name="s" value="<?php echo esc_attr( $bn_search ); ?>" autocomplete="off" data-hub-search
					placeholder="<?php echo esc_attr( sprintf( /* translators: %s service name */ __( 'Search for "%s"', 'brightnest' ), $bn_first ? wp_strip_all_tags( html_entity_decode( get_the_title( $bn_first[0] ) ) ) : __( 'a service', 'brightnest' ) ) ); ?>">
			</form>
		</div>
	</div>

	<div class="bn-container">
		<?php if ( $bn_cats ) : ?>
			<nav class="bn-hub-cats" data-carousel aria-label="<?php esc_attr_e( 'Service categories', 'brightnest' ); ?>">
				<button type="button" class="bn-hub-cats-arrow is-prev" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 20 ); // phpcs:ignore ?></button>
				<ul class="bn-hub-cats-track" data-track>
					<?php foreach ( $bn_cats as $bn_t ) : ?>
						<?php $bn_url = 'hub' === $bn_mode ? '#cat-' . $bn_t->slug : get_term_link( $bn_t ); ?>
						<li class="bn-carousel-item">
							<a class="<?php echo ( $bn_current && $bn_current->term_id === $bn_t->term_id ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( $bn_url ); ?>">
								<span class="bn-hub-cat-ic bn-tone-<?php echo esc_attr( brightnest_tone( $bn_t->slug ) ); ?>"><?php echo brightnest_icon( get_term_meta( $bn_t->term_id, 'bn_icon', true ) ?: 'home', 34 ); // phpcs:ignore ?></span>
								<span class="bn-hub-cat-name"><?php echo esc_html( $bn_t->name ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<button type="button" class="bn-hub-cats-arrow is-next" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 20 ); // phpcs:ignore ?></button>
			</nav>
		<?php endif; ?>

		<?php if ( 'hub' === $bn_mode ) : ?>
			<h1 class="screen-reader-text"><?php esc_html_e( 'All services', 'brightnest' ); ?></h1>
			<?php foreach ( $bn_cats as $bn_t ) : ?>
				<?php $bn_ids = $bn_services_of( $bn_t->term_id ); ?>
				<?php if ( ! $bn_ids ) { continue; } ?>
				<section class="bn-hub-row" id="cat-<?php echo esc_attr( $bn_t->slug ); ?>" data-carousel data-hub-row>
					<div class="bn-hub-row-head">
						<h2><?php echo esc_html( $bn_t->name ); ?></h2>
						<div class="bn-hub-row-nav">
							<a class="bn-hub-seeall" href="<?php echo esc_url( get_term_link( $bn_t ) ); ?>"><?php esc_html_e( 'See all', 'brightnest' ); ?></a>
							<button type="button" class="bn-hub-arrow" data-prev aria-label="<?php esc_attr_e( 'Previous', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 16 ); // phpcs:ignore ?></button>
							<button type="button" class="bn-hub-arrow" data-next aria-label="<?php esc_attr_e( 'Next', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-right', 16 ); // phpcs:ignore ?></button>
						</div>
					</div>
					<div class="bn-hub-track" data-track>
						<?php foreach ( $bn_ids as $bn_id ) { $bn_card( $bn_id ); } ?>
					</div>
				</section>
			<?php endforeach; ?>
			<p class="bn-hub-empty" data-hub-empty hidden><?php esc_html_e( 'No services match your search.', 'brightnest' ); ?></p>

		<?php else : ?>
			<section class="bn-hub-list">
				<div class="bn-hub-row-head">
					<h1>
						<?php
						if ( 'search' === $bn_mode ) {
							/* translators: %s search term */
							echo esc_html( sprintf( __( 'Results for “%s”', 'brightnest' ), $bn_search ) );
						} else {
							echo esc_html( $bn_current->name );
						}
						?>
					</h1>
					<a class="bn-hub-seeall" href="<?php echo esc_url( $bn_hub ); ?>"><?php esc_html_e( 'All services', 'brightnest' ); ?></a>
				</div>
				<?php $bn_ids = 'category' === $bn_mode ? $bn_services_of( $bn_current->term_id ) : wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ); ?>
				<?php if ( $bn_ids ) : ?>
					<div class="bn-hub-grid"><?php foreach ( $bn_ids as $bn_id ) { $bn_card( $bn_id ); } ?></div>
				<?php else : ?>
					<div class="bn-hub-none">
						<p><?php esc_html_e( 'No services found. Try another word, or message us — we will help.', 'brightnest' ); ?></p>
						<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( $bn_hub ); ?>"><?php esc_html_e( 'Browse all services', 'brightnest' ); ?></a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</div>

<?php get_template_part( 'template-parts/location-modal' ); ?>

<?php
get_footer();
