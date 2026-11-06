<?php
namespace DrPlus\Utils;

use DrPlus\Logger;
use DrPlus\Model\Booking;
use DrPlus\Utils;

class Skyroom extends Utils {
	private static $request_args = [
		'user-agent'	=> 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0',
		'timeout'		=> 60,
		'sslverify'		=> false,
		'headers'	=> [
			'Accept'		=> 'application/json',
			'Content-Type'	=> 'application/json',
		],
		'httpversion'	=> '1.1',
	];

	/**
	 * Helper function: Get API URL from api key
	 *
	 * @return string
	 */
	private static function get_api_url() : string {
		$options = Options::get_options( [
			'skyroom-api'	=> '',
		] );
		if( !$options['skyroom-api'] ) {
			return '';
		}
		return "https://www.skyroom.online/skyroom/api/{$options['skyroom-api']}";
	}

	/**
	 * Helper function: Get WordPress user info for send in request
	 *
	 * @param mixed $user
	 * @return array
	 */
	private static function get_wp_user_info( $user ) {
		$user = parent::get_user_object( $user );
		$gender = User::get_gender( $user->ID );
		if( $gender == 'male' ) {
			$gender = 1;
		} else if( $gender == 'female' ) {
			$gender = 2;
		} else {
			$gender = 0;
		}
		return [
			'username'		=> sanitize_title_with_dashes( is_numeric( $user->user_login ) ? "user-{$user->ID}" : $user->user_login ),
			'nickname'		=> $user->display_name,
			'password'		=> wp_generate_password( 8 ),
			'email'			=> User::get_email( $user ),
			'fname'			=> $user->first_name,
			'lname'			=> $user->last_name,
			'gender'		=> $gender,
			'status'		=> 1,
			'is_public'		=> false,
			'concurrent'	=> 2,
		];
	}

	/**
	 * Helper function: Send request to Skyroom
	 *
	 * @param string $action
	 * @param array $params
	 * @return object|WP_Error Body as object or WP_Error on failure
	 */
	private static function send_request( string $action, array $params = [], array $other_request_args = [] ) {
		$api_url = self::get_api_url();
		if( !$api_url ) {
			return new \WP_Error( 'skyroom_api_not_set', esc_html__( 'Skyroom API URL is not set.', 'drplus' ), [
				'status'	=> 500,
			] );
		}
		$request_args = self::$request_args;
		$request_args['body'] = [
			'action'	=> $action,
			'params'	=> $params,
		];
		$request_args['body'] = wp_json_encode( $request_args['body'] );

		$request_args = array_merge( $request_args, $other_request_args );

		$request = wp_remote_post( $api_url, $request_args );
		if( wp_remote_retrieve_response_code( $request ) === 200 ) {
			return json_decode( wp_remote_retrieve_body( $request ) );
		}
		Logger::error( 'Failed to connect to Skyroom API', [
			'action'	=> $action,
			'params'	=> $params,
		] );
		return new \WP_Error( 'skyroom_api_error', esc_html__( 'Failed to connect to Skyroom API.', 'drplus' ), [
			'status'	=> 500,
		] );
	}

	/**
	 * Helper function: Get book duration
	 *
	 * @param integer $book_id
	 * @return int|WP_Error
	 */
	private static function get_duration( $book_id ) {
		$options = Options::get_options( [
			'skyroom-add-time'	=> 5,
		] );
		$duration = Booking::query()->select( ['start_time', 'end_time'] )->where( 'book_id', $book_id )->first();
		if( empty( $duration ) ) {
			Logger::error( 'Booking data not found', [
				'book_id'	=> $book_id,
			] );
			return new \WP_Error( 'skyroom_get_duration', esc_html__( "Booking data not found", 'drplus' ), [
				'status'	=> 404,
			] );
		}
		$duration = ( strtotime( $duration->end_time ) - strtotime( $duration->start_time ) ) / 60;
		$duration += $options['skyroom-add-time'];
		return $duration;
	}

	/**
	 * Helper function: Get a WC order
	 *
	 * @param mixed $order
	 * @return object|WP_Error
	 */
	private static function get_order( $order ) {
		$order = wc_get_order( $order );
		if( empty( $order ) ) {
			Logger::error( 'Skyroom - Invalid order' );
			return new \WP_Error( 'skyroom_get_order', esc_html__( "Invalid order", 'drplus' ), [
				'status'	=> 404,
			] );
		}
		return $order;
	}

	/**
	 * Helper function: Get booking data of a order
	 *
	 * @param mixed $order
	 * @return array|WP_Error
	 */
	private static function get_booking_data( $order ) {
		$order = self::get_order( $order );
		if( is_wp_error( $order ) ) {
			return $order;
		}
		$booking_data = $order->get_meta( '_booking_data' );
		if( empty( $booking_data ) || !is_array( $booking_data ) ) {
			Logger::error( 'Skyroom - Booking data not found', [
				'order_id'	=> $order->get_id(),
			] );
			return new \WP_Error( 'skyroom_update_or_create_room', esc_html__( "Booking data not found", 'drplus' ), [
				'status'	=> 404,
			] );
		}
		return $booking_data;
	}

	/**
	 * Get room id
	 *
	 * @param integer $room_id
	 * @param mixed $order
	 * @param bool $update_or_create
	 * @return int|WP_Error
	 */
	private static function get_room_id( $room_id = 0, $order = null, bool $update_or_create = true ) {
		$room_id = absint( $room_id );
		if( !$room_id && !empty( $order ) ) {
			$order = self::get_order( $order );
			if( is_wp_error( $order ) ) {
				return $order;
			}
			$room_id = absint( $order->get_meta( '_skyroom_room_id' ) );
			if( $update_or_create ) {
				$room_id = self::update_or_create_room( $order );
			}
		} else if( !$room_id && empty( $order ) ) {
			Logger::error( 'Skyroom - Invalid room ID or order', [
				'room_id'	=> $room_id,
			] );
			return new \WP_Error( 'skyroom_get_room_id', esc_html__( "Invalid room ID or order", 'drplus' ), [
				'status'	=> 404,
			] );
		}
		return is_wp_error( $room_id ) ? $room_id : absint( $room_id );
	}

	public static function get_user_skyroom_id_from_meta( $user_id ) {
		$user_id = parent::get_user_id( $user_id );
		return absint( get_user_meta( $user_id, 'skyroom_id', true ) );
	}

	/**
	 * Create user in Skyroom with WordPress user
	 *
	 * @param mixed $user
	 * @return int|WP_Error The user ID in Skyroom
	 */
	public static function create_skyroom_user( $user = null, array $other_request_args = [] ) {
		$user = parent::get_user_object( $user );
		if( !$user ) return 0;

		$params = self::get_wp_user_info( $user );

		$body = self::send_request( 'createUser', $params, $other_request_args );
		if( is_wp_error( $body ) ) return $body;
		
		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "create_skyroom_user_{$body->error_code}" : 'create_skyroom_user';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in creating Skyroom user", 'drplus' );
			Logger::error( "Skyroom - Create user - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			update_user_meta( $user->ID, 'skyroom_id', $body->result );
			return $body->result;
		}
		Logger::error( 'Skyroom - Error in creating user', [
			'params'	=> $params,
		] );
		return new \WP_Error( 'create_skyroom_user', esc_html__( "Error in creating Skyroom user", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Get Skyroom user ID from user's meta
	 *
	 * @param mixed $user WP User
	 * @return int|WP_Error
	 */
	public static function get_user_skyroom_id( $user = null ) {
		$user = parent::get_user_object( $user );
		if( !$user ) return 0;

		$skyroom_id = self::get_user_skyroom_id_from_meta( $user->ID );
		if( !$skyroom_id ) {
			$skyroom_id = self::create_skyroom_user( $user );
			if( empty( $skyroom_id ) || is_wp_error( $skyroom_id ) ) {
				return $skyroom_id;
			}
		}
		return $skyroom_id;
	}

	public static function get_skyroom_users() {
		$body = self::send_request( 'getUsers' );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_get_users_{$body->error_code}" : 'skyroom_get_users';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in getting users", 'drplus' );
			Logger::error( "Skyroom - Get users - " . $error_text, [
				'code'		=> $error_code,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
			] );
		}
		if( !empty( $body->result ) ) {
			return wp_list_pluck( $body->result, 'username', 'id' );
		} else {
			return [];
		}
	}

	/**
	 * Update User info in Skyroom
	 *
	 * @param mixed $user WP User
	 * @return bool|WP_Error
	 */
	public static function update_skyroom_user( $user = null ) {
		$user = parent::get_user_object( $user );
		if( !$user ) return 0;

		$skyroom_id = self::get_user_skyroom_id_from_meta( $user->ID );
		if( !$skyroom_id ) {
			$skyroom_id = self::create_skyroom_user( $user );
			return $skyroom_id;
		}

		$skyroom_id = self::get_user_skyroom_id( $user );
		if( empty( $skyroom_id ) || is_wp_error( $skyroom_id ) ) return $skyroom_id;

		$params = self::get_wp_user_info( $user );
		$params['user_id'] = $skyroom_id;
		$params = parent::unset( $params, ['username'] );

		$body = self::send_request( 'updateUser', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "update_skyroom_user_{$body->error_code}" : 'update_skyroom_user';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in updating Skyroom user", 'drplus' );
			Logger::error( "Skyroom - update user - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			return parent::to_bool( $body->result );
		}
		Logger::error( 'Skyroom - Error in updating user', [
			'params'	=> $params,
		] );
		return new \WP_Error( 'update_skyroom_user', esc_html__( "Error in updating Skyroom user", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Delete User from Skyroom
	 *
	 * @param mixed $user WP User
	 * @return bool|WP_Error
	 */
	public static function delete_skyroom_user( $user = null ) {
		$user = parent::get_user_object( $user );
		if( !$user ) return 0;

		$skyroom_id = self::get_user_skyroom_id( $user );
		if( empty( $skyroom_id ) || is_wp_error( $skyroom_id ) ) return $skyroom_id;

		$params = [
			'user_id'	=> $skyroom_id,
		];

		$body = self::send_request( 'deleteUser', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "delete_skyroom_user_{$body->error_code}" : 'delete_skyroom_user';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in deleting Skyroom user", 'drplus' );
			Logger::error( "Skyroom - delete user - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			$success = parent::to_bool( $body->result );
			if( $success ) {
				delete_user_meta( $user->ID, 'skyroom_id' );
			}
			return $success;
		}

		Logger::error( "Skyroom - Error in deleting Skyroom user", [
			'params'	=> $params,
		] );
		return new \WP_Error( 'delete_skyroom_user', esc_html__( "Error in deleting Skyroom user", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Delete multiple skyroom users
	 *
	 * @param array[mixed] $user
	 * @return bool|WP_Error
	 */
	public static function delete_multiple_skyroom_users( array $users ) {
		$skyroom_ids = [];
		foreach( $users as $user ) {
			$user = parent::get_user_object( $user );
			$skyroom_id = self::get_user_skyroom_id( $user );
			if( empty( $skyroom_id ) || is_wp_error( $skyroom_id ) ) return $skyroom_id;
			$skyroom_ids[] = $skyroom_id;
		}

		if( !empty( $skyroom_ids ) ) {
			$skyroom_ids = array_unique( $skyroom_ids );
			$params = [
				'users'	=> $skyroom_ids,
			];

			$body = self::send_request( 'deleteUsers', $params );
			if( is_wp_error( $body ) ) return $body;

			if( isset( $body->ok ) && !$body->ok ) {
				$error_code = !empty( $body->error_code ) ? "delete_multiple_skyroom_users_{$body->error_code}" : 'delete_multiple_skyroom_users';
				$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in deleting multiple Skyroom users", 'drplus' );
				Logger::error( "Skyroom - delete multiple users - " . $error_text, [
					'code'		=> $error_code,
					'params'	=> $params,
				] );
				return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
					'status'	=> 500,
					'data'		=> [
						'params'	=> $params,
					],
				] );
			}
			if( !empty( $body->result ) ) {
				return true;
			}

			Logger::error( "Skyroom - Error in deleting multiple Skyroom users", [
				'params'	=> $params,
			] );
			return new \WP_Error( 'delete_multiple_skyroom_users', esc_html__( "Error in deleting multiple Skyroom users", 'drplus' ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		return false;
	}

	/**
	 * Get rooms of Skyroom
	 *
	 * @return array|WP_Error Key is room status and value is room id
	 */
	public static function get_rooms() {
		$body = self::send_request( 'getRooms' );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_get_rooms_{$body->error_code}" : 'skyroom_get_rooms';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in getting rooms", 'drplus' );
			Logger::error( "Skyroom - Get rooms - " . $error_text, [
				'code'		=> $error_code,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
			] );
		}
		if( !empty( $body->result ) ) {
			return wp_list_pluck( $body->result, 'status', 'id' );
		} else {
			return [];
		}
	}

	/**
	 * Create or update a room in Skyroom from WC order
	 *
	 * @param int|object $order
	 * @param string $action Set action
	 * @param boolean $add_users_to_room
	 * @return int|WP_Error ID of the room
	 */
	public static function update_or_create_room( $order, $action = '', $add_users_to_room = true ) {
		$order = self::get_order( $order );
		if( is_wp_error( $order ) ) {
			return $order;
		}
		$booking_data = self::get_booking_data( $order );
		if( is_wp_error( $booking_data ) ) {
			return $booking_data;
		}
		
		$options = Options::get_options( [
			'skyroom-op-login-first'	=> true,
			'skyroom-room-title'		=> esc_html__( 'Consultation with {specialist_name}', 'drplus' ),
		] );

		$title = $options['skyroom-room-title'];
		$title = str_replace( [
			'{specialist_name}',
		], [
			$booking_data['specialist_name'],
		], $title );
		$title = substr( $title, 0, 128 );

		$duration = self::get_duration( $booking_data['book_id'] );
		if( is_wp_error( $duration ) ) {
			return $duration;
		}

		$params = [
			'title'				=> $title,
			'guest_login'		=> false,
			'op_login_first'	=> parent::to_bool( $options['skyroom-op-login-first'] ),
			'max_users'			=> 4,
			'session_duration'	=> $duration,
			'status'			=> 1,
		];

		if( !$action ) {
			$room_id = $order->get_meta( '_skyroom_room_id' );
			if( empty( $room_id ) ) {
				$action = 'createRoom';
			} else {
				$action = 'updateRoom';
			}
		}

		// Check room exists in Skyroom
		if( $action == 'updateRoom' ) {
			$rooms = self::get_rooms();
			if( is_wp_error( $rooms ) ) {
				return $rooms;
			}
			if( empty( $rooms[$room_id] ) ) {
				$action = 'createRoom';
			}
		}

		// Modify params for create or update
		if( $action == 'createRoom' ) {
			$params['name'] = wp_generate_uuid4();
		} else {
			if( empty( $room_id ) ) {
				$room_id = $order->get_meta( '_skyroom_room_id' );
			}
			$params['room_id'] = absint( $room_id );
		}

		$body = self::send_request( $action, $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_update_or_create_room_{$body->error_code}" : 'skyroom_update_or_create_room';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in creating Skyroom room", 'drplus' );
			Logger::error( "Skyroom - Update or create room - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			if( $action != 'updateRoom' ) {
				$room_id = $body->result;
				$order->update_meta_data( '_skyroom_room_id', $room_id );
				$order->save();
			}
			if( $add_users_to_room ) {
				$options = Options::get_options( [
					'skyroom-patient-role'	=> 'user'
				] );
				Skyroom::add_room_users( [
					[
						'user'	=> UtilsSpecialists::get_user_id_by_specialist_id( $booking_data['specialist_id'] ),
						'role'	=> 'op',
					],
					[
						'user'	=> $order->get_customer_id(),
						'role'	=> $options['skyroom-patient-role'],
					]
				], $room_id, $order );
			}
			return $room_id;
		}

		Logger::error( "Skyroom - Error in creating Skyroom room", [
			'params'	=> $params,
		] );
		return new \WP_Error( 'skyroom_update_or_create_room', esc_html__( "Error in creating Skyroom room", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Get users of a room
	 *
	 * @param integer $room_id
	 * @param mixed $order
	 * @return array|WP_Error Return room users : Key is skyroom user id and value is access type. 1: user | 2: operator | 3: admin
	 */
	public static function get_room_users( $room_id = 0, $order = null ) {
		$room_id = self::get_room_id( $room_id, $order );
		if( empty( $room_id ) || is_wp_error( $room_id ) ) {
			return $room_id;
		}

		$params = [
			'room_id'	=> $room_id,
		];

		$body = self::send_request( 'getRoomUsers', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_get_room_users_{$body->error_code}" : 'skyroom_get_room_users';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in getting room users", 'drplus' );
			Logger::error( "Skyroom - Error in getting room users - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			return wp_list_pluck( $body->result, 'access', 'user_id' );
		} else {
			return [];
		}
	}

	/**
	 * Add multiple users to a room by room id or WC order. When using order if the room was not created it will create the room.
	 * If each user hasn't the Skyroom user's id it will create.
	 *
	 * @param array $users [
	 * 	'user'	=> mixed WP User
	 * 	'role'	=> string Accepts: 'user' | 'op' | 'admin'
	 * ]
	 * @param integer $room_id
	 * @param int|object $order
	 * @return bool|WP_Error
	 */
	public static function add_room_users( array $users, $room_id = 0, $order = null ) {
		$room_id = self::get_room_id( $room_id, $order );
		if( empty( $room_id ) || is_wp_error( $room_id ) ) {
			return $room_id;
		}

		// Convert users to skyroom users id
		$params_users = [];
		foreach( $users as $user ) {
			if( !empty( $user['user'] ) ) {
				$role = 1;
				if( $user['role'] == 'op' ) {
					$role = 3;
				} else if( $user['role'] == 'admin' ) {
					$role = 2;
				}
				$skyroom_user_id = self::get_user_skyroom_id( $user['user'] );
				if( !empty( $skyroom_user_id ) && !is_wp_error( $skyroom_user_id ) ) {
					$params_users[] = [
						'user_id'	=> $skyroom_user_id,
						'access'	=> $role,
					];
				}
			}
		}

		if( empty( $params_users ) ) {
			Logger::error( "Skyroom - No valid users provided" );
			return new \WP_Error( 'skyroom_add_room_users', esc_html__( "No valid users provided", 'drplus' ), [
				'status'	=> 400,
			] );
		}
			
		// Get room users and remove duplicate users from $params_users
		$current_room_users = self::get_room_users( $room_id );
		if( !is_wp_error( $current_room_users ) ) {
			foreach( $params_users as $index => $user ) {
				if( isset( $current_room_users[$user['user_id']] ) ) {
					unset( $params_users[$index] );
				}
			}
		} else {
			return $current_room_users;
		}

		$params = [
			'room_id'	=> $room_id,
			'users'		=> $params_users,
		];
		if( empty( $params['users'] ) ) {
			return true;
		}

		$body = self::send_request( 'addRoomUsers', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_add_room_users_{$body->error_code}" : 'skyroom_add_room_users';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in adding users to Skyroom room", 'drplus' );
			Logger::error( "Skyroom - add room user - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			return parent::to_bool( $body->result );
		}

		Logger::error( "Skyroom - Error in adding users to Skyroom room", [
			'params'	=> $params,
		] );
		return new \WP_Error( 'skyroom_add_room_users', esc_html__( "Error in adding users to Skyroom room", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Delete room 
	 *
	 * @param integer $room_id
	 * @param mixed $order
	 * @return bool|WP_Error
	 */
	public static function delete_room( $room_id = 0, $order = null ) {
		$room_id = self::get_room_id( $room_id, $order, false );
		if( empty( $room_id ) || is_wp_error( $room_id ) ) {
			return $room_id;
		}

		$params = [
			'room_id'	=> $room_id,
		];

		$body = self::send_request( 'deleteRoom', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_delete_room_{$body->error_code}" : 'skyroom_delete_room';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in deleting room", 'drplus' );
			Logger::error( "Skyroom - delete room - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			return Utils::to_bool( $body->result );
		}

		Logger::error( "Skyroom - Error in deleting room", [
			'params'	=> $params,
		] );
		return new \WP_Error( 'skyroom_delete_room', esc_html__( "Error in deleting room", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}

	/**
	 * Get room link for user
	 *
	 * @param mixed $order
	 * @param mixed $user
	 * @return string|WP_Error
	 */
	public static function get_room_link( $order, $user = null ) {
		$order = self::get_order( $order );
		if( is_wp_error( $order ) ) {
			return $order;
		}
		$user = parent::get_user_object( $user );
		if( empty( $user ) ) {
			Logger::error( 'Skyroom - Invalid user' );
			return new \WP_Error( 'skyroom_get_room_link', esc_html__( "Invalid user", 'drplus' ), [
				'status'	=> 400,
			] );
		}
		$room_id = self::get_room_id( 0, $order );
		if( empty( $room_id ) || is_wp_error( $room_id ) ) {
			return $room_id;
		}
		$user_skyroom_id = self::get_user_skyroom_id( $user );

		// Check current user is one of room's users
		$room_users = self::get_room_users( $room_id );
		if( is_wp_error( $room_users ) ) {
			return $room_users;
		}
		if( empty( $room_users[$user_skyroom_id] ) ) {
			$error_data = [
				'user_id'	=> $user_skyroom_id,
				'room_id'	=> $room_id,
			];
			Logger::error( 'Skyroom - User is not a member of this room', [
				'params'	=> $error_data,
			] );
			return new \WP_Error( 'skyroom_get_room_link', esc_html__( "User is not a member of this room", 'drplus' ), [
				'status'	=> 403,
				'data'		=> $error_data,
			] );
		}

		$booking_data = self::get_booking_data( $order );
		$duration = self::get_duration( $booking_data['book_id'] );

		$params = [
			'room_id'	=> $room_id,
			'user_id'	=> $user_skyroom_id,
			'access'	=> $room_users[$user_skyroom_id],
			'nickname'	=> $user->display_name,
			'language'	=> is_rtl() ? 'fa' : 'en',
			'ttl'		=> $duration * 60,
		];

		$body = self::send_request( 'createLoginUrl', $params );
		if( is_wp_error( $body ) ) return $body;

		if( isset( $body->ok ) && !$body->ok ) {
			$error_code = !empty( $body->error_code ) ? "skyroom_get_room_link_{$body->error_code}" : 'skyroom_get_room_link';
			$error_text = !empty( $body->error_code ) ? $body->error_message : __( "Error in getting room link", 'drplus' );
			Logger::error( "Skyroom - Get room link - " . $error_text, [
				'code'		=> $error_code,
				'params'	=> $params,
			] );
			return new \WP_Error( esc_html( $error_code ), esc_html( $error_text ), [
				'status'	=> 500,
				'data'		=> [
					'params'	=> $params,
				],
			] );
		}
		if( !empty( $body->result ) ) {
			return $body->result;
		}

		Logger::error( "Skyroom - Error in getting room link", [
			'params'	=> $params,
		] );
		return new \WP_Error( 'skyroom_get_room_link', esc_html__( "Error in getting room link", 'drplus' ), [
			'status'	=> 500,
			'data'		=> [
				'params'	=> $params,
			],
		] );
	}
}