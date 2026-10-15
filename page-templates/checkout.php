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
$bn_rating  = get_post_meta( $bn_sid, '_bn_rating', true );
$bn_reviews = get_post_meta( $bn_sid, '_bn_review_count', true );
$bn_cur     = brightnest_opt( 'currency' );
$bn_price   = function ( $n ) {
	return number_format_i18n( $n, floor( $n ) == $n ? 0 : 2 );
};
?>

<div class="bn-co" data-checkout>
	<div class="bn-container bn-co-grid">

		<div class="bn-co-main">
			<div class="bn-co-head">
				<div class="bn-co-stepline">
					<button type="button" class="bn-co-back" data-back aria-label="<?php esc_attr_e( 'Back', 'brightnest' ); ?>"><?php echo brightnest_icon( 'arrow-left', 22 ); // phpcs:ignore ?></button>
					<span class="bn-co-stepnum"><?php esc_html_e( 'Step', 'brightnest' ); ?> <span data-step-num>1</span> <?php esc_html_e( 'of', 'brightnest' ); ?> 4</span>
					<span class="bn-co-steptitle" data-step-title></span>
				</div>
				<div class="bn-co-progress" aria-hidden="true"><span data-progress></span></div>
				<h1 class="bn-co-title"><?php echo esc_html( get_the_title( $bn_sid ) ); ?></h1>
				<?php if ( '' !== $bn_rating ) : ?>
					<p class="bn-co-rating"><?php echo brightnest_icon( 'star', 15 ); // phpcs:ignore ?> <strong><?php echo esc_html( number_format_i18n( (float) $bn_rating, 2 ) ); ?></strong>
						<?php if ( $bn_reviews ) : ?><span class="bn-muted">(<?php echo esc_html( number_format_i18n( (int) $bn_reviews ) ); ?> <?php esc_html_e( 'bookings', 'brightnest' ); ?>)</span><?php endif; ?>
					</p>
				<?php endif; ?>
			</div>

			<!-- STEP 1: options -->
			<section class="bn-co-step" data-step="1" aria-label="<?php esc_attr_e( 'Select options', 'brightnest' ); ?>">
				<?php if ( count( $bn_groups ) > 1 ) : ?>
					<nav class="bn-co-tabs" data-tabs aria-label="<?php esc_attr_e( 'Categories', 'brightnest' ); ?>">
						<?php foreach ( $bn_groups as $bn_gi => $bn_g ) : ?>
							<a href="#bn-group-<?php echo esc_attr( $bn_gi ); ?>" class="bn-co-tab <?php echo 0 === $bn_gi ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr( $bn_gi ); ?>">
								<span class="bn-co-tab-icon bn-tone-<?php echo esc_attr( brightnest_tone( $bn_g['name'] ) ); ?>"><?php echo brightnest_icon( brightnest_service_icon( $bn_sid ), 26 ); // phpcs:ignore ?></span>
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
								<span class="bn-co-item-thumb bn-tone-<?php echo esc_attr( brightnest_tone( $bn_item['name'] ) ); ?>"><?php echo brightnest_icon( brightnest_service_icon( $bn_sid ), 30 ); // phpcs:ignore ?></span>
								<div class="bn-co-item-body">
									<h3><?php echo esc_html( $bn_item['name'] ); ?></h3>
									<?php if ( $bn_item['desc'] ) : ?><p><?php echo esc_html( $bn_item['desc'] ); ?></p><?php endif; ?>
									<div class="bn-co-item-foot">
										<span class="bn-co-item-price">
											<strong><?php echo esc_html( $bn_cur . ' ' . $bn_price( $bn_item['price'] ) ); ?></strong>
											<?php if ( $bn_item['old'] ) : ?><del><?php echo esc_html( $bn_cur . ' ' . $bn_price( $bn_item['old'] ) ); ?></del><?php endif; ?>
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
			</section>

			<!-- STEP 2: date & time -->
			<section class="bn-co-step" data-step="2" hidden aria-label="<?php esc_attr_e( 'Date and time', 'brightnest' ); ?>">
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'How often do you need this service?', 'brightnest' ); ?></h2>
					<div class="bn-co-choices" data-freq>
						<button type="button" class="bn-co-choice is-active" data-value="One Time"><strong><?php esc_html_e( 'One Time', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Book a single visit', 'brightnest' ); ?></small></button>
						<button type="button" class="bn-co-choice" data-value="Weekly"><strong><?php esc_html_e( 'Weekly', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Same pro every week', 'brightnest' ); ?></small></button>
						<button type="button" class="bn-co-choice" data-value="Every 2 Weeks"><strong><?php esc_html_e( 'Every 2 Weeks', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Twice a month', 'brightnest' ); ?></small></button>
					</div>
				</div>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'When would you like your service?', 'brightnest' ); ?></h2>
					<div class="bn-co-days" data-days></div>
				</div>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'What time would you like us to arrive?', 'brightnest' ); ?></h2>
					<div class="bn-co-times" data-times></div>
				</div>
				<div class="bn-co-block">
					<label class="bn-co-q" for="bn-co-notes"><?php esc_html_e( 'Any instructions or special requirements?', 'brightnest' ); ?></label>
					<textarea id="bn-co-notes" class="bn-co-input" rows="3" data-field="message" placeholder="<?php esc_attr_e( 'E.g. key under the mat, please call on arrival', 'brightnest' ); ?>"></textarea>
				</div>
			</section>

			<!-- STEP 3: address & contact -->
			<section class="bn-co-step" data-step="3" hidden aria-label="<?php esc_attr_e( 'Address and contact', 'brightnest' ); ?>">
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Where do you need the service?', 'brightnest' ); ?></h2>
					<button type="button" class="bn-co-addr-card" data-open-map>
						<span class="bn-feature-icon"><?php echo brightnest_icon( 'pin', 22 ); // phpcs:ignore ?></span>
						<span><strong data-addr-label><?php esc_html_e( 'Pin your location on the map', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Tap to change location', 'brightnest' ); ?></small></span>
						<?php echo brightnest_icon( 'arrow-right', 18 ); // phpcs:ignore ?>
					</button>
					<div class="bn-co-fields">
						<div class="bn-field">
							<label for="bn-co-city"><?php esc_html_e( 'City', 'brightnest' ); ?> *</label>
							<select id="bn-co-city" class="bn-co-input" data-field="area">
								<?php foreach ( brightnest_cities() as $bn_city ) : ?><option><?php echo esc_html( $bn_city ); ?></option><?php endforeach; ?>
							</select>
						</div>
						<div class="bn-field">
							<label for="bn-co-street"><?php esc_html_e( 'Area / Street', 'brightnest' ); ?> *</label>
							<input id="bn-co-street" class="bn-co-input" data-field="street" autocomplete="address-line1">
						</div>
						<div class="bn-field">
							<label for="bn-co-building"><?php esc_html_e( 'Building / Villa name', 'brightnest' ); ?> *</label>
							<input id="bn-co-building" class="bn-co-input" data-field="building">
						</div>
						<div class="bn-field">
							<label for="bn-co-apt"><?php esc_html_e( 'Apartment / Villa no.', 'brightnest' ); ?></label>
							<input id="bn-co-apt" class="bn-co-input" data-field="apartment" autocomplete="address-line2">
						</div>
					</div>
				</div>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Your contact details', 'brightnest' ); ?></h2>
					<div class="bn-co-fields">
						<div class="bn-field">
							<label for="bn-co-name"><?php esc_html_e( 'Full name', 'brightnest' ); ?> *</label>
							<input id="bn-co-name" class="bn-co-input" data-field="name" autocomplete="name">
						</div>
						<div class="bn-field">
							<label for="bn-co-phone"><?php esc_html_e( 'Mobile number', 'brightnest' ); ?> *</label>
							<input id="bn-co-phone" type="tel" class="bn-co-input" data-field="phone" autocomplete="tel" placeholder="+971 5X XXX XXXX">
						</div>
						<div class="bn-field bn-co-span2">
							<label for="bn-co-email"><?php esc_html_e( 'Email (for booking confirmation)', 'brightnest' ); ?></label>
							<input id="bn-co-email" type="email" class="bn-co-input" data-field="email" autocomplete="email">
						</div>
					</div>
				</div>
			</section>

			<!-- STEP 4: review & payment -->
			<section class="bn-co-step" data-step="4" hidden aria-label="<?php esc_attr_e( 'Review and pay', 'brightnest' ); ?>">
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Review your booking', 'brightnest' ); ?></h2>
					<dl class="bn-co-review" data-review></dl>
				</div>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Payment method', 'brightnest' ); ?></h2>
					<div class="bn-co-pay" data-pay>
						<label class="bn-co-radio"><input type="radio" name="bn_pay" value="Cash after service" checked> <span><strong><?php esc_html_e( 'Cash after service', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Pay the professional once the job is done', 'brightnest' ); ?></small></span></label>
						<label class="bn-co-radio"><input type="radio" name="bn_pay" value="Card on arrival"> <span><strong><?php esc_html_e( 'Card on arrival', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Debit / credit card via payment link or machine', 'brightnest' ); ?></small></span></label>
					</div>
				</div>
				<div class="bn-co-block">
					<h2 class="bn-co-q"><?php esc_html_e( 'Voucher code', 'brightnest' ); ?></h2>
					<div class="bn-co-voucher">
						<input class="bn-co-input" data-voucher-input placeholder="<?php esc_attr_e( 'Enter code', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Voucher code', 'brightnest' ); ?>">
						<button type="button" class="bn-btn bn-btn-ghost" data-voucher-apply><?php esc_html_e( 'Apply', 'brightnest' ); ?></button>
					</div>
					<p class="bn-co-voucher-msg" data-voucher-msg role="status"></p>
				</div>
				<label class="bn-co-terms"><input type="checkbox" data-terms> <span><?php esc_html_e( 'I agree to the terms & conditions and cancellation policy.', 'brightnest' ); ?></span></label>
			</section>

			<!-- DONE -->
			<section class="bn-co-step bn-co-done" data-step="done" hidden>
				<span class="bn-co-done-icon"><?php echo brightnest_icon( 'check', 40 ); // phpcs:ignore ?></span>
				<h2><?php esc_html_e( 'Booking received!', 'brightnest' ); ?></h2>
				<p><?php esc_html_e( 'Your booking reference is', 'brightnest' ); ?> <strong data-ref></strong>.</p>
				<p class="bn-muted"><?php esc_html_e( 'Our team will call or WhatsApp you shortly to confirm the details.', 'brightnest' ); ?></p>
				<div class="bn-404-actions">
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
						<small><?php esc_html_e( 'Total', 'brightnest' ); ?></small>
						<strong data-total-mini><?php echo esc_html( $bn_cur ); ?> 0</strong>
					</button>
					<button type="button" class="bn-btn bn-btn-primary bn-co-next" data-next disabled><?php esc_html_e( 'Next', 'brightnest' ); ?></button>
				</div>
			</div>
		</div>

		<aside class="bn-co-side" data-side>
			<div class="bn-co-card">
				<h2 class="bn-co-card-title"><?php esc_html_e( 'Booking Details', 'brightnest' ); ?></h2>
				<dl class="bn-co-dl">
					<dt><?php esc_html_e( 'Frequency', 'brightnest' ); ?></dt><dd data-sum="freq"><?php esc_html_e( 'One Time', 'brightnest' ); ?></dd>
					<dt><?php esc_html_e( 'Service', 'brightnest' ); ?></dt><dd><?php echo esc_html( get_the_title( $bn_sid ) ); ?></dd>
					<dt data-sum-row="date" hidden><?php esc_html_e( 'Date & Time', 'brightnest' ); ?></dt><dd data-sum="date" hidden></dd>
					<dt data-sum-row="addr" hidden><?php esc_html_e( 'Address', 'brightnest' ); ?></dt><dd data-sum="addr" hidden></dd>
				</dl>
				<div class="bn-co-lines" data-lines hidden>
					<h3><?php esc_html_e( 'Service Details', 'brightnest' ); ?></h3>
					<ul data-lines-list></ul>
				</div>
			</div>
			<div class="bn-co-card">
				<h2 class="bn-co-card-title"><?php esc_html_e( 'Payment Summary', 'brightnest' ); ?></h2>
				<dl class="bn-co-dl bn-co-money">
					<dt><?php esc_html_e( 'Subtotal', 'brightnest' ); ?></dt><dd data-sum="sub"><?php echo esc_html( $bn_cur ); ?> 0.00</dd>
					<dt data-sum-row="disc" hidden><?php esc_html_e( 'Discount', 'brightnest' ); ?> <span data-sum="code"></span></dt><dd data-sum="disc" hidden class="bn-co-green"></dd>
					<dt><?php esc_html_e( 'Service Fee', 'brightnest' ); ?></dt><dd class="bn-co-green"><?php esc_html_e( 'Free', 'brightnest' ); ?></dd>
					<dt class="bn-co-total"><?php esc_html_e( 'Total', 'brightnest' ); ?></dt><dd class="bn-co-total" data-sum="total"><?php echo esc_html( $bn_cur ); ?> 0.00</dd>
				</dl>
			</div>
			<div class="bn-co-card bn-co-why">
				<h2 class="bn-co-card-title"><?php echo esc_html( sprintf( /* translators: %s site */ __( 'Why choose %s?', 'brightnest' ), get_bloginfo( 'name' ) ) ); ?></h2>
				<ul>
					<li><?php echo brightnest_icon( 'shield', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Vetted, trained & insured professionals', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'tag', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Transparent pricing, no hidden fees', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'clock', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Flexible slots, 7 days a week', 'brightnest' ); ?></li>
					<li><?php echo brightnest_icon( 'headset', 18 ); // phpcs:ignore ?> <?php esc_html_e( 'Friendly support by phone & WhatsApp', 'brightnest' ); ?></li>
				</ul>
			</div>
		</aside>
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
					<input type="search" data-map-query placeholder="<?php esc_attr_e( 'Search for area, street name, landmark…', 'brightnest' ); ?>" aria-label="<?php esc_attr_e( 'Search location', 'brightnest' ); ?>">
					<button type="button" class="bn-map-locate" data-map-locate><?php esc_html_e( 'Set my location', 'brightnest' ); ?></button>
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
