<?php

/*!
 * Bayrell Cloud OS
 * 
 * MIT License
 *
 * (c) Copyright 2020 - 2022 "Ildar Bikmamatov" <support@bayrell.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace App\Admin\Routes;

use App\Models\Project;
use App\Routes\ProjectRoute as AppProjectRoute;
use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteList;


class ProjectRoute extends AppProjectRoute
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteList $routes)
	{
		$routes->addRoute([
			"url" => "/projects/",
			"name" => "site:project:index",
			"method" => [$this, "actionIndex"],
		]);
		$routes->addRoute([
			"url" => "/projects/add/",
			"name" => "site:project:add",
			"method" => [$this, "actionAdd"],
		]);
		$routes->addRoute([
			"url" => "/projects/delete/",
			"name" => "site:project:delete",
			"method" => [$this, "actionDelete"],
		]);
		$routes->addRoute([
			"url" => "/projects/settings/",
			"name" => "site:project:settings",
			"method" => [$this, "actionSettings"],
		]);
	}
	
	
	
	/**
	 * Check auth
	 */
	function isAdmin()
	{
		return true;
	}
	
	
	
	/**
	 * Project index
	 */
	function actionIndex()
	{
		$this->container->add_breadcrumb(
			static::url("site:project:index"),
			"Projects"
		);
		
		/* Get projects list */
		$projects = Project::getProjectsList();
		
		/* Set projects context */
		$this->setContext("projects", $projects);
		
		/* Set result */
		$this->render("@app/projects/index.twig");
	}
	
}