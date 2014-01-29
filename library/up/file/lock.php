<?php
namespace up\file;

/**
 * 
 * up\file\lock
 * 
 * @package    $file\lock
 * @copyright  Up framework (http://up.singleup.org). singleUp team (http://www.singleup.org)
 * @license    http://up.singleup.org/license/ (GNU)
 *
 */
class lock
{
	private static $lockFiles = array();
	
	/**
	 * File lock
	 *
	 * @param string $filename
	 * @param bool $waiting
	 * @return bool
	 */
	public static function begin( $filename, $waiting = false )
	{
		$operation = LOCK_EX;
		if ( $waiting === false ) $operation = LOCK_EX | LOCK_NB;
		
		$fp = fopen( $filename, 'w+' );

		if ( flock( $fp, $operation ) ) { 
			self::$lockFiles[$filename] = $fp;
		
			return true;
		}
		
		throw new exception( 'can not lock file ' . $filename );
	}
	
	/**
	 * Unlock file
	 *
	 * @param string $filename
	 * @return bool
	 */
	public static function end( $filename )
	{
		if ( !isset( self::$lockFiles[$filename] ) ) return false;
		
		$fp = self::$lockFiles[$filename];
		
		if ( !is_resource( $fp ) ) return false;
		
		flock( $fp, LOCK_UN );
		fclose( $fp );
		
		unset( self::$lockFiles[$filename] );
		
		return true;
	}
}