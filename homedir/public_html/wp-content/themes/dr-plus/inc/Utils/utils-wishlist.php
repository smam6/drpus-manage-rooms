<?php
namespace DrPlus\Utils;

use DrPlus\Model\Wishlist as ModelWishlist;
use DrPlus\Utils;

class Wishlist extends Utils {
	public static function get_user_wishlist( $user_id = 0 ) {
		if( !is_user_logged_in() ) return [];
		$user_id = parent::get_user_id( $user_id );

		return ModelWishlist::query()->where( 'user_id', $user_id )->get();
	}

	public static function is_in_wishlist( $product_id, $user_id = 0 ) {
		if( !is_user_logged_in() ) return false;
		$user_id = parent::get_user_id( $user_id );

		$wishlist = ModelWishlist::query()->where( [
			'product_id'	=> $product_id,
			'user_id'		=> $user_id,
		] )->get();
		return !empty( $wishlist->toArray() );
	}

	public static function add_to_wishlist( $product_id, $user_id = 0 ) {
		$user_id = parent::get_user_id( $user_id );

		return ModelWishlist::firstOrCreate( [
			'product_id'	=> $product_id,
			'user_id'		=> $user_id,
		] );
	}

	public static function remove_from_wishlist_by_id( $id ) {
		$wishlist = ModelWishlist::query()->where( [
			'id'	=> $id,
		] );
		return $wishlist->delete();
	}

	public static function remove_from_wishlist( $product_id, $user_id = 0 ) {
		$user_id = self::get_user_id( $user_id );

		$wishlist = ModelWishlist::query()->where( [
			'product_id'	=> $product_id,
			'user_id'		=> $user_id,
		] );
		return $wishlist->delete();
	}
}