/*
 * =============================================================================
 * jQuery for switching the content on a switch page via AJAX.
 * =============================================================================
 */

$ = jQuery;

/**
 * Perform onload actions.
 */
function mjkmhu_onload() {
    var $table = $('table.form-table');
    $table.ready(mjkmhu_load_current);

    mjkmhu_bind_years($table);
    mjkmhu_bind_divs($table);
    mjkmhu_bind_roles($table);
    mjkmhu_bind_arrive($table);
}

/**
 * PHP localization: MJKMHUserAjax
 *      MJKMHUser.current_data      // Table of row ID to year, div, role, alias
 *      MJKMHUser.year_options      // Table of year options
 *      MJKMHUser.div_options       // Table of division options
 *      MJKMHUser.years_to_dates    // Table of year format to start/end dates
 *      MJKMHUser.divs_to_roles     // Table of division id to role options
 *      MJKMHUser.roles_to_aliases  // Table of role id to qualifier options
 *      MJKMHUser.default_year      // The default year on a new row
 *      MJKMHUser.default_div       // The default division on a new row
 *      MJKMHUser.roles_key         // Field key for the roles repeater
 *      MJKMHUser.div_key           // Field key to get the division select
 *      MJKMHUser.role_key          // Field key to get the role select
 *      MJKMHUser.alias_key         // Field key to get the alias select
 *      MJKMHUser.year_key          // Field key to get the year select
 *      MJKMHUser.start_key         // Field key to get the start date picker
 *      MJKMHUser.end_key           // Field key to get the end date picker
 *      MJKMHUser.row_regex         // Regex pattern to get the row from an ID
 */

/*
 * =============================================================================
 * Binding
 * =============================================================================
 */

/**
 * Bind each year change to an event.
 * @param $parent
 */
function mjkmhu_bind_years($parent) {
    $parent.on('change', mjkmhu_years_selector(), mjkmhu_notify_year);
}

/**
 * Bind each division change to an event.
 * @param $parent
 */
function mjkmhu_bind_divs($parent) {
    $parent.on('change', mjkmhu_divs_selector(), mjkmhu_notify_div);
}

/**
 * Bind each role change to an event.
 * @param $parent
 */
function mjkmhu_bind_roles($parent) {
    $parent.on('change', mjkmhu_roles_selector(), mjkmhu_notify_role);
}

/**
 * Bind each newly arriving year to an event.
 * @param $parent
 */
function mjkmhu_bind_arrive($parent) {
    $parent.arrive(mjkmhu_years_selector(), mjkmhu_notify_arrive);
}


/*
 * =============================================================================
 * Event handlers
 * =============================================================================
 */

/**
 * React to a change in year.
 */
function mjkmhu_notify_year() {
    mjkmhu_change_year($(this).attr('name'), $(this).val());
}

/**
 * React to a change in division.
 */
function mjkmhu_notify_div() {
    mjkmhu_change_div($(this).attr('name'), $(this).val());
}

/**
 * React to a change in role.
 */
function mjkmhu_notify_role() {
    mjkmhu_change_role($(this).attr('name'), $(this).val());
}

/**
 * React to a new row arriving.
 */
function mjkmhu_notify_arrive() {
    mjkmhu_arrive($(this).attr('name'));
}

/*
 * =============================================================================
 * Changers
 * =============================================================================
 */

/**
 * Load the current data into existing rows.
 */
function mjkmhu_load_current() {
    var $rows = MJKMHUser.current_data;

    var $year_options = MJKMHUser.year_options;
    var $div_options = MJKMHUser.div_options;

    for (var $i = 0, $len = $rows.length; $i < $len; $i++) {
        $row = $rows[$i];

        // Gather
        var $year           = $row['year'];
        var $div            = $row['div'];
        var $role           = $row['role'];
        var $alias          = $row['alias'];

        var $dates          = MJKMHUser.years_to_dates[$year];
        var $start          = $dates['start'];
        var $end            = $dates['end'];

        var $role_options   = MJKMHUser.divs_to_roles[$div];
        var $alias_options  = MJKMHUser.roles_to_aliases[$role];

        // Selectors
        var $year_select        = mjkmhu_year_by_row($i);
        var $div_select         = mjkmhu_div_by_row($i);
        var $role_select        = mjkmhu_role_by_row($i);
        var $alias_select       = mjkmhu_alias_by_row($i);

        // Set
        mjkmhu_update_select($year_select, $year_options,  $year);
        mjkmhu_update_select($div_select, $div_options,  $div);
        mjkmhu_update_select($role_select, $role_options,  $role);
        mjkmhu_update_select($alias_select, $alias_options,  $alias);

        // Dates
        mjkmhu_update_datepicker($i, MJKMHUser.start_key, $start);
        mjkmhu_update_datepicker($i, MJKMHUser.end_key, $end);
    }
}

/**
 * Update the datepicker based on the new year.
 * @param $name
 * @param $value
 */
function mjkmhu_change_year($name, $value) {
    var $row = mjkmhu_row_from_name($name);
    var $dates = MJKMHUser.years_to_dates[$value];
    var $start = $dates['start'];
    var $end = $dates['end'];

    mjkmhu_update_datepicker($row, MJKMHUser.start_key, $start);
    mjkmhu_update_datepicker($row, MJKMHUser.end_key, $end);
}

/**
 * Update the role on division change (and trigger a role change, too).
 * @param $name
 * @param $value
 */
function mjkmhu_change_div($name, $value) {
    var $row = mjkmhu_row_from_name($name);

    var $role_opts = MJKMHUser.divs_to_roles[$value];
    var $role = mjkmhu_role_by_row($row);

    mjkmhu_update_select($role, $role_opts, $role_opts[0]['value']);
    $role.change();
}

/**
 * Update the alias based on role change.
 * @param $name
 * @param $value
 */
function mjkmhu_change_role($name, $value) {
    var $row = mjkmhu_row_from_name($name);

    var $alias_opts = MJKMHUser.roles_to_aliases[$value];
    var $alias = mjkmhu_alias_by_row($row);

    mjkmhu_update_select($alias, $alias_opts, $alias_opts[0]['value']);
}

/**
 * Fill in the values for a new row.
 * @param $name
 */
function mjkmhu_arrive($name) {
    var $row = mjkmhu_row_from_name($name);
    var $year_select = mjkmhu_year_by_row($row);

    // Only update if it's not an old row (existing rows still "arrive").
    if ($year_select.has('option').length === 0) {
        var $div_select = mjkmhu_div_by_row($row);
        var $role_select = mjkmhu_role_by_row($row);

        var $default_year = MJKMHUser.default_year;
        var $default_div = MJKMHUser.default_div;

        var $year_options = MJKMHUser.year_options;
        var $div_options = MJKMHUser.div_options;
        var $role_options = MJKMHUser.divs_to_roles[$default_div];

        mjkmhu_update_select($year_select, $year_options, $default_year);
        $year_select.change();

        mjkmhu_update_select($div_select, $div_options, $default_div);
        mjkmhu_update_select($role_select, $role_options, null);
    }
}


/*
 * =============================================================================
 * Selectors
 * =============================================================================
 */

/**
 * Return a JQ selector for the select fields of the given key.
 * @param $key
 * @returns {string}
 * @private
 */
function _mjkmhu_selects_selector($key) {
    return 'select[name*="[' + $key + ']"]';
}

/**
 * Return a JQ selector for the year select fields.
 * @returns {string}
 */
function mjkmhu_years_selector() {
    return _mjkmhu_selects_selector(MJKMHUser.year_key);
}

/**
 * Return a JQ selector for the division select fields.
 * @returns {string}
 */
function mjkmhu_divs_selector() {
    return _mjkmhu_selects_selector(MJKMHUser.div_key);
}

/**
 * Return a JQ selector for the role select fields.
 * @returns {string}
 */
function mjkmhu_roles_selector() {
    return _mjkmhu_selects_selector(MJKMHUser.role_key);
}

/**
 * Return a JQ selector for the alias select fields.
 * @returns {string}
 */
function mjkmhu_aliases_selector() {
    return _mjkmhu_selects_selector(MJKMHUser.alias_key);
}

/*
 * =============================================================================
 */

/**
 * Return text to make a JQ selector for the given row and key.
 *
 * @param $row
 * @param $key
 * @returns {string}
 * @private
 */
function _mjkmhu_row_selector($row, $key) {
    return '[name*="[' + MJKMHUser.roles_key + '][' + $row + '][' + $key +']"]';
}

/**
 * Return a JQ selector for the given row and key.
 * @param $row
 * @param $key
 * @returns {*|HTMLElement}
 * @private
 */
function _mjkmhu_by_row($row, $key) {
    return $(_mjkmhu_row_selector($row,  $key));
}

/**
 * Return a JQ selector for the year select in the given row.
 * @param $row
 * @returns {*|HTMLElement}
 */
function mjkmhu_year_by_row($row) {
    return _mjkmhu_by_row($row, MJKMHUser.year_key);
}

/**
 * Return a JQ selector for the division select in the given row.
 * @param $row
 * @returns {*|HTMLElement}
 */
function mjkmhu_div_by_row($row) {
    return _mjkmhu_by_row($row, MJKMHUser.div_key);
}

/**
 * Return a JQ selector for the role select in the given row.
 * @param $row
 * @returns {*|HTMLElement}
 */
function mjkmhu_role_by_row($row) {
    return _mjkmhu_by_row($row, MJKMHUser.role_key);
}

/**
 * Return a JQ selector for the alias select in the given row.
 * @param $row
 * @returns {*|HTMLElement}
 */
function mjkmhu_alias_by_row($row) {
    return _mjkmhu_by_row($row, MJKMHUser.alias_key);
}


/*
 * =============================================================================
 * Strings
 * =============================================================================
 */

/**
 * Return the row ID from the given element name.
 * @param $name
 * @returns {*}
 */
function mjkmhu_row_from_name($name) {
    var $re = new RegExp(MJKMHUser.row_regex);
    return $re.exec($name)[1];
}

/*
 * =============================================================================
 * Element manipulation
 * =============================================================================
 */

/**
 * Update a select field, including emptying it and selecting the new value.
 * @param $select
 * @param $options
 * @param $selected
 */
function mjkmhu_update_select($select, $options, $selected) {
    $select.empty();

    $.each($options,  function ($index, $option) {
        var $val = $option['value'];
        var $label = $option['label'];
        var $el = '<option value="' + $val + '">' + $label + '</option>';
        $select.append($el);
    });

    $select.val($selected);
}

/**
 * Update a datepicker field.
 * @param $row
 * @param $key
 * @param $value
 */
function mjkmhu_update_datepicker($row, $key, $value) {
    var $fields = $('.acf-field-date-picker');
    var $sel = '[class*="' + $key.replace(/_/g, '-') + '"]';
    var $fields = $fields.filter($sel);
    var $field = $fields.has(_mjkmhu_row_selector($row, $key));
    var $picker = $field.find('input.hasDatepicker');
    $picker.datepicker('setDate', new Date(parseInt($value)));
}

/*
 * =============================================================================
 * Miscellaneous
 * =============================================================================
 */

// Run the onload function
mjkmhu_onload();
