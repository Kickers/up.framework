<?php
namespace up\exec;

class process
{
	public static function kill( $pid, $signal = null )
	{
		$command = array( 'kill' );

		if ( !is_null( $signal ) ) $command[] = '-' . (int) $signal;
		$command[] = (int) $pid;

		$result = self::run( $command );

		if ( count( $result ) == 1 && $result[0] == '' ) return true;

		if ( is_array( $result ) ) $result = implode( PHP_EOL, $result );

		throw new exception( $result );
	}

	public static function runBackground( $command, $args = null )
	{
		$command = self::escapeCmd( $command );
		$args    = self::escapeArg( $args );

		$command = array( $command, $args, '> /dev/null 2>&1 & echo $!' );

		$result = self::run( $command );

		return $result;
	}

	public static function escapeCmd( $command )
	{
		return escapeshellcmd( (string) $command );
	}

	public static function escapeArg( $arg )
	{
		return escapeshellarg( (string) $arg );
	}

	private static function run( $command )
	{
		if ( is_array( $command ) ) $command = implode( ' ', $command );

		return exec( $command . ' 2>&1', $out );
	}
}