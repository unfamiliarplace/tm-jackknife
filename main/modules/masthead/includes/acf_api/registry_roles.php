<?php

/**
 * An ACF registry for the roles page.
 */
final class MJKMH_ACF_Roles extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

	/**
	 * Return a unique group ID.
	 */
	static function group(): string { return 'roles'; }

	/*
	 * =========================================================================
	 * Fields
	 * =========================================================================
	 */

	const show_emails   = 'show_emails';
	const divisions     = 'divisions';
	const name          = 'name';
	const notes         = 'notes';
	const d_priority    = 'd_priority';
	const roles         = 'roles';
	const title         = 'title';
	const r_priority    = 'r_priority';
	const voting        = 'voting';
	const email         = 'email';
	const archival      = 'archival';
	const aliases       = 'aliases';
	const alias         = 'alias';

	/**
	 * Add the filters for the group and fields.
	 */
	static function add_filters(): void {
		add_action('acf/init', [__CLASS__, 'add_group']);

		self::add_tab('Divisions');
		add_action('acf/init', [__CLASS__, 'add_divisions']);
		add_action('acf/init', [__CLASS__, 'add_name']);
		add_action('acf/init', [__CLASS__, 'add_notes']);
		add_action('acf/init', [__CLASS__, 'add_d_priority']);
		add_action('acf/init', [__CLASS__, 'add_roles']);
		add_action('acf/init', [__CLASS__, 'add_title']);
		add_action('acf/init', [__CLASS__, 'add_r_priority']);
		add_action('acf/init', [__CLASS__, 'add_voting']);
		add_action('acf/init', [__CLASS__, 'add_email']);
		add_action('acf/init', [__CLASS__, 'add_archival']);
		add_action('acf/init', [__CLASS__, 'add_aliases']);
		add_action('acf/init', [__CLASS__, 'add_alias']);

		self::add_tab('Layout');
		add_action('acf/init', [__CLASS__, 'add_show_emails']);
	}

	/**
	 * Add the group.
	 */
	static function add_group(): void {
		$mod = JKNAPI::module();

		self::add_acf_group([
			'title' => sprintf('%s %s Roles',
			$mod->space()->name(), $mod->name()),
            'location' => [
				[
					[
						'param' => 'options_page',
						'operator' => '==',
						'value' => JKNAPI::settings_page('roles')->slug()
					],
				],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'normal',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
		]);
	}

	/**
	 * Add the 'show_emails' field.
	 */
	static function add_show_emails(): void {
		self::add_acf_field(self::show_emails, [
			'label'     => 'Show emails on current masthead page?',
			'type'      => 'true_false',
			'instructions' => 'Public emails may be helpful for readers,'
				.' but increase the risk of exposure to spam.',
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

	/**
	 * Add the 'divisions' field.
	 */
	static function add_divisions(): void {
		self::add_acf_field(self::divisions, [
			'label'     => 'Divisions',
			'type'      => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => '',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add division'
		]);
	}

	/**
	 * Add the 'name' field.
	 */
	static function add_name(): void {
		self::add_acf_inner_field(self::divisions, self::name, [
			'label'     => 'Name',
			'type'      => 'text',
			'instructions' => 'The name of the division.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			]
		]);
	}

	/**
	 * Add the 'notes' field.
	 */
	static function add_notes(): void {
		self::add_acf_inner_field(self::divisions, self::notes, [
			'label'     => 'Notes',
			'type'      => 'text',
			'instructions' => 'Any notes pertinent to the division.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '40',
				'class' => '',
				'id' => '',
			]
		]);
	}

	/**
	 * Add the 'd_priority' field.
	 */
	static function add_d_priority(): void {
		self::add_acf_inner_field(self::divisions, self::d_priority, [
			'label'     => 'Priority',
			'type'      => 'number',
			'instructions' => 'Lower = more important.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
			'default_value' => 1,
			'min' => 1,
			'max' => '99',
			'step' => 1,
		]);
	}

	/**
	 * Add the 'roles' field.
	 */
	static function add_roles(): void {
		self::add_acf_inner_field(self::divisions, self::roles, [
			'label'     => 'Roles',
			'type'      => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => '1',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add role'
		]);
	}

	/**
	 * Add the 'title' field.
	 */
	static function add_title(): void {
		self::add_acf_inner_field(self::roles, self::title, [
			'label'     => 'Title',
			'type'      => 'text',
			'instructions' => 'The name of the role.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'r_priority' field.
	 */
	static function add_r_priority(): void {
		self::add_acf_inner_field(self::roles, self::r_priority, [
			'label'     => 'Priority',
			'type'      => 'number',
			'instructions' => 'Lower = more important.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'default_value' => 1,
			'min' => 1,
			'max' => '99',
			'step' => 1,
		]);
	}

	/**
	 * Add the 'voting' field.
	 */
	static function add_voting(): void {
		self::add_acf_inner_field(self::roles, self::voting, [
			'label'     => 'Automatic voting rights?',
			'type'      => 'true_false',
			'instructions' => 'As opposed to earned.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'email' field.
	 */
	static function add_email(): void {
		self::add_acf_inner_field(self::roles, self::email, [
			'label'     => 'Email',
			'type'      => 'text',
			'instructions' => '(Optional)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'placeholder'   => 'someone@themedium.ca'
		]);
	}

	/**
	 * Add the 'archival' field.
	 */
	static function add_archival(): void {
		self::add_acf_inner_field(self::roles, self::archival, [
			'label'     => 'Archival?',
			'type'      => 'true_false',
			'instructions' => 'As opposed to current.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
		]);
	}

	/**
	 * Add the 'aliases' field.
	 */
	static function add_aliases(): void {
		self::add_acf_inner_field(self::roles, self::aliases, [
			'label'     => 'Aliases',
			'type'      => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'collapsed' => '',
			'min' => '',
			'max' => '',
			'layout' => 'row',
			'button_label' => 'Add alias'
		]);
	}

	/**
	 * Add the 'alias' field.
	 */
	static function add_alias(): void {
		self::add_acf_inner_field(self::aliases, self::alias, [
			'label'     => 'Alias',
			'type'      => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '100',
				'class' => '',
				'id' => '',
			],
		]);
	}
}
