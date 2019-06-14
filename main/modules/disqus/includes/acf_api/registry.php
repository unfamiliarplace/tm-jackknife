<?php

/**
 * Creates the ACF registry for the Disqus settings page.
 */
final class MJKDisqus_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }
    
    // Cron schedules allowed
    const schedules = [
        'every_five_minutes', 'every_fifteen_minutes', 'every_half_hour',
        'hourly', 'daily'
    ];
    
    // Fields
    const shortname = 'shortname';
    const num_items = 'num_items';
    const hide_avatars = 'hide_avatars';
    const hide_mods = 'hide_mods';
    const avatar_size = 'avatar_size';
    const excerpt_length = 'excerpt_length';
    const use_cache = 'use_cache';
    const cache_sched = 'cache_sched';
    const sched_base = 'sched_base';


	/*
	 * =========================================================================
	 * Registration
	 * =========================================================================
	 */
    
    /**
     * Add the filters for the group and fields.
     */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Announcement
	    self::add_tab('Disqus API');
        add_action('acf/init', [__CLASS__, 'add_shortname']);
        add_action('acf/init', [__CLASS__, 'add_num_items']);
        add_action('acf/init', [__CLASS__, 'add_hide_avatars']);
        add_action('acf/init', [__CLASS__, 'add_hide_mods']);
        add_action('acf/init', [__CLASS__, 'add_avatar_size']);
        add_action('acf/init', [__CLASS__, 'add_excerpt_length']);

        self::add_tab('Caching');
        add_action('acf/init', [__CLASS__, 'add_use_cache']);
        add_action('acf/init', [__CLASS__, 'add_cache_sched']);
        add_action('acf/init', [__CLASS__, 'add_sched_base']);
        
        // Fill the cache schedules with cron values
        add_filter(sprintf('acf/load_field/key=%s',
                self::qualify_field(self::cache_sched)),
                [__CLASS__, 'fill_cache_sched']
        );
        
        // Redo cache and cron on save
        add_filter('acf/save_post', [__CLASS__, 'reset'], 20);
    }
    
    /**
     * Add the group.
     */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s %s', JKNAPI::space()->name(),
	            JKNAPI::module()->name()),
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


	/*
	 * =========================================================================
	 * Field adding
	 * =========================================================================
	 */
    
    /**
     * Add the 'shortname' field.
     */
    static function add_shortname(): void {
        self::add_acf_field(self::shortname, [
            'label' => 'Disqus shortname',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
        ]);
    }
    
    /**
     * Add the 'num_items' field.
     */
    static function add_num_items(): void {
        self::add_acf_field(self::num_items, [
            'label' => 'Number of comments',
            'type' => 'number',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '15',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 5,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 1,
            'max' => 25,
            'step' => 1,
        ]);
    }
    
    /**
     * Add the 'hide_avatars' field.
     */
    static function add_hide_avatars(): void {
        self::add_acf_field(self::hide_avatars, [
            'label' => 'Hide user avatars',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '10',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 1,
            'ui' => 1,
        ]);
    }
    
    /**
     * Add the 'hide_mods' field.
     */
    static function add_hide_mods(): void {
        self::add_acf_field(self::hide_mods, [
            'label' => 'Hide moderator comments',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '15',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }
    
    /**
     * Add the 'avatar_size' field.
     */
    static function add_avatar_size(): void {
        self::add_acf_field(self::avatar_size, [
            'label' => 'Avatar size',
            'layout' => 'horizontal',
            'choices' => [
                35 => '35px',
                48 => '48px',
                92 => '92px',
            ],
            'default_value' => 35,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ]
        ]);
    }
    
    /**
     * Add the 'excerpt_length' field.
     */
    static function add_excerpt_length(): void {
        self::add_acf_field(self::excerpt_length, [
            'label' => 'Excerpt length',
            'type' => 'number',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '15',
                'class' => '',
                'id' => '',
            ],
            'default_value' => 60,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 10,
            'max' => 240,
            'step' => 1,
        ]);
    }
    
    /**
     * Add the 'use_cache' field.
     */
    static function add_use_cache(): void {
        self::add_acf_field(self::use_cache, [
            'label' => 'Cache comments to speed up page loading time?',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 1,
            'ui' => 1,
        ]);
    }
    
    /**
     * Add the 'cache_sched' field.
     */
    static function add_cache_sched(): void {
        self::add_acf_field(self::cache_sched, [
            'label' => 'Cache refresh period',
            'layout' => 'vertical',
            'choices' => [],
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_cache),
                        'operator' => '==',
                        'value' => '1',
                    ]
                ]
            ],
            'default_value' => 0,
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ]
        ]);
    }
    
    /**
     * Add the 'sched_base' field.
     */
    static function add_sched_base(): void {
        self::add_acf_field(self::sched_base, [
            'label' => 'Starting from',
            'type' => 'time_picker',
            'instructions' => 'Just the hour and minute will be used.',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_cache),
                        'operator' => '==',
                        'value' => '1',
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ],
            'display_format' => 'g:i a',
            'return_format' => 'H:i',
        ]);
    }


	/*
	 * =========================================================================
	 * Field filling
	 * =========================================================================
	 */

	/**
	 * Return the cache_sched field filled with WP cron schedules.
	 * We will use a select few and they depend partly on the common module.
	 *
	 * @param array $field
	 * @return array
	 */
    static function fill_cache_sched(array $field): array {
        
        $schedules = wp_get_schedules();
        foreach($schedules as $name => $data) {
            if (in_array($name, self::schedules)) {
                $field['choices'][$name] = $data['display'];
            }
        }
        
        return $field;
    }


	/*
	 * =========================================================================
	 * Save behaviour
	 * =========================================================================
	 */

	/**
	 * Trigger a reset when the settings page is saved.
	 *
	 * @param string $pid
	 */
    static function reset(string $pid) {
        
        // Bail early if this isn't our page
        $field_key = self::qualify_field(self::shortname);
        if (!isset($_POST['acf'][$field_key])) return;
        
        MJKDisqusTools::reset();
    }
}
