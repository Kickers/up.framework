<?php
namespace up\cache;

use up\cache;

interface interfaces
{
	public function get( $key, $flag = null );

	public function set( $key, $value, $expire = cache::TTL_FOREVER, $flag = null );

	public function add( $key, $value, $expire = cache::TTL_FOREVER, $flag = null );

	public function del( $key, $timeout = 0 );

	public function inc( $key, $value = 1 );

	public function dec( $key, $value = 1 );

	public function replace( $key, $value, $expire = cache::TTL_FOREVER, $flag = null );

	public function prefix( $prefix = null );
}