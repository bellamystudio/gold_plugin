<?php

// Load these post type into the system


add_action( 'init', 'setup_gold' );
add_action( 'admin_init', 'add_setup_gold_admin');

function setup_gold() 
{
	write_log("ONCE--------------");
}

function add_setup_gold_admin()
{
	
}
