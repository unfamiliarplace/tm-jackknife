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

// Get the query var, make the page, echo it
if (isset($wp_query->query_vars[MJKVI_VOL_QVAR])) {
    $n_vol = $wp_query->query_vars[MJKVI_VOL_QVAR];

    $page = new MJKVI_PageVolume($n_vol);
    echo MJKCommonTools::cdn_images($page->format());
}
