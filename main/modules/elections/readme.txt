================================================================================
ELECTIONS
=========

Author(s):          Luke Sawczak
Created:            2015?
Last updated:       2017-09-24
Last documented:    2017-09-24

================================================================================
OVERVIEW
========

This module adds an ACF settings page and uses its data to create the
Elections information page with the times, results, voting instructions, etc.

================================================================================
FEATURES
========

--  Creates a page via Generation Tools.

--  Adds an ACF settings page to add upcoming and finished elections.
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

After bringing up to basic standards, an obvious direction to take it would be
for election coordinating. If it were to build on Staff Check, it could know
the list of eligible voters and send you reminder emails about voting. A more
ambitious change would be to use the election data here to implement an online
voting system. Election results could be automatically (and neutrally!)
determined, announced, and updated. It could be quite a powerful little tool.

================================================================================
TECHNICAL NOTES
===============

--  The page uses JKNRenderer.

--  The settings page uses JKNACF.
