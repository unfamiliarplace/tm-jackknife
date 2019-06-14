<?php

/*
 * =============================================================================
 * MJK: 4 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Remove image logo; our text and tagline as default; our structure
 * =============================================================================
 */

// MJK: 1/4: Removed unnecessary variables

$td_logo_text = td_util::get_option('tds_logo_text');
$td_tagline_text = stripslashes(td_util::get_option('tds_tagline_text'));

// MJK: 2/4: Use our default title and tagline
if (!$td_logo_text) $td_logo_text = 'The Medium';
if (!$td_tagline_text) $td_tagline_text = 'The Voice of the University of Toronto Mississauga';

// MJK: 3/4: Removed everything related to an image logo
?>

<!-- MJK: 4/4: Restructured the text logo to use a table and our CSS classes -->

<div class="td-logo-text-wrap">
    <div class="logo-text-container">
        <table class="med-logo"><tbody><tr>
            <td class="title">
                <a itemprop="url" class="td-logo-wrap" href="<?php echo esc_url(home_url( '/' )); ?>">
                    <div class="med-logo-text">
                        <?php echo $td_logo_text; ?>
                    </div>
                </a>
            </td>
            <td class="subtitle">
                <div class="med-tagline-text">
                    <?php
                        echo $td_tagline_text;
                    ?>
                </div>
            </td>
        </tr></tbody></table>
    </div>
</div>
