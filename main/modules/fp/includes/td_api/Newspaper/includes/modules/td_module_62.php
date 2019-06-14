<?php

/**
 * MJK: 4 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our image getter; more hover effects allowed
 */
class td_module_62 extends td_module_mx10 {

    // MJK: 1/4: Added this format for the title
	const hover_title = '<span class="td-post-category"' .
	                    ' style="text-transform: none !important;">%s</span>';

	function render($order_no) {
		ob_start();
		?>

        <div class="<?php echo $this->get_module_classes(["td-big-grid-post-$order_no", "td-big-grid-post", "td-small-thumb"]); ?>">
	        <?php
                // MJK: 2/4: Our image getter
	            echo MJKNPEnhanceAPI::image('td_324x160', $this);
	        ?>
            <div class="td-meta-info-container">
                <div class="td-meta-align">
                    <div class="td-big-grid-meta">
						<?php
                            // MJK: 3/4: Our module number
                            if (td_util::get_option('tds_category_module_62') == 'yes') { echo $this->get_category(); }
                        ?>
						<?php
                            // MJK: 4/4: Hover class for the title
                            echo '<div class="clearfix"></div>';
						    echo sprintf(self::hover_title, $this->get_title());
						?>
                    </div>
                </div>
            </div>

        </div>

		<?php return ob_get_clean();
	}
}