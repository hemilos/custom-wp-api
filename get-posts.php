<?php 
add_action( 'rest_api_init', 'custom_api_get_all_posts' );   

function custom_api_get_all_posts() {
    register_rest_route( 'wp/v2', '/get-post', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'custom_api_get_all_posts_callback',
    ));
}

function custom_api_get_all_posts_callback( $request ) {
	$headers = apache_request_headers();
	
    $posts_data = array();
    $paged = $request->get_param( 'page' );
    $paged = ( isset( $paged ) || ! ( empty( $paged ) ) ) ? $paged : 1; 
	
	$lang = $headers['App-Locale'];
	$lang = ( isset( $lang ) || ! ( empty( $lang ) ) ) ? $lang : 'en';
	$market = $headers['App-Market'];
	$market = ( isset( $market ) || ! ( empty( $market ) ) ) ? $market : 'eu';
	
	
	$arrs = array(
		'paged' => $paged,
		'post__not_in' => get_option( 'sticky_posts' ),
		'posts_per_page' => 100,            
		'post_type' => array( 'post' ),
		'post_status' => 'publish',
		'meta_query' => array(
			'relation'	=> 'AND',
			array(
				'key'	=>	'lang',
				'value'	=>	$lang,
				'compare'	=>	'='
			),
			'relation'	=> 'AND',
			array(
				'key'	=>	'market',
				'value'	=>	$market,
				'compare'	=>	'LIKE'
			)
		)
	);

	$posts = get_posts($arrs);
	if (count($posts)<=0){
		return new WP_REST_Response(null, 204);
	}

	foreach( $posts as $post ) {
		$id = $post->ID; 
		$post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;
		$L = get_field('lang',$id);
		$M = get_field('market',$id);
		$posts_data[] = (object) array( 
			'id' => $id, 
			'slug' => $post->post_name, 
			'title' => $post->post_title,
			'excerpt' => get_the_excerpt($id),
			'img_src' => $post_thumbnail
		);
	}
	return $posts_data;                   
} 

