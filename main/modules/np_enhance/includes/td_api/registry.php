<?php

/**
 * Registers new elements using the tagDiv API.
 */
final class MJKNPEnhance_TD {

	/**
	 * Add the registration hook.
	 */
	static function add_hooks() {
		add_action('td_global_after', [__CLASS__, 'register']);
	}

	/**
	 * Register all the parts.
	 */
    static function register() {

		$purl = plugins_url('', __FILE__); // path used for elements like images, css, etc which are available on end user
	    $nurl = $purl . '/Newspaper';
		$ppath = dirname(__FILE__); // used for internal (server side) files
		$npath = $ppath . '/Newspaper';

		// Add the default module (module 2 plus extra meta info)

		td_api_module::add('td_module_31',
			[
				'file' => $npath . "/includes/modules/td_module_31.php",
				'text' => 'MJK Default Module',
				'img' => $nurl . '/images/modules/td_module_31.png',
				'used_on_blocks' => [''],
				'excerpt_title' => 12, // value from module 2
				'excerpt_content' => 25, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => true,
				'class' => 'td_module_31 td_module_wrap td-animation-stack td_module_2',
				'group' => ''
			]
		);

		// Add the imageless module (module 8 plus extra meta info and excerpt)

		td_api_module::add('td_module_32',
			[
				'file' => $npath . "/includes/modules/td_module_32.php",
				'text' => 'MJK Imageless Module',
				'img' => $nurl . '/images/modules/td_module_32.png',
				'used_on_blocks' => [''],
				'excerpt_title' => 15, // value from module 8
				'excerpt_content' => 25, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => true,
				'class' => 'td_module_32 td_module_wrap td-animation-stack td_module_2', // instead of 8, for proper header sizes
				'group' => ''
			]
		);

		// Add the imageless and infoless module (module 8 plus excerpt)

		td_api_module::add('td_module_33',
			[
				'file' => $npath . "/includes/modules/td_module_33.php",
				'text' => 'MJK Imageless & Infoless Module',
				'img' => $nurl . '/images/modules/td_module_33.png',
				'used_on_blocks' => [''],
				'excerpt_title' => 15, // value from module 8
				'excerpt_content' => 25, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => false,
				'class' => 'td_module_33 td_module_wrap td-animation-stack td_module_2', // instead of 8, for proper header sizes
				'group' => ''
			]
		);

		// Add the homepage block main module (module 4 plus more meta info)

		td_api_module::add('td_module_41',
			[
				'file' => $npath . "/includes/modules/td_module_41.php",
				'text' => 'MJK Homepage Block Main Module',
				'img' => $nurl . '/images/modules/td_module_41.png',
				'used_on_blocks' => ['td_block_41'],
				'excerpt_title' => 15, // value from module 8
				'excerpt_content' => 25, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => false,
				'class' => 'td_module_41 td_module_wrap td-animation-stack td_module_4',
				'group' => ''
			]
		);

		// Add the homepage block small module (module 8 minus the image plus more meta info and an excerpt)

		td_api_module::add('td_module_42',
			[
				'file' => $npath . "/includes/modules/td_module_42.php",
				'text' => 'MJK Homepage Block Small Module',
				'img' => $nurl . '/images/modules/td_module_42.png',
				'used_on_blocks' => ['td_block_41', 'td_block_42'],
				'excerpt_title' => 15, // value from module 8
				'excerpt_content' => 15, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => false,
				'class' => 'td_module_42 td_module_wrap td-animation-stack td_module_8',
				'group' => ''
			]
		);

		// Add the homepage block imageless main module (module 4 minus the image plus more meta info)

		td_api_module::add('td_module_43',
			[
				'file' => $npath . "/includes/modules/td_module_43.php",
				'text' => 'MJK Homepage Block Imageless Main Module',
				'img' => $nurl . '/images/modules/td_module_43.png',
				'used_on_blocks' => ['td_block_42'],
				'excerpt_title' => 15, // value from module 8
				'excerpt_content' => 45, // value from module 2
				'enabled_on_more_articles_box' => true,
				'enabled_on_loops' => true,
				'uses_columns' => true, // if the module uses columns on the page template + loop
				'category_label' => false,
				'class' => 'td_module_43 td_module_wrap td-animation-stack td_module_4',
				'group' => ''
			]
		);


		// Add the basic homepage block (block 17 but with modules 41 + 42)

		td_api_block::add('td_block_41',
			[
				'map_in_visual_composer' => true,
				"name" => 'MJK Block 41',
				"base" => 'td_block_41',
				"class" => 'td_block_17', // Using block 17's style because why not
				"controls" => "full",
				"category" => 'Blocks',
				'icon' => $nurl . '/images/pagebuilder/block41.png',
				'file' => $npath . '/includes/shortcodes/td_block_41.php',
				"params" => array_merge(
					td_config::get_map_block_general_array(),
					td_config::get_map_filter_array(),
					td_config::get_map_block_ajax_filter_array(),
					td_config::get_map_block_pagination_array()
				)
			]
		);

		// Add the imageless homepage block (block 17 but with modules 43 + 42)

		td_api_block::add('td_block_42',
			[
				'map_in_visual_composer' => true,
				"name" => 'MJK Block 42',
				"base" => 'td_block_42',
				"class" => 'td_block_17', // Using block 17's style because why not
				"controls" => "full",
				"category" => 'Blocks',
				'icon' => $nurl . '/images/pagebuilder/block42.png',
				'file' => $npath . '/includes/shortcodes/td_block_42.php',
				"params" => array_merge(
					td_config::get_map_block_general_array(),
					td_config::get_map_filter_array(),
					td_config::get_map_block_ajax_filter_array(),
					td_config::get_map_block_pagination_array()
				)
			]
		);

		// Add a block that's a wrapper for module 31 (block 4 but with module 31)

		td_api_block::add('td_block_43',
			[
				'map_in_visual_composer' => true,
				"name" => 'MJK Block 43',
				"base" => 'td_block_43',
				"class" => 'td_block_4', // Using block 4's style because why not
				"controls" => "full",
				"category" => 'Blocks',
				'icon' => $nurl . '/images/pagebuilder/block43.png',
				'file' => $npath . '/includes/shortcodes/td_block_43.php',
				"params" => array_merge(
					td_config::get_map_block_general_array(),
					td_config::get_map_filter_array(),
					td_config::get_map_block_ajax_filter_array(),
					td_config::get_map_block_pagination_array()
				)
			]
		);

		// Add our custom header

		td_api_header_style::add('71',
			[
				'text' => '<strong> MJK Style 71 - </strong> Style 10 but text logo + inline search',
				'file' => $npath . '/parts/header/header-style-71.php',
				'img' => $nurl . '/images/panel/menu/icon-menu-71.png',
			]
		);

		// Add our "morphing" single template (controls templates 82, 83, and 84, which are based on 4, 5, and 9)

		td_api_single_template::add('single_template_81',
			[
				'file' => $npath . '/single_template_81.php',
				'text' => 'MJK Single Template 81 - Morphs into ST 4, 5, or 9',
				'img' => $nurl . '/images/panel/single_templates/single_template_81.png',
				'show_featured_image_on_all_pages' => false,

				// We shouldn't have to set this -- they should make it default!
				'bg_disable_background' => false,
				'bg_box_layout_config' => 'auto',
				'bg_use_featured_image_as_background' => false
			]
		);

		// Add our category template (template 3 plus modifications)

		td_api_category_template::add('td_category_template_91',
			[
				'file' => $npath . '/includes/category_templates/td_category_template_91.php',
				'img' => $nurl . '/images/panel/category_templates/icon-category-91.png',
				'text' => 'MJK Style 91'
			]
		);
	}
}
