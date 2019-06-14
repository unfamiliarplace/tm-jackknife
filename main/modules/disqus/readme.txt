================================================================================
DISQUS LATEST COMMENTS
======================

Author(s):          Luke Sawczak
Created:            2017-08-15
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

The purpose of this module is to add a shortcode to display a site's most recent
comments made using the Disqus system. This can also be displayed as a widget.

There are other plugins out there that do this, such as:
https://en-ca.wordpress.org/plugins/disqus-latest-comments

The good ones work by querying the Disqus API:
https://help.disqus.com/customer/portal/articles/1179651-widgets

However, to my knowledge none of them do it quite like this one does...
which is itself somewhat hackish, but works better than most!

(For example, the one cited does not cache results nor can the Javascript it
adds be loaded asynchronously, which leads to worse page load times.)

================================================================================
FEATURES
========

--  Adds an ACF settings page to set the Disqus API endpoint variables.

--  Fetches the Disqus "latest comments" script.

--  Caches the output of that script (if set in settings page).

--  Adds a cron job to refresh the cache at an interval from the settings page.

--  Adds a shortcode to display the output the script.

--  Adds a widget to display the shortcode.

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Add the shortcode and/or widget wherever you want.

================================================================================
FUTURE
======

An improvement is possible by using a better Disqus API endpoint (see the link
to their widgets page above). Right now we use the "latest comments" one, which
returns a JS script that calls document.write, and we regex the argument and
save that to disk. A better endpoint would let us fetch N comments as JSON to be
formatted and styled with more precision and freedom.

================================================================================
TECHNICAL NOTES
===============

--  The settings page uses JKNACF.

--  The cache uses JKNCache classes.
    It's purged on module deactivation.

--  The cron job uses JKNCron classes.
    It's scheduled on each page load if necessary. The schedule is cleared
    on module pause or deactivation.

--  The Disqus fetcher uses the Disqus API.

--  The shortcode and widget are done using standard WP methods.
