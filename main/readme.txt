================================================================================
The Medium Jackknife
====================

Author(s):          Luke Sawczak
Created:            2017-08
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

The Medium Jackknife is a plugin that takes advantage of the Jackknife plugin,
also developed at The Medium. It registers a space (id 'mjk') and a number of
modules that provide a wide variety of new features for the site.

Among these features is the ability to create an announcement banner;
to assign masthead positions; to credit multiple authors and photographers;
to organize articles by yearly volume and weekly issue; to automatically
generate pages with rich HTML content that would be difficult to code by hand;
and more.

It is designed around the Advanced Custom Fields plugin, in which format
all of its settings have been stored. It also takes full advantage of tagDiv's
Newspaper theme and the popular Visual Composer plugin yet without allowing key
functionality to depend on these.

The modules are listed below. Visit each one's readme.txt for more detail.

================================================================================
MODULES
=======

--  Announcement
    Allows you to create an above-the-fold announcement on the front page.

--  Board of Directors
    Creates a settings page where you enter board-related info (minutes,
    meeting dates, audits, etc.) from which a frontend page is generated.

--  Common Functions
    Miscellaneous functions used by other modules, e.g. category colours.

--  Disqus Latest Comments
    Adds a shortcode and widget to display the latest comments from Disqus
    with control over caching and how often they are updated.

--  Elections
    Creates a settings page where you enter elections-related info (times,
    candidates, results) from which a frontend page is generated.

--  Article Enrichment
    Enriches articles with features like corrections, updates, and attached
    files.

--  Front Page
    Works with the Newspaper theme to create a front page to show off image-
    and text-based content changing on a weekly basis.

--  Generation Tools
    Allows the automatic generation of page content. (Used by other modules.)

--  Masthead
    Allows you to define the roles of our organization and assign them to users.

--  Meta
    Expands post metadata with multiple authors, photographers, and more.

--  Newspaper Enhancements
    Works with the Newspaper theme, slightly modifying templates to put our
    meta and masthead data front and centre throughout the site.

--  Staff Check
    Catalogues article data and creates a page with a broad look at trends in
    contributions. Also tracks Staff Writers and Photographers and voters.

--  Volume & Issue
    Creates volumes (one publishing year) and issues (one week), associating
    them with posts and PDFs, and generates archives for browsing them.

================================================================================
FUTURE
======

See each module's readme's "future" box.

================================================================================
TECHNICAL NOTES
===============

--  The space is set up using JKNSpace.

--  Each module extends JKNModule.

--  Each settings page extends JKNSettingsPage.

--  Pretty much everything else also uses various JKN tools. :)
