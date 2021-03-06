<?php

namespace WeDevs\PM\Core\Permissions;

use WeDevs\PM\Core\Permissions\Abstract_Permission;
use WeDevs\PM\Comment\Models\Comment;
use WP_REST_Request;

class Edit_Comment extends Abstract_Permission {
   
    public function check() {
        $id = $this->request->get_param( 'comment_id' );
        $project_id = $this->request->get_param( 'project_id' );
        $user_id = get_current_user_id();

        if ( $user_id ) {

        	if ( $project_id && pm_has_project_managing_capability( $project_id, $user_id ) ) {
	            return true;
	        }
	        if ( Comment::find( $id )->created_by == $user_id  ){
	        	return true;
	        }

        }

        return new \WP_Error( 'Comment', __( "You have no permission.", "wedevs-project-manager" ) );
    }
}