<?php
namespace DrPlus\Utils;

use DrPlus\Utils;

class Validators extends Utils {
	/**
	 * Check if ID Code is correct or not(Iran only)
	 *
	 * @param string $string
	 * @return boolean
	 */
	public static function id_code( $string ) {
		$string = parent::convert_chars( $string );
		if( !preg_match( '/^[0-9]{10}$/', $string ) ) {
			return false;
		}
		
		for( $i = 0; $i < 10; $i++ )
			if( preg_match( '/^'.$i.'{10}$/', $string ) ) {
				return false;
			}
		
		for( $i = 0, $sum = 0; $i < 9; $i++ )
			$sum += ( ( 10-$i ) * intval( substr( $string, $i, 1 ) ) );
			$ret = $sum % 11;
			$parity = intval( substr( $string, 9, 1 ) );
			if( ( $ret < 2 && $ret == $parity ) || ( $ret >= 2 && $ret == 11 - $parity ) ) {
				return true;
			}
		
		return false;
	}

	/**
	 * Check if a string is a valid phone number or not
	 *
	 * @param integer|string $string
	 * @return boolean
	 */
	public static function phone( $string ) {
		return self::to_bool( preg_match( '/^(09[0-9]{9})$/', Sanitizers::phone( $string ) ) );
	}

	public static function card_number($cardNumber) {
		// حذف فاصله‌ها و کاراکترهای غیر عددی
		$cardNumber = preg_replace('/\D/', '', $cardNumber);
	
		// بررسی طول کارت
		if (strlen($cardNumber) !== 16) {
			return false;
		}
	
		// الگوریتم لانه (Luhn)
		$sum = 0;
		for ($i = 0; $i < 16; $i++) {
			$digit = (int)$cardNumber[$i];
			if ($i % 2 === 0) {
				$digit *= 2;
				if ($digit > 9) {
					$digit -= 9;
				}
			}
			$sum += $digit;
		}
	
		return ($sum % 10) === 0;
	}

	public static function shaba_number( $shaba ) {
		$shaba = parent::convert_chars( $shaba );
		// تبدیل به حروف بزرگ و حذف فاصله‌ها
		$shaba = strtoupper(str_replace(' ', '', $shaba));

		// اگر فقط ۲۴ رقم عددی داده شده بود، IR را اضافه کن
		if (preg_match('/^\d{24}$/', $shaba)) {
			$shaba = 'IR' . $shaba;
		}
	
		// بررسی طول و اینکه با IR شروع شود
		if (!preg_match('/^IR\d{24}$/', $shaba)) {
			return false;
		}
	
		// جابجایی ۴ کاراکتر اول به انتهای رشته
		$rearranged = substr($shaba, 4) . substr($shaba, 0, 4);
	
		// تبدیل حروف به عدد
		$numericShaba = '';
		for ($i = 0; $i < strlen($rearranged); $i++) {
			$char = $rearranged[$i];
			if (ctype_alpha($char)) {
				$numericShaba .= strval(ord($char) - 55); // A=10, B=11, ..., Z=35
			} else {
				$numericShaba .= $char;
			}
		}
	
		// محاسبه mod 97 عدد بزرگ
		$remainder = $numericShaba;
		while (strlen($remainder) > 2) {
			$part = substr($remainder, 0, 9);
			$remainder = ((int)$part % 97) . substr($remainder, strlen($part));
		}
	
		return ((int)$remainder % 97) === 1;
	}	
}