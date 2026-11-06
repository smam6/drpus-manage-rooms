<?php

use DrPlus\ElementorControls;
use DrPlus\Utils;
use DrPlus\Utils\Options;

if( !function_exists( "drplus_elementor_widget_categories" ) ) {
	function drplus_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'drplus',
			[
				'title'	=> esc_html__( 'Doctor plus', 'drplus' ),
				'icon'	=> 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'drplus_archive',
			[
				'title'	=> esc_html__( 'Doctor Plus - Archive', 'drplus' ),
				'icon'	=> 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'drplus_single_product',
			[
				'title'	=> esc_html__( 'Doctor Plus - Single Product', 'drplus' ),
				'icon'	=> 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'drplus_single_specialist',
			[
				'title'	=> esc_html__( 'Doctor Plus - Single Specialist', 'drplus' ),
				'icon'	=> 'fa fa-plug',
			]
		);
	}
}
add_action( 'elementor/elements/categories_registered', 'drplus_elementor_widget_categories' );

// MARK: El. Widgets
if( !function_exists( "drplus_register_elementor_widgets" ) ) {
	function drplus_register_elementor_widgets( $widgets_manager ) {
		// Add dark option controls to core widgets
		$core_widgets = ['container', 'heading', 'icon', 'text-editor', 'divider'];
		foreach( $core_widgets as $core_widget ) {
			if( file_exists( DRPLUS_DIR . "inc/Elementor/CoreWidgets/elementor-core-{$core_widget}.php" ) ) {
				include( DRPLUS_DIR . "inc/Elementor/CoreWidgets/elementor-core-{$core_widget}.php" );
			}
		}

		$widgets = [
			'button'						=> ['class'	=> 'Button'],
			'section-title'					=> ['class'	=> 'SectionTitle'],
			'simple_icon'					=> ['class'	=> 'SimpleIcon'],
			'proicon'						=> ['class'	=> 'ProIcon'],
			'proicon-group'					=> ['class'	=> 'ProIconGroup'],
			'archive'						=> ['class'	=> 'Archive'],
			'archive-2'						=> ['class'	=> 'Archive2'],
			'search'						=> ['class'	=> 'Search'],
			'specialists-search'			=> ['class'	=> 'SpecialistsSearch'],
			'services'						=> ['class'	=> 'Services'],
			'services2'						=> ['class'	=> 'Services2'],
			'specialists'					=> ['class' => 'Specialists'],
			'specialists-online-visits'		=> ['class' => 'SpecialistsOnlineVisits'],
			'specialists-offline-visits'	=> ['class' => 'SpecialistsOfflineVisits'],
			'specialist-slider'				=> ['class'	=> 'SpecialistSlider'],
			'products'						=> ['class'	=> 'Products', 'requirements' => ['woocommerce']],
			'products-2'					=> ['class'	=> 'Products2', 'requirements' => ['woocommerce']],
			'cta1'							=> ['class'	=> 'CTA1'],
			'cta2'							=> ['class'	=> 'CTA2'],
			'testimonials1'					=> ['class' => 'Testimonials1'],
			'testimonials2'					=> ['class' => 'Testimonials2'],
			'testimonials3'					=> ['class' => 'Testimonials3'],
			'statistics'					=> ['class' => 'Statistics'],
			'statistics-card'				=> ['class' => 'StatisticsCard'],
			'statistics-card2'				=> ['class' => 'StatisticsCard2'],
			'book-form'						=> ['class'	=> 'BookForm', 'requirements' => ['woocommerce']],
			'consult-form'					=> ['class'	=> 'ConsultForm', 'requirements' => ['woocommerce']],
			'accordion'						=> ['class'	=> 'Accordion'],
			'hospitals'						=> ['class'	=> 'Hospitals'],
			'clinics'						=> ['class'	=> 'Clinics'],
			'breadcrumb'					=> ['class' => 'BreadCrumb'],
			'reviews'						=> ['class' => 'Reviews'],

			// Single Product
			'product-featured-attributes'	=> ['class'	=> 'ProductFeaturedAttributes', 'requirements' => ['woocommerce']],
			'product-subtitle'				=> ['class'	=> 'ProductSubtitle', 'requirements' => ['woocommerce']],
			'product-rating'				=> ['class'	=> 'ProductRating', 'requirements' => ['woocommerce']],
			'product-reviews-count'			=> ['class'	=> 'ProductReviewsCount', 'requirements' => ['woocommerce']],
			'product-services'				=> ['class'	=> 'ProductServices', 'requirements' => ['woocommerce']],
			'wishlist-button'				=> ['class'	=> 'WishlistButton', 'requirements' => ['woocommerce']],

			// Archive
			'sort'	=> ['class'	=> 'Sort'],

			// Header
			'menu'				=> ['class' => 'Menu'],
			'mini-cart'			=> ['class' => 'MiniCart'],
			'account-button'	=> ['class' => 'AccountButton'],

			'theme-toggle-button'			=> ['class'	=> 'ThemeToggleButton'],

			// Single Specialist
			'specialist-name'				=> ['class' => 'SpecialistName'],
			'specialist-subtitle'			=> ['class' => 'SpecialistSubtitle'],
			'specialist-code'				=> ['class' => 'SpecialistCode'],
			'specialist-score'				=> ['class' => 'SpecialistScore'],
			'specialist-book-button'		=> ['class' => 'SpecialistBookButton'],
			'specialist-offices'			=> ['class' => 'SpecialistOffices'],
			'specialist-services'			=> ['class' => 'SpecialistServices'],
			'specialist-insurances'			=> ['class' => 'SpecialistInsurances'],
			'specialist-statistics'			=> ['class' => 'SpecialistStatistics'],
			'specialist-bio'				=> ['class' => 'SpecialistBio'],
			'specialist-certificate'		=> ['class' => 'SpecialistCertificate'],
			'specialist-faq'				=> ['class' => 'SpecialistFaq'],
			'specialist-related-specialist'	=> ['class' => 'SpecialistRelatedSpecialist'],

		];
		if( class_exists( "DrPlus\Utils" ) ) {
			$options = Options::get_options( [
				'booking'	=> true,
			] );
			if( !$options['booking'] ) {
				unset( $widgets['booking'] );
			}
			include( DRPLUS_DIR . "inc/ElementorControls.php" );
			foreach( $widgets as $filename => $widget ) {
				if( !empty( $widget['requirements'] ) && !Utils::should_include_module( $widget['requirements'] ) ) continue;
				$class = "\DrPlus\Elementor\\" . $widget['class'];

				if( !empty( $widget['folder'] ) ) {
					$path = DRPLUS_DIR . "inc/Elementor/elementor-{$filename}.php";
				} else {
					$path = DRPLUS_DIR . "inc/Elementor/elementor-{$filename}.php";
				}
				if( file_exists( DRPLUS_DIR . "inc/Elementor/elementor-{$filename}.php" ) && !class_exists( $class ) ) {
					include( DRPLUS_DIR . "inc/Elementor/elementor-{$filename}.php" );
					$widgets_manager->register( new $class() );
				}
			}
		}
	}
}
add_action( 'elementor/widgets/register', 'drplus_register_elementor_widgets' );

if( !function_exists( "drplus_register_elementor_fonts_group" ) ) {
	function drplus_register_elementor_fonts_group( $font_groups ) {
		$font_groups['drplus'] = __( 'Theme fonts', 'drplus' );
		return $font_groups;
	}
}
add_filter( 'elementor/fonts/groups', 'drplus_register_elementor_fonts_group' );

if( !function_exists( "drplus_register_elementor_additional_fonts" ) ) {
	function drplus_register_elementor_additional_fonts( $additional_fonts ) {
		if( class_exists( "DrPlus\Utils" ) ) {
			$active_fonts = Utils::get_active_fonts();
			foreach( $active_fonts as $font ) {
				$additional_fonts[$font] = 'drplus';
			}
		}
		return $additional_fonts;
	}
}
add_filter( 'elementor/fonts/additional_fonts', 'drplus_register_elementor_additional_fonts' );

if( !function_exists( "drplus_register_new_dynamic_tag_group" ) ) {
	function drplus_register_new_dynamic_tag_group( $dynamic_tags_manager ) {
		$dynamic_tags_manager->register_group(
			'drplus',
			[
				'title'	=> esc_html__( "Doctor Plus", 'drplus' ),
			]
		);
	}
}
add_action( 'elementor/dynamic_tags/register', 'drplus_register_new_dynamic_tag_group' );

if( !function_exists( "drplus_register_dynamic_tags" ) ) {
	function drplus_register_dynamic_tags( $dynamic_tags_manager ) {
		$tags = [
			'specialists-count'	=> ['class' => 'SpecialistsCount'],
		];
		if( class_exists( "DrPlus\Utils" ) ) {
			foreach( $tags as $filename => $tag ) {
				if( !empty( $tag['requirements'] ) && !Utils::should_include_module( $tag['requirements'] ) ) continue;
				$class = "\DrPlus\Elementor\DynamicTags\\" . $tag['class'];

				if( file_exists( DRPLUS_DIR . "inc/Elementor/DynamicTags/elementor-dt-{$filename}.php" ) && !class_exists( $class ) ) {
					include( DRPLUS_DIR . "inc/Elementor/DynamicTags/elementor-dt-{$filename}.php" );
					$dynamic_tags_manager->register( new $class() );
				}
			}
		}
	}
}
add_action( 'elementor/dynamic_tags/register', 'drplus_register_dynamic_tags' );

if( !function_exists( "drplus_elementor_add_line_height_variable" ) ) {
	function drplus_elementor_add_line_height_variable( $element ) {
		$settings = $element->get_settings();
    
		// چک کردن تمام فیلدهای typography
		foreach( $settings as $key => $value ) {
			if( strpos( $key, 'typography_line_height' ) !== false && !empty( $value['size'] ) ) {
				$value['unit'] = $value['unit'] ?? '';
				$value['unit'] = $value['unit'] == 'custom' ? '' : $value['unit'];
				$line_height = $value['size'] . $value['unit'];
				$element->add_render_attribute( '_wrapper', 'style', '--line-height: ' . $line_height );
				break;
			}
			
			// یا اگر مستقیم line_height تنظیم شده
			if( $key === 'line_height' && !empty( $value['size'] ) ) {
				$value['unit'] = $value['unit'] ?? '';
				$value['unit'] = $value['unit'] == 'custom' ? '' : $value['unit'];
				$line_height = $value['size'] . $value['unit'];
				$element->add_render_attribute( '_wrapper', 'style', '--line-height: ' . $line_height );
				break;
			}
		}
	}
}
add_action( 'elementor/frontend/before_render', 'drplus_elementor_add_line_height_variable' );