<?php

/**
 * Creates the ACF field group for the settings page.
 */
class MJKCommon_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

    // Fields
	const cdn_use               = 'cdn_use';
	const cf7_disable_scripts   = 'cf7_disable_scripts';
	const cf7_disable_styles    = 'cf7_disable_styles';
	const cf7_enable_pages      = 'cf7_enable_pages';
	const issuu_api_key         = 'issuu_api_key';
	const issuu_api_secret      = 'issuu_api_secret';
	const colour_main           = 'colour_main';
	const colour_news           = 'colour_news';
	const colour_opinion        = 'colour_opinion';
	const colour_arts           = 'colour_arts';
	const colour_features       = 'colour_features';
	const colour_sports         = 'colour_sports';
	const colour_photos         = 'colour_photos';

	/**
	 * Add the group and fields.
	 */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Announcement
	    self::add_tab('CDN');
        add_action('acf/init', [__CLASS__, 'add_cdn_use']);

        self::add_tab('CF7');
        add_action('acf/init', [__CLASS__, 'add_cf7_disable_scripts']);
        add_action('acf/init', [__CLASS__, 'add_cf7_disable_styles']);
        add_action('acf/init', [__CLASS__, 'add_cf7_enable_pages']);

        self::add_tab('Issuu');
        add_action('acf/init', [__CLASS__, 'add_issuu_api_key']);
        add_action('acf/init', [__CLASS__, 'add_issuu_api_secret']);

        self::add_tab('Colours');
        add_action('acf/init', [__CLASS__, 'add_colour_main']);
        add_action('acf/init', [__CLASS__, 'add_colour_news']);
        add_action('acf/init', [__CLASS__, 'add_colour_opinion']);
        add_action('acf/init', [__CLASS__, 'add_colour_arts']);
        add_action('acf/init', [__CLASS__, 'add_colour_features']);
        add_action('acf/init', [__CLASS__, 'add_colour_sports']);
	    add_action('acf/init', [__CLASS__, 'add_colour_photos']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s %s Settings',
                    JKNAPI::space()->name(), JKNAPI::module()->name()),
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => JKNAPI::settings_page()->slug()
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }

	/**
	 * Add the 'cdn_use' field.
	 */
	static function add_cdn_use(): void {
		self::add_acf_field(self::cdn_use, [
			'label' => 'Enable CDN filtering in modules?',
			'type' => 'true_false',
			'instructions' => 'At this moment, requires WP Rocket to be on'
                . ' and have a CDN set. That is, it piggybacks on WP Rocket.'
				. ' Also, modules may or may not honour this setting.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '100',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'cf7_disable_scripts' field.
	 */
	static function add_cf7_disable_scripts(): void {
		self::add_acf_field(self::cf7_disable_scripts, [
			'label' => 'Disable CF7 universal script loading?',
			'type' => 'true_false',
			'instructions' => 'Disabling Contact Form 7 loading on every page'
				. ' saves loading time.)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'cf7_disable_styles' field.
	 */
	static function add_cf7_disable_styles(): void {
		self::add_acf_field(self::cf7_disable_styles, [
			'label' => 'Disable CF7 universal style loading?',
			'type' => 'true_false',
			'instructions' => 'Disabling Contact Form 7 loading on every page'
			                  . ' saves loading time.)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'ui' => 1
		]);
	}

	/**
	 * Add the 'cf7_enable_pages' field.
	 */
	static function add_cf7_enable_pages(): void {
		self::add_acf_field(self::cf7_enable_pages, [
			'label' => 'Load CF7 anyway on these pages',
			'type' => 'post_object',
			'instructions' => 'Use to load Contact Form 7 on select pages.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::cf7_disable_scripts),
						'operator' => '==',
						'value' => '1',
					],
				],
				[
					[
						'field' => self::qualify_field(self::cf7_disable_styles),
						'operator' => '==',
						'value' => '1',
					],
				],
			],
			'wrapper' => [
				'width' => '40',
				'class' => '',
				'id' => '',
			],
			'post_type' => [
				0 => 'page',
			],
			'taxonomy' => [],
			'allow_null' => 0,
			'multiple' => 1,
			'return_format' => 'id',
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'issuu_api_key' field.
	 */
	static function add_issuu_api_key(): void {
		self::add_acf_field(self::issuu_api_key, [
			'label' => 'Issuu API key',
			'type' => 'text',
			'instructions' => 'N.B. At the moment this appears to be unused.',
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'readonly' => 0
		]);
	}

	/**
	 * Add the 'issuu_api_secret' field.
	 */
	static function add_issuu_api_secret(): void {
		self::add_acf_field(self::issuu_api_secret, [
			'label' => 'Issuu API secret',
			'type' => 'text',
			'instructions' => 'N.B. At the moment this appears to be unused.',
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'readonly' => 0
		]);
	}

	/**
	 * Add the 'colour_main' field.
	 */
	static function add_colour_main(): void {
		self::add_acf_field(self::colour_main, [
			'label' => 'Website theme colour',
			'type' => 'color_picker',
			'default_value' => '#11416f',
			'instructions' => 'Choose a general website colour for modules to'
				. ' use if they like. The default is #11416f.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_news' field.
	 */
	static function add_colour_news(): void {
		self::add_acf_field(self::colour_news, [
			'label' => 'News category colour',
			'type' => 'color_picker',
			'default_value' => '#11416f',
			'instructions' => 'Choose a news colour for modules to'
			                  . ' use if they like. The default is #11416f.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_opinion' field.
	 */
	static function add_colour_opinion(): void {
		self::add_acf_field(self::colour_opinion, [
			'label' => 'Opinion category colour',
			'type' => 'color_picker',
			'default_value' => '#51bfc1',
			'instructions' => 'Choose an opinion colour for modules to'
			                  . ' use if they like. The default is #51bfc1.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_arts' field.
	 */
	static function add_colour_arts(): void {
		self::add_acf_field(self::colour_arts, [
			'label' => 'Arts category colour',
			'type' => 'color_picker',
			'default_value' => '#ce1f32',
			'instructions' => 'Choose an arts colour for modules to'
			                  . ' use if they like. The default is #ce1f32.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_features' field.
	 */
	static function add_colour_features(): void {
		self::add_acf_field(self::colour_features, [
			'label' => 'Features category colour',
			'type' => 'color_picker',
			'default_value' => '#e5b927',
			'instructions' => 'Choose a features colour for modules to'
			                  . ' use if they like. The default is #e5b927.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_sports' field.
	 */
	static function add_colour_sports(): void {
		self::add_acf_field(self::colour_sports, [
			'label' => 'Sports category colour',
			'type' => 'color_picker',
			'default_value' => '#189647',
			'instructions' => 'Choose a sports colour for modules to'
			                  . ' use if they like. The default is #189647.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'colour_photos' field.
	 */
	static function add_colour_photos(): void {
		self::add_acf_field(self::colour_photos, [
			'label' => 'Photos/misc colour',
			'type' => 'color_picker',
			'default_value' => '#af8caf',
			'instructions' => 'Choose a photos (or misc) colour for modules to'
			                  . ' use if they like. The default is #af8caf.',
			'required' => 1,
			'conditional_logic' => '',
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}
}
