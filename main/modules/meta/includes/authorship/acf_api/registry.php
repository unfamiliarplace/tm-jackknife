<?php

/**
 * An ACF registry for the authorship portion.
 */
final class MJKMeta_ACF_Auth extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    /**
     * Define a unique group ID.
     *
     * @return string
     */
    static function group(): string { return 'auth'; }

    // Fields
    const authors = 'authors';
    const notes_contributors = 'notes_contributors';
    const photographers = 'photographers';
    const outside_photo_sources = 'outside_photo_sources';
    const outside_photo_source = 'outside_photo_source';
    const videographers = 'videographers';

    /**
     * Add the ACF filters to register the group and fields.
     */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_authors']);
        add_action('acf/init', [__CLASS__, 'add_notes_contributors']);
        add_action('acf/init', [__CLASS__, 'add_photographers']);
        
        add_action('acf/init', [__CLASS__, 'add_outside_photo_sources']);
        add_action('acf/init', [__CLASS__, 'add_outside_photo_source']);
        
        add_action('acf/init', [__CLASS__, 'add_videographers']);
    }


	/*
	 * =========================================================================
	 * Field adding
	 * =========================================================================
	 */

    /**
     * Add the group.
     */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s Authorship', JKNAPI::space()->name()),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 2,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'field',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => 'These replace the use of WordPress\'s "author"'
	            . ' metabox.',
        ]);
    }    
    
    /**
     * Add the 'authors' field.
     */
    static function add_authors(): void {
        self::add_acf_field(self::authors, [
            'label' => 'Author(s)',
            'type' => 'user',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '50',
                'class' => '',
                'id' => '',
            ],
            'role' => [
	            0 => 'author',
            ],
            'multiple' => 1,
            'allow_null' => 0,
        ]);
    }

    /**
     * Add the 'notes_contributors' field.
     */
    static function add_notes_contributors(): void {
        self::add_acf_field(self::notes_contributors, [
            'label' => 'Notes contributor(s)',
            'type' => 'user',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '50',
                'class' => '',
                'id' => '',
            ],
            'role' => [
                0 => 'author',
            ],
            'multiple' => 1,
            'allow_null' => 0,
        ]);
    }    

    /**
     * Add the 'photographers' field.
     */
    static function add_photographers(): void {
        self::add_acf_field(self::photographers, [
            'label' => 'Photographer(s)',
            'type' => 'user',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '35',
                'class' => '',
                'id' => '',
            ],
            'role' => [
                0 => 'author',
            ],
            'multiple' => 1,
            'allow_null' => 0,
        ]);
    }
    
    /**
     * Add the 'outside_photo_sources' field.
     */
    static function add_outside_photo_sources(): void {
        self::add_acf_field(self::outside_photo_sources, [
            'label' => 'Outside photo sources',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '35',
                'class' => '',
                'id' => '',
            ],
            'min' => 0,
            'max' => 0,
            'layout' => 'block',
            'button_label' => 'Add outside photo source',
        ]);
    }

    /**
     * Add the 'outside_photo_source' field.
     */
    static function add_outside_photo_source(): void {
        self::add_acf_inner_field(self::outside_photo_sources,
                self::outside_photo_source, [
            'label' => 'Outside photo source',
            'type' => 'text',
            'instructions' => 'url or photographer/publication',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ]);
    }

    /**
     * Add the 'videographers' field.
     */    
    static function add_videographers(): void {
        self::add_acf_field(self::videographers, [
            'label' => 'Videographers',
            'type' => 'user',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '30',
                'class' => '',
                'id' => '',
            ],
            'role' => [
                0 => 'author',
            ],
            'multiple' => 1,
            'allow_null' => 0,
        ]);
    }
}
