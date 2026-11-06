<?php

namespace DrPlus\Backend\Specialists;

use DrPlus\Utils\AdminUI;
use DrPlus\Utils\Options;
use DrPlus\Utils\SpecialistInsurancesRel;
use DrPlus\Utils\UtilsSpecialists;

class SpecialistServices extends SpecialistView {
	public static function view() {
		$user_id = parent::$specialist->user_id;
		$specialist_specialities = parent::$specialist->specialities->toArray();
		$specialist_specialities = wp_list_pluck( $specialist_specialities, 'speciality_id' );

		$options = Options::get_options( [
			'insurance'	=> true,
		] );
		
		$user_meta = parent::$specialist->meta;
		if( is_scalar( $user_meta ) ) {
			$user_meta = [];
		}
		
		if( !isset( $user_meta['services'] ) ) $user_meta['services'] = [];
		if( !isset( $user_meta['faqs'] ) ) $user_meta['faqs'] = [];
		
		$all_specialities = get_posts( [
			'post_type' => 'speciality',
			'numberposts' => -1,
		] );
		
		if( $options['insurance'] ) {
			$specialist_insurances = SpecialistInsurancesRel::get_user_insurances( $user_id )->pluck( 'insurance_id' );
			$all_insurances = UtilsSpecialists::get_insurances_terms();
		}

		?>
		<?php do_action( "drplus/backend/specialist/settings/services/before_specialities", self::$specialist, self::$user, self::$new ) ?>
		
		<div class="<?php echo parent::$PREFIX ?>specialities-wrap fullwidth">
			<?php
			AdminUI::select_with_label( [
				'label'				=> esc_html__( 'Specialities', 'drplus' ),
				'value'				=> $specialist_specialities,
				'id'				=> parent::$PREFIX . "specialities",
				'name'				=> parent::$PREFIX . "specialities[]",
				'select_classes'	=> ['drplus-select2'],
				'required'			=> true,
				'data-width'		=> '100%',
				'options'			=> wp_list_pluck( $all_specialities, 'post_title', 'ID' ),
				'multiple'			=> true
			] );
			?>
			<p class="description"><?php esc_html_e( 'These specialities are used in specialists searches and filters based on specialities.', 'drplus' ) ?></p>
		</div>

		<?php do_action( "drplus/backend/specialist/settings/services/after_specialities", self::$specialist, self::$user, self::$new ) ?>

		<?php if( $options['insurance'] ) { ?>
			<?php do_action( "drplus/backend/specialist/settings/services/before_insurances", self::$specialist, self::$user, self::$new ) ?>
			<div class="<?php echo parent::$PREFIX ?>covered_insurances-wrap">
				<?php
				AdminUI::select_with_label( [
					'label'				=> esc_html__( 'Covered insurances', 'drplus' ),
					'value'				=> $specialist_insurances,
					'id'				=> parent::$PREFIX . "insurances",
					'name'				=> parent::$PREFIX . "insurances[]",
					'select_classes'	=> ['drplus-select2'],
					'data-width'		=> '100%',
					'options'			=> wp_list_pluck( $all_insurances, 'name', 'id' ),
					'multiple'			=> true
				] );
				?>
				<p class="description"><?php printf( __( 'For manage insurances visit <a href="%s">insurances</a>', 'drplus' ), esc_url( admin_url( 'edit-tags.php?taxonomy=insurance' ) ) ) ?></p>
			</div>
			<?php do_action( "drplus/backend/specialist/settings/services/after_insurances", self::$specialist, self::$user, self::$new ) ?>
		<?php } ?>

		<?php
		do_action( "drplus/backend/specialist/settings/services/before_articles", self::$specialist, self::$user, self::$new );
		AdminUI::input_with_label( [
			'label'			=> esc_html__( 'Number of articles', 'drplus' ),
			'type'			=> 'number',
			'value'			=> $user_meta['articles'] ?? "",
			'id'			=> parent::$PREFIX . "articles",
			'name'			=> parent::$PREFIX . "meta[articles]",
			'input_classes'	=> ['regular-text', 'ltr'],
			'min'			=> 0,
		] );
		do_action( "drplus/backend/specialist/settings/services/after_articles", self::$specialist, self::$user, self::$new );
		?>
		
		<?php do_action( "drplus/backend/specialist/settings/services/before_services", self::$specialist, self::$user, self::$new ) ?>
		<div class="<?php echo parent::$PREFIX ?>services_wrap">
			<span class="<?php echo parent::$PREFIX ?>part-title"><?php esc_html_e( 'Services', 'drplus' ) ?></span>
			<div id="<?php echo parent::$PREFIX ?>services" class="<?php echo parent::$PREFIX ?>repeater_container">
				<?php foreach( $user_meta['services'] as $service_id => $service ) {
					parent::repeater_template( [
						'prefix'			=> parent::$PREFIX . 'service_',
						'id'				=> $service_id,
						'input_value'		=> $service['title'] ?? "",
						'input_label'		=> esc_html__( 'Title', 'drplus' ),
						'input_name'		=> 'title',
						'textarea_value'	=> $service['desc'] ?? "",
						'textarea_label'	=> esc_html__( 'Description', 'drplus' ),
						'textarea_name'		=> 'desc',
						'data_name'			=> 'services',
					] );
				} ?>
			</div>
			<button type="button" id="<?php echo parent::$PREFIX ?>service-add" class="<?php echo parent::$PREFIX ?>repeater-add"><?php esc_html_e( 'Add', 'drplus' ) ?></button>
			<script type="text/html" id="tmpl-<?php echo parent::$PREFIX ?>service_template">
				<?php echo parent::repeater_template( [
						'prefix'			=> parent::$PREFIX . 'service_',
						'id'				=> '{{{data.index}}}',
						'input_label'		=> esc_html__( 'Title', 'drplus' ),
						'input_name'		=> 'title',
						'textarea_label'	=> esc_html__( 'Description', 'drplus' ),
						'textarea_name'		=> 'desc',
						'data_name'			=> 'services',
					] ); ?>
			</script>
		</div>
		<?php do_action( "drplus/backend/specialist/settings/services/after_services", self::$specialist, self::$user, self::$new ) ?>

		<?php do_action( "drplus/backend/specialist/settings/services/before_faqs", self::$specialist, self::$user, self::$new ) ?>
		<div class="<?php echo parent::$PREFIX ?>faqs_wrap">
			<span class="<?php echo parent::$PREFIX ?>part-title"><?php esc_html_e( 'FAQs', 'drplus' ) ?></span>
			<div id="<?php echo parent::$PREFIX ?>faqs" class="<?php echo parent::$PREFIX ?>repeater_container">
				<?php foreach( $user_meta['faqs'] as $faq_id => $item ) {
					parent::repeater_template( [
						'prefix'			=> parent::$PREFIX . 'faq_',
						'id'				=> $faq_id,
						'input_value'		=> $item['question'] ?? "",
						'input_label'		=> esc_html__( 'Question', 'drplus' ),
						'input_name'		=> 'question',
						'textarea_value'	=> $item['answer'] ?? "",
						'textarea_label'	=> esc_html__( 'Answer', 'drplus' ),
						'textarea_name'		=> 'answer',
						'data_name'			=> 'faqs',
					] );
				} ?>
			</div>
			<button type="button" id="<?php echo parent::$PREFIX ?>faq-add" class="<?php echo parent::$PREFIX ?>repeater-add"><?php esc_html_e( 'Add', 'drplus' ) ?></button>
			<script type="text/html" id="tmpl-<?php echo parent::$PREFIX ?>faq_template">
				<?php echo parent::repeater_template( [
						'prefix'			=> parent::$PREFIX . 'faq_',
						'id'				=> '{{{data.index}}}',
						'input_label'		=> esc_html__( 'Question', 'drplus' ),
						'input_name'		=> 'question',
						'textarea_label'	=> esc_html__( 'Answer', 'drplus' ),
						'textarea_name'		=> 'answer',
						'data_name'			=> 'faqs',
					] ); ?>
			</script>
		</div>
		<?php do_action( "drplus/backend/specialist/settings/services/after_faqs", self::$specialist, self::$user, self::$new ) ?>
		<?php
	}
}