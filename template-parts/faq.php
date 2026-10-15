<?php
/**
 * FAQ accordion.
 *
 * @package BrightNest
 */

$bn_faqs = array(
	array( __( 'How do I book a service?', 'brightnest' ), __( 'Pick a service, choose your preferred date and time and submit the booking form. Our team will call or WhatsApp you to confirm the details and final price.', 'brightnest' ) ),
	array( __( 'Are your professionals vetted?', 'brightnest' ), __( 'Yes. Every professional is background-checked, trained and reviewed by customers after each job.', 'brightnest' ) ),
	array( __( 'Do I need to pay online?', 'brightnest' ), __( 'No payment is needed when you submit a request. You can pay after the service by cash, card or bank transfer.', 'brightnest' ) ),
	array( __( 'Do cleaners bring their own materials?', 'brightnest' ), __( 'Yes, cleaning materials and equipment can be included — just mention it in the notes when you book.', 'brightnest' ) ),
	array( __( 'Can I reschedule or cancel?', 'brightnest' ), __( 'Of course. Contact us at least 12 hours before your booking to reschedule or cancel free of charge.', 'brightnest' ) ),
	array( __( 'What if I am not happy with the service?', 'brightnest' ), __( 'Let us know within 24 hours and we will send a team back to fix it at no extra cost.', 'brightnest' ) ),
);
$bn_faqs = apply_filters( 'brightnest_faqs', $bn_faqs );
?>
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
