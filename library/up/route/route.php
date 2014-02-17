<?php
namespace up;

class route
{
	const OPTIONAL = '~';

	private $patterns = array();
	private $uri;

	private $patternMaps = array(
		  'string' => '\w'
		, 'int'    => '\d'
	);

	private $defaultPattern = '\w+';

	public function __construct( $uri = null )
	{
		if ( $uri === null )
			$uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';

		$this->uri( $uri );
	}

	public function uri( $uri = null )
	{
		if ( $uri === null )
			return $this->uri;

		$this->uri = $uri;
	}

	public function add( $pattern/*, $callback, array $params = array()*/ )
	{
		$this->patterns[$pattern] = array(
			  'callback' => $callback
			, 'params'   => $params
		);
	}

	public function parse()
	{
		foreach( $this->patterns as $pattern => $settings ) {
			$preparePattern = $this->prepare( $pattern, $settings['params'] );

			//var_dump($preparePattern);
		}
	}

	private function prepare( $parsePattern, $params )
	{
		$replace = $this->defaultPattern;
		$maps    = $this->patternMaps;
		$map     = array();
		$types   = array(
			  'required' => '([%%]+)'
			, 'optional' => '[%%]*'
		);
		$inc     = 0;

		$callback = function( $matches ) use( &$type, $types, $replace, $params, $maps, &$map, &$inc ){
			$search  = $matches[1];

			if ( isset( $params[$search] ) ) {
				$userType = $params[$search];

				switch ( gettype( $userType ) ) {
					case 'array':
						$replace = implode( '|', $userType );
						break;

					default:
						break;
				}
			}

			if ( $type == 'optional' )
				$replace = $search;

			$replace = str_replace( '%%', $replace, $types[$type] );

			$inc++;
			$from = '|||' . $inc . '|||';

			$map[$from] = $replace;

			return $from;
		};

		$type = 'required';
		$parsePattern = preg_replace_callback( '/\{([\w\*].*?)\}/i', $callback, $parsePattern);

		$type = 'optional';
		$parsePattern = preg_replace_callback( '/\[(.*?)\]/i', $callback, $parsePattern);

		$parsePattern = str_replace( array_keys( $map ), array_values( $map ), $parsePattern );

		var_dump($parsePattern);
		die;
	}
}