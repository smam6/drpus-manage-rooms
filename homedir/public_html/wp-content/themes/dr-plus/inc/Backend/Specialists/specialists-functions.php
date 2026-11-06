<?php
namespace DrPlus\Backend\Specialists;

use DrPlus\Model\Specialists;
use DrPlus\Utils\Speciality;

class Functions {
	private static $specialist_cache = [];
	public static function add_specialist_editor_link_metabox() {
		add_meta_box(
			'drplus-specialist-editor-link',
			__( 'Specialist editor', 'drplus' ),
			[ __CLASS__, 'render_specialist_editor_link_metabox' ],
			'specialist',
			'side',
			'high'
		);
	}

	public static function render_specialist_editor_link_metabox( $post ) {
		$sid = absint( get_post_meta( $post->ID, '_drplus_specialist_id', true ) );
		if( empty( $sid ) ) {
			echo '<p>' . esc_html__( 'No linked specialist record found for this post.', 'drplus' ) . '</p>';
			return;
		}

		$url = add_query_arg(
			[
				'page'	=> 'specialists',
				'tab'	=> 'view',
				'sid'	=> $sid,
			],
			admin_url( 'admin.php' )
		);
		?>
		<p><?php esc_html_e( 'Open the dedicated specialist editor to manage profile data.', 'drplus' ); ?></p>
		<p>
			<a href="<?php echo esc_url( $url ); ?>" class="button button-primary" rel="noopener noreferrer">
				<?php esc_html_e( 'Edit specialist details', 'drplus' ); ?>
			</a>
		</p>
		<?php
	}

	public static function redirect_add_new_specialist() {
		global $pagenow;
		if ( $pagenow !== 'post-new.php' ) return;
		if ( empty( $_GET['post_type'] ) || $_GET['post_type'] !== 'specialist' ) return;

		$target = add_query_arg(
			[
				'page'	=> 'specialists',
				'tab'	=> 'view',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $target );
		exit;
	}

	public static function show_specialist_title( $html, $post_id, $new_title, $new_slug, $post ) {
		if ( $post->post_type !== 'specialist' ) return $html;

		$permalink = get_permalink( $post_id );

		$button = $permalink ? '<p><a href="' . esc_url( $permalink ) . '" target="_blank" rel="noopener noreferrer"><button class="button" type="button">' . esc_html__( 'View specialist', 'drplus' ) . '</button></a></p>' : '';

		return sprintf(
			'<div class="drplus-specialist-info" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">%s %s</div>',
			'<p id="drplus-specialist-title" style="margin:0">' . get_the_title( $post_id ) . '</p>',
			$button,
		);
	}

	private static function get_specialist_by_post_id( $post_id ) {
		if ( isset( self::$specialist_cache[ $post_id ] ) ) {
			return self::$specialist_cache[ $post_id ];
		}

		$specialist_id = (int) get_post_meta( $post_id, '_drplus_specialist_id', true );
		if ( ! $specialist_id ) {
			self::$specialist_cache[ $post_id ] = null;
			return null;
		}

		$specialist = Specialists::query()->find( $specialist_id );
		self::$specialist_cache[ $post_id ] = $specialist ?: null;

		return self::$specialist_cache[ $post_id ];
	}

	public static function add_admin_columns( $columns ) {
		$new_columns = [
			'cb'           => $columns['cb'] ?? '<input type="checkbox" />',
			'image'        => __( 'Image', 'drplus' ),
			'title'        => __( 'Name', 'drplus' ),
			'specialities' => __( 'Specialities', 'drplus' ),
		];

		return array_merge( $new_columns, $columns );
	}

	public static function render_admin_column( $column, $post_id ) {
		if ( ! in_array( $column, [ 'image', 'specialities' ], true ) ) return;

		$specialist = self::get_specialist_by_post_id( $post_id );
		if ( empty( $specialist ) ) {
			echo '&mdash;';
			return;
		}

		if ( $column === 'image' ) {
			echo get_avatar( $specialist->user_id, 48 );
			return;
		}

		if ( $column === 'specialities' ) {
			$specialities_ids = [];
			if ( ! empty( $specialist->specialities ) ) {
				$specialities_ids = wp_list_pluck( $specialist->specialities->toArray(), 'speciality_id' );
			}

			if ( empty( $specialities_ids ) ) {
				echo '&mdash;';
				return;
			}

			$specialities_posts = Speciality::all(
				[
					'post_status' => 'publish',
					'post__in'    => array_values( array_unique( $specialities_ids ) ),
				],
				true
			);

			$links = [];
			foreach ( $specialities_posts as $speciality ) {
				$links[] = '<a href="' . get_edit_post_link( $speciality->ID ) . '">' . esc_html( $speciality->post_title ) . '</a>';
			}

			echo implode( ', ', $links );
		}
	}

	public static function apply_status_filter( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) return;
		if ( $query->get( 'post_type' ) !== 'specialist' ) return;

		$status = isset( $_GET['drplus_specialist_status'] ) ? sanitize_text_field( wp_unslash( $_GET['drplus_specialist_status'] ) ) : '';
		if ( empty( $status ) || $status === 'all' ) return;

		$specialists = Specialists::query()
			->select( 'post_id' )
			->where( 'status', $status )
			->whereNotNull( 'post_id' )
			->where( 'post_id', '!=', 0 )
			->get();

		$post_ids = [];
		if ( ! empty( $specialists ) ) {
			foreach ( $specialists as $item ) {
				if ( ! empty( $item->post_id ) ) {
					$post_ids[] = (int) $item->post_id;
				}
			}
		}

		if ( empty( $post_ids ) ) {
			$post_ids = [ 0 ];
		}

		$query->set( 'post__in', array_unique( $post_ids ) );
		if( $status == 'deleted' ) {
			$query->set( 'post_status', 'trash' );
		}
	}

	public static function status_views( $views ) {
		$current = isset( $_GET['drplus_specialist_status'] ) ? sanitize_text_field( wp_unslash( $_GET['drplus_specialist_status'] ) ) : 'all';

		$items = [
			'all'        => [ 'label' => __( 'All', 'drplus' ), 'count' => 0 ],
			'pending'    => [ 'label' => __( 'Pending to review', 'drplus' ), 'count' => 0 ],
			'active'     => [ 'label' => __( 'Active', 'drplus' ), 'count' => 0 ],
			'inactive'   => [ 'label' => __( 'Inactive', 'drplus' ), 'count' => 0 ],
			'incomplete' => [ 'label' => __( 'Incomplete', 'drplus' ), 'count' => 0 ],
			'rejected'   => [ 'label' => __( 'Rejected', 'drplus' ), 'count' => 0 ],
			'deleted'    => [ 'label' => __( 'Deleted', 'drplus' ), 'count' => 0 ],
		];

		$specialists_statuses = Specialists::query()
			->select( ['COUNT(`id`) AS counts', 'status'] )
			->whereNotNull( 'post_id' )
			->where( 'post_id', '!=', 0 )
			->groupBy( 'status' )
			->get()
			->pluck( 'counts', 'status' );

		foreach ( $specialists_statuses as $status => $count ) {
			if ( isset( $items[ $status ] ) ) {
				$items[ $status ]['count'] = $count;
				if ( $status !== 'deleted' ) {
					$items['all']['count'] += $count;
				}
			}
		}

		$base_url = remove_query_arg( ['paged', 'drplus_specialist_status'], wp_unslash( $_SERVER['REQUEST_URI'] ) );

		$new_views = [];
		foreach ( $items as $status => $data ) {
			$url = $status === 'all'
				? $base_url
				: add_query_arg( 'drplus_specialist_status', $status, $base_url );

			$new_views[ $status ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$current === $status ? ' class="current"' : '',
				esc_html( $data['label'] ),
				(int) $data['count']
			);
		}

		return $new_views;
	}

	public static function hide_author_filter_css() {
		global $typenow;
		if ( $typenow !== 'specialist' ) return;
		?>
		<style>
			select[name="author"] { display: none !important; }
		</style>
		<?php
	}

	public static function update_specialist_on_save_post( $post_id, $post, $update ) {
		if( !$update || $post->post_type != 'specialist' ) return;
		$specialist = Specialists::query()->where( 'post_id', $post->ID )->first();
		if( !empty( $specialist ) ) {
			$specialist->slug = $post->post_name;
			$specialist->save();
		}
	}

	public static function trash_specialist( $post_id ) {
		$post = get_post($post_id);
		if (!$post || $post->post_type !== 'specialist') {
			return;
		}
		$specialist = Specialists::query()->where( 'post_id', $post_id )->first();
		if( !empty( $specialist ) ) {
			$specialist->status = 'deleted';
			$specialist->save();
		}
	}

	public static function untrash_specialist( $post_id ) {
		$post = get_post($post_id);
		if (!$post || $post->post_type !== 'specialist') {
			return;
		}
		$specialist = Specialists::query()->where( 'post_id', $post_id )->first();
		if( !empty( $specialist ) ) {
			$specialist->status = 'inactive';
			$specialist->save();
		}
	}

	public static function delete_specialist( $post_id ) {
		$post = get_post($post_id);
		if (!$post || $post->post_type !== 'specialist') {
			return;
		}
		$specialist = Specialists::query()->where( 'post_id', $post_id )->first();
		if( !empty( $specialist ) ) {
			$specialist_user_id = $specialist->user_id;
			delete_user_meta( $specialist_user_id, 'drplus_onboard_step' );
			delete_user_meta( $specialist_user_id, 'drplus_specialist_subscription_plan' );
			$specialist->delete();
		}
	}
}
add_action( 'add_meta_boxes_specialist', [Functions::class, 'add_specialist_editor_link_metabox'] );
add_action( 'admin_init', [Functions::class, 'redirect_add_new_specialist'] );
add_filter( 'get_sample_permalink_html', [Functions::class, 'show_specialist_title'], 10, 5 );
add_filter( 'manage_edit-specialist_columns', [Functions::class, 'add_admin_columns'] );
add_action( 'manage_specialist_posts_custom_column', [Functions::class, 'render_admin_column'], 10, 2 );
add_action( 'pre_get_posts', [Functions::class, 'apply_status_filter'] );
add_filter( 'views_edit-specialist', [Functions::class, 'status_views'] );
add_action( 'admin_head-edit.php', [Functions::class, 'hide_author_filter_css'] );
add_action( 'save_post', [Functions::class, 'update_specialist_on_save_post'], 10, 3 );
add_action( 'wp_trash_post', [Functions::class, 'trash_specialist'], 10, 3 );
add_action( 'untrash_post', [Functions::class, 'untrash_specialist'], 10, 3 );
add_action( 'before_delete_post', [Functions::class, 'delete_specialist'], 10, 3 );