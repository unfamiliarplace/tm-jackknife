<?php

/**
 * An ACF registry for the options page.
 */
final class MJKNPE_ACF_Options extends JKNACF {

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
	const replace_author_page   = 'replace_author_page';
	const show_role_from        = 'show_role_from';
	const hide_feat_image_cats  = 'hide_feat_image_cats';
	const mainly_photo_cats     = 'mainly_photo_cats';
	const disable_contact       = 'disable_contact';
	const disable_viewcount     = 'disable_viewcount';

	/**
	 * Add the filters for the group and fields.
	 */
	static function add_filters(): void {

		// The group
		add_action('acf/init', [__CLASS__, 'add_group']);

		// The fields
		self::add_tab('Authors');
		add_action('acf/init', [__CLASS__, 'add_replace_author_page']);
		add_action('acf/init', [__CLASS__, 'add_show_role_from']);

		self::add_tab('Images');
		add_action('acf/init', [__CLASS__, 'add_hide_feat_image_cats']);
		add_action('acf/init', [__CLASS__, 'add_mainly_photo_cats']);

		self::add_tab('Miscellaneous');
		add_action('acf/init', [__CLASS__, 'add_disable_contact']);
		add_action('acf/init', [__CLASS__, 'add_disable_viewcount']);
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
	 * Add the 'replace_author_page' field.
	 */
	static function add_replace_author_page(): void {
		self::add_acf_field(self::replace_author_page, [
			'label' => 'Replace all author pages with our custom one?',
			'type' => 'true_false',
			'instructions' => 'Ours shows an author box including masthead' .
			                  ' roles, and shows all contribution types, not just author.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'default_value' => 1,
			'ui' => 1
		]);
	}

	/**
	 * Add the 'show_role_from' field.
	 */
	static function add_show_role_from(): void {
		self::add_acf_field(self::show_role_from, [
			'label' => 'Show author role from current year or publication year?',
			'layout' => 'horizontal',
			'type' => 'radio',
			'instructions' => 'When citing user roles on articles, consider' .
			                  ' their most recent role or the year the article' .
			                  ' was published? ("Preferred" roles take' .
			                  ' precedence over both.)',
			'choices' => [
				'current' => 'Current',
				'publication' => 'Publication',
			],
			'default_value' => 'current',
			'other_choice' => 0,
			'save_other_choice' => 0,
			'allow_null' => 0,
			'return_format' => 'value',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'hide_feat_image_cats' field.
	 */
	static function add_hide_feat_image_cats(): void {
		self::add_acf_field(self::hide_feat_image_cats, [
			'label' => 'Hide the featured image on these categories',
			'type' => 'taxonomy',
			'instructions' => 'Hiding a featured image allows you to have a' .
							' thumbnail without showing the image prominently.',
			'required' => 0,
			'conditional_logic' => 0,
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

	/**
	 * Add the 'mainly_photo_cats' field.
	 */
	static function add_mainly_photo_cats(): void {
		self::add_acf_field(self::mainly_photo_cats, [
			'label' => 'The following categories are mainly photos/galleries',
			'type' => 'taxonomy',
			'instructions' => 'In succinct credits (e.g. category lists),' .
			                  ' usually only the author is shown. But these' .
			                  ' categories will also credit the photographer.',
			'required' => 0,
			'conditional_logic' => 0,
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

	/**
	 * Add the 'disable_contact' field.
	 */
	static function add_disable_contact(): void {
		self::add_acf_field(self::disable_contact, [
			'label' => 'Remove extra contact methods on the user edit screen?',
			'type' => 'true_false',
			'instructions' => 'The theme adds a large number of rarely used' .
			                  ' contact methods that take a while to scroll' .
			                  ' through. Check this option to disable them.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'default_value' => 1,
			'ui' => 1
		]);
	}

	/**
	 * Add the 'disable_viewcount' field.
	 */
	static function add_disable_viewcount(): void {
		self::add_acf_field(self::disable_viewcount, [
			'label' => 'Disable AJAX post view counting?',
			'type' => 'true_false',
			'instructions' => 'Even if you disable it in the Theme Panel,' .
			                  ' this feature is still running and counting' .
			                  ' views at the cost of CPU usage. Disable it' .
			                  ' here to fully remove it. Note: This should not' .
			                  ' be disabled if the Theme Panel is set to show' .
			                  ' post viewcounts!',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'default_value' => 0,
			'ui' => 1
		]);
	}
}
