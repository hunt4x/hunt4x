<?php
function wpst_ajax_load_more_swipe() {

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
		exit;
	}

	$ads_displaying_frequency = get_theme_mod( 'wpst_ads_displaying_frequency', 5 );

	$args                   = json_decode( stripslashes( $_POST['query'] ), true );
	$args['paged']          = $_POST['page'] + 1;
	$args['max_page']       = $_POST['maxpage'];
	$args['post_type']      = 'post';
	$args['post_status']    = 'publish';
	$args['posts_per_page'] = $ads_displaying_frequency;
	$args['orderby']        = 'date';
	$args['order']          = 'DESC';
	// if( $_POST['random_posts'] ){
	// $args['post__not_in'] = array(implode(",", $_POST['firstPostIDs']));
	// }

	query_posts( $args );

	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'ajax_load_more_swipe_eval_1' ) );
		endwhile;
		eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'ajax_load_more_swipe_eval_2' ) );
	endif;
	die;
}
eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'ajax_load_more_swipe_eval_3' ) );
eval( WPSCORE()->eval_product_data( WPSCORE()->get_installed_theme( 'sku' ), 'ajax_load_more_swipe_eval_4' ) );
