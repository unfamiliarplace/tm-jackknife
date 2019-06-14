<?php

/**
 * MJK: 1 modification | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: our modules
 */
class td_block_42 extends td_block_17 {

	function inner($posts, $td_column_number = '') {

		$buffy = '';

		$td_block_layout = new td_block_layout();
		$td_post_count = 0; // the number of posts rendered


		if (!empty($posts)) {
			foreach ($posts as $post) {

				// MJK: 1/1: Replace module_4 -> _43 and  _8 -> _42
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

							if ($td_post_count == 4) { //make new column
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