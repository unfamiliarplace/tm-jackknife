<?php

/**
 * Registers new modules and blocks using the TD API.
 */
final class MJKFP_TD {

	/**
	 * Add the registration action.
	 */
	static function add_hooks(): void {
		add_action('td_global_after', [__CLASS__, 'register']);
	}

	/**
	 * Register all the parts.
	 */
	static function register(): void {

		$purl = plugins_url('', __FILE__); // path used for elements like images, css, etc which are available on end user
		$nurl = $purl . '/Newspaper';
		$ppath = dirname(__FILE__); // used for internal (server side) files
		$npath = $ppath . '/Newspaper';

		// Add the "mx" module 61 (module mx9 with updated meta info)

		td_api_module::add('td_module_61', [
			'file' => $npath . "/includes/modules/td_module_61.php",
			'text' => 'MJK Big Slide Main Article',
			'img' => '',
			'used_on_blocks' => [''],
			'excerpt_title' => 15, // value from module 8
			'excerpt_content' => '',
			'enabled_on_more_articles_box' => false,
			'enabled_on_loops' => false,
			'uses_columns' => false, // if the module uses columns on the page template + loop
			'category_label' => true,
			'class' => 'td-animation-stack td_module_mx9',
			'group' => ''
		]);

		// Add the "mx" module 62 (module mx10 with updated meta info)

		td_api_module::add('td_module_62', [
			'file' => $npath . "/includes/modules/td_module_62.php",
			'text' => 'MJK Big Slide Small Article',
			'img' => '',
			'used_on_blocks' => [''],
			'excerpt_title' => 15, // value from module 8
			'excerpt_content' => '',
			'enabled_on_more_articles_box' => false,
			'enabled_on_loops' => false,
			'uses_columns' => false, // if the module uses columns on the page template + loop
			'category_label' => true,
			'class' => 'td-animation-stack td_module_mx10',
			'group' => ''
		]);

		// Add the "big grid 2" block 51 (big grid 2 without hover and with module 61 and 62 instead of mx9 and mx10)

		td_api_block::add('td_block_51', [
			'map_in_visual_composer' => true,
			"name" => 'MJK Big Grid 2 Block',
			"base" => 'td_block_51',
			"class" => 'td_block_big_grid_2', // Using big grid 2's style because why not
			"controls" => "full",
			"category" => 'Blocks',
			'icon' => $nurl . '/images/pagebuilder/block51.png',
			'file' => $npath . '/includes/shortcodes/td_block_51.php',
			"params" => array_merge(
				td_config::get_map_block_general_array(),
				td_config::get_map_filter_array(),
				td_config::get_map_block_ajax_filter_array(),
				td_config::get_map_block_pagination_array()
			)
		]);

		// Add the "big grid slide" block 52 (big grid slide citing block 51 and using our posts from ACF)

		td_api_block::add('td_block_52', [
			'map_in_visual_composer' => true,
			"name" => 'MJK Big Grid Slide Block',
			"base" => 'td_block_52',
			"class" => 'td-big-grid-slide', // Using big grid slide's style because why not
			"controls" => "full",
			"category" => 'Blocks',
			'icon' => $nurl . '/images/pagebuilder/block52.png',
			'file' => $npath . '/includes/shortcodes/td_block_52.php',
			"params" => array_merge(
				td_config::get_map_block_general_array(),
				td_config::get_map_filter_array(),
				td_config::get_map_block_ajax_filter_array(),
				td_config::get_map_block_pagination_array()
			)
		]);
	}
}
