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
		if ($this->isPost())
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
				if(preg_match('/[^a-z_\-0-9\/]/i', $project_name))
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
			
			/* Create project */
			if ($form["error_code"] == 0)
			{
				$repo_path = "";
				$cmd = "";
				$res = "";
				
				/* Create mercurial project */
				if ($type == "hg")
				{
					$repo_path = "/data/repo/hg/" . $project_name;
					@mkdir($repo_path, 0775, true);
					$cmd = "/var/www/html/bin/project.init.sh hg " . $project_name;
					$res = shell_exec($cmd);
				}
				
				/* Create git project */
				if ($type == "git")
				{
					$repo_path = "/data/repo/git/" . $project_name;
					@mkdir($repo_path, 0775, true);
					$cmd = "/var/www/html/bin/project.init.sh git " . $project_name;
					$res = shell_exec($cmd);
				}
				
				if ($repo_path == "" || !is_dir($repo_path))
				{
					$form["error_code"] = -1;
					$form["result"][] = "Error create project folder";
				}
			}
			
			/* If is ok */
			if ($form["error_code"] == 0)
			{
				$form["error_code"] = 1;
				$form["result"][] = "Ok";
			}
		}
		
		/* Set form context */
		$this->setContext("form", $form);
		
		/* Set result */
		$this->render("@app/add_project.twig");
	}
	
}