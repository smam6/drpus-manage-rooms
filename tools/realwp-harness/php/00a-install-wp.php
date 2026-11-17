<?php
/**
 * 00a-install-wp.php — a genuine WordPress install (12 core tables + admin user).
 * Runs once, before the production dump is replayed on top.
 */
define('WP_INSTALLING', true);
$_SERVER['HTTP_HOST']='localhost:8080'; $_SERVER['REQUEST_URI']='/';
$_SERVER['SERVER_NAME']='localhost'; $_SERVER['REQUEST_METHOD']='GET';
require '/wordpress/wp-load.php';
require_once ABSPATH.'wp-admin/includes/upgrade.php';
if (!is_blog_installed()) {
  $r=wp_install('DrPlus Real','admin','admin@example.com',true,'','admin123');
  echo "installed uid={$r['user_id']}\n";
} else echo "already installed\n";
