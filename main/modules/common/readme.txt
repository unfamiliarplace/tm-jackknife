================================================================================
COMMON FUNCTIONS
================

Author(s):          Luke Sawczak
Created:            2015
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

This plugin provides various functions and data that either do miscellaneous
tasks or serve other modules.

================================================================================
FEATURES
========

--  Provides the MJKCommonTools class with the ability to yield our category
    colours, apply our CDN to URLs, and return the academic years our paper
    has existed in.

--  Lets you disable Contact Form 7 loading except on necessary pages.

--  Adds extra cron schedules.

--  Adds an "Online Administrator" custom role.

--  Disables the WPBeginner dashboard widget.

--  Loads custom fonts and provides them as CSS classes.

--  Allows the use of <style> HTML tags in post content.

--  Provides a settings page to configure several of the above submodules.

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Use MJKCommonTools in other modules to apply the CDN, get our academic
    years, and get our category colours.

================================================================================
FUTURE
======

Add settings to the ACF page for the functions that aren't configurable yet.

It's hard to know what else would need to change, because the things that go
in here tend to come up at random. Just keep an eye open when you're writing
new modules and ask yourself: Could another module find this useful? If so,
consider putting it in Common Functions instead and calling it from your module.

================================================================================
TECHNICAL NOTES
===============

--  The settings page uses JKNACF.
