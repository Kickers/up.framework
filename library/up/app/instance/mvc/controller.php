<?php
namespace up\app\instance\mvc;

use up\request;

abstract class controller
{
	protected $layout = 'default';

	protected $renderView   = true;
	protected $renderLayout = true;

	protected $controller;
	protected $action;

	private $request;

	/**
	 * @var \up\app\instance\mvc\view
	 */
	public $view;

	private $params = array();


	public function __construct( $controller, $action, $params )
	{
		$this->view = new view();
		$this->request = request::instance();

		$this->controller = $controller;
		$this->action     = $action;
		$this->params     = $params;
	}

	private function onPost( request $request, view $view ){}
	private function onGet( request $request, view $view ){}
	private function onPut( request $request, view $view ){}
	private function onDelete( request $request, view $view ){}
	public function onError( \Exception $Exception, view $view ){}

	public function request()
	{
		return $this->request;
	}

	public function getVars()
	{
		$vars = array();

		foreach( $this as $var => $val ) {
			$vars[$var] = $val;
		}

		unset( $vars['___variables'] );

		return $vars;
	}

	public function getView()
	{
		return $this->view;
	}

	public function preInit(){}
	public function init(){}
	public function after(){}
	public function before(){}

	protected function getParam( $name = null )
	{
		if ( $name === null )
			return $this->params;

		if ( isset( $this->params[$name] ) )
			return $this->params[$name];
	}
}