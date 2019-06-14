<?php

/**
 * A page template for an issue.
 */
class MJKVI_PageIssue extends MJKVI_Page {

	private $vol;
	private $iss;

	/*
	 * =========================================================================
	 * Constants
	 * =========================================================================
	 */
	
    // Classes for the various elements
    const cl_embed = 'mjk_vi_iss_embed';
    const cl_notes = 'mjk_vi_iss_notes';
    const cl_website = 'mjk_vi_iss_website';
    const cl_posts = 'mjk_vi_iss_posts';
    const cl_back = 'mjk_vi_back';

    // Formatting in case Newspaper is unavailable
    const cl_cat_heading = 'mjk_vi_cat_heading';
    const cl_cat_grid = 'mjk_vi_cat_grid';
    const cl_cat_row = 'mjk_vi_cat_row';
    const cl_post = 'mjk_vi_post';
    const cl_post_panel = 'mjk_vi_post_panel';
    const cl_post_thumb = 'mjk_vi_post_thumb';
	const cl_post_title = 'mjk_vi_post_title';

    // This is just sourced from the Front Page post block settings
    const cl_block = 'tm-fp-cat-block';

	/*
	 * =========================================================================
	 * Set up
	 * =========================================================================
	 */

	/**
	 * Parse the volume and issue numbers and load them -- or die if invalid.
	 *
	 * @param int|string $n_vol
	 * @param int|string $n_iss
	 */
	function __construct($n_vol, $n_iss) {
        $this->vol = MJKVIAPI::get_vol_by_num($n_vol);
        if (empty($this->vol)) wp_die($this->die_vol_fail($n_vol));

        $this->iss = $this->vol->get_issue_by_num($n_iss);
        if (empty($this->iss)) wp_die($this->die_iss_fail($n_vol, $n_iss));
    }

	/**
	 * Return an appropriate title for the page.
	 * @return string
	 */
	function get_page_title(): string {
        $cap = ($this->iss->is_summer) ? false : true;
        return sprintf('%s, %s', $this->vol->get_name(), $this->iss->get_name($cap));
    }


	/*
	 * =========================================================================
	 * Formatting
	 * =========================================================================
	 */

	/**
	 * Return the formatted html for the entire page.
	 *
	 * @param bool $breadcrumbs
	 * @return string
	 */
	function format(bool $breadcrumbs=true): string {
        $html = '';
        $html .= $this->format_header();
        $html .= $this->format_body($breadcrumbs);
        $html .= $this->format_footer();
        return (!empty($html)) ? do_shortcode($html) : '';
    }
	/**
	 * Return the formatted body html for the page.
	 *
	 * @param bool $breadcrumbs
	 * @return string
	 */
	function format_body(bool $breadcrumbs=true): string {
        $html = '';
        $html .= $this->format_css();
        $html .= $this->format_title();
        if ($breadcrumbs) $html .= $this->format_breadcrumbs($this->vol, $this->iss);

        // Use a "this is in the future" notice if necessary
        if ($this->iss->in_future()) {
                $html .= $this->format_future_notice();

        } else {
                $html .= $this->format_notes();
                $html .= $this->format_embed();
                $html .= $this->format_website();

                $ws = $this->iss->get_website();

                // Format the posts blocks only if the issue is from this website
                if (!empty($ws) && $ws->main) $html .= $this->format_posts();
        }

        return $html;
    }


	/*
	 * =========================================================================
	 * Top portion
	 * =========================================================================
	 */

	/**
	 * Return the formatted html for the visible title.
	 *
	 * @return string
	 */
	private function format_title(): string {
        $cap = ($this->iss->is_summer) ? false : true;
        return sprintf('<h1>%s, %s (%s)</h1>', $this->vol->get_name(),
                $this->iss->get_name($cap), $this->iss->format_date());
    }

	/**
	 * Return formatted html for the notice that this issue is in the future.
	 *
	 * @return string
	 */
	private function format_future_notice(): string {
        return '<h4>This issue has not yet been published.' .
               'Check here again after the given date.</h4>';
    }

	/**
	 * Return the formatted html for any notes.
	 *
	 * @return string
	 */
	private function format_notes(): string {
        if (!empty($this->iss->notes)) {
        	return sprintf('<p class="%s">%s</p>',
		        self::cl_notes, $this->iss->notes);
        } else {
        	return '';
        }
    }


	/*
	 * =========================================================================
	 * Embed
	 * =========================================================================
	 */

	/**
	 * Return formatted html for the embed, whatever it may be.
	 *
	 * @return string
	 */
	private function format_embed(): string {

		// Short-circuit summer issues; they have no print edition
		if ($this->iss->is_summer) {
			return '<br>';
		}

		// The AO embed is 384 high by default
		$is_embed = $this->iss->issuu_embed();
		$ao_embed = $this->iss->archiveorg_embed();

		// Discern which layout to use

		// Both embeds
		if (!empty($is_embed) && !empty($ao_embed)) {
			$html = $this->format_both_embeds($is_embed);

			// No embeds
		} elseif (empty($is_embed) && empty($ao_embed)) {
			$html = $this->format_no_embeds();

			// Issuu
		} elseif(empty($is_embed)) {
			$html = $this->format_one_embed($ao_embed);

			// Archiveorg
		} else {
			$html = $this->format_one_embed($is_embed);
		}

		// Wrap in div tags
		return sprintf('<div class="%s">%s</div>', self::cl_embed, $html);
	}

	/**
	 * Return formatted html for one embed (whether it be issuu or archiveorg).
	 *
	 * @param array $embed
	 * @return string
	 */
	private function format_one_embed(array $embed): string {
        $html = '';

        $html .= '[vc_row][vc_column width="1/1"]';
        $html .= $embed['html'];
        $html .= '[/vc_column][/vc_row]';

        return $html;
    }

	/**
	 * Return formatted html for cases where both suppliers have been provided.
	 *
	 * @param array $issuu_embed
	 * @return string
	 */
	private function format_both_embeds(array $issuu_embed): string {
        $html = '';

        $html .= '[vc_row][vc_column width="1/1"]';
        $html .= $issuu_embed['html'];

        // Don't do both embeds (costly and ugly) but just notify that AO is also there
        $html .= sprintf('<h6>This issue is also available on Archive.org: '
                . '<a href="%s"  title="%s, %s on Archive.org">/details/%s</a></h6>',
                $this->iss->archiveorg_url, $this->vol->get_name(),
                $this->iss->get_name(), $this->iss->archiveorg_id());
        $html .= '[/vc_column][/vc_row]';

        return $html;
    }

	/**
	 * Return formatted html for if neither embed exists.
	 *
	 * @return string
	 */
	private function format_no_embeds(): string {
        return '<h4>The print edition of this issue is not currently' .
               ' available online.</h4>';
    }


	/*
	 * =========================================================================
	 * Website
	 * =========================================================================
	 */

	/**
	 * Return formatted html for the website, if any.
	 *
	 * @return string
	 */
	private function format_website(): string {
        $ws = $this->iss->get_website();

        // Only show if there's a non-main website and this issue is available on it
        if (!empty($ws) && !$ws->main && $this->iss->on_site) {

                // If a specific url for this issue has been given, link to that
                if (!empty($this->iss->posts_url)) {
                        $link_html = sprintf(
                        	'%1$s website, specifically <a href="%2$s"' .
	                        ' title="%1$s website / %3$s, %4$s">here</a>',
                            $ws->name, $this->iss->posts_url,
	                        $this->vol->get_name(), $this->iss->get_name());

                // Otherwise give the same general link the volume uses
                } else {
                        $link_html = sprintf(
                        	'<a href="%1$s" title="%2$s website">' .
                                     '%2$s website</a>', $ws->link, $ws->name);
                }

                // Wrap in a p tag + the intro blather
                return sprintf(
                	'<p class="%s">Articles from this issue can be found' .
	                'on the %s.</p>', self::cl_website, $link_html);
        }

        return '';
    }


	/*
	 * =========================================================================
	 * Posts
	 * =========================================================================
	 */

	/**
	 * Return formatted html for the post blocks.
	 *
	 * @return string
	 */
	private function format_posts(): string {

        // $cat_to_posts is an array of category slugs to posts from that category
        $cat_to_posts = $this->iss->get_posts();

        $html = '';
        foreach($cat_to_posts as $cat_slug => $cat_posts) {
                $html .= $this->format_cat($cat_slug, $cat_posts);
        }

        return sprintf('<div class="%s">%s</div>', self::cl_posts, $html);
    }

	/**
	 * Format an indivivdual category.
	 *
	 * @param string $slug
	 * @param WP_Post[] $posts
	 * @return string
	 */
	private function format_cat(string $slug, array $posts): string {

        // Short-circuit if no posts
        if (empty($posts)) return '';

        // Get the category object
        $cat = get_category_by_slug($slug);

        if (JKNAPI::theme_dep_met('newspaper')) {
	        $html = '[vc_row][vc_column width="1/1" el_class="home_cat_blocks"]';

	        // Format post IDs properly for the Newspaper block shortcode
	        $post_ids = array_map(function (WP_Post $p): string { return $p->ID; },
		        $posts);

	        // These are the parameters for an NP block shortcode
	        $html .= $this->format_td_block([
		        'number'    => $slug == 'opinion' ? 45 : 44, // Opinion has no thumb
		        'post_ids'  => $post_ids,
		        'name'      => $cat->name,
		        'slug'      => $slug,
		        'colour'    => MJKCommonTools::colour($slug)
	        ]);

	        $html .= '[/vc_column][/vc_row]';

        } else {
        	$heading = sprintf('<h3 class="%s">%s</h3>',
		        self::cl_cat_heading, $cat->name);

        	$grid = JKNLayouts::grid($posts, 3, [$this, 'format_post'],
		        self::cl_cat_grid, self::cl_cat_row, self::cl_post);

        	$html = $heading . $grid;
        }

        // Wrap in an appropriate div
        return sprintf('<div class="mjk_vol_iss_posts">%s</div>', $html);
    }

	/**
	 * Return a formatted tagDiv (Newspaper) block for the given posts.
	 * Supply 'number', 'post_ids', 'name', 'slug', and 'colour' as arguments.
	 *
	 * @param array $args
	 * @return string
	 */
    private function format_td_block(array $args): string {
    	return sprintf(
    		'[td_block_%1$s td_column_number="2" limit="7"' .
		    ' td_ajax_preloading="preload" ajax_pagination="load_more"' .
		    ' post_ids="%2$s" custom_title="%3$s" custom_url="/%4$s"' .
	        ' header_color="#%5$s" header_text_color="#%5$s" el_class="%6$s"]',

		    $args['number'], implode(',', $args['post_ids']), $args['name'],
		    $args['slug'], $args['colour'], self::cl_block);
    }

	/**
	 * Return a formatted individual post if that Newspaper is inactive.
	 *
	 * @param WP_Post $p
	 * @return string
	 */
	function format_post(WP_Post $p): string {

		$thumb = get_the_post_thumbnail($p);
		$title = $p->post_title;
		$url = get_post_permalink($p->ID);

		$thumb_h = sprintf('<div class="%s">%s</div>',
			self::cl_post_thumb, $thumb);

		$title_h = sprintf('<div class="%s"><h4>%s</h4></div>',
			self::cl_post_title, $title);

		return sprintf('<div class="%s"><a href="%s" title="%s">%s</a></div>',
			self::cl_post_panel, $url, $title, $title_h . $thumb_h);
	}


	/*
	 * =========================================================================
	 * CSS
	 * =========================================================================
	 */

	/**
	 * Return the formatted CSS tag.
	 *
	 * @return string
	 */
    private function format_css(): string {

    	$main = MJKCommonTools::colour('main');

        return JKNCSS::tag('
            div.'.self::cl_embed.' {
                margin-bottom: 20px;
            }
    
            div.'.self::cl_embed.' .wpb_wrapper div {
                margin: auto;
            }
    
            div.'.self::cl_embed.' {
                text-align: center;
            }
    
            p.'.self::cl_notes.' {
                font-family: \'Roboto\', sans-serif;
                margin-top: 25px;
            }
    
            p.'.self::cl_back.' {
                display: block;
                font-size: small;
                font-family: \'Roboto\', sans-serif;
            }
    
            p.'.self::cl_back.' a:hover {
                text-decoration: underline;
            }
    
            p.'.self::cl_website.' {
                font-style: italic;
            }
            
			.'.self::cl_cat_grid.' {
				padding-bottom: 10px;
				margin-bottom: 30px;
				border-bottom: 1px solid #222;
			}
			
			.'.self::cl_cat_row.' {
				margin-bottom: 25px;
			}
			
			.'.self::cl_post_title.' h4 {
				margin-top: 0px;
				margin-bottom: 0px;
			}
			
			.'.self::cl_post_title.' {
				margin-bottom: 10px;
			}
			
			.'.self::cl_post_thumb.' {
				height: 200px;
			}
			
			.'.self::cl_post_panel.' {
				border: 1px solid '.JKNColours::hex_to_rgba($main, 0.2).';
				border-radius: 10px;
				padding: 10px;
				vertical-align: middle;
			}
			
			.'.self::cl_post_panel.':hover {
				background: '.JKNColours::hex_to_rgba($main, 0.1).';
			}            
        ');
    }
}
