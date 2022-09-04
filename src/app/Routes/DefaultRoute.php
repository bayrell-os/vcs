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

namespace App\Routes;

use App\Models\Project;
use App\Models\ProjectUser;
use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteList;


class DefaultRoute extends Route
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteList $route_container)
	{
		$route_container->addRoute([
			"url" => "/",
			"name" => "site:index",
			"method" => [$this, "actionIndex"],
		]);
	}
	
	
	
	/**
	 * Action index
	 */
	function actionIndex()
	{
		$auth = app(\TinyPHP\Auth::class);
		
		/* Get projects list */
		$projects = [];
		
		if (!$auth->isAdmin())
		{
			$groups = array_map(
				function ($item){ return "@" . $item; },
				$auth->getGroups()
			);
			$names = [ $auth->getLogin(), "@all", ...$groups ];
			
			$projects = ProjectUser::selectQuery()
				->fields(
					"project.type",
					"project.name",
					"t.value"
				)
				->where("t.name", "=", $names)
				->where("project.is_deleted", "=", 0)
				->innerJoin(
					Project::getTableName(),
					"project",
					"project.id = t.project_id"
				)
				->orderBy("project.name", "asc")
				->all(true)
			;
			
			/* Get full name */
			$projects = array_map(
				function($item)
				{
					$item["full_name"] = $item["type"] . "/" . $item["name"];
					return $item;
				},
				$projects
			);
			
			/* Remove duplicates */
			$projects = array_filter(
				$projects,
				function($item, $index) use ($projects)
				{
					$item_name = $item["name"];
					$found_index = array_search(
						$item["type"] . "/" . $item["name"],
						array_column($projects, 'full_name')
					);
					return $index <= $found_index;
				},
				ARRAY_FILTER_USE_BOTH
			);
		}
		else
		{
			$projects = Project::getProjectsList();
		}
		
		/* Set projects context */
		$this->setContext("projects", $projects);
		
		/* Set result */
		$this->render("@app/projects/index.twig");
	}
	
}