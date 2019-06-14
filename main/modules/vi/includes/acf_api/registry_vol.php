<?php

/**
 * ACF field group for the volume custom post type.
 */
class MJKVI_ACF_VOL extends JKNACF {

	/**
	 * Define a unique group.
	 *
	 * @return string
	 */
    static function group(): string { return 'vol'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Fields
    const num = 'num';
    const is_erindalian = 'is_erindalian';
    const website = 'website';
    const external = 'external';
    const notes = 'notes';
    const issues = 'issues';
    const iss_date = 'iss_date';
    const iss_holiday_delay = 'iss_holiday_delay';
    const iss_issuu_url = 'iss_issuu_url';
    const iss_archiveorg_url = 'iss_archiveorg_url';
    const iss_posts_url = 'iss_posts_url';
    const iss_on_site = 'iss_on_site';
    const iss_notes = 'iss_notes';
    const iss_is_special = 'iss_is_special';
    const iss_special = 'iss_special';
    const iss_skip = 'iss_skip';

	/**
	 * Add the filters for the group and fields.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);

        self::add_tab('Volume');
        add_action('acf/init', [__CLASS__, 'add_num']);
        add_action('acf/init', [__CLASS__, 'add_is_erindalian']);
        add_action('acf/init', [__CLASS__, 'add_website']);
        add_action('acf/init', [__CLASS__, 'add_external']);
        add_action('acf/init', [__CLASS__, 'add_notes']);

        self::add_tab('Issues');
        add_action('acf/init', [__CLASS__, 'add_issues']);
        add_action('acf/init', [__CLASS__, 'add_iss_date']);
        add_action('acf/init', [__CLASS__, 'add_iss_holiday_delay']);
        add_action('acf/init', [__CLASS__, 'add_iss_issuu_url']);
        add_action('acf/init', [__CLASS__, 'add_iss_archiveorg_url']);
        add_action('acf/init', [__CLASS__, 'add_iss_notes']);
        add_action('acf/init', [__CLASS__, 'add_iss_is_special']);
        add_action('acf/init', [__CLASS__, 'add_iss_special']);
        add_action('acf/init', [__CLASS__, 'add_iss_skip']);
        add_action('acf/init', [__CLASS__, 'add_iss_on_site']);
        add_action('acf/init', [__CLASS__, 'add_iss_posts_url']);
        
        // Sort websites
        add_filter(sprintf('acf/fields/post_object/query/key=%s',
                self::qualify_field(self::website)),
                [__CLASS__, 'set_website_sort'], 10, 3);

        // Filter website names (for main)
        add_filter(sprintf('acf/fields/post_object/result/key=%s',
                self::qualify_field(self::website)),
                [__CLASS__, 'filter_websites'], 10, 4);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s — %s',
                    JKNAPI::module()->name(), MJKVI_CPT_Volume::name()),
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => MJKVI_CPT_Volume::qid(),
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => [
                0 => 'permalink',
                1 => 'the_content',
                2 => 'excerpt',
                3 => 'custom_fields',
                4 => 'discussion',
                5 => 'comments',
                6 => 'revisions',
                7 => 'slug',
                8 => 'author',
                9 => 'format',
                10 => 'page_attributes',
                11 => 'featured_image',
                12 => 'categories',
                13 => 'tags',
                14 => 'send-trackbacks',
            ],
            'active' => 1,
            'description' => '',
        ]);
    }

	/*
	 * =========================================================================
	 * Volume itself
	 * =========================================================================
	 */

	/**
	 * Add the 'num' field.
	 */
    static function add_num(): void {
        self::add_acf_field(self::num, [
            'label' => 'Volume number',
            'type' => 'number',
            'instructions' => 'e.g. Volume 1 was 1974/75.',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '15',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'min' => 1,
            'max' => '',
            'step' => 1,
        ]);
    }

	/**
	 * Add the 'is_erindalian' field.
	 */
    static function add_is_erindalian(): void {
        self::add_acf_field(self::is_erindalian, [
            'label' => 'Is this a volume of The Erindalian?',
            'type' => 'true_false',
            'instructions' => '&nbsp;',
            'required' => 0,
            'conditional_logic' => 0,
            'ui' => 1,
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0
        ]);
    }

	/**
	 * Add the 'website' field.
	 */
    static function add_website(): void {
        self::add_acf_field(self::website, [
            'label' => 'Website',
            'instructions' => 'The best website on which to find the articles from this volume, if any.',
            'type' => 'post_object',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '33',
                'class' => '',
                'id' => '',
            ],
            'post_type' => [
                0 => 'mjk_vi_website',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'id',
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'external' field.
	 */
    static function add_external(): void {
        self::add_acf_field(self::external, [
            'label' => 'Where are the digital PDFs for this volume hosted?',
            'layout' => 'vertical',
            'choices' => [
                'issuu' => 'Issuu',
                'archiveorg' => 'Archive.org',
                'both' => 'Both / mixed',
                'none' => 'Neither',
            ],
            'default_value' => 'issuu',
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'type' => 'radio',
            'instructions' => '',
            'required' => 0,
            'wrapper' => [
                'width' => '27',
                'class' => '',
                'id' => '',
            ]
        ]);
    }

	/**
	 * Add the 'notes' field.
	 */
    static function add_notes(): void {
        self::add_acf_field(self::notes, [
            'label' => 'Notes',
            'type' => 'text',
            'instructions' => 'A totally optional field for adding general notes on a volume.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
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


	/*
	 * =========================================================================
	 * Issues
	 * =========================================================================
	 */

	/**
	 * Add the 'issues' field.
	 */
    static function add_issues(): void {
        self::add_acf_field(self::issues, [
            'label' => 'Issues',
            'type' => 'repeater',
            'instructions' => 'Add each of the volume\'s issues, one at a time.'
            . ' N.B. The order of this table is not what determines issue numbering, but the dates published.'
            . ' Note that any articles published after the last issue you create will be automatically handled'
            . ' by a "summer" issues.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'collapsed' => self::qualify_field(self::iss_date),
            'min' => '',
            'max' => '',
            'layout' => 'block',
            'button_label' => 'Add issue'
        ]);
    }

	/**
	 * Add the 'iss_date' field.
	 */
    static function add_iss_date(): void {
        self::add_acf_inner_field(self::issues, self::iss_date, [
            'label' => 'Date',
            'type' => 'date_picker',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '14',
                'class' => '',
                'id' => '',
            ],
            'display_format' => 'F j, Y',
            'return_format' => 'Ymd',
            'first_day' => 1
        ]);
    }

	/**
	 * Add the 'iss_holiday_delay' field.
	 */
    static function add_iss_holiday_delay(): void {
        self::add_acf_inner_field(self::issues, self::iss_holiday_delay, [
            'label' => 'Publication delayed by holiday?',
            'type' => 'true_false',
            'instructions' => 'e.g. Labour Day: posts online Monday, but paper'
                . ' prints Tuesday',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '18',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'iss_issuu_url' field.
	 */
    static function add_iss_issuu_url(): void {
        self::add_acf_inner_field(self::issues, self::iss_issuu_url, [
            'label' => 'Issuu URL',
            'type' => 'url',
            'instructions' => 'The PDF of the issue from Issuu.',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::external),
                        'operator' => '==',
                        'value' => 'issuu',
                    ]
                ],
                [
                    [
                        'field' => self::qualify_field(self::external),
                        'operator' => '==',
                        'value' => 'both',
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '34',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
        ]);
    }

	/**
	 * Add the 'iss_archiveorg_url' field.
	 */
    static function add_iss_archiveorg_url(): void {
        self::add_acf_inner_field(self::issues, self::iss_archiveorg_url, [
            'label' => 'archive.org URL',
            'type' => 'url',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::external),
                        'operator' => '==',
                        'value' => 'archiveorg',
                    ]
                ],
                [
                    [
                        'field' => self::qualify_field(self::external),
                        'operator' => '==',
                        'value' => 'both',
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '34',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => 'https://archive.org/details/erindalianmedium/...',
        ]);
    }

	/**
	 * Add the 'iss_on_site' field.
	 */
    static function add_iss_on_site(): void {

        // Need to get websites in order to check whether this field appears
        // N.B. Website ACF must be loaded by this point
        $main = MJKVIAPI::get_main_website();

        self::add_acf_inner_field(self::issues, self::iss_on_site, [
            'label' => 'Issue is available on the given website',
            'type' => 'true_false',
            'instructions' => 'Uncheck if this issue is missing from the website the rest of the volume can be found on',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::website),
                        'operator' => '!=',
                        'value' => $main->name,
                    ],
                    [
                        'field' => self::qualify_field(self::website),
                        'operator' => '!=',
                        'value' => '',
                    ]
                ]
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 1,
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'iss_posts_url' field.
	 */
    static function add_iss_posts_url(): void {

        // Need to get websites in order to check whether this field appears
        // N.B. Website ACF must be loaded by this point
        $main = MJKVIAPI::get_main_website();

        self::add_acf_inner_field(self::issues, self::iss_posts_url, [
            'label' => 'Posts URL',
            'type' => 'url',
            'instructions' => 'If the best place to find these posts is one of our websites and not the main one,'
            . ' you can enter a link directly to the posts.',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::website),
                        'operator' => '!=',
                        'value' => $main->name,
                    ],
                    [
                        'field' => self::qualify_field(self::website),
                        'operator' => '!=',
                        'value' => '',
                    ],
                    [
                        'field' => self::qualify_field(self::iss_on_site),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '55',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
        ]);
    }

	/**
	 * Add the 'iss_notes' field.
	 */
    static function add_iss_notes(): void {
        self::add_acf_inner_field(self::issues, self::iss_notes, [
            'label' => 'Notes (optional)',
            'type' => 'text',
            'instructions' => 'A totally optional place to add general notes on the issue.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '100',
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
	 * Add the 'iss_is_special' field.
	 */
    static function add_iss_is_special(): void {
        self::add_acf_inner_field(self::issues, self::iss_is_special, [
            'label' => 'Special issue?',
            'type' => 'true_false',
            'instructions' => 'e.g. a special name or special numbering',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'iss_special' field.
	 */
    static function add_iss_special(): void {
        self::add_acf_inner_field(self::issues, self::iss_special, [
            'label' => 'Special name',
            'type' => 'text',
            'instructions' => 'An optional special name for the issue (e.g. April Fool\'s).',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::iss_is_special),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '45',
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
	 * Add the 'iss_skip' field.
	 */
    static function add_iss_skip(): void {
        self::add_acf_inner_field(self::issues, self::iss_skip, [
            'label' => 'Skip numbering?',
            'type' => 'true_false',
            'instructions' => 'When numbering issues, skip this one?',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::iss_is_special),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }


	/*
	 * =========================================================================
	 * Field filling
	 * =========================================================================
	 */
    
    /**
     * Modify the website query to set the proper sort.
     *
     * @param array $args
     * @param array $field
     * @param string $post_id
     * @return array The modified query args.
     */
    static function set_website_sort(array $args, array $field,
            string $post_id): array {
        
        $args['order'] = 'DESC';
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = MJKVI_CPT_ArchivalWebsite::sort_key();
        
        return $args;
    }
    
    /**
     * Filter the websites to add a note about which one is the main one.
     *
     * @param string $title
     * @param WP_Post $post
     * @param array $field
     * @param int $post_id
     * @return string The filtered title.
     */
    static function filter_websites(string $title, WP_Post $post, array $field,
            int $post_id): string {
        
        $main = MJKVI_ACF_AW::get(MJKVI_ACF_AW::main, $post->ID);
        
        // If this is not the main website, just use its title
        if (!$main) {
            return $title;
            
        // Otherwise remove this function from the filter and modify title
        } else {
            
            remove_filter(sprintf('acf/fields/post_object/result/key=%s',
                self::qualify_field(self::website)),
                    [__CLASS__, 'filter_websites']);
            
            return $title . ' (Main site)';
        }
    }
}
