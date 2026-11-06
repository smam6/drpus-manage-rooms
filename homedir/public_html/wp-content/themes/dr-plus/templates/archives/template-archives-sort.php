<?php

use DrPlus\Components\Select;
use DrPlus\Utils;
use DrPlus\Utils\Archive;
use DrPlus\Utils\Options;

if( !defined( 'ABSPATH' ) ) exit;

$args = Utils::check_default( $args, [
	'label'	=> esc_html__( "Sort:", 'drplus' ),
	'sorts'	=> []
] );

if( empty( $args['sorts'] ) ) {
	$sorts = Archive::sorts();
} else {
	$sorts = $args['sorts'];
}

// Default archive sort
$options = Options::get_options( ['default_archive_sort' => 'newest'] );
$selected_archive_sort = $options['default_archive_sort'];
$selected_archive_sort = apply_filters( 'drplus/archive/default_sort', $selected_archive_sort, $sorts );
if( !array_key_exists( $selected_archive_sort, $sorts ) ) $selected_archive_sort = array_key_first( $sorts );

// Selected archive sort
if( !empty( $_GET['orderby'] ) && in_array( $_GET['orderby'], array_keys( $sorts ) ) ) {
	$selected_archive_sort = Utils::convert_chars( $_GET['orderby'] );
}

?>
<form action="" method="get" class="sort-form">
	<?php
	Select::view( [
		'wrap'	=> [
			'classes'	=> ['sort-wrap']
		],
		'label'		=> $args['label'],
		'options'	=> $sorts,
		'value'		=> $selected_archive_sort,
	] );
	?>
	<select name="orderby" class="orderby">
		<?php foreach( $sorts as $value => $label ) { ?>
			<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $value, $selected_archive_sort ) ?>><?php echo esc_html( $label ) ?></option>
		<?php } ?>
	</select>
	<?php Utils::query_string_form_fields( null, ['orderby', 'page'] ) ?>
</form>