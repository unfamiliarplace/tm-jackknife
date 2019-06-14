<?php

/**
 * MJK: 3 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our modules; 3 columns; 3 posts per column
 */
class td_block_45 extends td_block_17 {

	// MJK: 1/3: Prepare $atts array to force a block with 3 columns
    function render($atts, $content = null) {
	    $td_column_number = 3;
        $atts['td_column_number'] = $td_column_number;
        $GLOBALS['td_row_count'] = 1;
        $GLOBALS['td_column_count'] = '1/1';

        return parent::render($atts, $content); // sets the live atts, $this->atts, $this->block_uid, $this->td_query (it runs the query)
    }

	function inner($posts, $td_column_number = '') {

		$buffy = '';

		$td_block_layout = new td_block_layout();
		$td_post_count = 0; // the number of posts rendered


		if (!empty($posts)) {
			foreach ($posts as $post) {

				// MJK: 2/3: td_module_4 > _43, _8 > _42
				$td_module_4 = new td_module_43($post);
				$td_module_8 = new td_module_42($post);

				switch ($td_column_number) {

					case '1': //one column layout
						$buffy .= $td_block_layout->open12(); //added in 010 theme - span 12 doesn't use rows
						if ($td_post_count == 0) { //first post
							$buffy .= $td_module_4->render();
						} else {
							$buffy .= $td_module_8->render();
						}
						$buffy .= $td_block_layout->close12();
						break;

					case '2': //two column layout
						$buffy .= $td_block_layout->open_row();
						if ($td_post_count == 0) { //first post
							$buffy .= $td_block_layout->open6();
							$buffy .= $td_module_4->render();
							$buffy .= $td_block_layout->close6();
						} else { //the rest
							$buffy .= $td_block_layout->open6();
							$buffy .= $td_module_8->render();
						}
						break;

					case '3': //three column layout
						$buffy .= $td_block_layout->open_row();
						if ($td_post_count == 0) { //first post
							$buffy .= $td_block_layout->open4();
							$buffy .= $td_module_4->render();
							$buffy .= $td_block_layout->close4();
						} else { //2-3 cols
							$buffy .= $td_block_layout->open4();
							$buffy .= $td_module_8->render();

							// MJK: 3/3: New column after 3, not 4, posts
							if ($td_post_count == 3) { //make new column
								$buffy .= $td_block_layout->close4();
							}
						}
						break;
				}
				$td_post_count++;
			}
		}
		$buffy .= $td_block_layout->close_all_tags();
		return $buffy;
	}
}
