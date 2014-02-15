<?php
namespace up\app\instance;

use up\app\api;
use up\app\instance\mvc\controller;
use up\events;
use up\request;

class mvc implements api
{
	public $controller;
	public $action;
	public $params = array();

	const EVENT_NAMESPACE = __CLASS__;

	const EVENT_INIT_ACTION = 'init.action';


	public function __construct( array $params )
	{
		if ( !isset( $params['controller'] ) ) throw new \Exception( 'please set controller name' );
		if ( !isset( $params['action'] ) ) throw new \Exception( 'please set action name' );

		$this->controller = $params['controller'];
		$this->action     = $params['action'];
		$this->params     = $params['params'];
	}

	public function run()
	{
		$Action = $this->createAction();

		try {
			$Action->preInit();
			$Action->init();

			$Action->before( $Action->getView() );

			$actionExist = false;

			if ( method_exists( $Action, 'action' ) ) {
				$Action->action( $Action->getView() );
				$actionExist = true;
			}

			$requestMethod = 'on' . $_SERVER['REQUEST_METHOD'];

			if ( method_exists( $Action, $requestMethod ) ) {
				if ( is_callable( array( $Action, $requestMethod ) ) )
					$Action->$requestMethod( $Action->request(), $Action->getView() );
				else {
					if ( $actionExist === false )
						throw new \Exception( 'private method ' . $requestMethod );
				}
			}

			$Action->after( $Action->getView() );
		} catch ( \Exception $e ) {
			$Action->onError( $e, $Action->getView() );
			$Action->after( $Action->getView() );
		}

		return $Action->getVars();
	}


	/**
	 * @return \up\app\instance\mvc\controller
	 */
	private function createAction()
	{
		if ( !events::hasEventListner( self::EVENT_NAMESPACE, self::EVENT_INIT_ACTION ) ) {
			events::bind(
				  self::EVENT_NAMESPACE
				, self::EVENT_INIT_ACTION
				, function( $controller, $action ) {
					return  '\\' . implode(
						'\\'
						, array(
						  'app'
						, $controller
						, 'action'
						, $action
						)
					);
				}
			);
		}

		$Action = events::notify(
			  self::EVENT_NAMESPACE
			, self::EVENT_INIT_ACTION
			, array( $this->controller, $this->action )
		);

		if ( !is_object( $Action ) )
			$Action = new $Action( $this->controller, $this->action, $this->params );

		$this->checkAction( $Action );

		return $Action;
	}

	private function checkAction( $Action )
	{
		if ( !$Action instanceof controller ) $this->exception( 'action must be instance of \\up\\app\\instance\\mvc\\controller' );
		//if ( !method_exists( $Action, 'action' ) ) $this->exception( 'method action does not exist' );
	}

	private function exception( $error, $code = 0 )
	{
		throw new \Exception( $error, $code );
	}
}