<?php
namespace up;

use up\json\stand;

/**
 * Json decode/encode
 * up\json
 *
 * @package    $json
 * @relation   \up\json\stand
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 */
class json
{
	/**
	 * Encode data to json string
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function encode( $value )
	{
		if( function_exists( 'json_encode' ) )
			return json_encode( $value );

		return stand::encode( $value );
	}

	/**
	 * Decode json string
	 *
	 * @param string $value
	 * @return mixed
	 */
	public static function decode( $value, $class = null )
	{
		if( function_exists( 'json_decode' ) )
			$result = json_decode( $value );
		else
			$result = stand::decode( $value );

		if( !is_null( $class ) && ( is_array( $result ) || is_object( $result ) ) )
			return new $class( $result );

		return $result;
	}
}