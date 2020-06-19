<?php
/*
Plugin Name: Custom wp api
Author: JuanMi Carmona
Description: Create custom endpoint wordpress rest api
*/
$dir = plugin_dir_path( __FILE__ );
require_once($dir.'get-posts.php');
require_once($dir.'get-post-by-slug.php');
require_once($dir.'get-post-preview.php');