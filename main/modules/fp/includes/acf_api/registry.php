<?php

/**
 * An ACF group for a Front Page Card object type.
 */
class MJKFP_ACF extends JKNACF {

	/**
	 * Define the group.
	 *
	 * @return string
	 */
    static function group(): string { return 'fpc'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }

    // Fields
    
    // "Recency" of recent posts
    const recent_weeks = 2;
    
    // Allow older
    const recent_only = 'recent_only';
    
    // Spotlight repeater and subfields
    const spotlight = 'spotlight';
    const spotlight_type = 'spotlight_type';
    const spotlight_post = 'spotlight_post';
    const spotlight_post_older = 'spotlight_post_older';
    const spotlight_notice_header = 'spotlight_notice_header';
    const spotlight_notice_body = 'spotlight_notice_body';
    const spotlight_notice_link = 'spotlight_notice_link';
    
    // Spotlight randomization
    const shuffle_spotlights = 'shuffle_spotlights';
    
    // Slides repeater and subfields
    const slides = 'slides';
    const slides_big_post = 'slides_big_post';
    const slides_big_post_older = 'slides_big_post_older';
    const slides_small = 'slides_small';
    const slides_small_post = 'slides_small_post';
    const slides_small_post_older = 'slides_small_post_older';
    
    // Slide randomization
    const shuffle_all_posts = 'shuffle_all_posts';
    const shuffle_slides = 'shuffle_slides';
    const shuffle_posts = 'shuffle_posts';
    const shuffle_big_post = 'shuffle_big_post';

	/**
	 * Add the group and field filters.
	 */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Spotlight
	    self::add_tab('Spotlight');

	    // Spotlight randomization
	    add_action('acf/init', [__CLASS__, 'add_shuffle_spotlights']);

	    // Spotlight posts
        add_action('acf/init', [__CLASS__, 'add_spotlight']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_type']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_post']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_post_older']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_notice_header']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_notice_body']);
        add_action('acf/init', [__CLASS__, 'add_spotlight_notice_link']);

        // Slides
	    self::add_tab('Slides');

	    // Slide randomization
	    add_action('acf/init', [__CLASS__, 'add_shuffle_all_posts']);
	    add_action('acf/init', [__CLASS__, 'add_shuffle_slides']);
	    add_action('acf/init', [__CLASS__, 'add_shuffle_posts']);
	    add_action('acf/init', [__CLASS__, 'add_shuffle_big_post']);

	    // Slide panels
        add_action('acf/init', [__CLASS__, 'add_slides']);
        add_action('acf/init', [__CLASS__, 'add_slides_big_post']);
        add_action('acf/init', [__CLASS__, 'add_slides_big_post_older']);
        add_action('acf/init', [__CLASS__, 'add_slides_small']);
        add_action('acf/init', [__CLASS__, 'add_slides_small_post']);
        add_action('acf/init', [__CLASS__, 'add_slides_small_post_older']);

	    // Allow older
	    self::add_tab('Recency');
	    add_action('acf/init', [__CLASS__, 'add_recent_only']);


	    // Fill posts for spotlight and slider
	    $filled = [
	    	self::spotlight_post,
		    self::slides_big_post,
		    self::slides_small_post];

        foreach ($filled as $rpf) {
            add_filter(sprintf('acf/fields/post_object/query/key=%s',
	            self::qualify_field($rpf)),
	            [__CLASS__, 'filter_recent_posts'], 10, 3);
        }
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s Front Page Card', JKNAPI::space()->name()),
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => MJKFP_CPT_FPCard::qid(),
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

	/**
	 * Add the 'recent_only' field.
	 */
    static function add_recent_only(): void {
        self::add_acf_field(self::recent_only, [
            'label' => 'Only show recent articles in these post selectors?',
            'default_value' => 1,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => 'Speeds up article finding, but gives less choice.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'spotlight' field.
	 */
    static function add_spotlight(): void {
        self::add_acf_field(self::spotlight, [
            'label' => 'Spotlight posts',
            'min' => 0,
            'max' => 5,
            'layout' => 'row',
            'button_label' => 'Add spotlight',
            'collapsed' => self::qualify_field(self::spotlight_post),
            'type' => 'repeater',
            'instructions' => 'These are the text-only posts highlighted just below the menu on the front page. You can add up to 5 of them, or remove all of them to turn off spotlight altogether.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'spotlight_type' field.
	 */
    static function add_spotlight_type(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_type, [
            'label' => 'Type',
            'layout' => 'horizontal',
            'choices' => [
                'article' => 'Article',
                'notice' => 'Notice',
            ],
            'default_value' => 'Article',
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'type' => 'radio',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'spotlight_post' field.
	 */
    static function add_spotlight_post(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_post, [
            'label' => 'Article (recent only)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::spotlight_type),
                        'operator' => '==',
                        'value' => 'article',
                    ],
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ]
        ]);
    }

	/**
	 * Add the 'spotlight_post_older' field.
	 */
    static function add_spotlight_post_older(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_post_older, [
            'label' => 'Article (older included)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::spotlight_type),
                        'operator' => '==',
                        'value' => 'article',
                    ],
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'spotlight_notice_header' field.
	 */
    static function add_spotlight_notice_header(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_notice_header, [
            'label' => 'Notice title',
            'default_value' => 'Notice',
            'maxlength' => 20,
            'placeholder' => 'Notice',
            'prepend' => '',
            'append' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::spotlight_type),
                        'operator' => '==',
                        'value' => 'notice',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'readonly' => 0,
            'disabled' => 0,
        ]);
    }

	/**
	 * Add the 'spotlight_notice_body' field.
	 */
    static function add_spotlight_notice_body(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_notice_body, [
            'label' => 'Notice body',
            'default_value' => '',
            'maxlength' => 64,
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::spotlight_type),
                        'operator' => '==',
                        'value' => 'notice',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'readonly' => 0,
            'disabled' => 0,
        ]);
    }

	/**
	 * Add the 'spotlight_notice_link' field.
	 */
    static function add_spotlight_notice_link(): void {
        self::add_acf_inner_field(self::spotlight, self::spotlight_notice_link, [
            'label' => 'Notice link',
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::spotlight_type),
                        'operator' => '==',
                        'value' => 'notice',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'readonly' => 0,
            'disabled' => 0,
        ]);
    }

	/**
	 * Add the 'shuffle_spotlights' field.
	 */
    static function add_shuffle_spotlights(): void {
        self::add_acf_field(self::shuffle_spotlights, [
            'label' => 'Shuffle order of spotlights?',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '20',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'slides' field.
	 */
    static function add_slides(): void {
        self::add_acf_field(self::slides, [
            'label' => 'Slides',
            'min' => 1,
            'max' => 4,
            'layout' => 'row',
            'collapsed' => '',
            'type' => 'repeater',
            'instructions' => 'These are the slides on the front page. These will only display if the slider is set to Medium Big Grid Slide. You can choose one or two slides.',
            'required' => 1,
            'conditional_logic' => 0,
            'button_label' => 'Add slide',
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'slides_big_post' field.
	 */
    static function add_slides_big_post(): void {
        self::add_acf_inner_field(self::slides, self::slides_big_post, [
            'label' => 'Big article (recent only)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'slides_big_post_older' field.
	 */
    static function add_slides_big_post_older(): void {
        self::add_acf_inner_field(self::slides, self::slides_big_post_older, [
            'label' => 'Big article (older included)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'slides_small' field.
	 */
    static function add_slides_small(): void {
        self::add_acf_inner_field(self::slides, self::slides_small, [
            'label' => 'Small articles',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'collapsed' => self::qualify_field(self::slides_small_post),
            'min' => 3,
            'max' => 3,
            'layout' => 'row',
            'button_label' => 'Add article',
            'sub_fields' => [],
        ]);
    }

	/**
	 * Add the 'slides_small' field.
	 */
    static function add_slides_small_post(): void {
        self::add_acf_inner_field(self::slides_small, self::slides_small_post, [
            'label' => 'Article (recent only)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'slides_small' field.
	 */
    static function add_slides_small_post_older(): void {
        self::add_acf_inner_field(self::slides_small, self::slides_small_post_older, [
            'label' => 'Article (older included)',
            'post_type' => [
                0 => 'post',
            ],
            'taxonomy' => [],
            'allow_null' => 0,
            'multiple' => 0,
            'return_format' => 'object',
            'ui' => 1,
            'type' => 'post_object',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::recent_only),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '80',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'shuffle_all_posts' field.
	 */
    static function add_shuffle_all_posts(): void {
        self::add_acf_field(self::shuffle_all_posts, [
            'label' => 'Shuffle the order of all individual posts across all slides?',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '100',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'shuffle_slides' field.
	 */
    static function add_shuffle_slides(): void {
        self::add_acf_field(self::shuffle_slides, [
            'label' => 'Shuffle the order of the slides?',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::shuffle_all_posts),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'shuffle_posts' field.
	 */
    static function add_shuffle_posts(): void {
        self::add_acf_field(self::shuffle_posts, [
            'label' => 'Shuffle the order of small posts within slides?',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::shuffle_all_posts),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'shuffle_big_post' field.
	 */
    static function add_shuffle_big_post(): void {
        self::add_acf_field(self::shuffle_big_post, [
            'label' => 'Shuffle the big post along with the small posts in each slide?',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::shuffle_posts),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::shuffle_all_posts),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }


	/*
	 * =========================================================================
	 * Field filling
	 * =========================================================================
	 */

	/**
	 * Filter the choices for recent posts.
	 *
	 * @param array $args
	 * @param array $field
	 * @param string $pid
	 * @return array
	 */
    static function filter_recent_posts(array $args, array $field,
	        string $pid): array {
        
        $dt = JKNTime::dt_now();
        $dt->sub(new DateInterval(sprintf('P%sW', self::recent_weeks)));

        $args['date_query'] = [
            'after' => $dt->format('Ymd H:i:s e')
        ];

        return $args;
    }
}
