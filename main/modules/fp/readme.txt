This module was made by Luke Sawczak in August 2016
to add some options for customizing the front page,
and to add some Newspaper elements building on the
Newspaper Enhance module.

Last updated July 3, 2017 / v 2.7

Info about how it works can be found in the theme files
in the td api plugin folder, if you download them from Envato.

Added:

FRONT PAGE CARD custom post type with settings page.

(Note that old front page cards are automatically deleted on a daily basis.)

MODULES to add meta info or remove an image
all that keep an image have been switched to call mne_get_image

--module 61, based on module mx9 with extra meta info
--module 62, based on module mx10 with extra meta info

BLOCKS to put on homepage and to compose the big grid slider

--block 51, based on big grid 2 but sans hover and citing modules 61 and 62
--block 52, based on big grid slide but with our posts feed and citing block 51

===

================================================================================
FRONT PAGE
==========

Author(s):          Luke Sawczak
Created:            2016
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

This module adds Newspaper elements for displaying stories prominently on the
front page on a "shift" basis: you create a cards with start dates and they
will display till the next one becomes available.

These cards consist of Spotlights, which are text-based announcements or else
articles (WordPress posts), very useful for posts without images. They also
consist of Slides, which are used to fill a tagDiv slider on the front end.

Both are easily customizable, including shuffling to keep it always fresh.

================================================================================
FEATURES
========

--  Adds a custom post type for Front Page Cards.

--  Adds a setting page for these cards where you can set Spotlights and Slides
    to show off text-based and image-based articles.

--  Adds a shortcode to display Spotlights anywhere.

--  Adds tagDiv Newspaper elements that display Slides.

--  Allows you to shuffle these features to make sure the page is always varied.

--  Automatically clears out older cards on a regular basis.

================================================================================
USAGE
=====

1.  Make Front Page cards.

2.  Put the Spotlight shortcode on the front page.

3.  Put the slider on the front page.

================================================================================
FUTURE
======

--  Right now you can choose to get recent posts or older posts. This is so that
    you can continue to load new posts through Christmas break or summer. But
    it's implemented in ACF by using two different fields. That should really
    be one field.

--  A settings page with whether to trash old cards at all, whether to delete
    them permanently, how many to keep, etc. would be nice.

--  A fallback for when there are no cards might be a good idea.

--  The essential concept of this module isn't dependent on Newspaper. It's just
    that Newspaper's blocks are used to show slides. It would be good to make
    a conditional check and be able to show something even if Newspaper is off.

--  As a minor note, there are currently a bunch of public properties that
    should be private with getters.

================================================================================
TECHNICAL NOTES
===============

--  The custom post type uses JKNCPT.

--  The settings page uses JKNACF.

--  The slider uses the tagDiv API.
