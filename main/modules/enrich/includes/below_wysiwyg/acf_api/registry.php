<?php

/**
 * This creates a group for items below the post WYSIWYG editor.
 * It adds no fields on its own, but is meant to be shared by the submodules.
 */
class MJKEnrich_ACF_Below_WYSIWYG extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }

	/**
	 * Define the group.
	 *
	 * @return string
	 */
    static function group(): string { return 'below_wysiwyg'; }

	/**
	 * Add the filters.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        static::add_acf_group([
            'title' => sprintf('%s Article Enrichment', JKNAPI::space()->name()),
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'post',
                    ],
                ],
            ],
            'menu_order' => 3,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }
}
