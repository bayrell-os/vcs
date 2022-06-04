<?php

namespace App\Routes;

use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteContainer;


class SettingsRoute extends Route
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteContainer $route_container)
	{
		$route_container->addRoute([
			"url" => "/settings/",
			"name" => "site:settings",
			"method" => [$this, "actionSettings"],
		]);
	}
	
	
	
	/**
	 * Action index
	 */
	function actionSettings()
	{
		$this->add_breadcrumb(
			url("site:settings"),
			"Settings"
		);
		
		/* Set result */
		$this->render("@app/settings.twig");
	}
	
}