<pre>
<?php
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// SETUP
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	require_once 'config.php';
	require_once 'classes/database.php';
	
	// setup DATABASE
	$db = new Database( WP_HOST, WP_NAME, WP_USER, WP_PASS );
	
	// clean up posts a bit
	$result = $db->query('DELETE FROM '.WP_PREFIX.'posts WHERE post_status = "auto-draft";');
	echo ($result ? "Deleted auto-drafts \n" : "FAILED: \n".mysql_error()."\n");

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// RESET IDs
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// get posts (sorted by date)	
	$posts = $db->get('SELECT ID, post_date_gmt FROM wp_posts ORDER BY post_date_gmt ASC;');
	
	// parse through posts
	foreach( $posts as $key=>$post ) {
		// 
		$post_old_id = $post["ID"];
		$post_new_id = $key+1;
		
		$post["new_id"] = $post_new_id;
		print_r($post);
		
		// here should go a prefix for the id's, so we won't have duplicate errors
		
		// reset id
		$result = $db->query('UPDATE '.WP_PREFIX.'posts SET ID='.$post_new_id.' WHERE ID='.$post_old_id.';');
		echo ($result ? "Updated ".WP_PREFIX."posts for #".$post_old_id." \n" : "FAILED: \n".mysql_error()."\n");
		
		// update wp_postmeta new id
		$result = $db->query('UPDATE '.WP_PREFIX.'postmeta SET post_id='.$post_new_id.' WHERE post_id='.$post_old_id.';');
		echo ($result ? "Updated ".WP_PREFIX."postmeta for #".$post_old_id." \n" : "FAILED: \n".mysql_error()."\n");

		// update wp_term_relationships with new id
		$result = $db->query('UPDATE '.WP_PREFIX.'term_relationships SET object_id='.$post_new_id.' WHERE object_id='.$post_old_id.';');
		echo ($result ? "Updated ".WP_PREFIX."term_relationships for #".$post_old_id." \n" : "FAILED: \n".mysql_error()."\n");
		
		// update revisions
		$revisions = $db->get_rows('SELECT * FROM '.WP_PREFIX.'posts WHERE post_type="revision" AND post_name="'.$post_old_id.'-revision"');
		if ( $revisions > 0 ) {
			$result = $db->query('UPDATE '.WP_PREFIX.'posts SET post_name="'.$post_new_id.'-revision" WHERE post_type="revision" AND post_name="'.$post_old_id.'-revision";');
			echo ($result ? "Updated revisions for #".$post_old_id." \n" : "FAILED: \n".mysql_error()."\n");
		}
		
		// here should go attachment updates
	}
	
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Reset table Auto-Increment
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// get total rows
	$increment = $db->get('SELECT COUNT(*) AS count FROM '.WP_PREFIX.'posts;');
	$increment = $increment["count"]+1;
	
	$result = $db->query('ALTER TABLE '.WP_PREFIX.'posts AUTO_INCREMENT='.$increment.';');
	echo ($result ? "Auto-Increment reset to ".$increment." \n" : "FAILED: \n".mysql_error()."\n");
	
?>
</pre>