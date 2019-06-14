<?php

/*
 * =============================================================================
 * MJK: 3 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our subtitle; our meta info; our author boxes
 * Template: Our no featured image post
 *
 * Single post template 83 / Based on single post template 5
 * =============================================================================
 */

if (have_posts()) {
	the_post();

	$td_mod_single = new td_module_single($post);
	?>

    <article id="post-<?php echo $td_mod_single->post->ID;?>" class="<?php echo join(' ', get_post_class());?>" <?php echo $td_mod_single->get_item_scope();?>>
        <div class="td-post-header">

			<?php echo $td_mod_single->get_category(); ?>

            <header class="td-post-title">
				<?php echo $td_mod_single->get_title();?>

                <?php
                    // MJK: 1/3: Use our subtitle instead
                    echo MJKNPEnhanceAPI::subtitle($post);
                ?>


                <div class="td-module-meta-info">
	                <?php
                        // MJK: 2/3: Use our meta info
                        echo MJKNPEnhanceAPI::all_credits($post);
                        echo MJKNPEnhanceAPI::date($post);
	                ?>

					<?php echo $td_mod_single->get_comments();?>
					<?php echo $td_mod_single->get_views();?>
                </div>

            </header>

        </div>

		<?php echo $td_mod_single->get_social_sharing_top();?>


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
                // MJK: 3/3: Use our author boxes instead
                echo MJKNPEnhanceAPI::all_author_boxes($post);
	        ?>
			<?php echo $td_mod_single->get_item_scope_meta();?>
        </footer>

    </article> <!-- /.post -->

	<?php echo $td_mod_single->related_posts();?>

	<?php
} else {
	//no posts
	echo td_page_generator::no_posts();
}
