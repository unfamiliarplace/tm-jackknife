================================================================================
BOARD OF DIRECTORS
==================

Author(s):          Luke Sawczak
Created:            2015?
Last updated:       2017-09-24
Last documented:    2017-09-24

================================================================================
OVERVIEW
========

This module adds an ACF settings page and uses its data to create the
Board of Directors information page with the constitution, audits, meeting
minutes, etc.

================================================================================
FEATURES
========

--  Creates a page via Generation Tools.

--  Adds an ACF settings page to add board documents.
    The page is updated whenever this settings page is saved.

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Assign and schedule the page on the Generation Tools settings page.

================================================================================
FUTURE
======

This module is sold "as-is". ;)
But really, it's one of two that did not undergo an overhaul in summer 2017.

The renderer is somewhat primitive.

After bringing up to basic standards, one idea for the future would be to use
it in conjunction with Masthead to coordinate Board activities. For example,
say this added a settings page (or another tab) where it would show you the
board members for the year and you put in their contact info. Then, whenever
you add a meeting, it would email them for you. It would also send reminders
X days before the meeting, and so forth.

================================================================================
TECHNICAL NOTES
===============

--  The page uses JKNRenderer.

--  The settings page uses JKNACF.
