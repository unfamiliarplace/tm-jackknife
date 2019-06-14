<?php

/**
 * Allows <style> tags in posts.
 */
class MJKCommon_style_tags {

	/**
	 *  Allow <style> tags by preventing tinyMCE from stripping them
	 * and by dding them to the acceptable kses.
	 */
    static function run(): void {
        JKNEditing::allow_in_tinyMCE("style");
        JKNEditing::allow_in_kses("style", ["type" => []]);
    }
}
