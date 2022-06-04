<?php

namespace App\Routes;

use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteContainer;


class ProjectRoute extends Route
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteContainer $route_container)
	{
		$route_container->addRoute([
			"url" => "/add/",
			"name" => "site:project:add",
			"method" => [$this, "actionAdd"],
		]);
	}
	
	
	
	/**
	 * Action index
	 */
	function actionAdd()
	{
		$this->add_breadcrumb(
			url("site:project:add"),
			"Project add"
		);
		
		/* Set result */
		$this->render("@app/add_project.twig");
	}
	
}