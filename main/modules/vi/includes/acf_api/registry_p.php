<?php

/**
 * The ACF field group for an individual post.
 */
class MJKVI_ACF_P extends JKNACF {

	/**
	 * Define a unique group.
	 *
	 * @return string
	 */
    static function group(): string { return 'p'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }

    // Field
    const no_iss = 'no_iss';

	/**
	 * Add the group and field filters.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_no_iss']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s — Post options', JKNAPI::module()->name()),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 1,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => 'Settings for this post in volume and issue.',
        ]);
    }

	/**
	 * Add the 'no_iss' field.
	 */
    static function add_no_iss(): void {
        self::add_acf_field(self::no_iss, [
            'label' => 'Avoid attaching to a print issue?',
            'type' => 'true_false',
            'instructions' => 'If this is selected, the post will belong to a volume, but not to any issue.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'message' => '',
            'default_value' => 0,
            'ui' => 1,
        ]);
    }
}
