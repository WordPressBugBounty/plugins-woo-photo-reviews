<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( empty( $product ) || empty( $comment ) || $comment->comment_parent || empty( $settings ) ) {
	return;
}
$product_title = $product->get_title();
printf( '<div class="kt-reviews-image-container">' );
if ( get_comment_meta( $comment->comment_ID, 'reviews-images' ) ) {
	$image_post_ids = get_comment_meta( $comment->comment_ID, 'reviews-images', true );
	?>
    <div class="kt-wc-reviews-images-wrap-wrap">
		<?php
		$i = 0;
		foreach ( $image_post_ids as $image_post_id ) {
			if ( ! wc_is_valid_url( $image_post_id ) ) {
				$image_data  = wp_get_attachment_metadata( $image_post_id );
				$is_video    = strpos( $image_data['mime_type'] ?? '', 'video/' ) === 0;
				$alt         = get_post_meta( $image_post_id, '_wp_attachment_image_alt', true );
				$image_alt   = $alt ? $alt : $product_title;
				$big_img_src = ( isset( $image_data['sizes']['wcpr-photo-reviews'] ) ? wp_get_attachment_image_url( $image_post_id, 'wcpr-photo-reviews' ) : ( isset( $image_data['sizes']['medium_large'] ) ? wp_get_attachment_image_url( $image_post_id, 'medium_large' ) : ( isset( $image_data['sizes']['medium'] ) ? wp_get_attachment_image_url( $image_post_id, 'medium' ) : wp_get_attachment_thumb_url( $image_post_id, 'full' ) ) ) );
				?>
                <div class="reviews-images-item"
                     data-image_src="<?php echo esc_attr( apply_filters( 'woocommerce_photo_reviews_big_review_photo', $big_img_src, $image_post_id, $comment ) ) ?>"
                     data-index="<?php echo esc_attr( $i ); ?>">

					<?php
					if ( $is_video ) {
						printf( '<video class="review-images review-videos" src="%s" >%s</video>',
							esc_url( apply_filters( 'woocommerce_photo_reviews_thumbnail_photo', wp_get_attachment_url( $image_post_id ), $image_post_id, $comment ) ),
							esc_attr( apply_filters( 'woocommerce_photo_reviews_image_alt', $image_alt, $image_post_id, $comment ) ) );
					} else {
						?>
                        <img class="review-images" loading="lazy"
                             src="<?php echo esc_url( apply_filters( 'woocommerce_photo_reviews_thumbnail_photo', wp_get_attachment_image_url( $image_post_id ), $image_post_id, $comment ) ); ?>"
                             alt="<?php echo esc_attr( apply_filters( 'woocommerce_photo_reviews_image_alt', $image_alt, $image_post_id, $comment ) ) ?>"/>
						<?php
					}
					?>
                </div>
				<?php
			} else {
				$file_type = explode( '.', $image_post_id );
				$file_type = end( $file_type );
				if ( ! in_array( 'image/' . strtolower( $file_type ), $settings->get_params( 'upload_allow_images' ) ) ) {
					if ( strpos( $image_post_id, '.mp4' ) || strpos( $image_post_id, '.webm' ) ) {
						printf( '<div class="reviews-images-item" data-image_src="%s" data-index="%s"><video class="review-images review-videos" src="%s" >%s</video></div>',
							esc_attr( $image_post_id ), esc_attr( $i ), esc_url( $image_post_id ), esc_attr( $product_title ) );
					} elseif ( strpos( $image_post_id, '.shopee.' ) ) {
						?>
                        <div class="reviews-images-item" data-image_src="<?php echo esc_attr( $image_post_id ) ?>"
                             data-index="<?php echo esc_attr( $i ); ?>">
                            <img class="review-images" loading="lazy" src="<?php echo esc_url( $image_post_id ); ?>"
                                 alt="<?php echo esc_attr( $product_title ) ?>"/>
                        </div>
						<?php
					} else {
						printf( '<div class="reviews-images-item" data-image_src="%s" data-index="%s"><iframe class="review-images review-iframe" src="%s" frameborder="0" allowfullscreen></iframe></div>',
							esc_attr( $image_post_id ), esc_attr( $i ), esc_url( $image_post_id ) );
					}
				} else {
					?>
                    <div class="reviews-images-item" data-image_src="<?php echo esc_attr( $image_post_id ) ?>" data-index="<?php echo esc_attr( $i ); ?>">
                        <img class="review-images" loading="lazy" src="<?php echo esc_url( $image_post_id ); ?>" alt="<?php echo esc_attr( $product_title ) ?>"/>
                    </div>
					<?php
				}

			}
			$i ++;
		}
		?>
    </div>
    <div class="big-review-images">
        <div class="big-review-images-content"></div>
        <span class="wcpr-close"></span>
        <div class="wcpr-rotate">
            <input type="hidden" class="wcpr-rotate-value" value="0">
            <span class="wcpr-rotate-left wcpr_rotate-rotate-left-circular-arrow-interface-symbol" title="<?php esc_attr_e( 'Rotate left 90 degrees', 'woo-photo-reviews' ) ?>"></span>
            <span class="wcpr-rotate-right wcpr_rotate-rotating-arrow-to-the-right" title="<?php esc_attr_e( 'Rotate right 90 degrees', 'woo-photo-reviews' ) ?>"></span>
        </div>
		<?php
		if ( count( $image_post_ids ) > 1 ) {
			?>
            <span class="wcpr-prev"></span>
            <span class="wcpr-next"></span>
			<?php
		}
		?>
    </div>
	<?php
}
printf( '</div>' );
?>