<?php

/*
 * =============================================================================
 * MJK: 12 modifications | Last updated 2017-09-16 | Newspaper v8.1
 * Purpose: Our contributions loop; our author box
 * =============================================================================
 */

/*  ----------------------------------------------------------------------------
    the author template
 */



get_header();

//set the template id, used to get the template specific settings
$template_id = 'author';

//prepare the loop variables
global $loop_module_id, $loop_sidebar_position, $part_cur_auth_obj;
$loop_module_id = td_util::get_option('tds_' . $template_id . '_page_layout', 1); //module 1 is default
$loop_sidebar_position = td_util::get_option('tds_' . $template_id . '_sidebar_pos'); //sidebar right is default (empty)

// sidebar position used to align the breadcrumb on sidebar left + sidebar first on mobile issue
$td_sidebar_position = '';
if($loop_sidebar_position == 'sidebar_left') {
	$td_sidebar_position = 'td-sidebar-left';
}
//read the current author object - used by here in title and by /parts/author-header.php
$part_cur_auth_obj = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));


//set the global current author object, used by widgets (author widget)
td_global::$current_author_obj = $part_cur_auth_obj;

?>

    <div class="td-main-content-wrap td-container-wrap">
        <div class="td-container <?php echo $td_sidebar_position; ?>">
            <div class="td-crumb-container">
				<?php echo td_page_generator::get_author_breadcrumbs($part_cur_auth_obj); // generate the breadcrumbs ?>
            </div>
            <div class="td-pb-row">
				<?php

                // MJK: 1/12: Prepare the author box here, outside any layout
                $uid = $part_cur_auth_obj->ID;
                $author_box = MJKNPEnhanceAuthorBox::format_page_author_box($uid);

				switch ($loop_sidebar_position) {

					/*  ----------------------------------------------------------------------------
						This is the default option
						If you set the author template with the right sidebar the theme will use this part
					*/
					default:
						?>
                        <div class="td-pb-span8 td-main-content">
                            <div class="td-ss-main-content">
                                <div class="td-page-header">
                                    <h1 class="entry-title td-page-title">
                                        <span><?php echo $part_cur_auth_obj->display_name; ?></span>
                                    </h1>
                                </div>

								<?php
                                //load the author box located in - parts/page-author-box.php - can be overwritten by the child theme

                                    // MJK: 2/12: Comment out theme box & use ours
                                    //locate_template('parts/page-author-box.php', true);
                                    echo $author_box;
								?>

                                <!-- MJK: 3/12: Added this h2 block -->
                                <h2 itemprop="mainContentOfPage" class="entry-title td-page-title">Contributions</h2>

	                            <?php
                                    // MJK: 4/12: Merged and commented these out
                                    // locate_template('loop.php', true);
                                    // echo td_page_generator::get_pagination(); // the pagination

                                    // MJK: 5/12: Added the below
                                    $uid = $part_cur_auth_obj->ID;
                                    echo MJKNPEnhanceAuthor::format_all_credits($uid);
	                            ?>

                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar">
                            <div class="td-ss-main-sidebar">
								<?php get_sidebar(); ?>
                            </div>
                        </div>
						<?php
						break;



					/*  ----------------------------------------------------------------------------
						If you set the author template with sidebar left the theme will render this part
					*/
					case 'sidebar_left':
						?>
                        <div class="td-pb-span8 td-main-content <?php echo $td_sidebar_position; ?>-content">
                            <div class="td-ss-main-content">
                                <div class="td-page-header">
                                    <h1 class="entry-title td-page-title">
                                        <span><?php echo $part_cur_auth_obj->display_name; ?></span>
                                    </h1>
                                </div>

								<?php
								//load the author box located in - parts/page-author-box.php - can be overwritten by the child theme

                                    // MJK: 6/12: Comment out theme box & use ours
                                    //locate_template('parts/page-author-box.php', true);
                                    echo $author_box;
								?>

                                <!-- MJK: 7/12: Added this h2 block -->
                                <h2 itemprop="mainContentOfPage" class="entry-title td-page-title">Contributions</h2>

	                            <?php
                                    // MJK: 8/12: Merged and commented these out
                                    // locate_template('loop.php', true);
                                    // echo td_page_generator::get_pagination(); // the pagination

                                    // MJK: 9/12: Added the below
                                    $uid = $part_cur_auth_obj->ID;
                                    echo MJKNPEnhanceAuthor::format_all_credits($uid);
	                            ?>

                            </div>
                        </div>
                        <div class="td-pb-span4 td-main-sidebar">
                            <div class="td-ss-main-sidebar">
								<?php get_sidebar(); ?>
                            </div>
                        </div>
						<?php
						break;



					/*  ----------------------------------------------------------------------------
						If you set the author template with no sidebar the theme will use this part
					*/
					case 'no_sidebar':
						?>
                        <div class="td-pb-span12 td-main-content">
                            <div class="td-ss-main-content">
                                <div class="td-page-header">
                                    <h1 class="entry-title td-page-title">
                                        <span><?php echo $part_cur_auth_obj->display_name; ?></span>
                                    </h1>
                                </div>

								<?php
								//load the author box located in - parts/page-author-box.php - can be overwritten by the child theme

                                    // MJK: 10/12: Comment out theme box & use ours
                                    //locate_template('parts/page-author-box.php', true);
                                    echo $author_box;
								?>

                                <!-- MJK: 7/9: Added this h2 block -->
                                <h2 itemprop="mainContentOfPage" class="entry-title td-page-title">Contributions</h2>

	                            <?php
                                    // MJK: 11/12: Merged and commented these out
                                    // locate_template('loop.php', true);
                                    // echo td_page_generator::get_pagination(); // the pagination

                                    // MJK: 12/12: Added the below
                                    $uid = $part_cur_auth_obj->ID;
                                    echo MJKNPEnhanceAuthor::format_all_credits($uid);
	                            ?>

                            </div>
                        </div>
						<?php
						break;
				}
				?>
            </div> <!-- /.td-pb-row -->
        </div> <!-- /.td-container -->
    </div> <!-- /.td-main-content-wrap -->

<?php
get_footer();
