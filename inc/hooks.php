<?php 

add_action( 'admin_bar_menu', 'wpfap_toolbar', 999 );
function wpfap_toolbar($wp_admin_bar){
	
	if(is_singular()){
		$args = array(
			'id'    	=> 'wpfap_toolbar',
			'parent' 	=> 'top-secondary',
			'title' 	=> 'Front Admin',
			'href'  	=> '#',
			'meta'  	=> array( 'class' => 'ab-item' )
		);
		$wp_admin_bar->add_menu( $args );
	}
	
}