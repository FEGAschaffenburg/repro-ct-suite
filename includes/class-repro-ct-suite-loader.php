<?php

/**

 * Register all actions and filters for the plugin.

 *

 * @package    Repro_CT_Suite

 * @subpackage Repro_CT_Suite/includes

 */



class Repro_CT_Suite_Loader {



	/**

	 * The array of actions registered with WordPress.

	 *

	 * @var array $actions

	 */

	protected $actions;



	/**

	 * The array of filters registered with WordPress.

	 *

	 * @var array $filters

	 */

	protected $filters;



	/**

	 * Initialize the collections used to maintain the actions and filters.

	 */

	public function __construct() {

		$this->actions = array();

		$this->filters = array();

	}



	/**

	 * Add a new action to the collection to be registered with WordPress.

	 *

	 * @param string $hook          The name of the WordPress action that is being registered.

	 * @param object|string $component A reference to the instance of the object on which the action is defined, or class name.

	 * @param string $callback      The name of the function definition on the $component.

	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.

	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.

	 */

	public function add_action( string $hook, $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {

		$this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );

	}



	/**

	 * Add a new filter to the collection to be registered with WordPress.

	 *

	 * @param string $hook          The name of the WordPress filter that is being registered.

	 * @param object|string $component A reference to the instance of the object on which the filter is defined, or class name.

	 * @param string $callback      The name of the function definition on the $component.

	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.

	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.

	 */

	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {

		$this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );

	}



	/**

	 * A utility function that is used to register the actions and hooks into a single collection.

	 *

	 * @param array  $hooks         The collection of hooks that is being registered.

	 * @param string $hook          The name of the WordPress filter that is being registered.

	 * @param object|string $component A reference to the instance of the object on which the filter is defined, or class name.

	 * @param string $callback      The name of the function definition on the $component.

	 * @param int    $priority      The priority at which the function should be fired.

	 * @param int    $accepted_args The number of arguments that should be passed to the $callback.

	 * @return void

	 */

	private function add( array &$hooks, string $hook, $component, string $callback, int $priority, int $accepted_args ): void {

		$hooks[] = array(

			'hook'          => $hook,

			'component'     => $component,

			'callback'      => $callback,

			'priority'      => $priority,

			'accepted_args' => $accepted_args,

		);

	}



	/**

	 * Register the filters and actions with WordPress.

	 */

	public function run(): void {

		foreach ( $this->filters as $hook ) {

			add_filter(

				$hook['hook'],

				array( $hook['component'], $hook['callback'] ),

				$hook['priority'],

				$hook['accepted_args']

			);

		}



		foreach ( $this->actions as $hook ) {

			add_action(

				$hook['hook'],

				array( $hook['component'], $hook['callback'] ),

				$hook['priority'],

				$hook['accepted_args']

			);

		}

	}

}








