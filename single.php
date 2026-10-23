<?php
/**
 * Single blog post: title block, photo, article with a table of contents and a booking card beside it,
 * share buttons, related articles, booking band.
 *
 * @package BrightNest
 */

get_header();

while ( have_posts() ) :
	the_post();
	$bn_id    = get_the_ID();
	$bn_cat   = get_the_category();
	$bn_url   = get_permalink();
	$bn_title = wp_strip_all_tags( html_entity_decode( get_the_title() ) );
	$bn_share = array(
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $bn_title . ' ' . $bn_url ) ),
		'facebook' => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $bn_url ) ),
		'twitter'  => array( 'X', 'https://twitter.com/intent/tweet?text=' . rawurlencode( $bn_title ) . '&url=' . rawurlencode( $bn_url ) ),
		'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $bn_url ) ),
	);
	?>
	<div class="bn-read-progress" aria-hidden="true"><span data-read-progress></span></div>
	<article class="bn-post">
		<header class="bn-post-head<?php echo has_post_thumbnail() ? ' has-img' : ''; ?>">
			<div class="bn-container bn-post-wide"><?php brightnest_breadcrumbs(); ?></div>
			<div class="bn-container bn-post-wide bn-post-hero">
			<div class="bn-post-hero-copy">
				<?php if ( $bn_cat ) : ?>
					<a class="bn-pc-cat" href="<?php echo esc_url( get_category_link( $bn_cat[0] ) ); ?>"><?php echo esc_html( $bn_cat[0]->name ); ?></a>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="bn-post-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
				<?php endif; ?>
				<p class="bn-post-meta">
					<span><?php echo brightnest_icon( 'calendar', 16 ); // phpcs:ignore ?> <?php echo esc_html( get_the_date() ); ?></span>
					<span><?php echo brightnest_icon( 'clock', 16 ); // phpcs:ignore ?> <?php echo esc_html( brightnest_reading_time( $bn_id ) ); ?></span>
					<?php if ( get_the_modified_date( 'Ymd' ) > get_the_date( 'Ymd' ) ) : ?>
						<span><?php echo esc_html( sprintf( /* translators: %s date */ __( 'Updated %s', 'brightnest' ), get_the_modified_date() ) ); ?></span>
					<?php endif; ?>
				</p>
			</div>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="bn-post-img"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
			<?php endif; ?>
			</div>
		</header>

		<div class="bn-container bn-post-layout">
			<div class="bn-post-main">
				<div class="bn-prose bn-post-body" data-post-body><?php the_content(); ?></div>
				<?php wp_link_pages(); ?>
				<div class="bn-post-share">
					<strong><?php esc_html_e( 'Share this article', 'brightnest' ); ?></strong>
					<?php foreach ( $bn_share as $bn_net => $bn_s ) : ?>
						<a class="bn-share bn-share-<?php echo esc_attr( $bn_net ); ?>" href="<?php echo esc_url( $bn_s[1] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( /* translators: %s network */ __( 'Share on %s', 'brightnest' ), $bn_s[0] ) ); ?>"><?php echo brightnest_icon( $bn_net, 18 ); // phpcs:ignore ?></a>
					<?php endforeach; ?>
					<button type="button" class="bn-share bn-share-copy" data-copy-link="<?php echo esc_url( $bn_url ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'brightnest' ); ?>"><?php echo brightnest_icon( 'clipboard', 18 ); // phpcs:ignore ?><span data-copied hidden><?php esc_html_e( 'Copied', 'brightnest' ); ?></span></button>
				</div>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>

			<aside class="bn-post-side">
				<details class="bn-toc" data-toc hidden open>
					<summary><?php esc_html_e( 'On this page', 'brightnest' ); ?> <?php echo brightnest_icon( 'chevron-down', 18 ); // phpcs:ignore ?></summary>
					<nav aria-label="<?php esc_attr_e( 'On this page', 'brightnest' ); ?>"><ol data-toc-list></ol></nav>
				</details>
				<div class="bn-post-book">
					<strong><?php esc_html_e( 'Need help at home?', 'brightnest' ); ?></strong>
					<p><?php esc_html_e( 'Vetted professionals, clear prices and secure online payment.', 'brightnest' ); ?></p>
					<a class="bn-btn bn-btn-primary bn-btn-block" href="<?php echo esc_url( get_post_type_archive_link( 'bn_service' ) ); ?>"><?php esc_html_e( 'Book a service', 'brightnest' ); ?></a>
					<?php if ( brightnest_whatsapp_url() ) : ?>
						<a class="bn-btn bn-btn-wa bn-btn-block" href="<?php echo esc_url( brightnest_whatsapp_url( __( 'Hi, I would like to book a service.', 'brightnest' ) ) ); ?>" target="_blank" rel="noopener"><?php echo brightnest_icon( 'whatsapp', 18 ); // phpcs:ignore ?> WhatsApp</a>
					<?php endif; ?>
				</div>
			</aside>
		</div>
	</article>

	<?php
	$bn_related = get_posts(
		array(
			'posts_per_page' => 3,
			'post__not_in'   => array( $bn_id ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
			'category__in'   => $bn_cat ? array( $bn_cat[0]->term_id ) : array(),
			'fields'         => 'ids',
		)
	);
	if ( count( $bn_related ) < 3 ) {
		$bn_related = array_merge( $bn_related, get_posts( array( 'posts_per_page' => 3 - count( $bn_related ), 'post__not_in' => array_merge( array( $bn_id ), $bn_related ), 'fields' => 'ids' ) ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams
	}
	?>
	<?php if ( $bn_related ) : ?>
		<section class="bn-blog-related">
			<div class="bn-container">
				<h2><?php esc_html_e( 'Keep reading', 'brightnest' ); ?></h2>
				<div class="bn-pc-grid">
					<?php foreach ( $bn_related as $bn_rid ) { get_template_part( 'template-parts/post-card', null, array( 'id' => $bn_rid ) ); } ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<script>
	/* Article: table of contents from the H2 headings (the one being read is marked), reading bar,
	   tables that scroll sideways instead of squeezing, and "copy link". */
	(function () {
		var body = document.querySelector('[data-post-body]');
		var toc = document.querySelector('[data-toc]');
		if (body && toc) {
			var heads = Array.prototype.slice.call(body.querySelectorAll('h2'));
			if (heads.length >= 2) {
				var list = toc.querySelector('[data-toc-list]');
				var links = heads.map(function (h, i) {
					if (!h.id) { h.id = 'section-' + (i + 1); }
					var li = document.createElement('li');
					var a = document.createElement('a');
					a.href = '#' + h.id;
					a.textContent = h.textContent;
					li.appendChild(a);
					list.appendChild(li);
					return a;
				});
				// Phones and tablets: the list starts folded (it sits above the article there).
				if (window.matchMedia('(max-width: 1024px)').matches) { toc.open = false; }
				toc.hidden = false;
				list.addEventListener('click', function (e) {
					if (e.target.closest('a') && window.matchMedia('(max-width: 1024px)').matches) { toc.open = false; }
				});
				// The fade at the bottom of the list only while there is more to scroll to.
				var ends = function () { list.classList.toggle('is-end', list.scrollTop + list.clientHeight >= list.scrollHeight - 4); };
				list.addEventListener('scroll', ends, { passive: true });
				window.addEventListener('resize', ends);
				toc.addEventListener('toggle', ends);
				var mark = function () {
					var cur = 0;
					heads.forEach(function (h, i) { if (h.getBoundingClientRect().top < 160) { cur = i; } });
					links.forEach(function (a, i) { a.classList.toggle('is-current', i === cur); });
					var act = links[cur];
					if (act && window.matchMedia('(min-width: 1025px)').matches) {
						var top = act.offsetTop - list.offsetTop;
						if (top < list.scrollTop || top > list.scrollTop + list.clientHeight - 30) { list.scrollTop = top - 40; }
					}
				};
				window.addEventListener('scroll', function () { mark(); ends(); }, { passive: true });
				mark();
				ends();
			}
		}
		if (body) {
			body.querySelectorAll('table').forEach(function (t) {
				if (t.parentNode.classList.contains('bn-table-wrap')) { return; }
				var w = document.createElement('div');
				w.className = 'bn-table-wrap';
				t.parentNode.insertBefore(w, t);
				w.appendChild(t);
			});
			var bar = document.querySelector('[data-read-progress]');
			var art = document.querySelector('.bn-post');
			if (bar && art) {
				var prog = function () {
					var r = art.getBoundingClientRect();
					var p = Math.min(1, Math.max(0, -r.top / Math.max(1, r.height - window.innerHeight)));
					bar.style.transform = 'scaleX(' + p + ')';
				};
				window.addEventListener('scroll', prog, { passive: true });
				prog();
			}
		}
		var copy = document.querySelector('[data-copy-link]');
		if (copy) {
			copy.addEventListener('click', function () {
				var done = function () { var s = copy.querySelector('[data-copied]'); s.hidden = false; setTimeout(function () { s.hidden = true; }, 1800); };
				if (navigator.clipboard) { navigator.clipboard.writeText(copy.getAttribute('data-copy-link')).then(done); } else { done(); }
			});
		}
	})();
	</script>
	<?php
endwhile;

get_template_part( 'template-parts/blog-cta' );

get_footer();
