<?php

/*
 * =============================================================================
 * MJK: 7 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our image; our subtitle; our meta info; our subtemplates
 * Template: Our default one, using a featured image
 *
 * Single post template 82 / Based on Single post template 4
 * =============================================================================
 */

//get the global sidebar position from td_single_template_vars.php

locate_template('includes/wp_booster/td_single_template_vars.php', true);

get_header();

global $loop_sidebar_position, $td_sidebar_position, $post;

$td_mod_single = new td_module_single($post);

?>
    <article id="post-<?php echo $td_mod_single->post->ID;?>" class="<?php echo join(' ', get_post_class('td-post-template-4'));?> td-container-wrap" <?php echo $td_mod_single->get_item_scope();?>>
        <div class="td-post-header td-container">
            <div class="td-crumb-container"><?php echo td_page_generator::get_single_breadcrumbs($td_mod_single->title); ?></div>
            <div class="td-post-header-holder td-image-gradient">

	            <?php
                    // MJK: 1/7: Our image and generic featured image title
                    $img_tag = MJKNPEnhanceAPI::image('td_1068x0', $td_mod_single);
                    echo MJKNPEnhanceAPI::generic_featured_image_title($img_tag);
	            ?>

                <header class="td-post-title">
					<?php echo $td_mod_single->get_category(); ?>
					<?php echo $td_mod_single->get_title();?>

	                <?php
                        // MJK: 2/7: Our subtitle instead
                        echo MJKNPEnhanceAPI::subtitle($post);
	                ?>


                    <div class="td-module-meta-info">
	                    <?php
                            // MJK: 3/7: Our meta info instead
                            echo MJKNPEnhanceAPI::all_credits($post);
                            echo MJKNPEnhanceAPI::date($post);
	                    ?>
						<?php echo $td_mod_single->get_comments();?>
						<?php echo $td_mod_single->get_views();?>
                    </div>

	                <?php
                        // MJK: 4/7: Show a caption for the featured image
                        echo MJKNPEnhanceAPI::featured_image_caption($post);
                    ?>

                </header>
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

                                // MJK: 5/7: Require our template instead
                                require_once 'loop-single-82.php';

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

								// MJK: 6/7: Require our template instead
								require_once 'loop-single-82.php';

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

								// MJK: 7/7: Require our template instead
								require_once 'loop-single-82.php';

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
