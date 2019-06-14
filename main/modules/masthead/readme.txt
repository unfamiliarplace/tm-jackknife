================================================================================
MASTHEAD
======================

Author(s):          Luke Sawczak
Created:            2017-08-15
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

This module provides the ability to register various roles The Medium can have,
organized by division. For example, it's used to register the Editorial Board.

It then adds the ability to assign roles to individual users for a given year.

This information is used with Generation Tools to provide a Masthead page.

It can also be used, via the API, for many other purposes. (For example, in
Newspaper Enhancements, it's used to list people's roles at the bottom of
articles and on their author pages.)

================================================================================
FEATURES
========

--  Adds an ACF settings page to register divisions and roles.

--  Adds an ACF group to users to assign individual roles.

--  Adds a Masthead page to Generation Tools.

--  Provides an API to use the data in other ways.

================================================================================
USAGE
=====

1.  Register roles on the Masthead settings page.

2.  Assign roles to users on their edit page.

3.  Generate / schedule the generation of the Masthead page.

================================================================================
FUTURE
======

--  More data manipulation via the API for extended purposes.

--  The ability to add a description for each role. Use this data to generate
    a new page where people can see just where they could fit in!

================================================================================
TECHNICAL NOTES
===============

--  The settings page and user group both use JKNACF.

--  The masthead page uses JKNRenderer and MJKGenToolsAPI.
