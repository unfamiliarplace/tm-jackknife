<?php

/**
 * ACF for adding files to articles. Uses the below_wysiwyg group.
 */
class MJKEnrich_ACF_Files extends MJKEnrich_ACF_Below_WYSIWYG {

	/**
	 * Define the unique data ID -- important since the group is shared.
	 *
	 * @return string
	 */
	static function data_id(): string { return 'files'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Fields
    const files = 'files';
    const file_desc = 'file_desc';
    const file_id = 'file_id';

	/**
	 * Add the field filters.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_files']);
        add_action('acf/init', [__CLASS__, 'add_file_desc']);
        add_action('acf/init', [__CLASS__, 'add_file_id']);
    }

	/**
	 * Add the 'files' field.
	 */
    static function add_files(): void {
        self::add_acf_field(self::files, [
            'label' => 'Files',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'layout' => 'block',
            'button_label' => 'Add file',
            'collapsed' => self::qualify_field(self::file_desc)
        ]);
    }

	/**
	 * Add the 'file_desc' field.
	 */
    static function add_file_desc(): void {
        self::add_acf_inner_field(self::files, self::file_desc, [
            'label' => 'Description of the file',
            'type' => 'text',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => 70,
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ]);
    }

	/**
	 * Add the 'file_id' field.
	 */
    static function add_file_id(): void {
        self::add_acf_inner_field(self::files, self::file_id, [
            'label' => 'File',
            'type' => 'file',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => 30,
                'class' => '',
                'id' => '',
            ],
            'return_format' => 'id',
            'library' => 'all',
            'min_size' => '',
            'max_size' => '',
            'mime_types' => '',
        ]);
    }
}
