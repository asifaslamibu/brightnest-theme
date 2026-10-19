<?php
/**
 * Template Name: Checkout (multi-step)
 *
 * URL: /checkout/?service=ID&step=1[&voucher=CODE]
 *
 * @package BrightNest
 */

get_header();

$bn_sid = isset( $_GET['service'] ) ? absint( $_GET['service'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$bn_ok  = $bn_sid && 'bn_service' === get_post_type( $bn_sid ) && 'publish' === get_post_status( $bn_sid );

if ( ! $bn_ok ) :
	// No service chosen: let the visitor pick one.
	$bn_all = get_posts( array( 'post_type' => 'bn_service', 'posts_per_page' => -1, 'fields' => 'ids', 'orderby' => 'menu_order', 'order' => 'ASC' ) );
	?>
	<section class="bn-page-hero">
		<div class="bn-container">
			<?php brightnest_breadcrumbs(); ?>
			<h1><?php esc_html_e( 'Choose a service to book', 'brightnest' ); ?></h1>
		</div>
	</section>
	<section class="bn-section bn-section-tight">
		<div class="bn-container"><div class="bn-grid">
			<?php foreach ( $bn_all as $bn_id ) { brightnest_service_card( $bn_id ); } ?>
		</div></div>
	</section>
	<?php
	get_footer();
	return;
endif;

$bn_groups  = brightnest_service_options( $bn_sid );
$bn_hourly  = brightnest_hourly_config( $bn_sid );
$bn_rating  = get_post_meta( $bn_sid, '_bn_rating', true );
$bn_reviews = get_post_meta( $bn_sid, '_bn_review_count', true );
$bn_cur     = brightnest_opt( 'currency' );
$bn_price   = function ( $n ) {
	return number_format_i18n( $n, floor( $n ) == $n ? 0 : 2 );
};
?>

<div class="bn-co" data-checkout>
	<?php $bn_has_search = ! $bn_hourly && count( brightnest_service_items( $bn_sid ) ) > 10; ?>
	<?php // Like Justlife: step line, title and rating sit above the cards. ?>
	<div class="bn-container bn-co-top">
		<div class="bn-co-stepline">
			<button type="button" class="bn-co-back" data-back aria-label="<?php esc_attr_e( 'Back', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 22 ); // phpcs:ignore ?></button>
			<span class="bn-co-stepnum"><?php esc_html_e( 'Step', 'brightnest' ); ?> <span data-step-num>1</span> <?php esc_html_e( 'of', 'brightnest' ); ?> 4</span>
			<span class="bn-co-steptitle" data-step-title></span>
		</div>
		<div class="bn-co-progress" aria-hidden="true" hidden><span data-progress></span></div>
		<div class="bn-co-titlerow">
			<h1 class="bn-co-title"><?php echo esc_html( get_the_title( $bn_sid ) ); ?></h1>
			<?php if ( $bn_has_search ) : ?>
				<button type="button" class="bn-co-searchbtn" data-search-open aria-label="<?php esc_attr_e( 'Search options', 'brightnest' ); ?>"><?php echo brightnest_icon( 'search', 20 ); // phpcs:ignore ?></button>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $bn_rating ) : ?>
			<p class="bn-co-rating"><?php echo brightnest_icon( 'star', 15 ); // phpcs:ignore ?> <strong><?php echo esc_html( number_format_i18n( (float) $bn_rating, 2 ) ); ?></strong>
				<?php if ( $bn_reviews ) : ?><span>(<?php echo esc_html( number_format_i18n( (int) $bn_reviews ) ); ?> <?php esc_html_e( 'bookings', 'brightnest' ); ?>)</span><?php endif; ?>
			</p>
		<?php endif; ?>
		<?php $bn_areas = brightnest_service_areas( $bn_sid ); ?>
		<?php // The area (and so the prices) comes from the customer's location. ?>
		<button type="button" class="bn-co-locpill" data-loc-open>
			<span class="bn-co-locpill-ic"><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?></span>
			<span class="bn-co-locpill-text">
				<strong data-loc-label data-empty="<?php esc_attr_e( 'Set your location to see prices', 'brightnest' ); ?>"><?php esc_html_e( 'Set your location to see prices', 'brightnest' ); ?></strong>
				<small>· <b data-co-area-name></b></small>
			</span>
			<span class="bn-co-locpill-change"><?php esc_html_e( 'Change', 'brightnest' ); ?></span>
		</button>
		<div class="bn-co-area" hidden>
			<label for="bn-co-area"><?php esc_html_e( 'Service area', 'brightnest' ); ?></label>
			<select id="bn-co-area" data-area>
				<?php foreach ( $bn_areas as $bn_city => $bn_ar ) : ?>
					<option value="<?php echo esc_attr( $bn_city ); ?>"><?php echo esc_html( brightnest_city_label( $bn_city ) . ( $bn_ar['on'] ? '' : ' (' . __( 'not available', 'brightnest' ) . ')' ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<p class="bn-co-unavail" data-area-off role="alert" hidden></p>
	</div>

	<div class="bn-container bn-co-grid">

		<div class="bn-co-main">

			<!-- STEP 1: options -->
			<section class="bn-co-step" data-step="1" aria-label="<?php esc_attr_e( 'Select options', 'brightnest' ); ?>">
				<?php $bn_freqs = brightnest_service_frequencies( $bn_sid ); ?>
				<?php if ( $bn_freqs ) : ?>
					<div class="bn-co-block">
						<h2 class="bn-co-q"><?php esc_html_e( 'How often do you need your professional?', 'brightnest' ); ?></h2>
						<div class="bn-co-seg" data-freq role="radiogroup" aria-label="<?php esc_attr_e( 'Frequency', 'brightnest' ); ?>">
							<?php foreach ( $bn_freqs as $bn_fi => $bn_fr ) : ?>
								<button type="button" class="bn-co-choice <?php echo 0 === $bn_fi ? 'is-active' : ''; ?>" data-value="<?php echo esc_attr( $bn_fr[0] ); ?>"><?php echo esc_html( $bn_fr[0] ); ?>
									<?php if ( $bn_fr[1] > 0 ) : ?><small class="bn-co-save"><?php echo esc_html( sprintf( /* translators: %s: percent */ __( 'Save %s%%', 'brightnest' ), $bn_price( $bn_fr[1] ) ) ); ?></small><?php endif; ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $bn_hourly ) : ?>
					<div class="bn-co-block">
						<h2 class="bn-co-q"><?php echo esc_html( $bn_hourly['q_hours'] ); ?></h2>
						<div class="bn-co-pills" data-hourly="hours">
							<?php foreach ( $bn_hourly['hours'] as $bn_h ) : ?><button type="button" class="bn-co-pill" data-value="<?php echo esc_attr( $bn_h ); ?>"><?php echo esc_html( $bn_h ); ?></button><?php endforeach; ?>
						</div>
					</div>
					<div class="bn-co-block">
						<h2 class="bn-co-q"><?php echo esc_html( $bn_hourly['q_pros'] ); ?></h2>
						<div class="bn-co-pills" data-hourly="pros">
							<?php foreach ( $bn_hourly['pros'] as $bn_p ) : ?><button type="button" class="bn-co-pill" data-value="<?php echo esc_attr( $bn_p ); ?>"><?php echo esc_html( $bn_p ); ?></button><?php endforeach; ?>
						</div>
					</div>
					<?php if ( $bn_hourly['mat_on'] ) : ?>
						<div class="bn-co-block">
							<h2 class="bn-co-q"><?php echo esc_html( $bn_hourly['q_mat'] ); ?></h2>
							<div class="bn-co-pills bn-co-pills-wide" data-hourly="materials">
								<button type="button" class="bn-co-pill" data-value="0"><?php esc_html_e( 'No, I have them', 'brightnest' ); ?></button>
								<button type="button" class="bn-co-pill" data-value="1"><?php esc_html_e( 'Yes, please', 'brightnest' ); ?>
									<?php if ( $bn_hourly['materials'] > 0 ) : ?><small>+<span data-money="<?php echo esc_attr( $bn_hourly['materials'] ); ?>" data-hourly-mat><?php echo esc_html( $bn_cur . ' ' . $bn_price( $bn_hourly['materials'] ) ); ?></span>/<?php esc_html_e( 'hr', 'brightnest' ); ?></small><?php endif; ?></button>
							</div>
						</div>
					<?php endif; ?>
					<?php $bn_guide = brightnest_service_guide( $bn_sid ); ?>
					<?php if ( $bn_guide ) : ?>
						<details class="bn-co-guide">
							<summary><?php esc_html_e( 'How long should I book for?', 'brightnest' ); ?></summary>
							<table>
								<thead><tr><th><?php esc_html_e( 'Home size', 'brightnest' ); ?></th><th><?php esc_html_e( 'Suggested duration', 'brightnest' ); ?></th></tr></thead>
								<tbody>
									<?php foreach ( $bn_guide as $bn_gr ) : ?><tr><td><?php echo esc_html( $bn_gr[0] ); ?></td><td><?php echo esc_html( $bn_gr[1] ); ?></td></tr><?php endforeach; ?>
								</tbody>
							</table>
						</details>
					<?php endif; ?>
				<?php else : ?>
				<?php if ( $bn_has_search ) : ?>
					<div class="bn-co-search" data-search-box hidden>
						<?php echo brightnest_icon( 'search', 18 ); // phpcs:ignore ?>
						<input type="search" data-opt-search placeholder="<?php esc_attr_e( 'Search options, e.g. manicure', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search options', 'brightnest' ); ?>">
					</div>
					<p class="bn-co-noresult" data-opt-none hidden><?php esc_html_e( 'No options match your search.', 'brightnest' ); ?></p>
				<?php endif; ?>
				<?php $bn_thumb = get_the_post_thumbnail_url( $bn_sid, 'medium' ); ?>
				<?php if ( count( $bn_groups ) > 1 ) : ?>
					<nav class="bn-co-tabs" data-tabs aria-label="<?php esc_attr_e( 'Categories', 'brightnest' ); ?>">
						<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
							<a href="#bn-group-<?php echo esc_attr( $bn_gi ); ?>" class="bn-co-tab <?php echo 0 === $bn_gi ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr( $bn_gi ); ?>">
								<?php $bn_timg = ! empty( $bn_g['img'] ) ? $bn_g['img'] : $bn_thumb; ?>
								<?php if ( $bn_timg ) : ?>
									<span class="bn-co-tab-icon"><img src="<?php echo esc_url( $bn_timg ); ?>" alt="" loading="lazy"></span>
								<?php else : ?>
									<span class="bn-co-tab-icon bn-tone-<?php echo esc_attr( brightnest_tone( $bn_g['name'] ) ); ?>"><?php echo brightnest_service_icon_html( $bn_sid, 26 ); // phpcs:ignore ?></span>
								<?php endif; ?>
								<span><?php echo esc_html( $bn_g['name'] ); ?></span>
							</a>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>

				<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
					<div class="bn-co-group" id="bn-group-<?php echo esc_attr( $bn_gi ); ?>" data-group="<?php echo esc_attr( $bn_gi ); ?>">
						<h2 class="bn-co-group-title"><?php echo esc_html( $bn_g['name'] ); ?></h2>
						<?php foreach ( $bn_g['items'] as $bn_item ) : ?>
							<div class="bn-co-item" data-item="<?php echo esc_attr( $bn_item['id'] ); ?>">
								<?php $bn_iimg = $bn_item['img'] ? $bn_item['img'] : $bn_thumb; ?>
								<?php if ( $bn_iimg ) : ?>
									<span class="bn-co-item-thumb"><img src="<?php echo esc_url( $bn_iimg ); ?>" alt="" loading="lazy"></span>
								<?php else : ?>
									<span class="bn-co-item-thumb bn-tone-<?php echo esc_attr( brightnest_tone( $bn_item['name'] ) ); ?>"><?php echo brightnest_service_icon_html( $bn_sid, 30 ); // phpcs:ignore ?></span>
								<?php endif; ?>
								<div class="bn-co-item-body">
									<h3><span class="bn-co-item-x" data-qty-x></span><?php echo esc_html( $bn_item['name'] ); ?></h3>
									<?php if ( $bn_item['desc'] ) : ?><p><?php echo esc_html( $bn_item['desc'] ); ?></p><?php endif; ?>
									<div class="bn-co-item-foot">
										<span class="bn-co-item-price">
											<strong data-money="<?php echo esc_attr( $bn_item['price'] ); ?>" data-key="<?php echo esc_attr( $bn_item['key'] ); ?>"><?php echo esc_html( $bn_cur . ' ' . $bn_price( $bn_item['price'] ) ); ?></strong>
											<?php if ( $bn_item['old'] ) : ?><del data-money="<?php echo esc_attr( $bn_item['old'] ); ?>" data-old-of="<?php echo esc_attr( $bn_item['key'] ); ?>"><?php echo esc_html( $bn_cur . ' ' . $bn_price( $bn_item['old'] ) ); ?></del><?php endif; ?>
										</span>
										<span class="bn-co-qty" data-qty-wrap>
											<button type="button" class="bn-co-add" data-add><?php esc_html_e( 'Add', 'brightnest' ); ?> <span aria-hidden="true">+</span></button>
											<span class="bn-co-stepper">
												<button type="button" data-dec aria-label="<?php esc_attr_e( 'Decrease', 'brightnest' ); ?>">&minus;</button>
												<output data-qty>0</output>
												<button type="button" data-inc aria-label="<?php esc_attr_e( 'Increase', 'brightnest' ); ?>">+</button>
											</span>
										</span>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Any instructions or special requirements?', 'brightnest' ); ?></h2>
					<textarea id="bn-co-notes" class="bn-co-input" rows="3" data-field="message" placeholder="<?php esc_attr_e( 'Example: key under the mat, ironing, cleaning fridge, etc.', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Instructions', 'brightnest' ); ?>"></textarea>
				</div>
			</section>

			<!-- STEP 2: date & time -->
			<section class="bn-co-step" data-step="2" hidden aria-label="<?php esc_attr_e( 'Date and time', 'brightnest' ); ?>">
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'When would you like your service?', 'brightnest' ); ?></h2>
					<div class="bn-co-days" data-days></div>
				</div>
				<div class="bn-co-block" data-times-block>
					<h2 class="bn-co-q"><?php esc_html_e( 'What time would you like us to arrive?', 'brightnest' ); ?></h2>
					<div data-times></div>
					<p class="bn-co-err" data-err="time" role="alert"></p>
				</div>
			</section>

			<!-- STEP 3: address -->
			<section class="bn-co-step" data-step="3" hidden aria-label="<?php esc_attr_e( 'Address', 'brightnest' ); ?>">
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Where do you need the service?', 'brightnest' ); ?></h2>
					<button type="button" class="bn-co-pin" data-open-map>
						<?php echo brightnest_icon( 'pin', 18 ); // phpcs:ignore ?>
						<span data-addr-label><?php esc_html_e( 'Pin your location on the map', 'brightnest' ); ?></span>
					</button>
				</div>
				<div class="bn-co-block">
					<?php $bn_cities = brightnest_cities(); ?>
					<?php if ( count( $bn_cities ) > 1 ) : ?>
						<h3 class="bn-co-label"><?php esc_html_e( 'City', 'brightnest' ); ?></h3>
						<div class="bn-co-seg bn-co-cities" data-cities role="radiogroup" aria-label="<?php esc_attr_e( 'City', 'brightnest' ); ?>">
							<?php foreach ( $bn_cities as $bn_city ) : ?>
								<button type="button" class="bn-co-choice" data-value="<?php echo esc_attr( $bn_city ); ?>"><?php echo esc_html( brightnest_city_label( $bn_city ) ); ?></button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<div class="bn-field" data-wrap="street">
						<label for="bn-co-street"><?php esc_html_e( 'Area / Street', 'brightnest' ); ?></label>
						<input id="bn-co-street" class="bn-co-input" data-field="street" autocomplete="address-line1" placeholder="<?php esc_attr_e( 'e.g. Dubai Marina, Al Marsa Street', 'brightnest' ); ?>">
						<p class="bn-co-err" data-err="street"></p>
					</div>
					<div class="bn-co-fields">
						<div class="bn-field" data-wrap="building">
							<label for="bn-co-building"><?php esc_html_e( 'Building / Villa name', 'brightnest' ); ?></label>
							<input id="bn-co-building" class="bn-co-input" data-field="building" autocomplete="address-line2" placeholder="<?php esc_attr_e( 'e.g. Marina Heights', 'brightnest' ); ?>">
							<p class="bn-co-err" data-err="building"></p>
						</div>
						<div class="bn-field" data-wrap="apartment">
							<label for="bn-co-apartment"><?php esc_html_e( 'Apartment / Villa no.', 'brightnest' ); ?></label>
							<input id="bn-co-apartment" class="bn-co-input" data-field="apartment" placeholder="<?php esc_attr_e( 'e.g. 1204', 'brightnest' ); ?>">
							<p class="bn-co-err" data-err="apartment"></p>
						</div>
					</div>
				</div>
			</section>

			<!-- STEP 4: payment -->
			<section class="bn-co-step" data-step="4" hidden aria-label="<?php esc_attr_e( 'Payment', 'brightnest' ); ?>">
				<div class="bn-co-recap" data-recap></div>

				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Your contact details', 'brightnest' ); ?></h2>
					<div class="bn-co-fields">
						<div class="bn-field" data-wrap="name">
							<label for="bn-co-name"><?php esc_html_e( 'Full name', 'brightnest' ); ?></label>
							<input id="bn-co-name" class="bn-co-input" data-field="name" autocomplete="name">
							<p class="bn-co-err" data-err="name"></p>
						</div>
						<div class="bn-field" data-wrap="phone">
							<label for="bn-co-phone"><?php esc_html_e( 'Mobile number', 'brightnest' ); ?></label>
							<input id="bn-co-phone" type="tel" inputmode="tel" class="bn-co-input" data-field="phone" autocomplete="tel" placeholder="+971 5X XXX XXXX">
							<p class="bn-co-err" data-err="phone"></p>
						</div>
					</div>
					<div class="bn-field" data-wrap="email">
						<label for="bn-co-email"><?php esc_html_e( 'Email (for your receipt)', 'brightnest' ); ?></label>
						<input id="bn-co-email" type="email" class="bn-co-input" data-field="email" autocomplete="email" placeholder="you@example.com">
						<p class="bn-co-err" data-err="email"></p>
						<?php if ( '1' === (string) brightnest_opt( 'remind_on' ) ) : ?>
							<p class="bn-co-hint"><?php esc_html_e( 'We send your receipt here. If you leave before paying, we will send one reminder with a link back to your booking.', 'brightnest' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Payment method', 'brightnest' ); ?></h2>
					<div class="bn-co-pay" data-pay>
						<?php $bn_pi = 0; ?>
						<?php foreach ( brightnest_payment_methods() as $bn_pv => $bn_pm ) : ?>
							<label class="bn-co-radio"><input type="radio" name="bn_pay" value="<?php echo esc_attr( $bn_pv ); ?>" <?php checked( 0, $bn_pi ); ?>> <span><strong><?php echo esc_html( $bn_pm[0] ); ?></strong><small><?php echo esc_html( $bn_pm[1] ); ?></small></span></label>
							<?php $bn_pi++; ?>
						<?php endforeach; ?>
					</div>
					<details class="bn-co-more" data-voucher-box>
						<summary><?php esc_html_e( 'Have a voucher code?', 'brightnest' ); ?></summary>
						<div class="bn-co-voucher">
							<input class="bn-co-input" data-voucher-input placeholder="<?php esc_attr_e( 'Enter code', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Voucher code', 'brightnest' ); ?>">
							<button type="button" class="bn-btn bn-btn-ghost" data-voucher-apply><?php esc_html_e( 'Apply', 'brightnest' ); ?></button>
						</div>
						<p class="bn-co-voucher-msg" data-voucher-msg role="status"></p>
					</details>
				</div>

				<?php $bn_terms = get_page_by_path( 'terms' ); ?>
				<p class="bn-co-agree"><?php echo brightnest_icon( 'shield', 16 ); // phpcs:ignore ?>
					<span><?php echo esc_html( brightnest_opt( 'booking_note' ) ); ?>
					<?php if ( $bn_terms ) : ?>
						<?php esc_html_e( 'By booking you agree to our', 'brightnest' ); ?> <a href="<?php echo esc_url( get_permalink( $bn_terms ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'terms', 'brightnest' ); ?></a>.
					<?php endif; ?></span>
				</p>
			</section>

			<!-- DONE -->
			<section class="bn-co-step bn-co-done" data-step="done" hidden>
				<span class="bn-co-done-icon"><?php echo brightnest_icon( 'check', 40 ); // phpcs:ignore ?></span>
				<h2><?php esc_html_e( 'Booking received!', 'brightnest' ); ?></h2>
				<p><?php esc_html_e( 'Your booking reference is', 'brightnest' ); ?> <strong data-ref></strong></p>
				<dl class="bn-co-done-sum" data-done-sum></dl>
				<p class="bn-muted"><?php esc_html_e( 'Our team will call or WhatsApp you shortly to confirm.', 'brightnest' ); ?></p>
				<div class="bn-404-actions">
					<a class="bn-btn bn-btn-wa" data-done-wa hidden target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Send on WhatsApp', 'brightnest' ); ?></a>
					<a class="bn-btn bn-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'brightnest' ); ?></a>
					<a class="bn-btn bn-btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book another service', 'brightnest' ); ?></a>
				</div>
			</section>

			<div class="bn-co-bar" data-bar>
				<div class="bn-co-voucher-hint" data-voucher-hint hidden>
					<?php echo brightnest_icon( 'tag', 20 ); // phpcs:ignore ?>
					<span><strong data-voucher-code></strong> <?php esc_html_e( 'voucher code is ready, please select one or more options from the list to get this voucher code applied!', 'brightnest' ); ?></span>
				</div>
				<p class="bn-co-error" data-error role="alert"></p>
				<div class="bn-co-bar-row">
					<button type="button" class="bn-co-total-toggle" data-summary-toggle>
						<small data-count-mini><?php esc_html_e( 'Total', 'brightnest' ); ?></small>
						<strong data-total-mini><?php echo esc_html( $bn_cur ); ?> 0</strong>
					</button>
					<button type="button" class="bn-btn bn-btn-primary bn-co-next" data-next><?php esc_html_e( 'Continue', 'brightnest' ); ?></button>
				</div>
			</div>
		</div>

		<aside class="bn-co-side" data-side>
			<div class="bn-co-card">
				<h2 class="bn-co-card-title"><?php esc_html_e( 'Booking Details', 'brightnest' ); ?></h2>
				<dl class="bn-co-dl">
					<dt data-sum-row="addr" hidden><?php esc_html_e( 'Address', 'brightnest' ); ?></dt><dd data-sum="addr" hidden></dd>
					<?php if ( $bn_freqs ) : ?>
						<dt><?php esc_html_e( 'Frequency', 'brightnest' ); ?></dt><dd data-sum="freq"><?php echo esc_html( $bn_freqs[0][0] ); ?></dd>
					<?php endif; ?>
					<dt><?php esc_html_e( 'Service', 'brightnest' ); ?></dt><dd><?php echo esc_html( get_the_title( $bn_sid ) ); ?></dd>
					<dt data-sum-row="date" hidden><?php esc_html_e( 'Date & Time', 'brightnest' ); ?></dt><dd data-sum="date" hidden></dd>
					<dt data-lines hidden><?php esc_html_e( 'Service Details', 'brightnest' ); ?></dt><dd data-lines hidden><ul data-lines-list></ul></dd>
				</dl>
			</div>
			<div class="bn-co-card">
				<h2 class="bn-co-card-title"><?php esc_html_e( 'Payment Summary', 'brightnest' ); ?></h2>
				<dl class="bn-co-dl bn-co-money">
					<dt><?php esc_html_e( 'Subtotal', 'brightnest' ); ?></dt><dd data-sum="sub"><?php echo esc_html( $bn_cur ); ?> 0.00</dd>
					<dt data-sum-row="fdisc" hidden><?php esc_html_e( 'Frequency discount', 'brightnest' ); ?></dt><dd data-sum="fdisc" hidden class="bn-co-green"></dd>
					<dt data-sum-row="disc" hidden><?php esc_html_e( 'Discount', 'brightnest' ); ?> <span data-sum="code"></span></dt><dd data-sum="disc" hidden class="bn-co-green"></dd>
					<dt data-sum-row="afee" hidden><?php echo esc_html( brightnest_opt( 'area_fee_label' ) ); ?> <span data-sum="aname"></span></dt><dd data-sum="afee" hidden></dd>
					<?php $bn_fee = brightnest_service_fee(); ?>
					<dt><?php echo esc_html( brightnest_opt( 'service_fee_label' ) ); ?></dt>
					<dd class="<?php echo $bn_fee ? '' : 'bn-co-green'; ?>"><?php echo $bn_fee ? esc_html( $bn_cur . ' ' . number_format_i18n( $bn_fee, 2 ) ) : esc_html__( 'Free', 'brightnest' ); ?></dd>
					<dt class="bn-co-total"><?php esc_html_e( 'Total', 'brightnest' ); ?></dt><dd class="bn-co-total" data-sum="total"><?php echo esc_html( $bn_cur ); ?> 0.00</dd>
				</dl>
			</div>
			<?php $bn_why = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) brightnest_opt( 'checkout_why' ) ) ) ); ?>
			<?php if ( $bn_why ) : ?>
				<div class="bn-co-card bn-co-why">
					<h2 class="bn-co-card-title"><?php echo esc_html( sprintf( /* translators: %s site */ __( 'Why choose %s?', 'brightnest' ), get_bloginfo( 'name' ) ) ); ?></h2>
					<ul>
						<?php foreach ( $bn_why as $bn_wi => $bn_w ) : ?>
							<li><?php echo brightnest_icon( array( 'shield', 'tag', 'clock', 'headset', 'check' )[ min( $bn_wi, 4 ) ], 18 ); // phpcs:ignore ?> <?php echo esc_html( $bn_w ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</aside>
	</div>

	<!-- Option details popup (Justlife style): opens when an option is clicked -->
	<div class="bn-modal bn-od" data-od hidden role="dialog" aria-modal="true" aria-labelledby="bn-od-title">
		<div class="bn-modal-box bn-od-box">
			<div class="bn-modal-head">
				<h2 id="bn-od-title" data-od-title></h2>
				<button type="button" class="bn-modal-close" data-od-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 22 ); // phpcs:ignore ?></button>
			</div>
			<div class="bn-od-body">
				<div class="bn-od-media" data-od-media>
					<div class="bn-od-cap"><strong data-od-name></strong><span data-od-desc></span></div>
				</div>
				<div class="bn-od-info" data-od-info>
					<h3><?php esc_html_e( 'Highlights', 'brightnest' ); ?></h3>
					<p data-od-about></p>
					<ul class="bn-od-hl" data-od-hl></ul>
				</div>
			</div>
			<div class="bn-od-foot">
				<div class="bn-od-qty">
					<button type="button" data-od-dec aria-label="<?php esc_attr_e( 'Decrease', 'brightnest' ); ?>">&minus;</button>
					<output data-od-qty>1</output>
					<button type="button" data-od-inc aria-label="<?php esc_attr_e( 'Increase', 'brightnest' ); ?>">+</button>
				</div>
				<button type="button" class="bn-btn bn-btn-primary bn-od-add" data-od-add></button>
			</div>
		</div>
	</div>

	<!-- Address picker modal -->
	<div class="bn-modal" data-map-modal hidden role="dialog" aria-modal="true" aria-labelledby="bn-map-title">
		<div class="bn-modal-box">
			<div class="bn-modal-head">
				<h2 id="bn-map-title"><?php esc_html_e( 'Select Address', 'brightnest' ); ?></h2>
				<button type="button" class="bn-modal-close" data-map-close aria-label="<?php esc_attr_e( 'Close', 'brightnest' ); ?>"><?php echo brightnest_icon( 'close', 22 ); // phpcs:ignore ?></button>
			</div>
			<div class="bn-map-wrap">
				<form class="bn-map-search" data-map-search>
					<?php echo brightnest_icon( 'pin', 18 ); // phpcs:ignore ?>
					<input type="search" data-map-query placeholder="<?php esc_attr_e( 'Search area, street or building', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search location', 'brightnest' ); ?>">
					<button type="button" class="bn-map-locate" data-map-locate aria-label="<?php esc_attr_e( 'Use my current location', 'brightnest' ); ?>"><?php echo brightnest_icon( 'pin', 16 ); // phpcs:ignore ?> <span class="bn-map-locate-text"><?php esc_html_e( 'Locate me', 'brightnest' ); ?></span></button>
				</form>
				<ul class="bn-map-results" data-map-results hidden></ul>
				<div class="bn-map" data-map></div>
				<div class="bn-map-pin" aria-hidden="true">
					<span class="bn-map-tip"><?php esc_html_e( 'Move the map to set the exact position', 'brightnest' ); ?></span>
					<svg width="40" height="48" viewBox="0 0 24 30"><path d="M12 0C5.4 0 0 5.3 0 11.9 0 20.8 12 30 12 30s12-9.2 12-18.1C24 5.3 18.6 0 12 0z" fill="#e5383b"/><circle cx="12" cy="11.5" r="4.5" fill="#fff"/></svg>
				</div>
				<button type="button" class="bn-btn bn-btn-primary bn-map-confirm" data-map-confirm><?php esc_html_e( 'Confirm Pin Location', 'brightnest' ); ?></button>
			</div>
		</div>
	</div>
</div>

<?php

get_footer();
