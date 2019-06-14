<?php

/**
 * An ACF registry for the options page. Can show options from all submodules.
 */
final class MJKEnrich_ACF extends JKNACF {

	/**
	 * Define the type.
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

	// Fields
	const use_drop_cap          = 'use_drop_cap';
	const no_drop_cap_cats      = 'no_drop_cap_cats';

	/**
	 * Add the filters.
	 */
	static function add_filters(): void {

		// The group
		add_action('acf/init', [__CLASS__, 'add_group']);

		// The fields
		self::add_tab('Drop caps');
		add_action('acf/init', [__CLASS__, 'add_use_drop_cap']);
		add_action('acf/init', [__CLASS__, 'add_no_drop_cap_cats']);
	}

	/**
	 * Add the group.
	 */
	static function add_group(): void {
		self::add_acf_group([
			'title' => sprintf('%s %s Settings',
				JKNAPI::space()->name(), JKNAPI::module()->name()),
			'location' => [
				[
					[
						'param' => 'options_page',
						'operator' => '==',
						'value' => JKNAPI::settings_page()->slug()
					],
				],
			],
			'menu_order' => 0,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => ''
		]);
	}

	/**
	 * Add the 'use_drop_cap' field.
	 */
	static function add_use_drop_cap(): void {
		self::add_acf_field(self::use_drop_cap, [
			'label' => 'Enable drop caps on articles?',
			'type' => 'true_false',
			'instructions' => 'A drop cap is the larger first letter of a' .
				' post.<br>This setting overrides individual post settings.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'no_drop_cap_cats' field.
	 */
	static function add_no_drop_cap_cats(): void {
		self::add_acf_field(self::no_drop_cap_cats, [
			'label' => 'Suppress drop caps for the following categories',
			'type' => 'taxonomy',
			'instructions' => 'This setting overrides individual posts being on.',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_drop_cap),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'taxonomy' => 'category',
			'field_type' => 'multi_select',
			'allow_null' => 0,
			'add_term' => 0,
			'save_terms' => 0,
			'load_terms' => 0,
			'return_format' => 'id',
			'ajax'  => 0
		]);
	}
}
