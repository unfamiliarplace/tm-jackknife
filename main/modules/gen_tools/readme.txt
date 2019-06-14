This plugin (now module) was made by Luke Sawczak in
August 2015 to add various pages that can be generated
using data from Advanced Custom Fields and formatting from
Visual Composer.

Last updated August 4, 2017 / v 3.1

The first thing it does is add a scheduler settings page.
There, you can choose the settings for each you want to generate:
the WordPress page its content will sit on and how often to auto-generate it.

To add a new page, use the MJKGTAPI function register_page, supplying an ID,
a name, an explanation of where the data comes from, and a generator class
implementing MJKGTPageGenerator. Finally, you can also pass an array of
JKNSettingsPage objects along with any page IDs to generate whenevr those
settings are saved.

================================================================================
GENERATION TOOLS
================

Author(s):          Luke Sawczak
Created:            2015-08
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

Gen Tools provides tools for rendering dynamic page content and saving it
automatically to a WP page, either whenever the user wants or when the
data is updated or on a regular schedule.

Easily add pages using the MJKGTAPI by extending JKNRenderer.
Then configure, generate & schedule them on the Generation Tools settings page.

================================================================================
FEATURES
========

--  Adds an API to create self-generating content-rich pages.

--  Adds a Scheduler to work with these generated pages.

================================================================================
USAGE
=====

1.  Write your renderer extending JKNRenderer.

2.  Add your page using MJKGTAPI.

3.  Configure it on the Scheduler settings page.

================================================================================
FUTURE
======

--  The Javascript is the result of a long struggle with my first attempt to
    figure out AJAX. If I were to rewrite it from scratch now it would look
    quite different. It functions (almost always), but it could be much more
    elegant and concise, and make much better use of native jQuery functions.

================================================================================
TECHNICAL NOTES
===============

--  Nothing in particular...
