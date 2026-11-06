<?php
namespace DrPlus\Updates;

class V1_4_2_0 {
	public static $run_at_install = false;
	
	public static function update() {
		flush_rewrite_rules();
	}
}