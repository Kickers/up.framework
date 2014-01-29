<?php
namespace up\cache;

use up\cache;

class memcache implements interfaces
{
	/**
	 * @var \Memcache
	 */
	private $cache;

	private $prefix = __CLASS__;


	public function __construct( \Memcache $cache )
	{
		$this->cache = $cache;
	}
	
	public function get( $key, $flag = null )
	{
		$key = $this->getKey( $key );

		return $this->cache->get( $key, $flag );
	}

	public function set( $key, $value, $expire = cache::TTL_FOREVER, $flag = null )
	{
		$key = $this->getKey( $key );

		return $this->cache->set( $key, $value, $expire, $flag );
	}

	public function add( $key, $value, $expire = cache::TTL_FOREVER, $flag = null )
	{
		$key = $this->getKey( $key );

		return $this->cache->add( $key, $value, $expire, $flag );
	}

	public function del( $key, $timeout = 0 )
	{
		$key = $this->getKey( $key );

		return $this->cache->delete( $key, $timeout );
	}

	public function inc( $key, $value = 1 )
	{
		$key = $this->getKey( $key );

		return $this->cache->increment( $key, $value );
	}

	public function dec( $key, $value = 1 )
	{
		$key = $this->getKey( $key );

		return $this->cache->decrement( $key, $value );
	}

	public function replace( $key, $value, $expire = cache::TTL_FOREVER, $flag = null )
	{
		$key = $this->getKey( $key );

		return $this->cache->replace( $key, $value, $expire, $flag );
	}

	public function prefix( $prefix = null )
	{
		if ( $prefix === null )
			return $this->prefix;

		$this->prefix = $prefix;
	}

	private function getKey( $key )
	{
		if ( $this->prefix )
			return $this->prefix . $key;

		return $key;
	}
}