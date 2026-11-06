<?php
namespace Sheyda\Wallet\Utils;

use SheydaWalletUtils as WalletUtils;

class Settings extends WalletUtils {
	public static function get_default_colors() {
		$colors = [
			'color-bg-color'	=> '#ffffff',
			'color-primary'		=> '#1DBAB5',
			'color-secondary'	=> '#159F9B',
			'color-red'			=> '#d60043',
			'color-btn-text'	=> '#ffffff',
		];
		$theme = wp_get_theme()->get_template();
		$our_themes = [
			'mj-holding'	=> [
				'option'	=> 'mj_holding',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
			'ramzineh'		=> [
				'option'	=> 'ramzineh',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
			'bijan'			=> [
				'option'	=> 'bijan',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
			'alfapress'		=> [
				'option'	=> 'alfapress',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
			'lenz'			=> [
				'option'	=> 'lenz',
				'color-primary'	=> 'primary_color_1',
				'color-secondary'	=> 'secondary_color_1',
			],
			'dr-plus'		=> [
				'option'	=> 'drplus',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
			'luxina'		=> [
				'option'	=> 'luxina',
				'color-primary'	=> 'primary_color_100',
				'color-secondary'	=> 'secondary_color_100',
			],
		];

		if( isset( $our_themes[$theme] ) ) {
			$options = get_option( $our_themes[$theme]['option'], [] );
			if( !empty( $options ) ) {
				foreach( $colors as $color_name => $default_color ) {
					$colors[$color_name] = !empty( $our_themes[$theme][$color_name] ) ? $options[$our_themes[$theme][$color_name]] : $default_color;
				}
			}
		}
		return $colors;
	}

	public static function default_settings( $section ) {
		$default = [];
		if( $section == 'general' ) {
			$default = [
				'enable'						=> true,
				'enable_refund'					=> true,
				'wc_purchase_order_status'		=> 'wc-processing',
				'myaccount_item_title'			=> esc_html__( 'Wallet', 'sheyda_wallet' ),
				'myaccount_item_position'		=> 3,
			];
		} else if( $section == 'payment' ) {
			$default = [
				'enable'						=> true,
				'min_cart_value'				=> 0,
				'max_allowable_use'				=> 0,
			];
		} else if( $section == 'withdrawal' ) {
			$default = [
				'enable'						=> true,
				'minimum_withdrawal_request'	=> '0',
				'withdrawal_fee_type'			=> 'none',
				'withdrawal_fixed_fee'			=> '0',
				'withdrawal_percentage_fee'		=> 0
			];
		} else if( $section == 'topup' ) {
			$default = [
				'enable'				=> true,
				'predefined_amounts'	=> ['50000', '100000', '200000', '500000', '1000000'],
				'enable_topup_checkout_template'	=> true,
			];
		} else if( $section == 'style' ) {
			$default = self::get_default_colors();
		}
		return $default;
	}

	public static function get_settings( $section = 'general' ) {
		$option_name = "sheyda_wallet_settings_{$section}";
		if( $section == 'style' ) {
			$option_name .= self::get_lang_suffix();
		}
		$skips = [];
		$settings = get_option( $option_name, [] );
		if( $section == 'topup' ) {
			if( isset( $settings['predefined_amounts'] ) ) $skips = ['predefined_amounts'];
		}
		$settings = parent::check_default( $settings, self::default_settings( $section ), $skips );
		if( $section == 'general' && !parent::is_wc_active() ) $settings['enable'] = false;
		return $settings;
	}

	private static function get_style_text_to_save( $options, $styler, $selector_positions ) {
		$css_content_array = [];
		foreach( $styler as $option => $data ) {
			if( empty( $options[$option] ) ) continue;

			if( !isset( $css_content_array[$data['selector']] ) ) $css_content_array[$data['selector']] = [];

			$selector_properties = [];
			if( !isset( $data['variables'] ) ) {
				$type = $data['type'];
				if( $type == 'background' ) {
					$selector_properties = array_filter( parent::unset( $options[$option], ['media'] ) );
					if( !empty( $selector_properties['background-image'] ) ) {
						$selector_properties['background-image'] = "url({$selector_properties['background-image']})";
					}
					if( !empty( $selector_properties['background-color'] ) ) {
						$selector_properties['background-color'] = parent::minify_hex( $selector_properties['background-color'] );
					}
					if( empty( $selector_properties['background-image'] ) && !empty( $selector_properties['background-color'] ) ) {
						$selector_properties['background-image'] = 'unset';
					} else if( !empty( $selector_properties['background-image'] ) && empty( $selector_properties['background-color'] ) ) {
						$selector_properties['background-color'] = 'transparent';
					}
				} else if( $type == 'typography' ) {
					$selector_properties = array_filter( parent::unset( $options[$option], ['font-options', 'google'] ) );
					if( isset( $selector_properties['font-size'] ) && str_ends_with( $selector_properties['font-size'], "rem" ) ) {
						$selector_properties['font-size'] = floatval( $selector_properties['font-size'] ) . "rem";
					}
				} else if( $type == 'color' ) {
					$selector_properties['color'] = parent::minify_hex( $options[$option] );
				} else if( $type == 'background-color' ) {
					$selector_properties['background'] = parent::minify_hex( $options[$option] );
				} else if( $type == 'border-color' ) {
					$selector_properties['border-color'] = parent::minify_hex( $options[$option] );
				} else if( $type == 'border-bottom' ) {
					if( $options[$option]['border-style'] === 'none' ) {
						$selector_properties['border-bottom'] = '0';
					} else {
						$selector_properties['border-bottom'] = "{$options[$option]['border-bottom']} {$options[$option]['border-style']} " . parent::minify_hex( $options[$option]['border-color'] );
					}
				} else if( $type == 'padding' ) {
					$selector_properties['padding'] = [
						!empty( $options[$option]['padding-top'] ) ? absint( $options[$option]['padding-top'] ) . "px" : 0,
						!empty( $options[$option]['padding-right'] ) ? absint( $options[$option]['padding-right'] ) . "px" : 0,
						!empty( $options[$option]['padding-bottom'] ) ? absint( $options[$option]['padding-bottom'] ) . "px" : 0,
						!empty( $options[$option]['padding-left'] ) ? absint( $options[$option]['padding-left'] ) . "px" : 0,
					];
					$selector_properties['padding'] = implode( " ", $selector_properties['padding'] );
				}
			} else { // Has variables
				foreach( $data['variables'] as $variable => $variable_data ) {
					if( !isset( $variable_data['type'] ) ) continue;

					$variable = strpos( $variable, "--" ) === 0 ? $variable : "--{$variable}";
					$value = '';
					if( $variable_data['type'] == 'color' ) {
						$value = parent::minify_hex( $options[$option] );
					} else if( $variable_data['type'] == 'font-family' ) {
						if( !$options[$option]['font-family'] ) continue;
						$value = $options[$option]['font-family'];
					} else if( $variable_data['type'] == 'background-color' ) {
						$value = parent::minify_hex( $options[$option] );
					}
					if( isset( $variable_data['value_suffix'] ) ) {
						$value .= $variable_data['value_suffix'];
					}
					$selector_properties[$variable] = $value;
				}
			}

			if( !empty( $data['important'] ) ) {
				$selector_properties = array_map( fn( $value ) => "{$value}!important", $selector_properties );
			}

			$css_content_array[$data['selector']] = array_merge( $css_content_array[$data['selector']], $selector_properties );
		}
		$css_content_array = array_filter( $css_content_array );

		// Reposition selectors
		if( !empty( $css_content_array[":root"] ) ) {
			foreach( $selector_positions as $key => $value ) {
				if( is_int( $key ) ) {
					$selector = $value;
					$position = $key;
				} else {
					$selector = $key;
					$position = $value;
				}
				parent::reposition_array_element( $css_content_array, $selector, $position );
			}
		}

		$css_content = '';
		foreach( $css_content_array as $selector => $properties ) {
			$selector = str_replace( ", ", ",", $selector );
			$css_content .= $selector . '{';
			$last_property = array_key_last( $properties );
			foreach( $properties as $property => $value ) {
				$css_content .= "{$property}:{$value}";
				if( $property != $last_property ) {
					$css_content .= ";";
				}
			}
			$css_content .= "}";
		}
		return $css_content;
	}

	public static function save_settings( $section, $settings ) {
		$skips = [];
		if( $section == 'topup' && isset( $settings['predefined_amounts'] ) ) $skips = ['predefined_amounts'];
		$settings = parent::check_default( $settings, self::default_settings( $section ), $skips );
		$option_name = "sheyda_wallet_settings_{$section}";
		if( $section == 'style' ) {
			$option_name .= self::get_lang_suffix();
		}

		if( $section == 'style' ) {
			$file = self::get_style_file( 'path' );

			$styler = [
				'color-primary'	=> [
					'selector'	=> ':root',
					'variables'	=> [
						'sw-primary'	=> [
							'type'	=> 'color',
						],
						'sw-primary-20'	=> [
							'type'			=> 'color',
							'value_suffix'	=> '33',
						],
					],
				],
				'color-secondary'	=> [
					'selector'	=> ':root',
					'variables'	=> [
						'sw-secondary'	=> [
							'type'	=> 'color',
						],
					],
				],
				'color-red'	=> [
					'selector'	=> ':root',
					'variables'	=> [
						'sw-red'	=> [
							'type'	=> 'color',
						],
						'sw-red-20'	=> [
							'type'			=> 'color',
							'value_suffix'	=> '33',
						],
					],
				],
				'color-btn-text'	=> [
					'selector'	=> ':root',
					'variables'	=> [
						'sw-btn-text'	=> [
							'type'	=> 'color',
						],
					],
				],
			];

			$selector_positions = [
				':root'
			];

			$css_content = self::get_style_text_to_save( $settings, $styler, $selector_positions );
			file_put_contents( $file, $css_content );

			$version_option_name = "sheyda_wallet_custom_style_version" . self::get_lang_suffix();
			update_option( $version_option_name, time(), false );
		}

		update_option( $option_name, $settings, false );
	}

	public static function get_lang_suffix() {
		$lang = '';
		if( isset( $_REQUEST['lang'] ) ) {
			$lang = sanitize_key( wp_unslash( $_REQUEST['lang'] ) );
		}
		if( empty( $lang ) && has_filter( 'wpml_current_language' ) ) {
			$lang = apply_filters( 'wpml_current_language', null );
		}
		if( empty( $lang ) && function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'slug' );
			if( empty( $lang ) && function_exists( 'pll_default_language' ) ) {
				$lang = pll_default_language( 'slug' );
			}
		}
		if( empty( $lang ) && has_filter( 'wpml_default_language' ) ) {
			$lang = apply_filters( 'wpml_default_language', null );
		}
		$lang_suffix = !empty( $lang ) ? "-{$lang}" : '';

		return $lang_suffix;
	}

	/**
	 * Get style file path or uri
	 *
	 * @param  string $get
	 * @return string
	 */
	public static function get_style_file( $get = 'path' ) {
		$lang_suffix = self::get_lang_suffix();

		$upload_dir = wp_upload_dir();
		$css_filename = "sheyda_wallet{$lang_suffix}.css";

		return $get == 'path' ? $upload_dir['basedir'] . "/{$css_filename}" : $upload_dir['baseurl'] . "/{$css_filename}";
	}
}