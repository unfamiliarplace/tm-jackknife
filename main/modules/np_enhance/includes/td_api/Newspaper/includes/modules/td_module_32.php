<?php

/**
 * MJK: 3 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Our meta info; add excerpt
 */
class td_module_32 extends td_module_8 {

	function render() {
		ob_start();
		?>

        <div class="<?php echo $this->get_module_classes();?>">

            <div class="item-details">
				<?php echo $this->get_title();?>

                <div class="td-module-meta-info">
					<?php
					    // MJK: 1/3: Pass our module # instead
                        if (td_util::get_option('tds_category_module_32') == 'yes') { echo $this->get_category(); }
                    ?>
					<?php
                        // MJK: 2/3: Use our credits and date instead
                        echo MJKNPEnhanceAPI::succinct_credits($this->post);
                        echo MJKNPEnhanceAPI::date($this->post);
					?>

					<?php echo $this->get_comments();?>
                </div>
            </div>

            <!-- MJK 3/3: Add excerpt (code from module 2) -->
            <div class="td-excerpt">
		        <?php echo $this->get_excerpt();?>
            </div>

			<?php echo $this->get_quotes_on_blocks();?>

        </div>

		<?php return ob_get_clean();
	}
}