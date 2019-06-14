<?php

/**
 * Register new elements using the TD API.
 */
final class MJKVI_TD {

	/**
	 * Add the action to register the parts.
	 */
	static function add_hooks() {
		add_action('td_global_after', [__CLASS__, 'register']);
	}

	static function register() {

		$purl = plugins_url('', __FILE__); // path used for elements like images, css, etc which are available on end user
		$nurl = $purl . '/Newspaper';
		$ppath = dirname(__FILE__); // used for internal (server side) files
		$npath = $ppath . '/Newspaper';

		// Add the basic issue post block (block 17 but with modules 31 + 42; 3cols forced; 3 per row)

		td_api_block::add('td_block_44', [
			'map_in_visual_composer' => false,
			"name" => 'MJK Block 44',
			"base" => 'td_block_44',
			"class" => 'td_block_17', // Using block 17's style because why not
			"controls" => "full",
			"category" => 'Blocks',
			'icon' => '',
			'file' => $npath . '/includes/shortcodes/td_block_44.php',
			"params" => array_merge(
				td_config::get_map_block_general_array(),
				td_config::get_map_filter_array(),
				td_config::get_map_block_ajax_filter_array(),
				td_config::get_map_block_pagination_array()
			)
		]);

		// Add the basic issue post block (block 17 but with modules 43 [no thumb] + 42; 3cols forced; 3 per row)

		td_api_block::add('td_block_45', [
			'map_in_visual_composer' => false,
			"name" => 'MJK Block 45',
			"base" => 'td_block_45',
			"class" => 'td_block_17', // Using block 17's style because why not
			"controls" => "full",
			"category" => 'Blocks',
			'icon' => '',
			'file' => $npath . '/includes/shortcodes/td_block_45.php',
			"params" => array_merge(
				td_config::get_map_block_general_array(),
				td_config::get_map_filter_array(),
				td_config::get_map_block_ajax_filter_array(),
				td_config::get_map_block_pagination_array()
			)
		]);
	}
}
