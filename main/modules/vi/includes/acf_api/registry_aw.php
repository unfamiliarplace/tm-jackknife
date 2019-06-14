<?php

/**
 * ACF field group for the archival website custom post type.
 */
class MJKVI_ACF_AW extends JKNACF {

	/**
	 * Define a unique group.
	 *
	 * @return string
	 */
    static function group(): string { return 'aw'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Fields
    const name = 'name';
    const link = 'link';
    const current = 'current';
    const main = 'main';
    const date_from = 'date_from';
    const date_till = 'date_till';
    const thumb = 'thumb';
    const notes = 'notes';

	/**
	 * Add the filters for the group and fields.
	 */
	static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_name']);
        add_action('acf/init', [__CLASS__, 'add_link']);
        add_action('acf/init', [__CLASS__, 'add_date_from']);
        add_action('acf/init', [__CLASS__, 'add_current']);
        add_action('acf/init', [__CLASS__, 'add_main']);
        add_action('acf/init', [__CLASS__, 'add_date_till']);
        add_action('acf/init', [__CLASS__, 'add_thumb']);
        add_action('acf/init', [__CLASS__, 'add_notes']);
    }

	/**
	 * Add the group.
	 */
	static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s — %s',
                    JKNAPI::module()->name(), MJKVI_CPT_ArchivalWebsite::name()),
            'fields' => [],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => MJKVI_CPT_ArchivalWebsite::qid()
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
	 * Add the 'name' field.
	 */
	static function add_name(): void {
        self::add_acf_field(self::name, [
            'label' => 'Name',
            'type' => 'text',
            'instructions' => 'An identifier for the website',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '50',
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
	 * Add the 'link' field.
	 */
	static function add_link(): void {
        self::add_acf_field(self::link, [
            'label' => 'Link',
            'type' => 'url',
            'instructions' => 'The link to the website. Preferably a subdomain of themedium.ca.',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '50',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
        ]);
    }

	/**
	 * Add the 'date_from' field.
	 */
	static function add_date_from(): void {
        self::add_acf_field(self::date_from, [
            'label' => 'In use from',
            'type' => 'date_picker',
            'instructions' => 'The start of this website\'s use.',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'display_format' => 'F j, Y',
            'return_format' => 'Ymd',
            'first_day' => 1,
        ]);
    }

	/**
	 * Add the 'current' field.
	 */
	static function add_current(): void {
        self::add_acf_field(self::current, [
            'label' => 'Currently in use?',
            'type' => 'true_false',
            'instructions' => 'Whether the website is currently in use or is  archival.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'main' field.
	 */
	static function add_main(): void {
        self::add_acf_field(self::main, [
            'label' => 'Is this our main website?',
            'type' => 'true_false',
            'instructions' => 'Whether this is the main website of The Medium.',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::current),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }

	/**
	 * Add the 'date_till' field.
	 */
	static function add_date_till(): void {
        self::add_acf_field(self::date_till, [
            'label' => 'In use till',
            'type' => 'date_picker',
            'instructions' => 'When this website stopped being in active use.',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::current),
                        'operator' => '!=',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'display_format' => 'F j, Y',
            'return_format' => 'Ymd',
            'first_day' => 1,
        ]);
    }

	/**
	 * Add the 'thumb' field.
	 */
	static function add_thumb(): void {
        self::add_acf_field(self::thumb, [
            'label' => 'Thumbnail',
            'type' => 'image',
            'instructions' => 'An image representing the website. Preferably a screenshot. Small size is fine.',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '40',
                'class' => '',
                'id' => '',
            ],
            'return_format' => 'id',
            'preview_size' => 'thumbnail',
            'library' => 'all',
            'min_width' => '',
            'min_height' => '',
            'min_size' => '',
            'max_width' => '',
            'max_height' => '',
            'max_size' => '',
            'mime_types' => '',
        ]);
    }

	/**
	 * Add the 'notes' field.
	 */
	static function add_notes(): void {
        self::add_acf_field(self::notes, [
            'label' => 'Notes',
            'type' => 'textarea',
            'instructions' => 'Notes on the website\'s purpose or history. Which articles or which type of content can be found here?',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '60',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => 4,
            'new_lines' => '',
        ]);
    }
}
