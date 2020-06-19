<?php
add_action( 'rest_api_init',  'register_routes');

function register_routes() {
	register_rest_route( 'wp/v2', '/preview/(?P<id>[a-zA-Z0-9-]+)', array(
			'methods'         		=> WP_REST_Server::READABLE,
			'callback'        		=> 'get_preview',
		) 
	);
}

function get_preview($request){
        // use the helper methods to get the parameters
        $id = $request['id'];
        // Only return the newest

        //$latest_revision = wp_get_post_revision($id);
		$latest_revision = get_post($id);
		$post_thumbnail = ( has_post_thumbnail( $latest_revision->post_parent) ) ? get_the_post_thumbnail_url( $latest_revision->post_parent) : null;
		$relateds = get_field('related_post',$latest_revision->post_parent);
		$relateds_data = [];


        if ($latest_revision) {
            if ( empty($latest_revision) ) {
                return new WP_REST_Response(null, 404);
            }
			if ($relateds){
				foreach($relateds as $related) {
					$relateds_data[] = (object) array(
						'title' => $related->post_title,
						'excerpt' => get_the_excerpt($related->ID),
						'slug' => $related->post_name,
						'image' => ( has_post_thumbnail( $related->ID ) ) ? get_the_post_thumbnail_url( $related->ID ) : null
					);
				}
			}

			$post_data[] = (object) array( 
				'id' => $id, 
				'slug' => get_post_field('post_name', $id),
				'title' => get_post_field('post_title', $id),
				'content' => get_post_field('post_content', $id),
				'img_src' => $post_thumbnail,
				'related' => $relateds_data,
				'date' => date("d M y" ,strtotime(get_the_date('Y-m-d',$id)))
			);
			
            return rest_ensure_response($post_data);

        } else {
            return new WP_REST_Response(null, 404);
        }
    }
