<?php
add_action( 'rest_api_init', 'custom_api_get_post_slug' );   

function custom_api_get_post_slug() {
    register_rest_route( 'wp/v2', '/get-post/(?P<slug>[a-zA-Z0-9-]+)', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'custom_api_get_post_slug_callback',
    ));
}

function custom_api_get_post_slug_callback( $request ) {
	$headers = apache_request_headers();
	
    $posts_data = array();
    $paged = $request->get_param( 'page' );
    $paged = ( isset( $paged ) || ! ( empty( $paged ) ) ) ? $paged : 1; 
	$slug = $request['slug'];
		
	if ( isset( $slug ) || ! ( empty( $slug ) ) ) {
		$arrs = array(
			'paged' => $paged,
            'post__not_in' => get_option( 'sticky_posts' ),
            'posts_per_page' => 100,            
            'post_type' => array( 'post' ),
			'post_status' => 'publish',
			'name' => $slug			

        );
		
		$posts = get_posts($arrs);
	
		if (count($posts)<=0) {
			return new WP_REST_Response(null, 404);
		}
		foreach( $posts as $post ) {
			$id = $post->ID; 
			$post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;
			$relateds = get_field('related_post',$id);
			$relateds_data = [];
			if ($relateds){
				foreach($relateds as $related) {
					$relateds_data[] = (object) array(
						'title' => $related->post_title,
						'excerpt' => get_the_excerpt($id),
						'slug' => $related->post_name,
						'image' => ( has_post_thumbnail( $related->ID ) ) ? get_the_post_thumbnail_url( $related->ID ) : null
					);
				}
			}
			$posts_data[] = (object) array( 
				'id' => $id, 
				'slug' => $post->post_name, 
				'title' => $post->post_title,
				'content' => $post->post_content,
				'img_src' => $post_thumbnail,
				'related' => $relateds_data,
				'date' => date("d M y" ,strtotime(get_the_date('Y-m-d',$id)))
			);
		}
		
	} else {
		return new WP_REST_Response(null, 404);
	}
    return $posts_data;                   
} 

