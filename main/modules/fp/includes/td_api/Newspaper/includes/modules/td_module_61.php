<?php

/**
 * MJK: 5 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our image getter; our meta info; more hover effects allowed
 */
class td_module_61 extends td_module_mx9 {

    // MJK: 1/5: Added this format for the title
	const hover_title = '<span class="td-post-category"' .
                        ' style="text-transform: none !important;">%s</span>';

	function render() {
		ob_start();
		?>

        <div class="<?php echo $this->get_module_classes(["td-big-grid-post-0", "td-big-grid-post", "td-big-thumb"]); ?>">
	        <?php
                // MJK: 2/5: Our image
	            echo MJKNPEnhanceAPI::image('td_741x486', $this);
	        ?>
            <div class="td-meta-info-container">
                <div class="td-meta-align">
                    <div class="td-big-grid-meta">
						<?php
                            // MJK: 3/5: Our module number
                            if (td_util::get_option('tds_category_module_61') == 'yes') { echo $this->get_category(); }
                        ?>
						<?php
                            // MJK: 4/5: Title is now within this hover category
                            echo '<div class="clearfix"></div>';
                            echo sprintf(self::hover_title, $this->get_title());
						?>
                    </div>
	                <?php
                        // MJK: 5/5: Our meta info and it also gets a hover class
                        $meta = '';
                        $meta .= '<div class="td-module-meta-info td-post-category">';
                        $meta .= MJKNPEnhanceAPI::succinct_credits($this->post, true);
                        $meta .= MJKNPEnhanceAPI::date($this->post, true);
                        $meta .= '</div>';

                        echo $meta;
	                ?>
                </div>
            </div>

        </div>

		<?php return ob_get_clean();
	}
}