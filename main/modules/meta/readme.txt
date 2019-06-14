================================================================================
META
====

Author(s):          Luke Sawczak
Created:            2017-08
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

This module adds a subtitle field to every post, as well as an authorship field
group. This latter allows you to move beyond WordPress's "one author per post"
base to a system with multiple authors, photographers, contributors of notes,
outside photo sources, and videographers.

It also provides an API to interact with the data thereby provided, such as
finding all contributors for a post or all posts for a contributor.

================================================================================
FEATURES
========

--  Adds an ACF group to posts to add a subtitle.

--  Adds an ACf group to posts to add detailed authorship information.

--  Provides an API to interact with the data.

================================================================================
USAGE
=====

1.  Add data to posts as you create them.

2.  Use the API elsewhere to display the data and catalogue users and posts.

================================================================================
FUTURE
======

--  The outside photo sources could be more sophisticated. Ideally it would
    have a radio button or dropdown to choose what kind it is, e.g. human,
    website, other organization. It would then generate appropriate formatting.

--  There is currently no way to associate order of photo sources with photos
    in the post if there are both Medium photographers and outside photos.
    Right now (and this is also an interaction with Newspaper Enhancements),
    if the first photo is from an outside source and the second is one of ours,
    the forced order of the credits will give the wrong impression as to
    authorship (e.g. v43 iss2 “syria to sauga”).


================================================================================
TECHNICAL NOTES
===============

--  The post metaboxes use JKNACF.
