================================================================================
VOLUME & ISSUE
==============

Author(s):          Luke Sawczak
Created:            2016
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

Creates volumes (one publishing year) and issues (one week), associating them
with posts and PDFs, and creates shortcodes for archives for browsing them.

Also creates archival websites to keep track of our various web presences.

Adds an API for working with volumes, issues, and websites. The volumes and
issues are particularly useful for returning posts from their time ranges.

================================================================================
FEATURES
========

--  Adds custom post types for volumes and archival websites.

--  Adds settings pages for the same; for general options; and for posts.

--  Adds a range of shortcodes to create archives of volumes and websites.

--  Adds a couple of widgets to show off archives or individual issues.

--  Adds page templates for volumes and issues.

--  Adds an API to work with all the components.

================================================================================
USAGE
=====

1.  Create volumes, populating them with issues.

2.  Create archival websites.

3.  Insert the shortcodes on the archive page (or wherever).

4.  Insert the widgets where you'd like them to appear.

5.  Change any custom settings on the options page or on a per-post basis
    while editing posts. Posts will automatically assign themselves to the
    correct issue and volume.

6.  Use the API to do more with the data, such as have every article link
    back to its volume and issue.

================================================================================
FUTURE
======

--  This is not the earliest code, but it isn't the newest either. A lot of it
    is bloated with revised attempts to do things. A rewrite could be useful.

--  There are probably more ways to use this data. For example, why not a new
    fun widget saying "Check out a random issues from the archives"? :)

--  Consider making a page template for an archival website, the same as for
    a volume or issue. In the case of the AW, it could show the metadata about
    the site and then the main content could perhaps be an iframe of the site.

--  The code technically doesn't depend on Visual Composer or Newspaper, but
    the fallbacks are rudimentary. It would be a good idea to bulk them up in
    case there is ever a plugin or theme change. I'm thinking specifically of
    the shortcodes and the page templates.

--  I suggest editing the volume ACF and object so that its primary key is the
    year, not the number. This would have been impossible before we had
    JKNAcademicYear and MJKCommonTools::get_academic_years, but now it's an
    easy matter to fill an ACF select field with allowed academic years.
    The voodoo to turn volume # into academic year with the Erindalian thing
    could be done away with.

    To do this, I recommend:
        --  Adding a year field and a filler to pre-fill it with options
        --  Update it, either in the db (only ~50 entries) or programmatically
            using MJKVI_VOL_ACF::add_row
        --  Rewrite Volume and API code to make sure year-to-num transitions
            are consistent (needed for e.g. vol title). Also have num-to-vol
            stay around for e.g. template (https://themedium.ca/v/44 etc.)
        --  Remove the $is_erindalian thing and make it dynamic. Years < 1974
            are Erindalian, >= are not. Then delete the ACF field and its data
            to simplify the volume CPT edit screen.

--  As a minor note, there are currently a bunch of public properties that
    should be private with getters.

================================================================================
TECHNICAL NOTES
===============

--  The settings pages uses JKNACF.

--  The custom post types use JKNCPT.

--  The issue page template uses blocks added via the tagDiv API.