<?php

/**
 * MJK: 7 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our posts; our sub-block
 */
class td_block_52 extends td_block_big_grid_slide {

    private $internal_block_instance;

	function render($atts, $content = null) {

		// MJK: 1/7: Use td_block_51 instead of td_block_big_grid_2
		$this->internal_block_instance = new td_block_51();


		// This 'in_big_grid_slide' param is set to not generate css (@see generate_css)
		$this->internal_block_instance->in_big_grid_slide = true;


		$this->block_uid = td_global::td_generate_unique_id(); //update unique id on each render

		$buffy = ''; //output buffer

		extract(shortcode_atts(
			[
				'limit' => 4,
				'sort' => '',
				'category_id' => '',
				'category_ids' => '',
				'tag_slug' => '',
				'force_columns' => '',
				'autoplay' => '',
				'offset' => 0
			],$atts));

		if (empty($td_column_number)) {
			$td_column_number = td_global::vc_get_column_number(); // get the column width of the block
		}

		if ($td_column_number == 3) {

			// MJK: 2/7: Commented out this line.
			// Explanation: 'current' means overall. But we'll show all our posts.
			// $current_limit = intval($limit);

			$post_limit = constant(get_class($this->internal_block_instance) . '::POST_LIMIT');

			// MJK: 3/7: Commented out this line. We get our posts from the API
			// $td_query = td_data_source::get_wp_query($atts);

			// MJK: 4/7: Added this post getter
			$fpcard = MJKFP_API::current_fpcard();
			$posts = $fpcard->get_posts();
			$current_limit = count($posts);

			// MJK: 5/7: $td_query->posts becomes $posts (and in the next condition)
			if (!empty($posts)) {

				if ( ( $current_limit > $post_limit ) and
				     ( count( $posts ) > $post_limit ) and
				     ! ( td_util::tdc_is_live_editor_iframe()
				         or td_util::tdc_is_live_editor_ajax() ) ) {

					$buffy .= '<div class="td-big-grid-slide td_block_wrap" id="iosSlider_' . $this->block_uid . '">';
					$buffy .= '<div class="td-theme-slider td_block_inner" id="' . $this->block_uid . '">';


					$current_offset = 0;

					$atts['class'] = 'item';

					while ( $current_limit > 0 ) {

						$atts['offset'] = $offset + $current_offset;

						// MJK: 6/7: Added third argument to slice post array
						$buffy .= $this->internal_block_instance->render( $atts,
							null,
							array_slice($posts, $offset + $current_offset, $post_limit));

						$current_offset += $post_limit;
						$current_limit -= $post_limit;
					}

					$buffy .= '</div>';//end slider (if slider)

					$buffy .= '<i class = "td-icon-left"></i>';
					$buffy .= '<i class = "td-icon-right"></i>';

					$buffy .= '</div>';//end iosSlider (if slider)

					$autoplay_settings = '';
					$current_autoplay  = filter_var( $autoplay, FILTER_VALIDATE_INT );

					if ( $current_autoplay !== false ) {
						$autoplay_settings = 'autoSlide: true, autoSlideTimer: ' . $current_autoplay * 1000 . ',';
					}

					$slide_javascript = ';jQuery(document).ready(function() {
                        jQuery("#iosSlider_' . $this->block_uid . '").iosSlider({
                            snapToChildren: true,
                            desktopClickDrag: true,
                            keyboardControls: true,
                            responsiveSlides: true,
                            infiniteSlider: true,
                            ' . $autoplay_settings . '
                            navPrevSelector: jQuery("#iosSlider_' . $this->block_uid . ' .td-icon-left"),
                            navNextSelector: jQuery("#iosSlider_' . $this->block_uid . ' .td-icon-right")
                        });
                    });';

					td_js_buffer::add_to_footer( $slide_javascript );

				} else {

					// MJK: 7/7: Added third argument
					$buffy .= $this->internal_block_instance->render( $atts, null, $posts );
				}
			}

		} else {

			// Show an info placeholder
			if (td_util::tdc_is_live_editor_iframe() or td_util::tdc_is_live_editor_ajax()) {
				$buffy .= '<div class="td_block_wrap tdc-big-grid-slide"></div>';
			}
		}
		return $buffy;
	}
}
