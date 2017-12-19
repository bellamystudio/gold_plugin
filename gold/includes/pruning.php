<?php

/**
	 * Uses transient instead of cronjob, will run on wp call in frontend AND backend, every 30 seconds (transient)
	 */
	function prune_posts($forced = false) {
		write_log("PRUNE: Starting");
		$lastrun = get_transient('auto-prune-posts-lastrun');
		$i_delete = 0;
	    $force_delete = 0; // Set this to 1 and will will not send the post to the bin.
		
		
		if ($forced || false === $lastrun) {

					$period_php = 		cs_get_option("prune_feeds"); // Will be in format so strtotime can handle this [int][space][string] example: "4 day" or "5 month"
					write_log("PRUNE: Timings " . $period_php);
					// Get all posts for this category
					//$myposts = get_posts('category=' . $cat_id.'&post_type='.$the_type.'&numberposts=-1');

						// Do only the last 50 (by date, for 1 cat)
						$myposts = get_posts('post_type=metal_prices&numberposts=50&order=ASC&orderby=post_date');
					
						foreach ($myposts AS $post) {
							$post_date_plus_visibleperiod = strtotime($post->post_date . " +" . $period_php);
							$now = strtotime("now");

							if ($post_date_plus_visibleperiod < $now) {
								// GOGOGO !
								$i_delete++;
								delete_post_and_attachments($post->ID, $force_delete);
	
									$body = "Deleting post ID : ".$post->ID. "\n";
									$body .= "Post title : ".$post->post_title. "\n";
									$body .= "Settings (Delete or Trash) : ".( ($force_delete) ? 'Delete' : 'Trash' ). "\n";
									//wp_mail("ian@digitalmovement.co.uk",'Plugin auto prune posts notification',$body);
									write_log('PRUNE: '.$body); 									
							 }
						} // End of For loop
			set_transient('auto-prune-posts-lastrun', 'lastrun: '.time(),300); // 300 seconds default
		}
	}

	/**
	 * Actually deletes post and its attachments
	 */
	 
	 function delete_post_and_attachments($post_id, $force_delete) {
		$atts = get_children(array (
         'post_parent' => $post_id,
         'post_status' => 'inherit',
         'post_type' => 'attachment'
         ));
         if ($atts) {
         	foreach ($atts as $att) {
         		// Deletes this attachment
         		wp_delete_attachment($att->ID, $force_delete);
         	}
         }

         // Now delete post
         wp_delete_post($post_id, $force_delete);
	}

