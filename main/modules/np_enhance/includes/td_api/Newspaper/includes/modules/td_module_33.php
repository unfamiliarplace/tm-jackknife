<?php

/**
 * MJK: 2 modifications | Last updated 2018-02-04 | Newspaper v8.6
 * Purpose: Remove meta info; add excerpt
 */
class td_module_33 extends td_module_8 {

	function render() {
		ob_start();
		?>

        <div class="<?php echo $this->get_module_classes();?>">

            <div class="item-details">
				<?php echo $this->get_title();?>
                <!-- MJK 1/2: Remove all meta info -->
            </div>

            <!-- MJK 2/2: Add excerpt (code from module 2) -->
            <div class="td-excerpt">
		        <?php echo $this->get_excerpt();?>
            </div>

			<?php echo $this->get_quotes_on_blocks();?>

        </div>

		<?php return ob_get_clean();
	}
}