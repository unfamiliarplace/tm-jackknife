<?php

/*
 * =====================================================================
 * Require all main module files
 * =====================================================================
 */

$module_dirs = [
    'anc', 'board', 'common', 'disqus', 'elections', 'enrich', 'fp',
    'gen_tools', 'masthead', 'meta', 'np_enhance', 'staff_check', 'vi'
];

foreach($module_dirs as $mdir) {
    require_once sprintf('modules/%s/module.php', $mdir);
}

/** 
 * Use the Jackknife API to register our modules.
 */
final class MJK_TheMediumJackknife {
    
    /*
     * Create and register the space, along with its modules and settings pages.
     */
    static function register_space() {
       
        /*
         * =====================================================================
         * Create the space and set its menu settings
         * =====================================================================
         */
        
        $space = JKNAPI::create_space('mjk', 'TM Jackknife');
        $space->set_icon_url(sprintf('%s/assets/menu-icon.png', $space->url()));
        $space->set_menu_order(83);
        
        
        /*
         * =====================================================================
         * Create all the dependencies
         * =====================================================================
         */
        
        // Plugins
        
        $acf_pro_dep = new JKNPluginDependency([
            'id'        => 'acf_pro',
            'name'      => 'Advanced Custom Fields Pro',
            'url'       => 'https://www.advancedcustomfields.com',
            'file'      => 'advanced-custom-fields-pro/acf.php'
        ]);
        
        $vc_dep = new JKNPluginDependency([
            'id'        => 'vc',
            'name'      => 'Visual Composer',
            'url'       => 'https://vc.wpbakery.com',
            'file'      => 'js_composer/js_composer.php'
        ]);
        
        $disqus_dep = new JKNPluginDependency([
            'id'        => 'disqus',
            'name'      => 'Disqus Comment System',
            'url'       => 'https://disqus.com',
            'file'      => 'disqus-comment-system/disqus.php'
        ]);

        // Plugins checked to activate optional behaviour

	    new JKNPluginDependency([
		    'id'        =>  'cf7',
		    'name'      =>  'Contact Form 7',
		    'url'       =>  'https://contactform7.com',
		    'file'      =>  'contact-form-7/wp-contact-form-7.php'
	    ]);

	    new JKNPluginDependency([
		    'id'        => 'wpua',
		    'name'      => 'WP User Avatar',
		    'url'       => 'http://wordpress.org/plugins/wp-user-avatar',
		    'file'      => 'wp-user-avatar/wp-user-avatar.php'
	    ]);
        
        // Themes
        
        $np_dep = new JKNThemeDependency([
            'id'        => 'newspaper',
            'name'      => 'Newspaper',
            'url'       => 'http://themeforest.net/user/tagDiv/portfolio',
            'author'    => 'tagDiv'
        ]);
        
        // Our own modules
        
        $vi_dep = new JKNModuleDependency([
            'id' => 'vi',
            'space_id' => $space->id()
        ]);
        
        $gen_tools_dep = new JKNModuleDependency([
            'id' => 'gen_tools',
            'space_id' => $space->id()
        ]);
        
        $meta_dep = new JKNModuleDependency([
            'id' => 'meta',
            'space_id' => $space->id()
        ]);
        
        $common_dep = new JKNModuleDependency([
            'id' => 'common',
            'space_id' => $space->id()
        ]);

	    $mh_dep = new JKNModuleDependency([
		    'id' => 'masthead',
		    'space_id' => $space->id()
	    ]);
        
        
        /*
         * =====================================================================
         * Create all the modules and their settings pages
         * =====================================================================
         */
        
        // Announcement
        $anc = new MJKAnc($space);        
        $anc
                ->add_plugin_dependency($acf_pro_dep);
        $anc_spage = new class($anc) extends JKNSettingsPageACF {};
        
        // Board of Directors
        $board = new MJKBoard($space);       
        $board
                ->add_module_dependency($gen_tools_dep)
	            ->add_module_dependency($common_dep)
                ->add_plugin_dependency($acf_pro_dep);
         $board_spage = new class($board) extends JKNSettingsPageACF {};
        
        // Common
        $common = new MJKCommon($space);        
        $common
                ->add_plugin_dependency($acf_pro_dep);
	    $common_spage = new class($common) extends JKNSettingsPageACF {};
        
        // Disqus Comments
        $disqus = new MJKDisqusComments($space);
        $disqus
                ->add_module_dependency($common_dep)
                ->add_plugin_dependency($acf_pro_dep)
                ->add_plugin_dependency($disqus_dep);
        $disqus_spage = new class($disqus) extends JKNSettingsPageACF {};
        
        // Elections
        $elections = new MJKElections($space);
        $elections
                ->add_module_dependency($gen_tools_dep)
                ->add_plugin_dependency($acf_pro_dep);
        $elections_spage = new class($elections) extends JKNSettingsPageACF {};
        
        // Article Enrichment
        $enrich = new MJKEnrich($space);        
        $enrich
	            ->add_module_dependency($common_dep)
                ->add_module_dependency($gen_tools_dep)
                ->add_module_dependency($vi_dep)
                ->add_plugin_dependency($acf_pro_dep);
        $enrich_spage = new class($enrich) extends JKNSettingsPageACF {};

        // Front Page
        $fp = new MJKFP($space);
        $fp
                ->add_module_dependency($meta_dep)
	            ->add_theme_dependency($np_dep)
                ->add_plugin_dependency($acf_pro_dep);
        
        // Generation Tools
        $gen_tools = new MJKGenTools($space);        
        $gen_tools
                ->add_module_dependency($vi_dep);
        $gen_tools_spage = $spage = new MJKGTScheduler($gen_tools);
        
        // Masthead
        $masthead = new MJKMasthead($space);
        $masthead
                ->add_module_dependency($gen_tools_dep)
                ->add_plugin_dependency($vc_dep);
	    $masthead_spage = new class($masthead) extends JKNSettingsPageACF {
        	function id(): string { return 'roles'; }
        };
        
        // Article Meta
        $meta = new MJKMeta($space);
        $meta
                ->add_plugin_dependency($acf_pro_dep);
        
        // Newspaper Enhancements
        $np_enhance = new MJKNPEnhance($space);        
        $np_enhance
                ->add_module_dependency($common_dep)
                ->add_module_dependency($meta_dep)
                ->add_plugin_dependency($acf_pro_dep)
                ->add_theme_dependency($np_dep);
        $np_enhance_spage = new class($np_enhance) extends JKNSettingsPageACF {};
        
        // Staff Check
        $sc = new MJKStaffCheck($space);
	    $sc
	            ->add_module_dependency($common_dep)
                ->add_module_dependency($gen_tools_dep)
                ->add_module_dependency($meta_dep)
	            ->add_module_dependency($mh_dep)
                ->add_module_dependency($vi_dep)
                ->add_plugin_dependency($acf_pro_dep);
	    $sc_spage = new class($sc) extends JKNSettingsPageACF {};
        
        // Volume & Issue
        $vi = new MJKVI($space);
        $vi
                ->add_module_dependency($common_dep)
                ->add_plugin_dependency($acf_pro_dep);
        $vi_spage = new class($vi) extends JKNSettingsPageACF {};
        
        
        /*
         * =====================================================================
         * Add all the settings pages to the space along with their order
         * =====================================================================
         */        
        
        $space->add_settings_page($anc_spage,               30  );
        $space->add_settings_page($gen_tools_spage,         40  );
        $space->add_settings_page($board_spage,             50  );
        $space->add_settings_page($elections_spage,         60  );
        $space->add_settings_page($common_spage,            70  );
	    $space->add_settings_page($masthead_spage,          80  );
        $space->add_settings_page($enrich_spage,            90  );
        $space->add_settings_page($np_enhance_spage,        100 );
	    $space->add_settings_page($sc_spage,                110 );
        $space->add_settings_page($vi_spage,                120 );
        $space->add_settings_page($disqus_spage,            140 );
    }
}

MJK_TheMediumJackknife::register_space();
