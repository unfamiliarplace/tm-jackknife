<?php

/**
 * An ACF registry for the roles on a user page.
 */
final class MJKMH_ACF_User extends JKNACF {

	/**
	 * Return a unique group ID.
	 *
	 * @return string
	 */
	static function group(): string { return 'user'; }

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_USER; }

	/*
	 * =========================================================================
	 * Fields
	 * =========================================================================
	 */

	const status        = 'status';
	const roles         = 'roles';
	const division      = 'division';
	const role          = 'role';
	const year          = 'year';
	const alias         = 'alias';
	const preferred     = 'preferred';
	const interim       = 'interim';
	const start         = 'start';
	const end           = 'end';

	// JS
	const js_handle     = 'mjk_mh_user_ajax';
	const js_path       = 'js/mjkmh_acf_user.js.js';
	const js_ver        = '0.000223';


	/*
	 * =========================================================================
	 * Adding filters and group
	 * =========================================================================
	 */

	/**
	 * Add the filters for the gropu and fields.
	 */
	static function add_filters(): void {
		add_action('acf/init', [__CLASS__, 'add_group']);

		add_action('acf/init', [__CLASS__, 'add_status']);
		add_action('acf/init', [__CLASS__, 'add_roles']);
		add_action('acf/init', [__CLASS__, 'add_year']);
		add_action('acf/init', [__CLASS__, 'add_division']);
		add_action('acf/init', [__CLASS__, 'add_role']);
		add_action('acf/init', [__CLASS__, 'add_alias']);
		add_action('acf/init', [__CLASS__, 'add_preferred']);
		add_action('acf/init', [__CLASS__, 'add_interim']);
		add_action('acf/init', [__CLASS__, 'add_start']);
		add_action('acf/init', [__CLASS__, 'add_end']);

		// Validate values
		add_action(sprintf('acf/validate_value/key=%s',
			self::qualify_field(self::start)), [__CLASS__, 'validate_start'],
			99, 4);

		add_action(sprintf('acf/validate_value/key=%s',
			self::qualify_field(self::end)), [__CLASS__, 'validate_end'],
			99, 4);
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
						'param' => 'user_form',
						'operator' => '==',
						'value' => 'all'
					],
				],
            ],
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'standard',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
		]);
	}

	/*
	 * =========================================================================
	 * Adding fields
	 * =========================================================================
	 */

	/**
	 * Add the 'status' field.
	 */
	static function add_status(): void {
		self::add_acf_field(self::status, [
			'label'     => 'Student status',
			'type'      => 'text',
			'instructions' => 'e.g. year, programs, alumnus/alumna',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '',
				'class' => '',
				'id' => '',
			]
		]);
	}

	/**
	 * Add the 'roles' field.
	 */
	static function add_roles(): void {
		self::add_acf_field(self::roles, [
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
			'min' => '',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add role'
		]);
	}

	/**
	 * Add the 'year' field.
	 */
	static function add_year(): void {
		self::add_acf_inner_field(self::roles, self::year, [
			'label'     => 'Year',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '15',
				'class' => '',
				'id' => '',
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'division' field.
	 */
	static function add_division(): void {
		self::add_acf_inner_field(self::roles, self::division, [
			'label'     => 'Filter by division',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'role' field.
	 */
	static function add_role(): void {
		self::add_acf_inner_field(self::roles, self::role, [
			'label'     => 'Role',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'alias' field.
	 */
	static function add_alias(): void {
		self::add_acf_inner_field(self::roles, self::alias, [
			'label'     => 'Display (allows archival aliases)',
			'type'      => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '25',
				'class' => '',
				'id' => '',
			],
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0
		]);
	}

	/**
	 * Add the 'preferred' field.
	 */
	static function add_preferred(): void {
		self::add_acf_inner_field(self::roles, self::preferred, [
			'label'     => 'Preferred?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
			'ui'    => '1'
		]);
	}

	/**
	 * Add the 'interim' field.
	 */
	static function add_interim(): void {
		self::add_acf_inner_field(self::roles, self::interim, [
			'label'     => 'Interim?',
			'type'      => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => [
				'width' => '12',
				'class' => '',
				'id' => '',
			],
			'ui'    => '1'
		]);
	}

	/**
	 * Add the 'start' field.
	 */
	static function add_start(): void {
		self::add_acf_inner_field(self::roles, self::start, [
			'label'     => 'Start date',
			'type'      => 'date_picker',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::interim),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '17',
				'class' => '',
				'id' => '',
			],
			'display_format' => 'F j, Y',
			'return_format' => 'Ymd',
			'first_day' => 1
		]);
	}

	/**
	 * Add the 'end' field.
	 */
	static function add_end(): void {
		self::add_acf_inner_field(self::roles, self::end, [
			'label'     => 'End date',
			'type'      => 'date_picker',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::interim),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
            'display_format' => 'F j, Y',
            'return_format' => 'Ymd',
            'first_day' => 1
		]);
	}

	/*
	 * =========================================================================
	 * Field validating
	 * =========================================================================
	 */

	/**
	 * Validate a date field.
	 *
	 * @param mixed $valid Whether the field is already valid.
	 * @param mixed $value The current value of the field.
	 * @param array $field The field array.
	 * @param string $input The name of the input DOM element.
	 * @return string|bool false = invalid; true = valid; string = invalid.
	 */
	static function _validate_date($valid, $value, $field, $input) {
		if (!$valid) return $valid;

		$re = sprintf('#%s#', self::row_regex());
		$matches = [];
		preg_match($re, $input, $matches);
		$row_i = $matches[1];

		$rows = $_POST['acf'][self::qualify_field(self::roles)];
		$row = reset($rows);
		for ($i = 0; $i < $row_i; $i++) {
			$row = next($rows);
		}

		$that_year_format = $row[self::qualify_field(self::year)];

		$this_dt = JKNTime::dt($value);
		$that_year = JKNAcademicYear::make_from_year($that_year_format);

		if (!$that_year->contains($this_dt)) {
			$valid = sprintf('This must be within the selected year (%s to %s).',
				$that_year->start()->format('F j, Y'),
				$that_year->end()->format('F j, Y'));
		}

		return $valid;
	}

	/**
	 * Validate the start date field.
	 *
	 * @param mixed $valid Whether the field is already valid.
	 * @param mixed $value The current value of the field.
	 * @param array $field The field array.
	 * @param string $input The name of the input DOM element.
	 * @return string|bool false = invalid; true = valid; string = invalid.
	 */
	static function validate_start($valid, $value, $field, $input) {
		return self::_validate_date($valid, $value, $field, $input);
	}

	/**
	 * Validate the end date field.
	 *
	 * @param mixed $valid Whether the field is already valid.
	 * @param mixed $value The current value of the field.
	 * @param array $field The field array.
	 * @param string $input The name of the input DOM element.
	 * @return string|bool false = invalid; true = valid; string = invalid.
	 */
	static function validate_end($valid, $value, $field, $input) {
		return self::_validate_date($valid, $value, $field, $input);
	}


	/*
	 * =========================================================================
	 * JS field filling
	 * =========================================================================
	 */

	/**
	 * Return a regex pattern for the name of a DOM element that picks out its
	 * row. N.B. Does not include delimiters because it's primary for JS.
	 *
	 * @return string
	 */
	static function row_regex(): string {
		return sprintf('acf\[%s\]\[(.*?)\]', self::qualify_field(self::roles));
	}

	/**
	 * Localize and enqueue the Javascript file.
	 */
	static function enqueue_js(): void {
		add_action('acf/input/admin_enqueue_scripts', function() {

			// Enqueue arrive-2.4.1.js
			JKNJavascript::enqueue_arrive();

			// Enqueue our script
			wp_enqueue_script(static::js_handle,
				plugins_url(static::js_path, __FILE__),
				$deps=['jquery'],
				$ver=static::js_ver,
				$in_footer=true
			);

			$this_year = new JKNAcademicYear();

			// Localize our script
			$localizations = [
				'current_data'      => self::current_data(),
				'year_options'      => self::year_options(),
				'div_options'       => self::div_options(),
				'years_to_dates'    => self::years_to_dates(),
				'divs_to_roles'     => self::divs_to_roles(),
				'roles_to_aliases'  => self::roles_to_aliases(),
				'default_year'      => $this_year->format(),
				'default_div'       => '!all',
				'roles_key'         => self::qualify_field(self::roles),
				'div_key'           => self::qualify_field(self::division),
				'role_key'          => self::qualify_field(self::role),
				'alias_key'         => self::qualify_field(self::alias),
				'year_key'          => self::qualify_field(self::year),
				'start_key'         => self::qualify_field(self::start),
				'end_key'           => self::qualify_field(self::end),
				'row_regex'         => self::row_regex()
			];

			wp_localize_script(static::js_handle, 'MJKMHUser', $localizations);
		});
	}

	/**
	 * Return an array of existing data by row.
	 *
	 * @return array[]
	 */
	static function current_data(): array {
		global $user_id;

		if (empty($user_id)) return [];

		$rows = [];
		if (self::have_rows(self::roles, $user_id)) {
			while (self::have_rows(self::roles, $user_id)) {
				the_row();

				$year = self::sub(self::year);
				$div = self::sub(self::division);
				$role_title = self::sub(self::role);
				$alias = self::sub(self::alias);

				$role = MJKMHAPI::role($role_title);
				if (is_null($role)) {
					wp_die(sprintf('User %s has the role "%s" which has not'
						. ' been registered. Please check the database.',
						$user_id, $role_title));
				}

				if (empty($div) || ($div == '!all')) {
					$div = $role->division_name();
				}

				if (empty($alias)) $alias = $role_title;

				$rows[] = ['year' => $year, 'div' => $div,
				             'role' => $role_title, 'alias' => $alias];
			}
		}

		reset_rows();
		return $rows;
	}

	/**
	 * Return an array of usable years.
	 *
	 * @return JKNAcademicYear[]
	 */
	static function years(): array {
		$this_year = new JKNAcademicYear();
		$limit = $this_year->year() + 1;
		return array_reverse(MJKCommonTools::academic_years(null, $limit));
	}

	/**
	 * Return an array of year options [value => year, label => year].
	 *
	 * @return array[]
	 */
	static function year_options() {
		$options = [];
		foreach(self::years() as $year) {
			$format = $year->format();
			$options[] = ['value' => $format, 'label' => $format];
		}
		return $options;
	}

	/**
	 * Return an array of div options [value => name, label => name].
	 *
	 * @return array[]
	 */
	static function div_options() {
		$options = [];

		$options[] = ['value' => '!all', 'label' => 'All'];

		foreach(MJKMHAPI::divisions() as $division) {
			$name = $division->name();
			$options[] = ['value' => $name, 'label' => $name];
		}

		$options[] = ['value' => '!archival', 'label' => 'Archival'];

		return $options;
	}

	/**
	 * Return an array of [div_name => role_options] for all divisions.
	 *
	 * @return array[]
	 */
	static function divs_to_roles() {
		$dtr = [];

		$all = [];
		$archival = [];
		foreach(MJKMHAPI::divisions() as $div) {

			foreach ($div->roles() as $role) {
				$title = $role->title();
				$option = ['value' => $title, 'label' => $title];
				$all[] = $option;
				if (!$role->archival())$dtr[$div->name()][] = $option;
				if ($role->archival()) $archival[] = $option;
			}
		}

		$dtr = ['!all' => $all] + $dtr + ['!archival' => $archival];
		return $dtr;
	}

	/**
	 * Return an array of ['value' => alias, 'label' => alias] for all roles.
	 *
	 * @return array[]
	 */
	static function roles_to_aliases(): array {
		$table = [];

		foreach(MJKMHAPI::roles() as $role) {
			$table[$role->title()] = [];
			foreach($role->aliases() as $a) {
				$table[$role->title()][] = ['value' => $a, 'label' => $a];
			}
		}

		return $table;
	}

	/**
	 * Return an array of ['start' => start, 'end' => end] for the given year.
	 *
	 * @return string[]
	 */
	static function years_to_dates(): array {
		$table = [];

		foreach(self::years() as $ay) {
			$start = $ay->start()->format('U\0\0\0');
			$end = $ay->end()->format('U\0\0\0');
			$table[$ay->format()] = ['start' => $start, 'end' => $end];
		}

		return $table;
	}
}
