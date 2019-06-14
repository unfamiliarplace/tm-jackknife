<?php

/*
 * =============================================================================
 * MJK: 4 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our video tags; our subtemplates
 * Template: Our video post
 *
 * Single post template 84 / Based on Single post template 9
 * =============================================================================
 */

//get the global sidebar position from td_single_template_vars.php

locate_template('includes/wp_booster/td_single_template_vars.php', true);

get_header();

global $loop_sidebar_position, $td_sidebar_position, $post;

$td_mod_single = new td_module_single($post);

?>
    <article id="post-<?php echo $td_mod_single->post->ID;?>" class="<?php echo join(' ', get_post_class('td-post-template-9'));?> td-container-wrap" <?php echo $td_mod_single->get_item_scope();?>>
        <div class="td-container">
            <div class="td-crumb-container"><?php echo td_page_generator::get_single_breadcrumbs($td_mod_single->title); ?></div>
            <div class="td-post-featured-video">

				<?php

				// MJK: 1/4: Run this through our tags instead of echoing it
				if (!empty(td_global::$load_featured_img_from_template)) {
					$base = td_global::$load_featured_img_from_template;
				} else {
					$base = 'td_1068x0';
				}
				echo MJKNPEnhanceAPI::video_tags($base, $td_mod_single);

				?>

            </div>
        </div>

        <div class="td-container">
            <div class="td-pb-row">
				<?php

				//the default template
				switch ($loop_sidebar_position) {
					default:
						?>
                        <div class="td-pb-span8 td-main-content" role="main">
                            <div class="td-ss-main-content">
	                            <?php

	                            // MJK: 2/4: Require our template instead
	                            require_once 'loop-single-84.php';

	                            comments_template('', true);
	                            ?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar" role="complementary">
                            <div class="td-ss-main-sidebar">
								<?php get_sidebar(); ?>
                            </div>
                        </div>
						<?php
						break;

					case 'sidebar_left':
						?>
                        <div class="td-pb-span8 td-main-content <?php echo $td_sidebar_position; ?>-content" role="main">
                            <div class="td-ss-main-content">
	                            <?php

	                            // MJK: 3/4: Require our template instead
	                            require_once 'loop-single-84.php';

	                            comments_template('', true);
	                            ?>
                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar" role="complementary">
                            <div class="td-ss-main-sidebar">
								<?php get_sidebar(); ?>
                            </div>
                        </div>
						<?php
						break;

					case 'no_sidebar':
						?>
                        <div class="td-pb-span12 td-main-content" role="main">
                            <div class="td-ss-main-content">
	                            <?php

	                            // MJK: 4/4: Require our template instead
	                            require_once 'loop-single-84.php';

	                            comments_template('', true);
	                            ?>
                            </div>
                        </div>
						<?php
						break;

				}
				?>
            </div> <!-- /.td-pb-row -->
        </div> <!-- /.td-container -->
    </article> <!-- /.post -->

<?php

get_footer();
