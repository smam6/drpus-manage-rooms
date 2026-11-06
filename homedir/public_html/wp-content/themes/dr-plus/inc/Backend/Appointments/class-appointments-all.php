<?php

namespace DrPlus\Backend\Appointments;

class All extends AppointmentsList {
	protected static $PREFIX = '';
	public static function view() {
		self::$PREFIX = parent::$PREFIX;

		?>
		<div class="<?php echo self::$PREFIX ?>appointments-list">
			<form action="" method="POST">
				<?php
				parent::$table->prepare_items();
				parent::$table->views();
				parent::$table->display();
				?>
			</form>
		</div>
		<?php
	}
}