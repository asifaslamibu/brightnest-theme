<?php
/**
 * Meta boxes: service details, review details, request viewer, category icon.
 *
 * @package BrightNest
 */

defined( 'ABSPATH' ) || exit;

function brightnest_add_meta_boxes() {
	add_meta_box( 'bn_service_details', __( 'Service Details', 'brightnest' ), 'brightnest_service_meta_box', 'bn_service', 'normal', 'high' );
	add_meta_box( 'bn_review_details', __( 'Review Details', 'brightnest' ), 'brightnest_review_meta_box', 'bn_review', 'side' );
	add_meta_box( 'bn_request_details', __( 'Request Details', 'brightnest' ), 'brightnest_request_meta_box', 'bn_request', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'brightnest_add_meta_boxes' );

/**
 * Service fields: tabbed box (Basics / Booking & prices / What's included).
 * Field names match brightnest_save_meta(); the option and "included" editors are
 * JS repeaters that write into the same textareas, so existing data keeps working.
 */
function brightnest_service_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$m = function ( $key ) use ( $post ) {
		return get_post_meta( $post->ID, '_bn_' . $key, true );
	};
	$icon = $m( 'icon' );
	$mode = $m( 'mode' );
	$cur  = brightnest_opt( 'currency' );
	?>
	<div class="bn-adm" data-bn-service>
		<nav class="bn-adm-tabs" role="tablist">
			<button type="button" class="is-active" data-tab="basics"><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Basics', 'brightnest' ); ?></button>
			<button type="button" data-tab="booking"><span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'Booking & prices', 'brightnest' ); ?></button>
			<button type="button" data-tab="areas"><span class="dashicons dashicons-location"></span> <?php esc_html_e( 'Areas & prices', 'brightnest' ); ?></button>
			<button type="button" data-tab="included"><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( "What's included", 'brightnest' ); ?></button>
			<button type="button" data-tab="faq"><span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'FAQ', 'brightnest' ); ?></button>
		</nav>

		<!-- Basics -->
		<section class="bn-adm-panel is-active" data-panel="basics">
			<label class="bn-adm-switch bn-adm-switch-big">
				<input type="hidden" name="bn_active" value="0">
				<input type="checkbox" name="bn_active" value="1" <?php checked( brightnest_service_active( $post->ID ) ); ?>>
				<span class="bn-adm-switch-ui" aria-hidden="true"></span>
				<span><strong><?php esc_html_e( 'Service is ON', 'brightnest' ); ?></strong> — <?php esc_html_e( 'customers can see and book it. Switch off to hide it everywhere on the website.', 'brightnest' ); ?></span>
			</label>
			<div class="bn-adm-grid">
				<label class="bn-adm-field">
					<span><?php esc_html_e( 'Starting price', 'brightnest' ); ?> (<?php echo esc_html( $cur ); ?>)</span>
					<input type="number" step="0.01" min="0" name="bn_price" value="<?php echo esc_attr( $m( 'price' ) ); ?>" placeholder="99">
					<small><?php esc_html_e( 'Shown on cards as "From AED 99". Filled in automatically from your cheapest option if you leave it empty.', 'brightnest' ); ?></small>
				</label>
				<label class="bn-adm-field">
					<span><?php esc_html_e( 'Price unit (optional)', 'brightnest' ); ?></span>
					<input type="text" name="bn_price_unit" value="<?php echo esc_attr( $m( 'price_unit' ) ); ?>" placeholder="<?php esc_attr_e( 'per hour, per visit…', 'brightnest' ); ?>">
				</label>
				<label class="bn-adm-field">
					<span><?php esc_html_e( 'Display order', 'brightnest' ); ?></span>
					<input type="number" name="menu_order" value="<?php echo esc_attr( (int) $post->menu_order ); ?>" min="0" step="1">
					<small><?php esc_html_e( 'Lower numbers appear first on the website.', 'brightnest' ); ?></small>
				</label>
				<label class="bn-adm-field">
					<span><?php esc_html_e( 'Typical duration', 'brightnest' ); ?></span>
					<input type="text" name="bn_duration" value="<?php echo esc_attr( $m( 'duration' ) ); ?>" placeholder="2 - 4 hours">
				</label>
				<div class="bn-adm-field">
					<span><?php esc_html_e( 'Rating & review count (optional)', 'brightnest' ); ?></span>
					<div class="bn-adm-inline">
						<input type="number" step="0.1" min="0" max="5" name="bn_rating" value="<?php echo esc_attr( $m( 'rating' ) ); ?>" placeholder="4.8" aria-label="<?php esc_attr_e( 'Rating', 'brightnest' ); ?>">
						<input type="number" min="0" name="bn_review_count" value="<?php echo esc_attr( $m( 'review_count' ) ); ?>" placeholder="120" aria-label="<?php esc_attr_e( 'Review count', 'brightnest' ); ?>">
					</div>
					<small><?php esc_html_e( 'Only use real numbers. Leave empty to hide.', 'brightnest' ); ?></small>
				</div>
			</div>

			<label class="bn-adm-switch">
				<input type="checkbox" name="bn_featured" value="1" <?php checked( $m( 'featured' ), '1' ); ?>>
				<span class="bn-adm-switch-ui" aria-hidden="true"></span>
				<span><strong><?php esc_html_e( 'Popular service', 'brightnest' ); ?></strong> — <?php esc_html_e( 'show it in the top services on the homepage', 'brightnest' ); ?></span>
			</label>

			<div class="bn-adm-field">
				<span><?php esc_html_e( 'Icon', 'brightnest' ); ?></span>
				<div class="bn-adm-icons">
					<label class="bn-adm-icon" title="<?php esc_attr_e( 'Use the category icon', 'brightnest' ); ?>">
						<input type="radio" name="bn_icon" value="" <?php checked( $icon, '' ); ?>>
						<span><span class="dashicons dashicons-category"></span><em><?php esc_html_e( 'Category', 'brightnest' ); ?></em></span>
					</label>
					<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
						<label class="bn-adm-icon" title="<?php echo esc_attr( $label ); ?>">
							<input type="radio" name="bn_icon" value="<?php echo esc_attr( $key ); ?>" <?php checked( $icon, $key ); ?>>
							<span><?php echo brightnest_icon( $key, 28 ); // phpcs:ignore ?><em><?php echo esc_html( $label ); ?></em></span>
						</label>
					<?php endforeach; ?>
				</div>
				<small><?php esc_html_e( 'A featured image (right sidebar) replaces the icon on service cards.', 'brightnest' ); ?></small>
			</div>
		</section>

		<!-- Booking & prices -->
		<section class="bn-adm-panel" data-panel="booking" hidden>
			<h3 class="bn-adm-h"><?php esc_html_e( '1. How do customers book this service?', 'brightnest' ); ?></h3>
			<div class="bn-adm-modes bn-adm-modes-3">
				<label class="bn-adm-mode">
					<input type="radio" name="bn_mode" value="hourly" <?php checked( $mode, 'hourly' ); ?>>
					<span><span class="dashicons dashicons-clock"></span><strong><?php esc_html_e( 'By the hour', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Hours × professionals (+ materials). E.g. home cleaning, maids.', 'brightnest' ); ?></small></span>
				</label>
				<label class="bn-adm-mode">
					<input type="radio" name="bn_mode" value="" <?php checked( $mode, '' ); ?>>
					<span><span class="dashicons dashicons-list-view"></span><strong><?php esc_html_e( 'Menu of options', 'brightnest' ); ?></strong><small><?php esc_html_e( 'Customers add items from a price list in tabs. E.g. salon, AC, sofa.', 'brightnest' ); ?></small></span>
				</label>
				<label class="bn-adm-mode">
					<input type="radio" name="bn_mode" value="single" <?php checked( $mode, 'single' ); ?>>
					<span><span class="dashicons dashicons-tag"></span><strong><?php esc_html_e( 'One fixed price', 'brightnest' ); ?></strong><small><?php esc_html_e( 'One price for the whole job — the starting price in Basics.', 'brightnest' ); ?></small></span>
				</label>
			</div>

			<div class="bn-adm-hourly" data-show-mode="hourly">
				<h3 class="bn-adm-h"><?php esc_html_e( '2. Hourly price & choices', 'brightnest' ); ?></h3>
				<div class="bn-adm-grid">
					<label class="bn-adm-field">
						<span><?php esc_html_e( 'Price per professional per hour', 'brightnest' ); ?> (<?php echo esc_html( $cur ); ?>)</span>
						<input type="number" step="0.01" min="0" name="bn_hourly_rate" value="<?php echo esc_attr( $m( 'hourly_rate' ) ); ?>" placeholder="39">
					</label>
					<div class="bn-adm-field">
						<span><?php esc_html_e( 'Hours customers can choose', 'brightnest' ); ?></span>
						<div class="bn-adm-inline">
							<input type="number" min="1" max="12" name="bn_hours_min" value="<?php echo esc_attr( '' !== $m( 'hours_min' ) ? $m( 'hours_min' ) : 1 ); ?>" aria-label="<?php esc_attr_e( 'From', 'brightnest' ); ?>">
							<em><?php esc_html_e( 'to', 'brightnest' ); ?></em>
							<input type="number" min="1" max="12" name="bn_hours_max" value="<?php echo esc_attr( '' !== $m( 'hours_max' ) ? $m( 'hours_max' ) : 8 ); ?>" aria-label="<?php esc_attr_e( 'To', 'brightnest' ); ?>">
						</div>
					</div>
					<label class="bn-adm-field">
						<span><?php esc_html_e( 'Most professionals per booking', 'brightnest' ); ?></span>
						<input type="number" min="1" max="10" name="bn_pros_max" value="<?php echo esc_attr( '' !== $m( 'pros_max' ) ? $m( 'pros_max' ) : 4 ); ?>">
					</label>
				</div>
				<label class="bn-adm-switch">
					<input type="hidden" name="bn_materials_on" value="0">
					<input type="checkbox" name="bn_materials_on" value="1" <?php checked( '0' !== (string) $m( 'materials_on' ) ); ?> data-toggle-target="materials">
					<span class="bn-adm-switch-ui" aria-hidden="true"></span>
					<span><strong><?php esc_html_e( 'Ask about materials / supplies', 'brightnest' ); ?></strong></span>
				</label>
				<div class="bn-adm-grid" data-toggle-box="materials">
					<label class="bn-adm-field">
						<span><?php esc_html_e( 'Materials price per professional per hour', 'brightnest' ); ?> (<?php echo esc_html( $cur ); ?>)</span>
						<input type="number" step="0.01" min="0" name="bn_materials_rate" value="<?php echo esc_attr( $m( 'materials_rate' ) ); ?>" placeholder="10">
						<small><?php esc_html_e( 'Leave 0 to offer them for free.', 'brightnest' ); ?></small>
					</label>
					<label class="bn-adm-field">
						<span><?php esc_html_e( 'Name on the bill', 'brightnest' ); ?></span>
						<input type="text" name="bn_materials_name" value="<?php echo esc_attr( $m( 'materials_name' ) ); ?>" placeholder="<?php esc_attr_e( 'Cleaning materials', 'brightnest' ); ?>">
					</label>
				</div>
				<p class="bn-adm-example" data-hourly-example></p>
				<details class="bn-adm-raw">
					<summary><?php esc_html_e( 'Change the questions customers see', 'brightnest' ); ?></summary>
					<div class="bn-adm-grid">
						<label class="bn-adm-field"><span><?php esc_html_e( 'Hours question', 'brightnest' ); ?></span><input type="text" name="bn_q_hours" value="<?php echo esc_attr( $m( 'q_hours' ) ); ?>" placeholder="<?php esc_attr_e( 'How many hours do you need your professional to stay?', 'brightnest' ); ?>"></label>
						<label class="bn-adm-field"><span><?php esc_html_e( 'Professionals question', 'brightnest' ); ?></span><input type="text" name="bn_q_pros" value="<?php echo esc_attr( $m( 'q_pros' ) ); ?>" placeholder="<?php esc_attr_e( 'How many professionals do you need?', 'brightnest' ); ?>"></label>
						<label class="bn-adm-field"><span><?php esc_html_e( 'Materials question', 'brightnest' ); ?></span><input type="text" name="bn_q_materials" value="<?php echo esc_attr( $m( 'q_materials' ) ); ?>" placeholder="<?php esc_attr_e( 'Need cleaning materials?', 'brightnest' ); ?>"></label>
					</div>
					<label class="bn-adm-field">
						<span><?php esc_html_e( '"How long should I book for?" guide', 'brightnest' ); ?></span>
						<textarea name="bn_guide" rows="5" placeholder="Studio|2 hours&#10;1 bedroom|2–3 hours"><?php echo esc_textarea( $m( 'guide' ) ); ?></textarea>
						<small><?php esc_html_e( 'One per line: Home size|Duration. Leave empty for the standard guide, or type "-" to hide it.', 'brightnest' ); ?></small>
					</label>
				</details>
			</div>

			<div class="bn-adm-options" data-show-mode="">
				<h3 class="bn-adm-h"><?php esc_html_e( '2. Price list', 'brightnest' ); ?></h3>
				<p class="bn-adm-help"><?php esc_html_e( 'Tabs show at the top of the checkout (e.g. "Bestsellers", "Manicure"). Add the options customers can choose under each tab.', 'brightnest' ); ?></p>
				<div class="bn-opt-groups" data-opt-groups></div>
				<p><button type="button" class="button button-secondary" data-add-group><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add tab', 'brightnest' ); ?></button></p>
				<details class="bn-adm-raw">
					<summary><?php esc_html_e( 'Bulk edit as text (paste many options at once)', 'brightnest' ); ?></summary>
					<p class="description"><?php esc_html_e( 'One option per line: Tab | Option name | Description | Price | Old price (optional)', 'brightnest' ); ?></p>
					<textarea name="bn_options" rows="8" class="large-text code" data-opt-raw><?php echo esc_textarea( $m( 'options' ) ); ?></textarea>
					<button type="button" class="button" data-opt-reload><?php esc_html_e( 'Apply text to the editor above', 'brightnest' ); ?></button>
				</details>
			</div>

			<div data-show-mode="single">
				<p class="bn-adm-help"><?php esc_html_e( 'Customers book the whole service at the "Starting price" set in the Basics tab.', 'brightnest' ); ?></p>
			</div>

			<h3 class="bn-adm-h"><?php esc_html_e( '3. "How often?" choices', 'brightnest' ); ?></h3>
			<p class="bn-adm-help"><?php esc_html_e( 'Offer repeat visits with a discount (e.g. Weekly −10%). Remove all rows to hide the question.', 'brightnest' ); ?></p>
			<input type="hidden" name="bn_freqs_sent" value="1">
			<ol class="bn-freq-edit" data-freq-list>
				<?php foreach ( brightnest_service_frequencies( $post->ID ) as $i => $fr ) : ?>
					<li>
						<input name="bn_freqs[<?php echo (int) $i; ?>][0]" value="<?php echo esc_attr( $fr[0] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Weekly', 'brightnest' ); ?>">
						<label><input type="number" min="0" max="90" step="0.5" name="bn_freqs[<?php echo (int) $i; ?>][1]" value="<?php echo esc_attr( $fr[1] ); ?>"> <?php esc_html_e( '% off', 'brightnest' ); ?></label>
						<button type="button" class="button-link bn-opt-del" data-freq-del><span class="dashicons dashicons-trash"></span></button>
					</li>
				<?php endforeach; ?>
			</ol>
			<button type="button" class="button" data-freq-add><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add choice', 'brightnest' ); ?></button>
		</section>

		<!-- Areas & prices -->
		<section class="bn-adm-panel" data-panel="areas" hidden>
			<p class="bn-adm-help"><?php esc_html_e( 'Choose where this service is offered and how it is priced in each area: the same price, a % change (e.g. 10 = 10% more, -5 = 5% less), or the area\'s own prices. Areas come from BrightNest → Settings → Business.', 'brightnest' ); ?></p>
			<?php
			$bn_ar_hourly = 'hourly' === get_post_meta( $post->ID, '_bn_mode', true );
			$bn_ar_items  = $bn_ar_hourly ? array() : brightnest_service_items( $post->ID );
			$bn_ar_cur    = brightnest_opt( 'currency' );
			$bn_ar_base   = array(
				'rate'      => (float) get_post_meta( $post->ID, '_bn_hourly_rate', true ),
				'materials' => (float) get_post_meta( $post->ID, '_bn_materials_rate', true ),
			);
			?>
			<input type="hidden" name="bn_areas_sent" value="1">
			<table class="bn-adm-areas">
				<thead><tr><th><?php esc_html_e( 'Area', 'brightnest' ); ?></th><th><?php esc_html_e( 'Offered', 'brightnest' ); ?></th><th><?php esc_html_e( 'Pricing', 'brightnest' ); ?></th></tr></thead>
				<tbody>
					<?php foreach ( brightnest_service_areas( $post->ID ) as $city => $ar ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $city ); ?></th>
							<td>
								<label class="bn-adm-switch">
									<input type="hidden" name="bn_areas[<?php echo esc_attr( $city ); ?>][on]" value="0">
									<input type="checkbox" name="bn_areas[<?php echo esc_attr( $city ); ?>][on]" value="1" <?php checked( $ar['on'] ); ?>>
									<span class="bn-adm-switch-ui" aria-hidden="true"></span>
								</label>
							</td>
							<td>
								<div class="bn-ar-pricing" data-area-pricing>
									<select name="bn_areas[<?php echo esc_attr( $city ); ?>][mode]" data-area-mode aria-label="<?php echo esc_attr( sprintf( /* translators: %s area */ __( 'Pricing in %s', 'brightnest' ), $city ) ); ?>">
										<option value="same" <?php selected( $ar['mode'], 'same' ); ?>><?php esc_html_e( 'Same price', 'brightnest' ); ?></option>
										<option value="pct" <?php selected( $ar['mode'], 'pct' ); ?>><?php esc_html_e( '% change', 'brightnest' ); ?></option>
										<option value="own" <?php selected( $ar['mode'], 'own' ); ?>><?php esc_html_e( 'Own prices', 'brightnest' ); ?></option>
									</select>
									<label class="bn-adm-pct" data-show-for="pct"><input type="number" step="0.5" min="-90" max="500" name="bn_areas[<?php echo esc_attr( $city ); ?>][adj]" value="<?php echo esc_attr( $ar['adj'] ); ?>"> %</label>
								</div>
								<div class="bn-ar-own" data-show-for="own">
									<?php if ( $bn_ar_hourly ) : ?>
										<label><?php esc_html_e( 'Per professional per hour', 'brightnest' ); ?>
											<span class="bn-opt-money"><span><?php echo esc_html( $bn_ar_cur ); ?></span><input type="number" min="0" step="0.01" name="bn_areas[<?php echo esc_attr( $city ); ?>][rate]" value="<?php echo esc_attr( $ar['rate'] ? $ar['rate'] : '' ); ?>" placeholder="<?php echo esc_attr( $bn_ar_base['rate'] ); ?>"></span></label>
										<label><?php esc_html_e( 'Materials per hour', 'brightnest' ); ?>
											<span class="bn-opt-money"><span><?php echo esc_html( $bn_ar_cur ); ?></span><input type="number" min="0" step="0.01" name="bn_areas[<?php echo esc_attr( $city ); ?>][materials]" value="<?php echo esc_attr( null !== $ar['materials'] ? $ar['materials'] : '' ); ?>" placeholder="<?php echo esc_attr( $bn_ar_base['materials'] ); ?>"></span></label>
									<?php elseif ( $bn_ar_items ) : ?>
										<table class="bn-ar-prices">
											<thead><tr><th><?php esc_html_e( 'Option', 'brightnest' ); ?></th><th><?php esc_html_e( 'Base', 'brightnest' ); ?></th><th><?php echo esc_html( sprintf( /* translators: %s area */ __( 'Price in %s', 'brightnest' ), $city ) ); ?></th></tr></thead>
											<tbody>
												<?php $bn_seen = array(); ?>
												<?php foreach ( $bn_ar_items as $bn_it ) : ?>
													<?php if ( isset( $bn_seen[ $bn_it['key'] ] ) ) { continue; } $bn_seen[ $bn_it['key'] ] = 1; ?>
													<tr>
														<td><?php echo esc_html( $bn_it['name'] ); ?></td>
														<td class="bn-ar-base"><?php echo esc_html( $bn_ar_cur . ' ' . $bn_it['price'] ); ?></td>
														<td><span class="bn-opt-money"><span><?php echo esc_html( $bn_ar_cur ); ?></span><input type="number" min="0" step="0.01" name="bn_areas[<?php echo esc_attr( $city ); ?>][prices][<?php echo esc_attr( $bn_it['key'] ); ?>]" value="<?php echo esc_attr( isset( $ar['prices'][ $bn_it['key'] ] ) ? $ar['prices'][ $bn_it['key'] ] : '' ); ?>" placeholder="<?php echo esc_attr( $bn_it['price'] ); ?>"></span></td>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									<?php endif; ?>
									<small><?php esc_html_e( 'Leave a price empty to use the base price. Added new options? Save the service first, then set their area prices here.', 'brightnest' ); ?></small>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>

		<!-- What's included -->
		<section class="bn-adm-panel" data-panel="included" hidden>
			<p class="bn-adm-help"><?php esc_html_e( 'Shown as a checklist on the service page.', 'brightnest' ); ?></p>
			<ul class="bn-inc-list" data-inc-list></ul>
			<p><button type="button" class="button button-secondary" data-add-inc><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add item', 'brightnest' ); ?></button></p>
			<textarea name="bn_includes" hidden data-inc-raw><?php echo esc_textarea( $m( 'includes' ) ); ?></textarea>
		</section>

		<!-- FAQ -->
		<section class="bn-adm-panel" data-panel="faq" hidden>
			<p class="bn-adm-help"><?php esc_html_e( 'Questions specific to this service. They appear first, followed by the general FAQs from BrightNest → Settings → FAQ.', 'brightnest' ); ?></p>
			<?php $faqs = get_post_meta( $post->ID, '_bn_faqs', true ); ?>
			<input type="hidden" name="bn_faqs_sent" value="1">
			<ol class="bn-faq-edit" data-faq-list>
				<?php foreach ( is_array( $faqs ) ? $faqs : array() as $i => $faq ) : ?>
					<li>
						<input name="bn_faqs[<?php echo (int) $i; ?>][0]" value="<?php echo esc_attr( $faq[0] ); ?>" placeholder="<?php esc_attr_e( 'Question', 'brightnest' ); ?>">
						<textarea name="bn_faqs[<?php echo (int) $i; ?>][1]" rows="2" placeholder="<?php esc_attr_e( 'Answer', 'brightnest' ); ?>"><?php echo esc_textarea( $faq[1] ); ?></textarea>
						<button type="button" class="button-link bn-opt-del" data-faq-del><span class="dashicons dashicons-trash"></span></button>
					</li>
				<?php endforeach; ?>
			</ol>
			<button type="button" class="button" data-faq-add data-name="bn_faqs"><span class="dashicons dashicons-plus"></span> <?php esc_html_e( 'Add question', 'brightnest' ); ?></button>
		</section>
	</div>
	<?php
}

/**
 * Review fields.
 */
function brightnest_review_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$rating  = get_post_meta( $post->ID, '_bn_rating', true ) ?: 5;
	$service = get_post_meta( $post->ID, '_bn_service_name', true );
	?>
	<p><label for="bn_rating"><strong><?php esc_html_e( 'Stars (1-5)', 'brightnest' ); ?></strong></label><br>
		<input type="number" min="1" max="5" id="bn_rating" name="bn_rating" value="<?php echo esc_attr( $rating ); ?>" class="small-text"></p>
	<p><label for="bn_service_name"><strong><?php esc_html_e( 'Service used', 'brightnest' ); ?></strong></label><br>
		<input type="text" id="bn_service_name" name="bn_service_name" value="<?php echo esc_attr( $service ); ?>" class="widefat"></p>
	<p class="description"><?php esc_html_e( 'Title = customer name, content = review text.', 'brightnest' ); ?></p>
	<?php
}

/**
 * Request viewer (read-only details + status).
 */
function brightnest_request_meta_box( $post ) {
	wp_nonce_field( 'bn_save_meta', 'bn_meta_nonce' );
	$fields = array(
		'_bn_ref'       => __( 'Booking ref', 'brightnest' ),
		'_bn_type'      => __( 'Type', 'brightnest' ),
		'_bn_service'   => __( 'Service', 'brightnest' ),
		'_bn_items'     => __( 'Items', 'brightnest' ),
		'_bn_frequency' => __( 'Frequency', 'brightnest' ),
		'_bn_subtotal'  => __( 'Subtotal', 'brightnest' ),
		'_bn_freq_discount' => __( 'Frequency discount', 'brightnest' ),
		'_bn_voucher'   => __( 'Voucher', 'brightnest' ),
		'_bn_discount'  => __( 'Discount', 'brightnest' ),
		'_bn_area_fee'  => __( 'Area charge', 'brightnest' ),
		'_bn_fee'       => __( 'Service fee', 'brightnest' ),
		'_bn_total'     => __( 'Total', 'brightnest' ),
		'_bn_payment'   => __( 'Payment method', 'brightnest' ),
		'_bn_location'  => __( 'Map pin', 'brightnest' ),
		'_bn_name'    => __( 'Name', 'brightnest' ),
		'_bn_phone'   => __( 'Phone', 'brightnest' ),
		'_bn_email'   => __( 'Email', 'brightnest' ),
		'_bn_date'    => __( 'Preferred date', 'brightnest' ),
		'_bn_time'    => __( 'Preferred time', 'brightnest' ),
		'_bn_area'    => __( 'Area / City', 'brightnest' ),
		'_bn_address' => __( 'Address', 'brightnest' ),
		'_bn_message' => __( 'Notes / Message', 'brightnest' ),
	);
	$status = get_post_meta( $post->ID, '_bn_status', true ) ?: 'new';
	echo '<table class="bn-req-table">';
	foreach ( $fields as $key => $label ) {
		$val = get_post_meta( $post->ID, $key, true );
		if ( '' === $val ) {
			continue;
		}
		if ( '_bn_email' === $key ) {
			$val = '<a href="mailto:' . esc_attr( $val ) . '">' . esc_html( $val ) . '</a>';
		} elseif ( '_bn_location' === $key ) {
			$val = '<a href="' . esc_url( 'https://www.google.com/maps?q=' . $val ) . '" target="_blank" rel="noopener">' . esc_html( $val ) . ' ↗</a>';
		} elseif ( in_array( $key, array( '_bn_subtotal', '_bn_freq_discount', '_bn_discount', '_bn_area_fee', '_bn_fee', '_bn_total' ), true ) ) {
			$val = esc_html( brightnest_opt( 'currency' ) . ' ' . $val );
		} elseif ( '_bn_phone' === $key ) {
			$val = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $val ) ) . '">' . esc_html( $val ) . '</a>';
		} else {
			$val = nl2br( esc_html( $val ) );
		}
		printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html( $label ), $val ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	if ( function_exists( 'brightnest_payment_badge' ) && brightnest_payment_badge( $post->ID ) ) {
		printf( '<tr><th>%s</th><td>%s %s</td></tr>', esc_html__( 'Online payment', 'brightnest' ), brightnest_payment_badge( $post->ID ), esc_html( (string) get_post_meta( $post->ID, '_bn_transaction', true ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</table>';
	?>
	<p><label for="bn_admin_notes"><strong><?php esc_html_e( 'Admin notes (private)', 'brightnest' ); ?></strong></label><br>
		<textarea id="bn_admin_notes" name="bn_admin_notes" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'E.g. assigned to Maria, customer asked for eco products', 'brightnest' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_bn_admin_notes', true ) ); ?></textarea></p>
	<p><label for="bn_status"><strong><?php esc_html_e( 'Status', 'brightnest' ); ?></strong></label><br>
		<select name="bn_status" id="bn_status">
			<?php foreach ( array( 'new', 'confirmed', 'completed', 'cancelled' ) as $s ) : ?>
				<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( ucfirst( $s ) ); ?></option>
			<?php endforeach; ?>
		</select></p>
	<?php
}

/**
 * Save handler for all meta boxes.
 */
function brightnest_save_meta( $post_id, $post ) {
	if ( ! isset( $_POST['bn_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['bn_meta_nonce'] ), 'bn_save_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'bn_service' === $post->post_type ) {
		$text = array( 'bn_price_unit', 'bn_duration', 'bn_icon' );
		foreach ( $text as $f ) {
			update_post_meta( $post_id, '_' . $f, isset( $_POST[ $f ] ) ? sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) : '' );
		}
		update_post_meta( $post_id, '_bn_price', isset( $_POST['bn_price'] ) && '' !== $_POST['bn_price'] ? (float) $_POST['bn_price'] : '' );
		update_post_meta( $post_id, '_bn_rating', isset( $_POST['bn_rating'] ) && '' !== $_POST['bn_rating'] ? min( 5, max( 0, (float) $_POST['bn_rating'] ) ) : '' );
		update_post_meta( $post_id, '_bn_review_count', isset( $_POST['bn_review_count'] ) && '' !== $_POST['bn_review_count'] ? absint( $_POST['bn_review_count'] ) : '' );
		update_post_meta( $post_id, '_bn_featured', empty( $_POST['bn_featured'] ) ? '' : '1' );
		update_post_meta( $post_id, '_bn_includes', isset( $_POST['bn_includes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_includes'] ) ) : '' );
		update_post_meta( $post_id, '_bn_options', isset( $_POST['bn_options'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bn_options'] ) ) : '' );
		$mode = isset( $_POST['bn_mode'] ) ? sanitize_key( $_POST['bn_mode'] ) : '';
		update_post_meta( $post_id, '_bn_mode', in_array( $mode, array( 'hourly', 'single' ), true ) ? $mode : '' );
		if ( isset( $_POST['bn_active'] ) ) {
			update_post_meta( $post_id, '_bn_active', '1' === $_POST['bn_active'] ? '1' : '0' );
		}

		// Hourly choices.
		foreach ( array( 'hours_min' => array( 1, 12 ), 'hours_max' => array( 1, 12 ), 'pros_max' => array( 1, 10 ) ) as $k => $lim ) {
			if ( isset( $_POST[ 'bn_' . $k ] ) ) {
				update_post_meta( $post_id, '_bn_' . $k, max( $lim[0], min( $lim[1], absint( $_POST[ 'bn_' . $k ] ) ) ) );
			}
		}
		if ( isset( $_POST['bn_materials_on'] ) ) {
			update_post_meta( $post_id, '_bn_materials_on', '1' === $_POST['bn_materials_on'] ? '1' : '0' );
		}
		foreach ( array( 'materials_name', 'q_hours', 'q_pros', 'q_materials' ) as $k ) {
			if ( isset( $_POST[ 'bn_' . $k ] ) ) {
				update_post_meta( $post_id, '_bn_' . $k, sanitize_text_field( wp_unslash( $_POST[ 'bn_' . $k ] ) ) );
			}
		}
		if ( isset( $_POST['bn_guide'] ) ) {
			update_post_meta( $post_id, '_bn_guide', sanitize_textarea_field( wp_unslash( $_POST['bn_guide'] ) ) );
		}

		// "How often?" choices: [ label, discount % ].
		if ( isset( $_POST['bn_freqs_sent'] ) ) {
			$freqs = array();
			foreach ( isset( $_POST['bn_freqs'] ) && is_array( $_POST['bn_freqs'] ) ? wp_unslash( $_POST['bn_freqs'] ) : array() as $row ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$label = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
				if ( '' !== $label ) {
					$freqs[] = array( $label, max( 0, min( 90, (float) ( isset( $row[1] ) ? $row[1] : 0 ) ) ) );
				}
			}
			update_post_meta( $post_id, '_bn_freqs', $freqs );
		}

		// Areas: offered or not + price change %.
		if ( isset( $_POST['bn_areas_sent'] ) ) {
			$areas = array();
			$in    = isset( $_POST['bn_areas'] ) && is_array( $_POST['bn_areas'] ) ? wp_unslash( $_POST['bn_areas'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			foreach ( brightnest_cities() as $city ) {
				$row            = isset( $in[ $city ] ) && is_array( $in[ $city ] ) ? $in[ $city ] : array();
				$mode   = isset( $row['mode'] ) && in_array( $row['mode'], array( 'same', 'pct', 'own' ), true ) ? $row['mode'] : 'same';
				$prices = array();
				foreach ( isset( $row['prices'] ) && is_array( $row['prices'] ) ? $row['prices'] : array() as $k => $v ) {
					if ( '' !== trim( (string) $v ) && (float) $v > 0 ) {
						$prices[ sanitize_key( $k ) ] = round( (float) $v, 2 );
					}
				}
				$areas[ $city ] = array(
					'on'        => isset( $row['on'] ) && '1' === (string) $row['on'] ? '1' : '0',
					'mode'      => $mode,
					'adj'       => isset( $row['adj'] ) ? max( -90, min( 500, (float) $row['adj'] ) ) : 0,
					'prices'    => $prices,
					'rate'      => isset( $row['rate'] ) && '' !== trim( (string) $row['rate'] ) ? max( 0, round( (float) $row['rate'], 2 ) ) : '',
					'materials' => isset( $row['materials'] ) && '' !== trim( (string) $row['materials'] ) ? max( 0, round( (float) $row['materials'], 2 ) ) : '',
				);
			}
			update_post_meta( $post_id, '_bn_areas', $areas );
		}
		if ( isset( $_POST['bn_faqs_sent'] ) ) {
			$faqs = array();
			foreach ( isset( $_POST['bn_faqs'] ) && is_array( $_POST['bn_faqs'] ) ? wp_unslash( $_POST['bn_faqs'] ) : array() as $row ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$q = isset( $row[0] ) ? sanitize_text_field( $row[0] ) : '';
				$a = isset( $row[1] ) ? sanitize_textarea_field( $row[1] ) : '';
				if ( '' !== $q && '' !== $a ) {
					$faqs[] = array( $q, $a );
				}
			}
			update_post_meta( $post_id, '_bn_faqs', $faqs );
		}
		update_post_meta( $post_id, '_bn_hourly_rate', isset( $_POST['bn_hourly_rate'] ) && '' !== $_POST['bn_hourly_rate'] ? (float) $_POST['bn_hourly_rate'] : '' );
		update_post_meta( $post_id, '_bn_materials_rate', isset( $_POST['bn_materials_rate'] ) && '' !== $_POST['bn_materials_rate'] ? (float) $_POST['bn_materials_rate'] : '' );

		// No starting price entered: use the hourly rate or the cheapest option.
		if ( '' === get_post_meta( $post_id, '_bn_price', true ) ) {
			$auto = '';
			if ( 'hourly' === get_post_meta( $post_id, '_bn_mode', true ) ) {
				$auto = get_post_meta( $post_id, '_bn_hourly_rate', true );
			} elseif ( '' !== trim( (string) get_post_meta( $post_id, '_bn_options', true ) ) ) {
				$prices = array_filter( wp_list_pluck( brightnest_service_items( $post_id ), 'price' ) );
				$auto   = $prices ? min( $prices ) : '';
			}
			if ( '' !== $auto ) {
				update_post_meta( $post_id, '_bn_price', (float) $auto );
			}
		}
	}

	if ( 'bn_review' === $post->post_type ) {
		update_post_meta( $post_id, '_bn_rating', isset( $_POST['bn_rating'] ) ? min( 5, max( 1, absint( $_POST['bn_rating'] ) ) ) : 5 );
		update_post_meta( $post_id, '_bn_service_name', isset( $_POST['bn_service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bn_service_name'] ) ) : '' );
	}

	if ( 'bn_request' === $post->post_type && isset( $_POST['bn_admin_notes'] ) ) {
		update_post_meta( $post_id, '_bn_admin_notes', sanitize_textarea_field( wp_unslash( $_POST['bn_admin_notes'] ) ) );
	}
	if ( 'bn_request' === $post->post_type && isset( $_POST['bn_status'] ) ) {
		$status = sanitize_key( $_POST['bn_status'] );
		if ( in_array( $status, array( 'new', 'confirmed', 'completed', 'cancelled' ), true ) ) {
			update_post_meta( $post_id, '_bn_status', $status );
		}
	}
}
add_action( 'save_post', 'brightnest_save_meta', 10, 2 );

/*
 * Category icon field.
 */
function brightnest_cat_icon_add_field() {
	?>
	<div class="form-field">
		<label for="bn_cat_icon"><?php esc_html_e( 'Icon', 'brightnest' ); ?></label>
		<select name="bn_cat_icon" id="bn_cat_icon">
			<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}
add_action( 'bn_service_cat_add_form_fields', 'brightnest_cat_icon_add_field' );

function brightnest_cat_icon_edit_field( $term ) {
	$icon = get_term_meta( $term->term_id, 'bn_icon', true );
	?>
	<tr class="form-field">
		<th><label for="bn_cat_icon"><?php esc_html_e( 'Icon', 'brightnest' ); ?></label></th>
		<td>
			<select name="bn_cat_icon" id="bn_cat_icon">
				<?php foreach ( brightnest_service_icon_choices() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $icon, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
}
add_action( 'bn_service_cat_edit_form_fields', 'brightnest_cat_icon_edit_field' );

function brightnest_cat_icon_save( $term_id ) {
	if ( isset( $_POST['bn_cat_icon'] ) && current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core term screens verify nonce.
		update_term_meta( $term_id, 'bn_icon', sanitize_key( $_POST['bn_cat_icon'] ) );
	}
}
add_action( 'created_bn_service_cat', 'brightnest_cat_icon_save' );
add_action( 'edited_bn_service_cat', 'brightnest_cat_icon_save' );

/*
 * Category: which homepage tab it appears in.
 */
function brightnest_home_groups() {
	return array(
		''      => __( 'Automatic', 'brightnest' ),
		'clean' => __( 'Cleaning', 'brightnest' ),
		'fix'   => __( 'Maintenance', 'brightnest' ),
		'more'  => __( 'Lifestyle & health', 'brightnest' ),
	);
}

function brightnest_cat_group_add_field() {
	?>
	<div class="form-field">
		<label for="bn_home_group"><?php esc_html_e( 'Homepage tab', 'brightnest' ); ?></label>
		<select name="bn_home_group" id="bn_home_group">
			<?php foreach ( brightnest_home_groups() as $k => $label ) : ?><option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
		</select>
		<p><?php esc_html_e( 'Which tab of the homepage services section shows this category.', 'brightnest' ); ?></p>
	</div>
	<?php
}
add_action( 'bn_service_cat_add_form_fields', 'brightnest_cat_group_add_field', 11 );

function brightnest_cat_group_edit_field( $term ) {
	$cur = get_term_meta( $term->term_id, 'bn_home_group', true );
	?>
	<tr class="form-field">
		<th><label for="bn_home_group"><?php esc_html_e( 'Homepage tab', 'brightnest' ); ?></label></th>
		<td>
			<select name="bn_home_group" id="bn_home_group">
				<?php foreach ( brightnest_home_groups() as $k => $label ) : ?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( $cur, $k ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Which tab of the homepage services section shows this category.', 'brightnest' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'bn_service_cat_edit_form_fields', 'brightnest_cat_group_edit_field', 11 );

function brightnest_cat_group_save( $term_id ) {
	if ( isset( $_POST['bn_home_group'] ) && current_user_can( 'manage_categories' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- core term screens verify nonce.
		$g = sanitize_key( $_POST['bn_home_group'] ); // phpcs:ignore WordPress.Security.NonceVerification
		update_term_meta( $term_id, 'bn_home_group', array_key_exists( $g, brightnest_home_groups() ) ? $g : '' );
	}
}
add_action( 'created_bn_service_cat', 'brightnest_cat_group_save' );
add_action( 'edited_bn_service_cat', 'brightnest_cat_group_save' );
