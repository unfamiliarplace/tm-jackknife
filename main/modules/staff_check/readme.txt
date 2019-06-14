================================================================================
STAFF CHECK
===========

Author(s):          Luke Sawczak
Created:            2015?
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

This module lets you check how effectively the newspaper is running.

It creates the Staff Check API and set of objects that do various tabulations
of post and user data for a given year.

It also adds a page to Generation Tools to display all of it.

Finally, it can add or remove Staff Writer and Photographer roles for you
and alert you about users who are about to qualify for a new role or to vote.

================================================================================
FEATURES
========

--  Adds an API allowing you to create a Staff Check for a given academic year.

--  Adds a Gen Tools switch page allowing you see each year's Staff Check.

--  Adds a settings page allowing you to schedule the automatic addition or
    removal of staff roles as appropriate, and notifications about users who
    almost qualify as staff.

================================================================================
USAGE
=====

1.  Schedule the Staff Check page on the Generation Tools settings page.
    Ideally schedule it before Sunday so you can see the 'almost' groups
    in time to print their names in the new issue the moment their qualify
    for a new title.

2.  Set any comic exclusion rules on the settings page.

3.  Set your auto role adding/removing preferences and email preferences on
    the settings page.

================================================================================
FUTURE
======

--  It would be really good to have a more complex model for thresholds. Right
    now it's half the issues, rounded down, used as both the number of issues
    a user must contribute to in order to be a voter and the number of
    contributions a user must make in order to be a staff writer/photographer.

    The ideal would be to have a separate setting for each of the three
    categories on the settings page, with two modes: (1) Fixed number of issues
    from, say, 3 - 10. (2) Half the issues in either semester. And these
    could be further modified by an option to count across both semesters or
    just one. There is a LOT of flexibility lacking on the threshold just now.
    However, any solution must also allow you to respect past years, where they
    might have had other systems. No titles should be retroactively stripped!

--  Using the list of eligible voters, it would be quite easy to coordinate that
    with the elections module. In fact, you could go on to implement online
    voting: send all voting users an email with a password and create a system
    where a user can enter a vote and change it up to the election date.

--  There are lots of possibilities for more data manipulation and extraction.
    For example, why not tabulate Outside Photo Sources across all posts,
    and come up with numbers as to how many photos are done in-house vs. how
    many are sourced from Google and other non-Medium students? Could be useful.

================================================================================
TECHNICAL NOTES
===============

--  The post tabulation the Volume & Issue API and the Meta API.

--  The user tabulation uses the Masthead API.

--  The page generation uses the Generation Tools API.

--  The dynamic colours are from Common Functions.

--  Several JKN tools used, among them Strings, Colours, Cron & AcademicYear.
