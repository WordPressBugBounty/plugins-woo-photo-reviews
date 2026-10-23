<?php
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! function_exists( 'vi_include_folder' ) ) {
	function vi_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		$sfiles = scandir( $path );
		if ( ! is_array( $sfiles ) ) {
			return;
		}
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename'];
					if ( ! empty( $ext_file['extension'] ) ) {
						if ( in_array( $ext_file['extension'], $ext ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}
if ( ! function_exists( 'woocommerce_version_check' ) ) {
	function woocommerce_version_check( $version = '3.0' ) {
		if ( ! defined( 'WC_VERSION' ) ) {
			return false;
		}

		return version_compare( WC_VERSION, $version, '>=' );
	}
}
if ( ! function_exists( 'villatheme_array_flatten' ) ) {
	function villatheme_array_flatten( $params , $allow_empty = true) {
		if (!is_array($params)){
			return !$allow_empty && !$params ? array() :array($params);
		}
		$result = array();
		foreach ($params as $val){
			if (!$allow_empty && !$val){
				continue;
			}
			$result = array_merge($result, villatheme_array_flatten($val));
		}
		return $result;
	}
}
if ( ! function_exists( 'viwcpr_set_time_limit' ) ) {
	function viwcpr_set_time_limit() {
		ini_set( 'max_execution_time', '3000' );
		ini_set( 'max_input_time', '3000' );
		ini_set( 'default_socket_timeout', '3000' );
		@set_time_limit( 0 );
	}
}
if ( ! function_exists( 'viwcpr_filter_review_image_ids' ) ) {
	/**
	 * Keep only WordPress attachment IDs for reviews-images meta.
	 *
	 * @param array|string $ids Comma-separated string or list of IDs.
	 * @return int[]
	 */
	function viwcpr_filter_review_image_ids( $ids ) {
		if ( is_string( $ids ) ) {
			$ids = explode( ',', $ids );
		}
		if ( ! is_array( $ids ) ) {
			return array();
		}
		$filtered = array();
		foreach ( $ids as $id ) {
			$id = absint( $id );
			if ( $id && 'attachment' === get_post_type( $id ) ) {
				$filtered[] = $id;
			}
		}

		return array_values( array_unique( $filtered ) );
	}
}
if ( ! function_exists( 'viwcpr_review_upload_token_state' ) ) {
	/**
	 * Raw token for the current upload_file() call. Empty until that call starts one.
	 *
	 * @param string|null $set New token for this upload, or null to read.
	 * @return string
	 */
	function viwcpr_review_upload_token_state( $set = null ) {
		static $token = '';
		if ( is_string( $set ) ) {
			$token = $set;
		}

		return $token;
	}
}
if ( ! function_exists( 'viwcpr_begin_review_upload_token' ) ) {
	/**
	 * Start a new token for one upload_file() call.
	 *
	 * @return string
	 */
	function viwcpr_begin_review_upload_token() {
		$token = wp_generate_password( 32, false, false );
		viwcpr_review_upload_token_state( $token );

		return $token;
	}
}
if ( ! function_exists( 'viwcpr_current_review_upload_token' ) ) {
	/**
	 * Token started by the latest upload_file() call in this request.
	 *
	 * @return string
	 */
	function viwcpr_current_review_upload_token() {
		return viwcpr_review_upload_token_state();
	}
}
if ( ! function_exists( 'viwcpr_stamp_review_attachment' ) ) {
	/**
	 * Mark an attachment created by the review upload flow.
	 *
	 * @param int $attachment_id Attachment ID.
	 */
	function viwcpr_stamp_review_attachment( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		$token         = viwcpr_current_review_upload_token();
		if ( ! $attachment_id || ! preg_match( '/^[A-Za-z0-9]{32}$/', $token ) ) {
			return;
		}
		update_post_meta( $attachment_id, '_wcpr_review_attachment', '1' );
		update_post_meta( $attachment_id, '_wcpr_upload_token', wp_hash( $token ) );
	}
}
if ( ! function_exists( 'viwcpr_filter_owned_review_image_ids' ) ) {
	/**
	 * Keep attachment IDs stamped with this upload token and not bound yet.
	 *
	 * @param array|string $ids   Comma-separated string or list of IDs.
	 * @param string       $token Raw upload token. Empty and non-matching values drop every ID.
	 * @return int[]
	 */
	function viwcpr_filter_owned_review_image_ids( $ids, $token ) {
		$token = is_string( $token ) ? $token : '';
		if ( ! preg_match( '/^[A-Za-z0-9]{32}$/', $token ) ) {
			return array();
		}
		$ids = viwcpr_filter_review_image_ids( $ids );
		if ( ! $ids ) {
			return array();
		}
		$token_hash = wp_hash( $token );
		$owned      = array();
		foreach ( $ids as $id ) {
			if ( '1' !== (string) get_post_meta( $id, '_wcpr_review_attachment', true ) ) {
				continue;
			}
			$stored = (string) get_post_meta( $id, '_wcpr_upload_token', true );
			if ( '' === $stored || ! hash_equals( $stored, $token_hash ) ) {
				continue;
			}
			$bound = (string) get_post_meta( $id, '_wcpr_bound_comment', true );
			if ( '' !== $bound && '0' !== $bound ) {
				continue;
			}
			$owned[] = $id;
		}

		return $owned;
	}
}