<?php

/*
 * =============================================================================
 * MJK: 1 modification | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our author boxes
 * Template: Our default one, using a featured image
 *
 * Single post template 82 / Based on Single post template 4
 * =============================================================================
 */

if (have_posts()) {
	the_post();

	$td_mod_single = new td_module_single($post);

	?>

	<?php echo $td_mod_single->get_social_sharing_top(); ?>

    <div class="td-post-content">
		<?php echo $td_mod_single->get_content();?>
    </div>


    <footer>
		<?php echo $td_mod_single->get_post_pagination();?>
		<?php echo $td_mod_single->get_review();?>

        <div class="td-post-source-tags">
			<?php echo $td_mod_single->get_source_and_via();?>
			<?php echo $td_mod_single->get_the_tags();?>
        </div>

		<?php echo $td_mod_single->get_social_sharing_bottom();?>
		<?php echo $td_mod_single->get_next_prev_posts();?>
		<?php
            // MJK: 1/1: Use our author boxes instead
		    echo MJKNPEnhanceAPI::all_author_boxes($post);
        ?>
		<?php echo $td_mod_single->get_item_scope_meta();?>
    </footer>

	<?php echo $td_mod_single->related_posts();?>

	<?php
} else {
	//no posts
	echo td_page_generator::no_posts();
}
