<?php

/**
 * MJK: 6 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Get posts from the caller (instead of globals); our modules
 */
class td_block_51 extends td_block_big_grid_2 {

    const POST_LIMIT = 4;

    // MJK: 1/6: Added third optional argument to receive posts
	function render($atts, $content = null, array $posts_from_caller = []){

		// for big grids, extract the td_grid_style
		extract(shortcode_atts(
			[
				'td_grid_style' => 'td-grid-style-1'
			], $atts));

		if ( empty( $atts ) ) {
			$atts = array();
		}
		$atts['limit'] = self::POST_LIMIT;

		parent::render($atts); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)

		$buffy = '';

        $buffy .= '<div class="' . $this->get_block_classes([$td_grid_style, 'td_block_big_grid_2']) . '" ' . $this->get_block_html_atts() . '>';

		//get the block css
		$buffy .= $this->get_block_css();

		$buffy .= '<div id=' . $this->block_uid . ' class="td_block_inner">';

		// MJK: 2/6: Call inner with that third argument, the post for the block
		$buffy .= $this->inner($posts_from_caller);

		$buffy .= '<div class="clearfix"></div>';
		$buffy .= '</div>';
		$buffy .= '</div> <!-- ./block -->';
		return $buffy;
	}

	function inner($posts, $td_column_number = '') {

		$buffy = '';

		if (!empty($posts)) {

			if ($td_column_number==1 || $td_column_number==2) {
				$buffy .= td_util::get_block_error('Big grid 2', 'Please move this shortcode on a full row in order for it to work.');
			} else {
				$buffy .= '<div class="td-big-grid-wrapper">';

				$post_count = 0;

				// when 2 posts make post scroll full
				$td_scroll_posts = '';
				if (count($posts) == 2) {
					$td_scroll_posts = ' td-scroll-full';
				}

				foreach ($posts as $post) {

					if ($post_count == 0) {

						// MJK: 3/6: Use td_module_61 instead of _mx9
						$td_module_mx9 = new td_module_61($post);
						$buffy .= $td_module_mx9->render();

						$buffy .= '<div class="td-big-grid-scroll' . $td_scroll_posts . '">';
						$post_count++;
						continue;
					}

					// MJK: 4/6: Use td_module_62 instead of _mx10
					$td_module_mx10 = new td_module_62($post);
					$buffy .= $td_module_mx10->render($post_count);

					$post_count++;
				}

				if ($post_count < self::POST_LIMIT) {

					for ($i = $post_count; $i < self::POST_LIMIT; $i++) {

						if ($post_count == 0) {
							$td_module_mx_empty = new td_module_mx_empty();

							// MJK: 5/6: td_module_mx9 -> _61
							$buffy .= $td_module_mx_empty->render($i, 'td_module_61');

							$buffy .= '<div class="td-big-grid-scroll' . $td_scroll_posts . '">';
							$post_count++;
							continue;
						}

						$td_module_mx_empty = new td_module_mx_empty();

						// MJK: 6/6: td_module_mx10 -> _62
						$buffy .= $td_module_mx_empty->render($i, 'td_module_62');

						$post_count++;

					}
				}
				$buffy .= '</div>'; // close td-big-grid-scroll
				$buffy .= '</div>'; // close td-big-grid-wrapper
			}
		}

		return $buffy;
	}
}
