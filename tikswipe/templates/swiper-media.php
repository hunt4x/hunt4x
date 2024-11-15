<div class="swiper-slide<?php if ( get_post_format() === 'video' ) :
	?> swiper-video-slide<?php endif; ?>" data-id="<?php echo get_the_id(); ?>">

	<?php echo apply_filters( 'wps_paywall_premium_badge', '', get_the_id() ); ?>

	<?php
	ob_start(
		function ( $buffer ) {
			return apply_filters( 'wps_paywall_media_content', $buffer, get_the_id() );
		}
	);
	?>
	<div class="slide-bg <?php echo get_post_format(); ?>"></div>
	<?php if ( get_post_format() === 'video' ) : ?>
		<?php
		if ( ! empty( get_post_meta( get_the_id(), 'embed', true ) ) ) :
			if ( has_post_thumbnail() ) {
				$image_url = get_the_post_thumbnail_url( get_the_id(), 'ms-large' );
			}
			?>
			<img class="embed-thumbnail content-img
			<?php
			if ( get_post_meta( get_the_id(), '_file_format', true ) === 'portrait' ) :
				?>
				is-portrait<?php endif; ?>" src="<?php echo esc_url( $image_url ); ?>">
			<div class="embed-play-button"><a href="#!"><img src="<?php echo get_template_directory_uri(); ?>/img/play.svg" width="50"></a></div>
			<div class="embed-content"><?php echo get_post_meta( get_the_id(), 'embed', true ); ?></div>
			<a class="playvideo" href="#!"></a>
		<?php elseif ( ! empty( get_post_meta( get_the_id(), '_video_file_url', true ) ) || ! empty( get_post_meta( get_the_id(), 'video_url', true ) ) ) : ?>
			<video-js id="video-<?php echo get_query_var( 'paged', 1 ); ?>-<?php echo get_the_id(); ?>" data-postid="<?php echo get_the_id(); ?>" class="vjs-default-skin vjs-show-big-play-button-on-pause
											<?php
											if ( get_post_meta( get_the_id(), '_file_format', true ) === 'portrait' ) :
												?>
				is-portrait<?php endif; ?>"></video-js>
		<?php elseif ( ! empty( get_post_meta( get_the_id(), 'embed', true ) ) ) : ?>
			<?php echo get_post_meta( get_the_id(), 'embed', true ); ?>
			<a class="playvideo" href="#!"></a>
		<?php endif; ?>
		<?php
	elseif ( get_post_format() === 'image' ) :
		if ( has_post_thumbnail() ) {
			$image_url = get_the_post_thumbnail_url( get_the_id(), 'ms-large' );
		}
		?>
		<img class="content-img
		<?php
		if ( get_post_meta( get_the_id(), '_file_format', true ) === 'portrait' ) :
			?>
			is-portrait<?php endif; ?>" src="<?php echo esc_url( $image_url ); ?>">
	<?php endif; ?>

	<?php ob_end_flush(); ?>

	<?php get_template_part( 'templates/post', 'content' ); ?>
</div>
