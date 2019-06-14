<?php

/**
 * ACF registry for a subtitle field group.
 */
class MJKEnrich_ACF_Drop_Caps extends JKNACF {

	/**
	 * Define the group.
	 *
	 * @return string
	 */
	static function group(): string { return 'dc'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Field
    const use_drop_cap = 'use_drop_cap';

	/**
	 * Add the group and field filters.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_use_drop_cap']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group( [
            'title' => 'MJK Article Enrichment Drop Caps',
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
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }

	/**
	 * Add the 'use_drop_cap' field.
	 */
    static function add_use_drop_cap(): void {
        self::add_acf_field(self::use_drop_cap, [
            'label' => 'Enable drop cap?',
            'type' => 'true_false',
            'instructions' => 'Uncheck to turn off the drop cap for this post.' .
	            ' Has no effect if the global setting is off or the'
                . ' category is excluded.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
            ],
            'default_value' => 1,
            'ui' => 1
        ]);
    }    
}
