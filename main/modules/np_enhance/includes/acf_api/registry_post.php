<?php

/**
 * An ACF registry for the per-post options.
 */
class MJKNPE_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_POST; }
    
    // Field
    const hide_feat     = 'hide_feat';
    const mainly_photo    = 'mainly_photo';

	/**
	 * Add the filters for the group and fields.
	 */
    static function add_filters(): void {
        add_action('acf/init', [__CLASS__, 'add_group']);
        add_action('acf/init', [__CLASS__, 'add_hide_feat']);
	    add_action('acf/init', [__CLASS__, 'add_mainly_photo']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => 'MJK Newspaper Enhancements',
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
	 * Add the 'hide_feat' field.
	 */
    static function add_hide_feat(): void {
        self::add_acf_field(self::hide_feat, [
            'label' => 'Hide featured image?',
            'type' => 'true_false',
            'instructions' => 'For posts in which you want to add a featured'
            . ' image so that it shows up as a thumbnail on category and'
            . ' archive pages and such, but not have it display in the post.'
            . ' This can be set on a per-category basis on the settings page.',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
            ],
            'default_value' => 0,
            'ui' => 1
        ]);
    }

	/**
	 * Add the 'mainly_photo' field.
	 */
	static function add_mainly_photo(): void {
		self::add_acf_field(self::mainly_photo, [
			'label' => 'Primarily a photo or photo gallery?',
			'type' => 'true_false',
			'instructions' => 'If this is set, the photographer will be'
							  . ' credited even wherever succinct credits are,'
							  . ' used e.g. in category listings.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'default_value' => 0,
			'ui' => 1
		] );
	}
}
