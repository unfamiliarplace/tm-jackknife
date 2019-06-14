================================================================================
ARTICLE ENRICHMENT
==================

Author(s):          Luke Sawczak
Created:            2015?
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

This plugin enriches articles in a number of ways. It adds drop caps to
every article, and allows you to attach files, updates, and corrections to
any post. The last two are also gathered and presented on auto-generated pages.
There are congifurable settings for all of them.

================================================================================
FEATURES
========

--  Adds several ACF fields to posts: drop caps, updates, corrections, & files.

--  Adds two generated pages: Correction Notices and Updates.

--  Adds a settings page for general settings.

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Assign and schedule the corrections and updates pages on the Generation
    Tools settings page.

3.  Attach corrections, files, and updates to every article on the post editing
    screen as needed.

================================================================================
FUTURE
======

Files, corrections and updates currently share an ACF group in order not to add
too many cluttery metaboxes to the post editing screen. However, it would likely
be preferable to separate out all the submodules into their own modules. One
advantage would be that dependencies could be better targeted -- right now the
whole module needs Generation Tools even though only corrections and updates
uses it. Also, I think giving modules room to breathe always invites expansion
and creativity. This module is a holdover from the days when all our functions
were packed into as few spaces as possible, not realizing the downsides.

================================================================================
TECHNICAL NOTES
===============

--  The settings page and post metaboxes use JKNACF.

--  The two pages use JKNRendererSwitch and Generation Tools.
