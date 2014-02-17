<?php
/*$query = '/controller.action.method/id/1/';
$pattern = '#\/(\w+)\.(\w+)[\.*](\w*)\/*(.*)#i';

preg_match( $pattern, $query, $out );

var_dump($out);

die;*/

require_once '../library/up/route/route.php';

$route = new \up\route();

$callback = function( array $result ) {

	//return false; //to stop parse other patterns
	//return true|null; //to parse other patterns
	//return string; //to parse other patterns by new uri
};

/*$route->add( '/{controller:string:index}/{action:string:show}/', $callback );
$route->add( '/{controller:[string]:index}/{action:string:show}/', $callback );
$route->add( '/blog/{action:string}/', $callback, array( 'controller' => 'blog' ) );*/

/*$route->add( '/{language}[/]', function( array $result ){

}, array(
	'language' => array( 'ru', 'en' )
));*/

/path/file.php =>

->add(
	  'namespace'
	, array(
		'/home/*.yaml' => '/home/cache/$1.php'
	)
	, function( $file ) {

	}
);

$route->add(
	  '/{controller}.{action}[.]{method}[/]{*} : last' // '#\/([\d\w].*)\.([\d\w].*)\.#'
	, $callback
	, array (
	    'method'     => '\w+'
	  , '*'          => function( array $results ) {
		  //return array for merge with current
	  }
	)
);

$route->parse();