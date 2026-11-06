<?php
namespace DrPlus\Cache;

use DrPlus\Utils;

abstract class Cache {
	protected $name = ''; // Fill
	protected $lifetime = 10080; // Fill // Minute

	protected $model;
	protected $dir = '';
	protected $file = '';

	public $code = 0;
	public $type = '';
	public $object_id = 0;
	public $value = '';
	public $items = []; // Multiple results from DB

	/**
	 * Fill object properties from an associative array.
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function fill( array $attributes ) {
		foreach( $attributes as $key => $value ) {
			$this->__set( $key, $value );
		}
	}

	/**
	 * Cache constructor.
	 *
	 * @param string|int $code
	 * @param string $type
	 * @param integer $object_id
	 * @param boolean $get Set false when you want just create an instance of the class
	 */
	public function __construct( $code = null, $type = null, $object_id = null, $get = true ) {
		if( $code ) {
			$code = $this->hash_code( $code );
		}

		$this->fill( [
			'code'		=> $code,
			'type'		=> $type,
			'object_id'	=> $object_id,
		] );

		$this->set_dir();
		$this->set_file();
		$this->set_model();

		if( $get ) {
			if( $this->code || $this->type || $this->object_id ) {
				$this->get( $this->code, $this->type, $this->object_id );
			}
		}
	}

	/**
	 * Magic setter for properties.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
		if( $key == 'code' ) {
			$this->code = $this->hash_code( $value );
			$this->set_file();
		}
	}

	/**
	 * Convert string to crc32.
	 *
	 * @param mixed $code Convert anything to crc32 (array or object will convert to json)
	 * @return int crc32 code
	 */
	public function hash_code( $code ) : int {
		if( !is_numeric( $code ) ) {
			if( !is_scalar( $code ) ) {
				$code = json_encode( $code );
			} else {
				$code = Utils::convert_chars( $code );
			}
			return crc32( $code );
		}
		return (int) $code;
	}

	/**
	 * Set the directory path for cache files.
	 *
	 * @return void
	 */
	public function set_dir() {
		if( !$this->dir ) {
			$upload_dir = trailingslashit( Utils::get_upload_dir( 'base' ) );
			$section_name = Utils::convert_to_pascal_case( $this->name );
			$this->dir = $upload_dir . "drplus_cache/" . $section_name;
			wp_mkdir_p( $this->dir );
		}
	}

	/**
	 * Set the database handler for this cache section.
	 *
	 * @return void
	 */
	protected function set_model() {
		$section_name = Utils::convert_to_pascal_case( $this->name );
		$model_class = "DrPlus\Model\\" . $section_name . "Cache";
		$this->model = new $model_class;
	}

	/**
	 * Set the file path for the cache file.
	 *
	 * @return void
	 */
	protected function set_file() {
		if( !$this->dir ) {
			$this->set_dir();
		}
		$this->file = $this->code ? "{$this->dir}/{$this->code}" : '';
	}

	/**
	 * Load data from a cache file and fill object properties.
	 *
	 * @param string $code
	 * @return void
	 */
	public function set_data_from_file( $code = '' ) {
		if( $code ) {
			$this->code = $code;
		}
		$this->set_file();
		if( $this->file && file_exists( $this->file ) ) {
			$file_content = json_decode( file_get_contents( $this->file ), true );
			if( is_array($file_content) ) {
				$this->fill( [
					'type'		=> $file_content['type'] ?? '',
					'object_id'	=> $file_content['object_id'] ?? 0,
					'value'		=> $file_content['value'] ?? '',
				] );
			}
		}
	}

	/**
	 * Retrieve cached data from file or from DB.
	 *
	 * @param string|int|null $code
	 * @param string|null $type
	 * @param integer|null $object_id
	 * @return mixed
	 */
	public function get( $code = null, $type = null, $object_id = null ) {
		if( !empty( $this->value ) ) {
			return $this->value;
		}
		// Get from attributes
		if( $code === null ) {
			$code = $this->code;
		}
		if( $type === null ) {
			$type = $this->type;
		}
		if( $object_id === null ) {
			$object_id = $this->object_id;
		}
		// Update attributes
		$this->fill( [
			'code'		=> $code,
			'type'		=> $type,
			'object_id'	=> $object_id,
		] );

		if( $this->code ) {
			$this->set_file();
			if( file_exists( $this->file ) ) {
				$this->set_data_from_file();
				return $this->value;
			}
			$data = $this->model->first_by_code( $this->code, $this->object_id );
			if( $data ) {
				$this->fill( [
					'type'		=> $data->type,
					'object_id'	=> $data->object_id,
					'value'		=> $data->val
				] );
			}
			return $this->value;
		}
		if( $this->type ) {
			$data = $this->model->get_by_type( $this->type, $this->object_id );

			$this->fill( [
				'items'	=> $data
			] );
			return $this->items;
		}
		if( $this->object_id ) {
			$data = $this->model->get_by_object_id( $this->object_id );
			$this->fill( [
				'items'	=> $data
			] );
			return $this->items;
		}

		return $this->value;
	}

	/**
	 * Update the cache file or database with the current value.
	 *
	 * @param bool $save_to_db If true, save to DB instead of file.
	 * @return void
	 */
	public function update( bool $save_to_db = false ) {
		if( !$this->code || !$this->value ) return;

		$this->set_file();
		if( !$this->file ) return;

		if( !$save_to_db ) {
			$file_content = [
				'code'		=> $this->code,
				'type'		=> $this->type,
				'object_id'	=> $this->object_id,
				'value'		=> $this->value,
			];
			file_put_contents( $this->file, wp_json_encode( $file_content ), LOCK_EX );
		} else {
			$expire = Utils::convert_chars( date( 'Y-m-d H:i:s', strtotime( "+{$this->lifetime} minutes" ) ) );
			if( !$this->type ) {
				$this->type = '';
			}
			if( !$this->object_id ) {
				$this->object_id = 0;
			}
			$this->model::updateOrCreate( [
				'code'		=> $this->code,
				'type'		=> $this->type,
				'object_id'	=> $this->object_id,
			], [
				'code'		=> $this->code,
				'type'		=> $this->type,
				'object_id'	=> $this->object_id,
				'val'		=> $this->value,
				'expire'	=> $expire,
			] );
		}
	}

	/**
	 * Sync the cache: save to DB and delete the file.
	 *
	 * @return void
	 */
	public function sync() {
		$this->update( true );
		$this->delete_file();
	}

	/**
	 * Sync all cache files to the database and delete the files.
	 *
	 * @return void
	 */
	public function sync_all() {
		$files = glob( "{$this->dir}/*" );
		$this->model::query()->beginTransaction();
		foreach( $files as $file ) {
			$this->set_data_from_file( wp_basename( $file ) );
			$this->sync();
		}
		$this->model::query()->commit();
	}

	/**
	 * Delete the cache file.
	 *
	 * @return void
	 */
	public function delete_file( $file = null ) {
		$file = $file ?? $this->file;
		if( $file && file_exists( $file ) ) {
			wp_delete_file( $file );
		}
	}

	/**
	 * Delete the cache file and corresponding DB entry.
	 *
	 * @return void
	 */
	public function delete() {
		$this->delete_file();
		$where = [];
		if( $this->code ) {
			$where['code'] = $this->code;
		}
		if( $this->type ) {
			$where['type'] = $this->type;
		}
		if( $this->object_id ) {
			$where['object_id'] = $this->object_id;
		}
		global $wpdb;
		$wpdb->delete( $this->model->getTable(), $where );
	}

	/**
	 * Delete all cache files and clear the database table.
	 *
	 * @return void
	 */
	public function delete_all() {
		$files = glob( "{$this->dir}/*" );
		foreach( $files as $file ) {
			wp_delete_file( $file );
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM `" . $this->model->getTable() . "`" );
	}

	/**
	 * Delete cache file and/or database entries based on given arguments.
	 *
	 * @param array $args {
	 *     Optional. Arguments to determine which cache entries to delete.
	 *
	 *     @type string|int|array $code      Cache code to match. Default ''.
	 *     @type string|array     $type      Cache type to match. Default ''.
	 *     @type int|array        $object_id Object ID to match. Default 0.
	 *     @type bool             $expired   Whether to delete only expired entries. Default false.
	 *     @type string           $condition SQL condition to join where clauses ('AND' or 'OR'). Default 'OR'.
	 * }
	 * @return void
	 */
	public function delete_by( $args = [] ) {
		$args = Utils::check_default( $args, [
			'code'		=> '',
			'type'		=> '',
			'object_id'	=> 0,
			'expired'	=> false,
			'condition'	=> 'OR',
		], ['code', 'type', 'object_id'] );
		$args['condition'] = strtoupper( $args['condition'] );

		if( is_array( $args['code'] ) && count( $args['code'] ) === 1 ) {
			$args['code'] = array_values( $args['code'] )[0];
		}
		if( is_array( $args['type'] ) && count( $args['type'] ) === 1 ) {
			$args['type'] = array_values( $args['type'] )[0];
		}
		if( is_array( $args['object_id'] ) && count( $args['object_id'] ) === 1 ) {
			$args['object_id'] = array_values( $args['object_id'] )[0];
		}

		$where = [];
		$where_values = [];
		if( !empty( $args['code'] ) ) {
			if( is_array( $args['code'] ) ) {
				$args['code'] = array_map( fn( $code ) => $this->hash_code( $code ), $args['code'] );
				$where[] = '`code` IN (' . Utils::db_placeholder( $args['code'], '%d' ) . ')';
				$where_values = array_merge( $where_values, $args['code'] );
				foreach( $args['code'] as $code ) {
					$this->delete_file( "{$this->dir}/{$code}" );
				}
			} else {
				$where[] = '`code`=%d';
				$where_values[] = $this->hash_code( $args['code'] );
				$this->delete_file( "{$this->dir}/{$args['code']}" );
			}
		}
		if( !empty( $args['type'] ) ) {
			if( is_array( $args['type'] ) ) {
				$where[] = '`type` IN (' . Utils::db_placeholder( $args['type'], '%s' ) . ')';
				$where_values = array_merge( $where_values, $args['type'] );
			} else {
				$where[] = '`type`=%s';
				$where_values[] = $args['type'];
			}
		}
		if( !empty( $args['object_id'] ) ) {
			if( is_array( $args['object_id'] ) ) {
				$where[] = '`object_id` IN (' . Utils::db_placeholder( $args['object_id'], '%d' ) . ')';
				$where_values = array_merge( $where_values, $args['object_id'] );
			} else {
				$where[] = '`object_id`=%d';
				$where_values[] = $args['object_id'];
			}
		}
		if( $args['expired'] ) {
			$where[] = '`expire` <= %s';
			$where_values[] = date( 'Y-m-d H:i:s' );
		}

		if( !empty( $where ) ) {
			global $wpdb;
			$query = "DELETE FROM `" . $this->model->getTable() . "` WHERE " . implode( " {$args['condition']} ", $where );
			$wpdb->query( $wpdb->prepare( $query, $where_values ) );
		}
	}
}