<?php

/**
 * An ACF registry for the options page.
 */
final class MJKVI_ACF_Options extends JKNACF {

	/**
	 * Define a unique group.
	 *
	 * @return string
	 */
	static function group(): string { return 'opts'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

	// Fields
	const advertise          = 'advertise';
	const no_issue_cats      = 'no_issue_cats';

	/**
	 * Add the filters for the group and fields.
	 */
	static function add_filters(): void {

		// The group
		add_action('acf/init', [__CLASS__, 'add_group']);

		// The fields
		add_action('acf/init', [__CLASS__, 'add_advertise']);
		add_action('acf/init', [__CLASS__, 'add_no_issue_cats']);
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
			'description' => '',
		]);
	}

	/**
	 * Add the 'advertise' field.
	 */
	static function add_advertise(): void {
		self::add_acf_field(self::advertise, [
			'label' => 'Show a link to advertise for future issues?',
			'type' => 'true_false',
			'instructions' => 'This appears on volume archives.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 1,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'no_issue_cats' field.
	 */
	static function add_no_issue_cats(): void {
		self::add_acf_field(self::no_issue_cats, [
			'label' => 'For these categories, do not attach posts to a specific'
				. ' issue.<br><em>The post will then link to the volume, but' .
			           ' not vice versa.</em>',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '75',
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
