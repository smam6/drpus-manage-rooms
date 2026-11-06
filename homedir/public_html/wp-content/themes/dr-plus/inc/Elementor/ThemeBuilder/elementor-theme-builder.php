<?php
namespace DrPlus\Elementor;

class ThemeBuilder {
	private static $templates = [];

	public static function init() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			return;
		}

		self::$templates = [
			'single-specialist' => [
				'class'		=> 'DrPlus\Elementor\ThemeBuilder\SingleSpecialist',
				'label'		=> esc_html__( 'Single Specialist', 'drplus' ),
				'post_type'	=> 'specialist',
				'type'		=> 'single',
			],
			// 'archive-specialist' => [
			// 	'class'		=> 'DrPlus\Elementor\ThemeBuilder\ArchiveSpecialist',
			// 	'label'		=> esc_html__( 'Archive Specialist', 'drplus' ),
			// 	'post_type'	=> 'specialist',
			// 	'type'		=> 'archive',
			// ],
		];

		foreach( array_keys( self::$templates ) as $template_name ) {
			require_once DRPLUS_DIR . "inc/Elementor/ThemeBuilder/Documents/class-document-{$template_name}.php";
		}

		/**
		 * ثبت Document Type ها
		 */
		add_action( 'elementor/documents/register', [ __CLASS__, 'register_documents' ] );

		/**
		 * ثبت Location ها
		 */
		add_action( 'elementor/theme/register_locations', [ __CLASS__, 'register_locations' ] );

		/**
		 * کمک به UI در بعضی نسخه‌ها
		 */
		add_filter( 'elementor_pro/theme_builder/get_template_types', [ __CLASS__, 'register_template_types' ] );

		/**
		 * Preview پیش‌فرض داخل ادیتور
		 */
		add_filter( 'elementor/query/get_query_args/current_query', [ __CLASS__, 'filter_preview_query_args' ], 20, 2 );
		add_filter( 'elementor/theme/posts_archive/query_posts/query_vars', [ __CLASS__, 'filter_preview_query_args' ], 20, 2 );

		/**
		 * برای بعضی نسخه‌ها بهتر است preview id هم ذخیره شود
		 */
		add_action( 'save_post', [ __CLASS__, 'maybe_set_default_preview_meta' ], 20, 3 );
	}

	public static function register_documents( $documents_manager ) {
		foreach( self::$templates as $key => $data ) {
			$documents_manager->register_document_type(
				$key,
				$data['class']
			);
		}
	}

	public static function register_locations( $manager ) {
		foreach( self::$templates as $key => $data ) {
			$manager->register_location( $key, [
				'label'           => $data['label'],
				'multiple'        => true,
				'public'          => true,
				'edit_in_content' => true,
			] );
		}
	}

	public static function register_template_types( $types ) {
		foreach( self::$templates as $key => $data ) {
			$types[$key] = [
				'label' => $data['label'],
			];
		}

		return $types;
	}

	/**
	 * Default preview for elementor
	 */
	public static function filter_preview_query_args( $query_args ) {
		$post_id = get_the_ID();

		if ( ! $post_id && ! empty( $_GET['post'] ) ) {
			$post_id = absint( $_GET['post'] );
		}

		if ( ! $post_id ) {
			return $query_args;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );

		if ( ! $document && class_exists( '\ElementorPro\Plugin' ) ) {
			$document = \ElementorPro\Plugin::elementor()->documents->get_doc_or_auto_save( $post_id );
		}

		if ( ! $document ) {
			return $query_args;
		}

		$name = method_exists( $document, 'get_name' ) ? $document->get_name() : '';

		if ( ! array_key_exists( $name, self::$templates ) ) {
			return $query_args;
		}

		$template = self::$templates[ $name ];

		if ( 'single' === $template['type'] ) {
			$preview_id = self::get_default_single_preview_id( $template['post_type'] );

			if ( $preview_id ) {
				$query_args['p']         = (int) $preview_id;
				$query_args['page_id']   = 0;
				$query_args['post_type'] = $template['post_type'];
				$query_args['name']      = get_post_field( 'post_name', $preview_id );

				unset(
					$query_args['pagename'],
					$query_args['attachment'],
					$query_args['attachment_id'],
					$query_args['preview_id']
				);
			}
		} elseif ( 'archive' === $template['type'] ) {
			$archive_link = get_post_type_archive_link( $template['post_type'] );

			$query_args['post_type'] = $template['post_type'];
			$query_args['p']         = 0;
			$query_args['page_id']   = 0;

			unset(
				$query_args['name'],
				$query_args['pagename'],
				$query_args['attachment'],
				$query_args['attachment_id'],
				$query_args['preview_id']
			);

			if ( $archive_link ) {
				$parsed = wp_parse_url( $archive_link );

				if ( ! empty( $parsed['path'] ) ) {
					$query_args['pagename'] = trim( $parsed['path'], '/' );
				}
			}
		}

		return $query_args;
	}

	
	/**
	 * ذخیره preview id برای برخی نسخه‌ها/سازگاری بهتر
	 */
	public static function maybe_set_default_preview_meta( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'elementor_library' !== $post->post_type ) {
			return;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $document ) {
			return;
		}

		$name = $document->get_name();

		if( !array_key_exists( $name, self::$templates ) ) return;

		$existing = get_post_meta( $post_id, '_elementor_preview_id', true );
		if ( $existing ) {
			return;
		}

		$template = self::$templates[$name];
		if( $template['type'] == 'single' ) {
			$preview_id = self::get_default_single_preview_id( $template['post_type'] );
			if ( $preview_id ) {
				update_post_meta( $post_id, '_elementor_preview_id', $preview_id );
			}
		}
	}

	public static function get_default_single_preview_id( $post_type ) {
		$manual = (int) get_option( "drplus_default_{$post_type}_preview_id", 0 );
		if ( $manual && $post_type === get_post_type( $manual ) && 'publish' === get_post_status( $manual ) ) {
			return $manual;
		}

		$posts = get_posts( [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );

		return ! empty( $posts[0] ) ? (int) $posts[0] : 0;
	}
}
ThemeBuilder::init();