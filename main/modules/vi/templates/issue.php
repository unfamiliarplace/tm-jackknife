<?php

/*
 * =========================================================================
 * Landing template for an issue page.
 * =========================================================================
 */

// These are set because they are used for ordering in some parts of Newspaper
global $wp_query;
set_query_var('page', 1);
set_query_var('paged', 1);

// Get the query vars, make the page, echo it
if (isset($wp_query->query_vars[MJKVI_VOL_QVAR]) &&
    isset($wp_query->query_vars[MJKVI_ISS_QVAR])) {

    $n_vol = $wp_query->query_vars[MJKVI_VOL_QVAR];
    $n_iss = $wp_query->query_vars[MJKVI_ISS_QVAR];

    $page = new MJKVI_PageIssue($n_vol, $n_iss);
    echo MJKCommonTools::cdn_images($page->format());
}
