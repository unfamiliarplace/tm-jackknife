<?php

/**
 * MJK: 3 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: No image; our meta info
 */
class td_module_43 extends td_module_4 {

	function render() {
		ob_start();
		?>

        <div class="<?php echo $this->get_module_classes();?>">
            <div class="td-module-image">
				<?php
                    // MJK: 1/3: Remove image
                    // echo $this->get_image('td_324x235');
				?>
				<?php
				    // MJK: 2/3: Pass our module # instead
                    if (td_util::get_option('tds_category_module_43') == 'yes') { echo $this->get_category();
                }?>
            </div>

			<?php echo $this->get_title(); ?>

            <div class="td-module-meta-info">
	            <?php
                    // MJK: 3/3: Use our credits and date instead
                    echo MJKNPEnhanceAPI::succinct_credits($this->post);
                    echo MJKNPEnhanceAPI::date($this->post);
	            ?>
				<?php echo $this->get_comments();?>
            </div>

            <div class="td-excerpt">
				<?php echo $this->get_excerpt(); ?>
            </div>

			<?php echo $this->get_quotes_on_blocks();?>

        </div>

		<?php
		return ob_get_clean();
	}
}