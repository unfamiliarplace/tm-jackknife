<?php

/**
 * MJK: 1 modification | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our module
 */
class td_block_43 extends td_block_4 {

	function inner($posts, $td_column_number = '') {

		$buffy = '';

		$td_block_layout = new td_block_layout();
		if (empty($td_column_number)) {
			$td_column_number = td_global::vc_get_column_number(); // get the column width of the block from the page builder API
		}
		$td_post_count = 0; // the number of posts rendered
		$td_current_column = 1; //the current column


		if (!empty($posts)) {
			foreach ($posts as $post) {

				// MJK: 1/1: Replace module_2 -> _31
				$td_module_2 = new td_module_31($post);

				switch ($td_column_number) {

					case '1': //one column layout
						$buffy .= $td_block_layout->open12(); //added in 010 theme - span 12 doesn't use rows
						$buffy .= $td_module_2->render($post);
						$buffy .= $td_block_layout->close12();
						break;

					case '2': //two column layout
						$buffy .= $td_block_layout->open_row();

						$buffy .= $td_block_layout->open6();
						$buffy .= $td_module_2->render($post);
						$buffy .= $td_block_layout->close6();

						if ($td_current_column == 2) {
							$buffy .= $td_block_layout->close_row();
						}
						break;

					case '3': //three column layout
						$buffy .= $td_block_layout->open_row();

						$buffy .= $td_block_layout->open4();
						$buffy .= $td_module_2->render($post);
						$buffy .= $td_block_layout->close4();

						if ($td_current_column == 3) {
							$buffy .= $td_block_layout->close_row();
						}
						break;
				}

				//current column
				if ($td_current_column == $td_column_number) {
					$td_current_column = 1;
				} else {
					$td_current_column++;
				}


				$td_post_count++;
			}
		}
		$buffy .= $td_block_layout->close_all_tags();
		return $buffy;
	}
}