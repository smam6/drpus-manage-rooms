<?php
/**
 * Dr Plus Child Theme entry point.
 *
 * Implements: spec W13 (ABSPATH guard in every PHP file)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**************************************************************************
***************************************************************************/
/* DON'T REMOVE THIS LINE 👇🏻 */
include( trailingslashit( get_stylesheet_directory() ) . 'ChildInit.php' );
/* DON'T REMOVE THIS LINE 👆🏻 */
/**************************************************************************
***************************************************************************/

/**************************************************************************
✅ START EDIT FROM HERE 👇🏻
HAPPY CODING 😊
***************************************************************************/

/**
 * Room Capacity Management module loader.
 *
 * Implements: spec 8.2, 8.8 (feature code lives only in inc/capacity/, never here)
 */
require_once trailingslashit( get_stylesheet_directory() ) . 'inc/capacity/bootstrap.php';
