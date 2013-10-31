<?php
namespace Forkmodule\Backend;

use \Forkmodule\Forkcontroller;

/**
 * Backend Action class
 */
class Action extends Forkcontroller {
	/**
	 * Create method
	 */
	public function create() {
		$content = $this->app['twig']->render('backend.actions.index.php', array('moduleName' => $this->app['module.name'], 'action' => $this->name));
		file_put_contents($this->app['module.dir.backend'] . 'actions/'.$this->name.'.php', $content);

		$content = $this->app['twig']->render('backend.layout.templates.index.tpl', array('moduleName' => $this->app['module.name'], 'action' => $this->name));
		file_put_contents($this->app['module.dir.backend'] . 'layout/templates/'.$this->name.'.tpl', $content);
	}
}
