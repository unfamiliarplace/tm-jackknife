<?php

/**
 * MJK: 9 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Stop showing sibling categories; add 'no subcats' class to title
 */
class td_category_template_91 extends td_category_template_3 {

	function render() {
		?>

        <!-- subcategory -->
        <div class="td-category-header td-container-wrap">
            <div class="td-container">
                <div class="td-pb-row">
                    <div class="td-pb-span12">

                        <div class="td-crumb-container"><?php echo parent::get_breadcrumbs(); ?></div>

	                    <?php
                            // MJK: 1/9: To the end of this block.
                            // First look up child categories in order to maybe
                            // apply a no-cats class to the title.
                            // Then the title & child cats are echoed as before.

                            $child_cats = self::get_sibling_categories([
                                'show_background_color' => true
                            ]);

                            $extra_cl = empty($child_cats) ? 'cat-no-subcats' : '';
                            printf(
                                    '<h1 itemprop="name"' .
                                    'class="entry-title td-page-title %s">' .
                                    '%s</h1>', $extra_cl, parent::get_title()
                            );

                            echo $child_cats;
	                    ?>

						<?php echo parent::get_description(); ?>

                    </div>
                </div>
				<?php
                    // MJK: 2/9: Commented out pull down categories
                    // echo parent::get_pull_down();
                ?>
            </div>
        </div>

		<?php
	}

	/**
	 * MJK: 3/9: Overrides parent to disallow sibling categories.
	 *
	 * @param string $params
	 * @return string
	 */
	protected function get_sibling_categories($params='') {
		$buffy = '';

		//the subcategories

        // MJK: 4/9: Use td_global::$current ... instead of $this->current
		if (!empty(td_global::$current_category_obj->cat_ID)) {

			//check for subcategories
			$subcategories = get_categories( [
				// MJK: 5/9: Use td_global::$current ... instead of $this->current
				'child_of'      => td_global::$current_category_obj->cat_ID,
				'hide_empty'    => false,
				'fields'        => 'ids',
			]);

			//if we have child categories
			if ( $subcategories ) {
				// get child categories
				$categories_objects = get_categories( [
					// MJK: 6/9: Use td_global::$current ... instead of $this->current
					'parent'     => td_global::$current_category_obj->cat_ID,
					'hide_empty' => 0,
					'number'     => self::SIBLING_CATEGORY_LIMIT
				]);
			}

			// MJK: 7/9: This block is commented out.

//			// if no child categories get siblings
//			if (empty($categories_objects)) {
//				$categories_objects = get_categories(array(
//					'parent'        => $this->current_category_obj->parent,
//					'hide_empty'    => 0,
//					'number'        => self::SIBLING_CATEGORY_LIMIT
//				));
//			}
		}


		/**
		 * if we have categories to show... show them
		 */
		if (!empty($categories_objects)) {
			$buffy = '<div class="td-category-siblings">';
			$buffy .= '<ul class="td-category">';
			foreach ($categories_objects as $category_object) {

				// ignore featured cat and uncategorized
				if (($category_object->name == TD_FEATURED_CAT) OR
				    (strtolower($category_object->cat_name) == 'uncategorized')) {
					continue;
				}

				if (!empty($category_object->name) and td_util::get_category_option($category_object->cat_ID,'tdc_hide_on_post') != 'hide') {
					$class = '';

					// MJK: 8/9: This block is commented out.
//					if($category_object->cat_ID == $this->current_category_id) {
//						$class = 'td-current-sub-category';
//					}


					$td_css_inline = new td_css_inline();

					// @todo we can add more properties as needed, ex: show_border_color
					if (!empty($params_array['show_background_color'])) {
						$tdc_color_current_cat = td_util::get_category_option($category_object->cat_ID, 'tdc_color');
						$tdc_cat_title_color = td_util::readable_colour($tdc_color_current_cat, 200, 'rgba(0, 0, 0, 0.9)', '#fff');
						$td_css_inline->add_css (
							[
								'background-color' => $tdc_color_current_cat,
								'color' => $tdc_cat_title_color,
								'border-color' => $tdc_color_current_cat
							]
						);
					}


					$buffy .= '<li class="entry-category"><a ' . $td_css_inline->get_inline_css() . ' class="' . $class . '"  href="' . get_category_link($category_object->cat_ID) . '">' . $category_object->name . '</a></li>';
				}
			}
			$buffy .= '</ul>';


			// subcategory dropdown list
			$buffy .= '<div class="td-subcat-dropdown td-pulldown-filter-display-option">';
			$buffy .= '<div class="td-subcat-more"><i class="td-icon-menu-down"></i></div>';

			// the dropdown list
			$buffy .= '<ul class="td-pulldown-filter-list">';
			$buffy .= '</ul>';

			$buffy .= '</div>';

			$buffy .= '<div class="clearfix"></div>';

			$buffy .= '</div>';
		}


		// compile the custom css
		if (!empty($params_array['current_category_css'])) {
			// MJK: 9/9: Use td_global::$current ... instead of $this->current
			$tdc_color = td_util::get_category_option(td_global::$current_category_obj->cat_ID, 'tdc_color');

			$td_css_compiler = new td_css_compiler($params_array['current_category_css']);
			$td_css_compiler->load_setting_raw('current_category_color', $tdc_color);
			td_css_buffer::add_to_footer($td_css_compiler->compile_css());
		}


		return $buffy;
	}
}
