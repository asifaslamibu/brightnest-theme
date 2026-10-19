<?php
/**
 * FAQ accordion.
 *
 * @package BrightNest
 */

// Global FAQs (BrightNest → Settings → FAQ) plus the service's own questions on service pages.
$bn_faqs = brightnest_faqs( is_singular( 'bn_service' ) ? get_the_ID() : 0 );
$bn_faqs = apply_filters( 'brightnest_faqs', $bn_faqs );
?>
<?php if ( ! $bn_faqs ) { return; } ?>
<div class="bn-faq">
	<?php foreach ( $bn_faqs as $bn_i => $bn_faq ) : ?>
		<details class="bn-faq-item" <?php echo 0 === $bn_i ? 'open' : ''; ?>>
			<summary><?php echo esc_html( $bn_faq[0] ); ?> <?php echo brightnest_icon( 'chevron-down', 20 ); // phpcs:ignore ?></summary>
			<p><?php echo esc_html( $bn_faq[1] ); ?></p>
		</details>
	<?php endforeach; ?>
</div>
<script type="application/ld+json">
<?php
echo wp_json_encode(
	array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $f ) {
				return array(
					'@type'          => 'Question',
					'name'           => $f[0],
					'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ),
				);
			},
			$bn_faqs
		),
	)
);
?>
</script>
