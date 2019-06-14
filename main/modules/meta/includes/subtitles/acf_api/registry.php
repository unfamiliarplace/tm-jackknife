<?php

/**
 * ACF registry for a subtitle field group.
 */
class MJKMeta_ACF_Subtitle extends JKNACF {

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
    static function group(): string { return 'sub'; }
    
    // Field
    const subtitle = 'subtitle';

	/**
	 * Add the filters for the group and field.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_subtitle']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => 'MJK Subtitle',
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'seamless',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',]);
    }

	/**
	 * Add the 'subtitle' field.
	 */
    static function add_subtitle(): void {
        self::add_acf_field(self::subtitle, [
            'label' => 'Subtitle',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
            ],
            'default_value' => '',
            'placeholder' => 'Subtitle',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ] );
    }
}
