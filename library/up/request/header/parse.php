<?php
namespace up\header;

/**
 * cURL curl
 * 
 * \Up\Curl
 * 
 * @package    $Header\Parse
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class parse
{
	const EOL = "\n";

	public static $content;
	
	public static function parse( $headerStr )
	{
		if ( function_exists( '\\http_parse_headers' ) ) return \http_parse_headers( $headerStr );
		
		$result  = array();
		
		$headers = self::explodeHeaderStr( $headerStr );
		
		foreach ( $headers as $num => $header ) {
			if ( !$header ) continue;
			
			if ( strpos( $header, ':' ) === false )
			{
				if ( $num == 0 ) 
				{
					$result = self::parseStatus( $header, $result );
				}
				else
				{
					$result = self::addHeader( $header, $result );
				}
				
				continue;
			}
			
			$values = explode( ':', $header, 2 );
			
			$headerName  = self::getHeaderName( $values[0] );
			$headerValue = $values[1];
			
			if ( isset( $result[$headerName] ) )
			{
				if ( is_array( $result[$headerName] ) )
				{
					$result[$headerName][] = $headerValue;
				}
				else
				{
					$result[$headerName] = array( $result[$headerName], $headerValue );
				}
			} 
			else
			{
				$result[$headerName] = $headerValue;
			}
		}
		
		return self::trim( $result );
	}
	
	private static function addHeader( $header, array $result )
	{
		$prev = end( $result );
		
		if ( $prev === false ) return $result;
		
		$key = key($result);
		
		if ( is_array( $result[$key] ) )
		{
			end( $result[$key] );
			
			$result[$key][key($result[$key])] .= self::EOL . $header;
		}
		else 
		{
			$result[$key] = $result[$key] . self::EOL . $header;
		}
		
		return $result;
	}
	
	private static function parseStatus( $header, array $result )
	{
		if ( preg_match( '#([\D]{1,}) (.*?) HTTP/[\d]{1}\.[\d]{1}#sU', $header, $out ) )
		{
			$result['Request Method'] = $out[1];
			$result['Request Url']    = $out[2];
		}
		elseif ( preg_match( '#HTTP/[\d]{1}\.[\d]{1} ([\d]{1,}) (.*?)#sU', $header, $out ) )
		{
			$result['Response Code']   = (int) $out[1];
			$result['Response Status'] = $out[2];
		}
		
		return $result;
	}
	
	private static function getHeaderName( $name )
	{
		return implode( '-', 
					array_map( 'ucfirst', 
						explode( '-', 
							strtolower(
								$name  //I know :)
							)
						)
					)
				);
	}
	
	private static function trim( array $result )
	{
		foreach ( $result as $name => $value ) {
			if ( is_array( $value ) ) $result[$name] = self::trim( $value );
			elseif ( is_string( $value ) ) $result[$name] = trim( $value );
		}
		
		return $result;
	}
	
	private static function explodeHeaderStr( $headerStr )
	{
		if ( stripos( $headerStr, "\r\n\r\n" ) === false )
			return array();

		$headerStr = explode( "\r\n\r\n", $headerStr, 2 );
		self::$content = $headerStr[1];
		$headerStr = explode( "\n\n", $headerStr[0], 2 );
		
		return explode( self::EOL, $headerStr[0] );
	}
}