<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Medical extends Utils {
	public static function blood_types() {
		return [
			'A+'	=> 'A+',
			'A-'	=> 'A-',
			'B+'	=> 'B+',
			'B-'	=> 'B-',
			'AB+'	=> 'AB+',
			'AB-'	=> 'AB-',
			'O+'	=> 'O+',
			'O-'	=> 'O-',
		];
	}

	public static function allergy_types() {
		return [
			'food'				=> esc_html__( 'Food', 'drplus' ),
			'medication'		=> esc_html__( 'Medication', 'drplus' ),
			'environmental'		=> esc_html__( 'Environmental', 'drplus' ),
			'chemical'			=> esc_html__( 'Chemical', 'drplus' ),
			'insect_stings'		=> esc_html__( 'Insect stings', 'drplus' ),
			'other'				=> esc_html_x( 'Other', 'allergy', 'drplus' ),
		];
	}

	public static function allergy_reactions() {
		return [
			'hives'					=> esc_html__( 'Hives', 'drplus' ),
			'shortness_of_breath'	=> esc_html__( 'Shortness of breath', 'drplus' ),
			'swelling'				=> esc_html__( 'Swelling', 'drplus' ),
			'anaphylactic_shock'	=> esc_html__( 'Anaphylactic shock', 'drplus' ),
			'itching'				=> esc_html__( 'Itching', 'drplus' ),
			'nausea_or_vomiting'	=> esc_html__( 'Nausea or vomiting', 'drplus' ),
			'other'					=> esc_html__( 'Other symptoms', 'drplus' ),
		];
	}

	public static function allergy_severity() {
		return [
			'mild'				=> esc_html__( 'Mild', 'drplus' ),
			'moderate'			=> esc_html__( 'Moderate', 'drplus' ),
			'severe'			=> esc_html__( 'Severe', 'drplus' ),
			'life_threatening'	=> esc_html__( 'Life-threatening', 'drplus' ),
		];
	}
}