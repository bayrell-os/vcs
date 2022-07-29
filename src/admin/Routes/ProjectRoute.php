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
use TinyPHP\RouteContainer;


class ProjectRoute extends AppProjectRoute
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteContainer $route_container)
	{
		$route_container->addRoute([
			"url" => "/projects/",
			"name" => "site:project:index",
			"method" => [$this, "actionIndex"],
		]);
		
		$route_container->addRoute([
			"url" => "/projects/add/",
			"name" => "site:project:add",
			"method" => [$this, "actionAdd"],
		]);
		
		$route_container->addRoute([
			"url" => "/projects/settings/",
			"name" => "site:project:settings",
			"method" => [$this, "actionSettings"],
		]);
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
		$this->render("@app_admin/projects/index.twig");
	}
	
	 
	
	/**
	 * Project add
	 */
	function actionAdd()
	{
		$this->container->add_breadcrumb(
			static::url("site:project:index"),
			"Projects"
		);
		
		$this->container->add_breadcrumb(
			static::url("site:project:add"),
			"Project add"
		);
		
		$form = [
			"result" => [],
			"data" => [
				"type" => "",
				"project_name" => "",
			],
			"error_fields" => [
				"type" => [],
				"project_name" => [],
			],
			"error_code" => 0,
		];
		
		/* Is post ? */
		if ($this->container->isPost())
		{
			$form = $this->postProjectAdd($form);
		}
		
		/* Set context */
		$this->setContext("form", $form);
		
		/* Render */
		$this->render("@app_admin/projects/add.twig");
	}
	
	
	
	/**
	 * Project settings
	 */
	function actionSettings()
	{
		$this->container->add_breadcrumb(
			static::url("site:project:index"),
			"Projects"
		);
		
		$project_type = $this->container->get("type");
		$project_name = $this->container->get("name");
		
		/* Set context */
		$this->setContext("project_type", $project_type);
		$this->setContext("project_name", $project_name);
		
		$this->container->add_breadcrumb
		(
			static::url_get_add(
				static::url("site:project:settings"),
				[
					"type"=>$project_type,
					"name"=>$project_name,
				]
			),
			"Settings"
		);
		
		/* Is post ? */
		if ($this->container->isPost())
		{
			$users = $this->container->post("users", []);
			Project::saveUsers($project_type, $project_name, $users);
		}
		
		/* Read users */
		$users = Project::getUsers($project_type, $project_name);
		
		/* Sort users */
		usort(
			$users,
			function ($a, $b)
			{
				if ($a["value"] != $b["value"])
				{
					return ($a["value"] < $b["value"]) ? -1 : 1;
				}
				if ($a["name"] == $b["name"])
				{
					return 0;
				}
				return ($a["name"] < $b["name"]) ? -1 : 1;
			}
		);
		
		/* Set context */
		$this->setContext("users", $users);
		
		/* Render */
		$this->render("@app_admin/projects/settings.twig");
	}
	
}