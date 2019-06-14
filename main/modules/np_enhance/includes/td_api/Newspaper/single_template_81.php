<?php

/*
 * =============================================================================
 * MJK | New | Last updated 2018-02-04 | Newspaper v8.6
 * Template 81 is an automatic switch for templates 82, 83, and 84.
 * =============================================================================
 */

// Secure global variables
locate_template('includes/wp_booster/td_single_template_vars.php', true);

// Load the appropriate template
require_once MJKNPE_TemplateSwitcher::get_template($post);
