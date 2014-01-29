<?php
namespace up\json;

use up\object;

/**
 * Stand-in json function
 * Up\Json\Stand
 *
 * @package    $Json\Stand
 * @relation   $Object
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 */

class stand
{
	private static $_use;

	/**
	 * Encode data to json string
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function encode( $value )
	{
		self::_clearClass();

		return self::_encode( $value );
	}

	/**
	 * Decode json string
	 *
	 * @param string $value
	 * @return mixed
	 */
	public static function decode( $value )
	{
		self::_clearClass();

		return self::_decode( $value );
	}


	private static function _encode( $value )
	{
		if( $value === null )
			return 'null';
		if( $value === false )
			return 'false';
		if( $value === true )
			return 'true';

		if( is_scalar( $value ) ) {
			if( is_float( $value ) )
				return floatval( str_replace( ",", ".", strval( $value ) ) );

			if( is_string( $value ) ) {
				static $jsonReplaces = array ( array ( "\\", "/", "\n", "\t", "\r", "\b", "\f", '"' ), array ( '\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"' ) );

				return '"' . str_replace( $jsonReplaces[0], $jsonReplaces[1], $value ) . '"';
			}
			else return $value;
		}

		$isList = true;
		for( $i = 0, reset( $value ); $i < count( $value ); $i++, next( $value ) ) {
			if( key( $value ) !== $i ) {
				$isList = false;
				break;
			}
		}

		$result = array ();

		if( $isList ) {
			foreach( $value as $v )
				$result[] = self::_encode( $v );

			return '[' . implode( ',', $result ) . ']';
		}
		else {
			foreach( $value as $k => $v )
				$result[] = self::_encode( $k ) . ':' . self::_encode( $v );

			return '{' . implode( ',', $result ) . '}';
		}
	}

	private static function _decode( $value )
	{
		$str = self::_clear( $value );

		switch( strtolower( $str ) ) {
			case 'true':
				return true;
				break;
			case 'false':
				return false;
				break;
			case 'null':
				return null;
				break;

			default:
				$m = array ();

				if( is_numeric( $str ) ) {
					return ( (float) $str == (integer) $str ) ? (integer) $str : (float) $str;
				}
				elseif( preg_match( '/^("|\').*(\1)$/s', $str, $m ) && $m[1] == $m[2] ) {
					$delim       = substr( $str, 0, 1 );
					$chrs        = substr( $str, 1, -1 );
					$utf8        = '';
					$strlen_chrs = strlen( $chrs );

					for( $c = 0; $c < $strlen_chrs; ++$c ) {
						$substr_chrs_c_2 = substr( $chrs, $c, 2 );
						$ord_chrs_c      = ord( $chrs{$c} );

						switch( true ) {
							case $substr_chrs_c_2 == '\b':
								$utf8 .= chr( 0x08 );
								++$c;
								break;
							case $substr_chrs_c_2 == '\t':
								$utf8 .= chr( 0x09 );
								++$c;
								break;
							case $substr_chrs_c_2 == '\n':
								$utf8 .= chr( 0x0A );
								++$c;
								break;
							case $substr_chrs_c_2 == '\f':
								$utf8 .= chr( 0x0C );
								++$c;
								break;
							case $substr_chrs_c_2 == '\r':
								$utf8 .= chr( 0x0D );
								++$c;
								break;

							case $substr_chrs_c_2 == '\\"':
							case $substr_chrs_c_2 == '\\\'':
							case $substr_chrs_c_2 == '\\\\':
							case $substr_chrs_c_2 == '\\/':
								if( ( $delim == '"' && $substr_chrs_c_2 != '\\\'' ) ||
								( $delim == "'" && $substr_chrs_c_2 != '\\"' )
								) {
									$utf8 .= $chrs{++$c};
								}
								break;

							case preg_match( '/\\\u[0-9A-F]{4}/i', substr( $chrs, $c, 6 ) ):
								$utf16 = chr( hexdec( substr( $chrs, ( $c + 2 ), 2 ) ) ) . chr( hexdec( substr( $chrs, ( $c + 4 ), 2 ) ) );
								$utf8 .= self::_utf162utf8( $utf16 );
								$c += 5;
								break;

							case ( $ord_chrs_c >= 0x20 ) && ( $ord_chrs_c <= 0x7F ):
								$utf8 .= $chrs{$c};
								break;

							case ( $ord_chrs_c & 0xE0 ) == 0xC0:
								$utf8 .= substr( $chrs, $c, 2 );
								++$c;
								break;

							case ( $ord_chrs_c & 0xF0 ) == 0xE0:
								$utf8 .= substr( $chrs, $c, 3 );
								$c += 2;
								break;

							case ( $ord_chrs_c & 0xF8 ) == 0xF0:
								$utf8 .= substr( $chrs, $c, 4 );
								$c += 3;
								break;

							case ( $ord_chrs_c & 0xFC ) == 0xF8:
								$utf8 .= substr( $chrs, $c, 5 );
								$c += 4;
								break;

							case ( $ord_chrs_c & 0xFE ) == 0xFC:
								$utf8 .= substr( $chrs, $c, 6 );
								$c += 5;
								break;
						}
					}

					return $utf8;
				}
				elseif( preg_match( '/^\[.*\]$/s', $str ) || preg_match( '/^\{.*\}$/s', $str ) ) {
					if( $str{0} == '[' ) {
						$stk = array ( 3 );
						$arr = array ();
					}
					else {
						if( self::$_use & 6 ) {
							$stk = array ( 4 );
							$obj = array ();
						}
						else {
							$stk = array ( 4 );
							$obj = new object();
						}
					}

					array_push( $stk, array ( 'what' => 1, 'where' => 0, 'delim' => false ) );

					$chrs = substr( $str, 1, -1 );
					$chrs = self::_clear( $chrs );

					if( $chrs == '' ) {
						if( reset( $stk ) == 3 )
							return $arr;
						else return $obj;
					}

					$strlen_chrs = strlen( $chrs );

					for( $c = 0; $c <= $strlen_chrs; ++$c ) {
						$top             = end( $stk );
						$substr_chrs_c_2 = substr( $chrs, $c, 2 );

						if( ( $c == $strlen_chrs ) || ( ( $chrs{$c} == ',' ) && ( $top['what'] == 1 ) ) ) {
							$slice = substr( $chrs, $top['where'], ( $c - $top['where'] ) );
							array_push( $stk, array ( 'what' => 1, 'where' => ( $c + 1 ), 'delim' => false ) );

							if( reset( $stk ) == 3 )
								array_push( $arr, self::decode( $slice ) );
							elseif( reset( $stk ) == 4 ) {
								$parts = array ();

								if( preg_match( '/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts ) ) {
									$key = self::decode( $parts[1] );
									$val = self::decode( $parts[2] );

									if( self::$_use & 6 )
										$obj[$key] = $val;
									else $obj->$key = $val;
								}
								elseif( preg_match( '/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts ) ) {
									$key = $parts[1];
									$val = self::decode( $parts[2] );

									if( self::$_use & 6 )
										$obj[$key] = $val;
									else $obj->$key = $val;
								}

							}

						}
						elseif( ( ( $chrs{$c} == '"' ) || ( $chrs{$c} == "'" ) ) && ( $top['what'] != 2 ) ) {
							array_push( $stk, array ( 'what' => 2, 'where' => $c, 'delim' => $chrs{$c} ) );
						}
						elseif( ( $chrs{$c} == $top['delim'] ) && ( $top['what'] == 2 ) && ( ( strlen( substr( $chrs, 0, $c ) ) - strlen( rtrim( substr( $chrs, 0, $c ), '\\' ) ) ) % 2 != 1 ) ) {
							array_pop( $stk );
						}
						elseif( ( $chrs{$c} == '[' ) && in_array( $top['what'], array ( 1, 3, 4 ) ) ) {
							array_push( $stk, array ( 'what' => 3, 'where' => $c, 'delim' => false ) );
						}
						elseif( ( $chrs{$c} == ']' ) && ( $top['what'] == 3 ) ) {
							array_pop( $stk );
						}
						elseif( ( $chrs{$c} == '{' ) && in_array( $top['what'], array ( 1, 3, 4 ) ) ) {
							array_push( $stk, array ( 'what' => 4, 'where' => $c, 'delim' => false ) );
						}
						elseif( ( $chrs{$c} == '}' ) && ( $top['what'] == 4 ) ) {
							array_pop( $stk );
						}
						elseif( ( $substr_chrs_c_2 == '/*' ) && in_array( $top['what'], array ( 1, 3, 4 ) ) ) {
							array_push( $stk, array ( 'what' => 5, 'where' => $c, 'delim' => false ) );
							$c++;
						}
						elseif( ( $substr_chrs_c_2 == '*/' ) && ( $top['what'] == 5 ) ) {
							array_pop( $stk );
							$c++;

							for( $i = $top['where']; $i <= $c; ++$i )
								$chrs = substr_replace( $chrs, ' ', $i, 1 );
						}
					}

					if( reset( $stk ) == 3 )
						return $arr;
					elseif( reset( $stk ) == 4 )
						return $obj;
				}
		}
	}

	private static function _utf162utf8( $utf16 )
	{
		return mb_convert_encoding( $utf16, 'UTF-8', 'UTF-16' );
	}

	private static function _clear( $str )
	{
		$str = preg_replace( array ( '#^\s*//(.+)$#m', '#^\s*/\*(.+)\*/#Us', '#/\*(.+)\*/\s*$#Us' ), '', $str );

		return trim( $str );
	}

	private static function _clearClass()
	{
		self::$_use = null;
	}
}