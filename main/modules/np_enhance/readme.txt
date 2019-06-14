================================================================================
NEWSPAPER ENHANCEMENTS
======================

Author(s):          Luke Sawczak
Created:            2016-08
Last updated:       2018-02-04
Last documented:    2018-02-04

================================================================================
OVERVIEW
========

This module works with the Newspaper theme, modifying numerous templates to put
our meta and masthead data front and centre throughout the site, beside other
customizations. It also provides an API for templates to interface with our
Meta and Masthead modules, and adds miscellaneous actions to modify theme
behaviour, all using the tagDiv native API.

It is intended to work with the 'Newspaper-child' child theme.

Its inner structure under the td_api mirrors the Newspaper theme folder to allow
easy location of the modified templates.

================================================================================
FEATURES
========

--  Adds an ACF page to set global options and customizations.

--  Adds ACF post metaboxes to customize per-post behaviour.

--  Adds the option to switch off extra user contact methods and AJAX post
    viewcounts.

--  Adds a custom author page that shows masthead roles and displays
    contributions of all types, not just author.

--  Adds a template switcher so that a single template can redirect to several
    depending on various qualities of the post.

--  Adds a bunch of CSS.

--  Adds new templates, blocks, modules, a header, and shortcodes that can be
    added through the tagDiv post editing and theme panel natively:

        MODULES (individual posts displayed in lists, such as category pages)
        --  31, based on module 2
        --  32, based on module 8
        --  33, based on module 8
        --  41, based on module 4, for use on the front page
        --  42, based on module 8, for use on the front page
        --  43, based on module 4, for use on the front page

        BLOCKS (groups of modules)

        --  41, based on block 17, for use on the front page
        --  42, based on block 17, for use on the front page

        HEADER (everything from the main menu up)

        --  71, based on header 10, with our own logo and search layout

        SINGLES (individual article pages)

        --  81, a switchboard for the others
        --  82, based on single 4
        --  83, based on single 5
        --  84, based on single 9

        CATEGORY (the category page layout)

        -- 91, based on template 3

    All of these are explained with their changes and purpose in more detail
    in the files themelves. They are intended to be easy to keep updated.
    Ensure you take a look at them after any major Newspaper revision!

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Set per-post variables while editing posts.

3.  Use the modules, blocks, single templates, etc. throughout the site.

================================================================================
FUTURE
======

--  Keep updating on an ongoing basis to keep pace with theme developments.

--  There is a 'cred-sprite.png' sprite sheet for the authorship icons. This is
    an outdated model. Icon fonts are now in. Moreover, Newspaper comes with
    one installed, I believe, so it should be an easy transition. Just need
    to update the CSS and the API's icon method.

--  There is currently an option for the roles displayed in the author boxes
    at the bottom of each article. The choice is between showing the person's
    role from the time the article was published, or their most recent. A good
    idea would be to add an option to show the role with the highest priority.
    This wasn't possible before we were able to set the priorities of roles.
    It means going to the post author box roles function in author_box.php,
    sorting the user's roles by priority, and choosing the first one.

--  The Theme Panel has a lot of CSS in it, about 1300 lines at time of writing.
    Although this is a good way to load CSS (it's async, I think, which is why
    I had to move some above-the-fold stuff to a regular enqueued stylesheet),
    some of the styles in there are not theme-dependent but NPEnhance-dependent.
    For proper separation of purpose those styles should be removed from the
    theme panel and added to NPEnhance. (They could even be added by database
    update in NPEnhance in order to continue to use the Theme Panel's loading.)

        --  But note that some of the styles are actually NOT Newspaper-related
            at all, e.g. the Sharify code. I'm not sure the best way to handle
            that; a separate module or plugin for each doesn't seem practical.
            Maybe best just to leave that in the Theme Panel.

        --  If you do this, you might as well clean up the CSS while you're
            at it. It was last done a couple years ago, and as features have
            evolved and changed or been left behind, there must be some extra
            CSS we could drop. And some classes must overlap due to on-the-fly
            fixes made without reference to existing styles for speed's sake.

--  The CSS file included here currently hardcodes category colours. If you
    can think of a way to dynamically write those (using MJKCommonTools --
    compare how VI does it on the issue page), do that instead.

================================================================================
TECHNICAL NOTES
===============

--  The settings page and post metaboxes use JKNACF.

--  All the templates are added through the tagDiv API.

--  The Meta and Masthead modules are used extensively to populate the templates
    with our data.

--  The author.php template is also from Newspaper, but it can't be added by the
    API. Instead, it's added just by filtering template_include.
