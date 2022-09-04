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

use App\AppHelper;
use App\Models\Project;
use App\Models\User;
use App\Models\UserGroup;
use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteList;


class ProjectRoute extends Route
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteList $route_container)
	{
		$route_container->addRoute([
			"url" => "/add/",
			"name" => "site:project:add",
			"method" => [$this, "actionAdd"],
		]);
		$route_container->addRoute([
			"url" => "/delete/",
			"name" => "site:project:delete",
			"method" => [$this, "actionDelete"],
		]);
		$route_container->addRoute([
			"url" => "/settings/",
			"name" => "site:project:settings",
			"method" => [$this, "actionSettings"],
		]);
	}
	
	
	
	/**
	 * Check auth
	 */
	function isAdmin()
	{
		$auth = app(\TinyPHP\Auth::class);
		return $auth->isAdmin();
	}
	
	
	
	/**
	 * Post project add
	 */
	function postProjectAdd($form)
	{
		$type = trim($this->container->post("type"));
		$project_name = trim($this->container->post("project_name"));
		
		/* Remove slash */
		$project_name = preg_replace("/\/+$/", "", $project_name);
		$project_name = preg_replace("/^\/+/", "", $project_name);
		$project_name = preg_replace("/\/+/", "/", $project_name);
		
		$form["data"]["type"] = $type;
		$form["data"]["project_name"] = $project_name;
		
		/* Check type */
		if ($type == "")
		{
			$form["error_fields"]["type"][] = "Field 'type' must not be empty";
			$form["error_code"] = -1;
		}
		else
		{
			if (!in_array($type, ["hg", "git"]))
			{
				$form["error_fields"]["type"][] = "Field 'type' must be 'hg' or 'git'";
				$form["error_code"] = -1;
			}
		}
		
		/* Check project name */
		if ($project_name == "")
		{
			$form["error_fields"]["project_name"][] = "Field 'project_name' must not be empty";
			$form["error_code"] = -1;
		}
		else
		{
			if (preg_match('/[^a-z_\-0-9\/]/i', $project_name))
			{
				$form["error_fields"]["project_name"][] =
					"Field 'project_name' must contains only a-z, 0-9, _, -, /";
				$form["error_code"] = -1;
			}
			
			$project_name_arr = explode("/", $project_name);
			if (count($project_name_arr) > 3)
			{
				$form["error_fields"]["project_name"][] = "Count of '/' must be less than 2";
				$form["error_code"] = -1;
			}
		}
		
		if ($form["error_code"] != 0) return $form;
		
		/* Create project */
		try
		{
			$repo_path = Project::createProject($type, $project_name);
			if ($repo_path == "" || !is_dir($repo_path))
			{
				$form["error_code"] = -1;
				$form["result"][] = "Error create project folder";
			}
		}
		catch (\Exception $e)
		{
			$repo_path = "";
			$form["error_code"] = -1;
			$form["result"][] = $e->getMessage();
		}
		
		/* If is ok */
		if ($form["error_code"] == 0)
		{
			$form["error_code"] = 1;
			$form["result"][] = "Ok";
		}
		
		return $form;
	}
	
	
	
	/**
	 * Project add
	 */
	function actionAdd()
	{
		$this->container->add_breadcrumb(
			static::url("site:project:add"),
			"Project add"
		);
		
		/* No permission */
		if (!$this->isAdmin())
		{
			$this->render("@app/nopermission.twig");
		}
		
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
		$this->render("@app/projects/add.twig");
	}
	
	
	
	/**
	 * Project delete
	 */
	function actionDelete()
	{
		$this->container->add_breadcrumb(
			static::url("site:project:delete"),
			"Project delete"
		);
		
		/* No permission */
		if (!$this->isAdmin())
		{
			$this->render("@app/nopermission.twig");
		}
		
		$project_id = $this->container->get("id");
		
		/* Find project */
		$project = Project::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			$this->render("@app/projects/delete.twig");
			return;
		}
		
		/* Is post ? */
		if ($this->container->isPost())
		{
			$this->setContext("is_post", true);
			
			Project::removeProject($project->id);
		}
		
		$this->setContext("project", $project->toArray());
		
		/* Render */
		$this->render("@app/projects/delete.twig");
	}
	
	
	
	/**
	 * Project settings
	 */
	function actionSettings()
	{
		$auth = app(\TinyPHP\Auth::class);
		
		$project_id = $this->container->get("id");
				
		$this->container->add_breadcrumb
		(
			static::url_get_add(
				static::url("site:project:settings"),
				[
					"id" => $project_id,
				]
			),
			"Settings"
		);
		
		/* No permission */
		if (!$this->isAdmin())
		{
			$this->render("@app/nopermission.twig");
		}
		
		$project = Project::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			$this->render("@app/projects/settings.twig");
			return;
		}
		
		$project_rename_error = "";
		$project_rename_name = $project->name;
		
		/* Is post ? */
		if ($this->container->isPost())
		{
			$users = $this->container->post("users", []);
			Project::saveUsers($project_id, $users);
			
			/* Rename project */
			$project_rename_name = $this->container->post("project_rename_name", "");
			if ($project->name != $project_rename_name)
			{
				try
				{
					$project = Project::renameProject(
						$project_id,
						$project_rename_name
					);
					$project_rename_name = $project->name;
				}
				catch (\Exception $e)
				{
					$project_rename_error = $e->getMessage();
				}
			}
			
			/* Check repo path */
			Project::checkRepoPath($project_id);
		}
		
		/* Read users */
		$users = Project::getUsers($project_id);
		
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
		
		/* Get user and group list */
		$users_list = User::selectQuery()
			->where("is_deleted", 0)
			->where("banned", 0)
			->all()
		;
		$users_list = array_map(function($item){
			return $item["login"];
		}, $users_list);
		
		/* Get user and group list */
		$groups_list = UserGroup::selectQuery()
			->where("is_deleted", 0)
			->all()
		;
		$groups_list = array_map(function($item){
			return "@".$item["name"];
		}, $groups_list);
		
		/* Merge and sort */
		$users_list = array_merge($users_list, $groups_list);
		sort($users_list);
		
		/* Set context */
		$this->setContext("project", $project->toArray());
		$this->setContext("project_id", $project_id);
		$this->setContext("project_type", $project->type);
		$this->setContext("project_name", $project->name);
		$this->setContext("project_rename_name", $project_rename_name);
		$this->setContext("project_rename_error", $project_rename_error);
		$this->setContext("users", $users);
		$this->setContext("users_list", $users_list);
		
		/* Render */
		$this->render("@app/projects/settings.twig");
	}
	
}