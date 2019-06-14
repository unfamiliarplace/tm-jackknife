<?php
/**
 Plugin Name: The Medium Jackknife
 Plugin URI: https://themedium.ca
 Description: Modules built on the Jackknife framework.
 Author: Luke Sawczak
 Version: 1.2
 Author URI: https://sawczak.ca
 */

// This is the Jackknife hook. By using it we load only if JKN is active.
add_action('jkn_register', function(): void { require_once 'jkn_api.php'; });
