<?php

class Validation
{
	static public function check_empty($value, $desc) {
		if ( empty( $value ) ) throw new Exception("{$desc}必填", -1);
	}

	static public function check_url($value, $desc) {
		self::check_empty($value, $desc);
		return 'http://' . str_replace('http://', '', $value);
	}
}