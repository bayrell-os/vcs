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

namespace App\Models;

use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Models\UserRoles;
use TinyPHP\Utils;
use TinyORM\Model;


class Project extends Model
{
	/**
	 * Return table name
	 */
	static function getTableName()
	{
		return "projects";
	}
	
	
	
	/**
	 * Return list of primary keys
	 */
	static function pk()
	{
		return ["id"];
	}
	
	
	
	/**
	 * Returns tables fields
	 */
	static function fields()
	{
		return
		[
			"id" => [],
			"type" => [],
			"name" => [],
			"is_deleted" => [],
			"gmtime_created" => [],
			"gmtime_updated" => [],
		];
	}
	
	
	
	/**
	 * Return if auto increment
	 */
	static function isAutoIncrement()
	{
		return true;
	}
	
	
	
	/**
	 * Returns true if need to update timestamp
	 */
	static function updateTimestamp()
	{
		return true;
	}
	
	
	
	/**
	 * Save the model to the database.
	 */
	public function save($connection_name = "default")
	{
		return parent::save($connection_name);
	}
	
	
	
	/**
	 * Validate project name
	 */
	static function validateProjectName($project_name)
	{
		if ($project_name == "")
		{
			return false;
		}
		if (preg_match('/[^a-z_\-0-9\/]/i', $project_name))
		{
			return false;
		}
		$project_name_arr = explode("/", $project_name);
		if (count($project_name_arr) > 3)
		{
			return false;
		}
		return true;
	}
	
	
	
	/**
	 * Parse project full name
	 */
	static function parseProjectFullName($project_full_name)
	{
		$arr = explode($project_full_name, 1);
		
		$type = isset($arr[0]) ? $arr[0] : "";
		$project_name = isset($arr[1]) ? $arr[1] : "";
		
		return [$type, $project_name];
	}
	
	
	
	/**
	 * Get repo path
	 */
	static function getRepoPath($type, $project_name)
	{
		if (!static::validateProjectName($project_name)) return "";
		
		$repo_path = "";
		
		if ($type == "hg")
		{
			$repo_path = "/data/repo/hg/" . $project_name;
		}
		else if ($type == "git")
		{
			$repo_path = "/data/repo/git/" . $project_name;
		}
		
		$repo_path = preg_replace("/\/+$/", "", $repo_path);
		
		return $repo_path;
	}
	
	
	
	/**
	 * Check if folder is repo path
	 */
	static function getProjectTypeByRepoPath($repo_path)
	{
		$type = "";
		if (strpos($repo_path, "/data/repo/hg/") === 0)
		{
			$type = "hg";
		}
		else if (strpos($repo_path, "/data/repo/git/") === 0)
		{
			$type = "git";
		}
		return $type;
	}
	
	
	
	/**
	 * Check if folder is repo path
	 */
	static function isRepoPath($repo_path)
	{
		$type = static::getProjectTypeByRepoPath($repo_path);
		if ($type == "hg")
		{
			if (!is_dir($repo_path)) return false;
			if (!is_dir($repo_path . "/.hg")) return false;
			return true;
		}
		if ($type == "git")
		{
			if (!is_dir($repo_path)) return false;
			if (!file_exists($repo_path . "/config")) return false;
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * Get project name by path
	 */
	static function getProjectNameByRepoPath($repo_path)
	{
		$project_name = "";
		if (strpos($repo_path, "/data/repo/hg/") === 0)
		{
			$project_name = substr($repo_path, strlen("/data/repo/hg/"));
		}
		else if (strpos($repo_path, "/data/repo/git/") === 0)
		{
			$project_name = substr($repo_path, strlen("/data/repo/git/"));
		}
		if (!static::validateProjectName($project_name)) return "";
		return $project_name;
	}
	
	
	
	/**
	 * Scan projects in folder
	 */
	static function scanProjects($type, $folder, $level)
	{
		if ($level >= 3) return [];
		
		$projects = [];
		
		if (!file_exists($folder)) return [];
		if (!is_dir($folder)) return [];
		
		$res = scandir($folder, SCANDIR_SORT_ASCENDING);
		foreach ($res as $name)
		{
			if ($name == "." or $name == "..") continue;
			
			$repo_path = $folder . "/" . $name;
			
			if (static::isRepoPath($repo_path))
			{
				$project_name = static::getProjectNameByRepoPath($repo_path);
				$projects[] =
				[
					"type" => $type,
					"name" => $project_name,
					"path" => $repo_path,
				];
			}
			else
			{
				$projects = array_merge(
					$projects,
					static::scanProjects($type, $repo_path, $level + 1)
				);
			}
		}
		
		return $projects;
	}
	
	
	
	/**
	 * Returns project list
	 */
	static function getProjectsList()
	{
		$projects = static::selectQuery()
			->fields(
				"id",
				"type",
				"name"
			)
			->where("is_deleted", "=", 0)
			->orderBy("name", "asc")
			->all(true)
		;
		
		$projects = array_map
		(
			function($item)
			{
				return [
					"id" => $item["id"],
					"type" => $item["type"],
					"name" => $item["name"],
					"path" => (
						$item["type"] == "git" ? ("/data/repo/git/" . $item["name"]) : (
						$item["type"] == "hg" ? ("/data/repo/hg/" . $item["name"]) :
						""
					)),
				];
			},
			$projects
		);
		
		// $projects = array_merge($projects, static::scanProjects("hg", "/data/repo/hg", 0));
		// $projects = array_merge($projects, static::scanProjects("git", "/data/repo/git", 0));
		
		usort(
			$projects,
			function ($a, $b)
			{
				if ($a["name"] == $b["name"])
				{
					return 0;
				}
				return ($a["name"] < $b["name"]) ? -1 : 1;
			}
		);
		
		$res = [];
		foreach ($projects as $row)
		{
			$index = Utils::array_find_index($res, function ($item) use ($row){
				return $item["path"] == $row["path"];
			});
			if ($index === null)
			{
				$res[] = $row;
			}
		}
		
		return $res;
	}
	
	
	
	/**
	 * Filter project name
	 */
	static function filterProjectName($project_name)
	{
		$project_name = preg_replace("/\/+$/", "", $project_name);
		$project_name = preg_replace("/^\/+/", "", $project_name);
		$project_name = preg_replace("/\/+/", "/", $project_name);
		
		$project_name_arr = explode("/", $project_name);
		$project_name_arr = array_map(
			function($name){
				return trim($name);
			},
			$project_name_arr
		);
		
		return implode("/", $project_name_arr);
	}
	
	
	
	/**
	 * Create project
	 */
	static function createProject($project_type, $project_name)
	{
		if (!static::validateProjectName($project_name))
		{
			throw new \Exception("Project name must be valid");
			return null;
		}
		
		$project_name = static::filterProjectName($project_name);
		
		$project_name_arr = explode("/", $project_name);
		for ($i=1; $i<=count($project_name_arr); $i++)
		{
			$sub_project_name = implode("/", array_slice($project_name_arr, 0, $i));
			if (static::isRepoPath("/data/repo/hg/" . $sub_project_name))
			{
				throw new \Exception("Project already exists");
				return "";
			}
			if (static::isRepoPath("/data/repo/git/" . $sub_project_name))
			{
				throw new \Exception("Project already exists");
				return "";
			}
		}
		
		$repo_path = static::getRepoPath($project_type, $project_name);
		if (file_exists($repo_path))
		{
			throw new \Exception("Project already exists");
			return "";
		}
		
		if ($repo_path && ($project_type == "hg" || $project_type == "git"))
		{
			$project = static::findOrCreate([
				"type" => $project_type,
				"name" => $project_name,
			]);
			$project->is_deleted = 0;
			$project->save();
			
			$repo_path_id = "/data/repo/id/" . $project->id;
			@mkdir($repo_path_id, 0775, true);
			$cmd = "/var/www/html/bin/project.init.sh " . $project_type . " " . $project->id;
			$res = shell_exec($cmd);
			
			$base_repo_path = dirname($repo_path);
			@mkdir($base_repo_path, 0775, true);
			shell_exec("ln -s /data/repo/id/" . $project->id . " " . $repo_path);
			
			/* Save project name */
			if ($project_type == "git")
			{
				$file_path = $repo_path_id . "/project_name.txt";
				file_put_contents($file_path, $project_name);
			}
			else if ($project_type == "hg")
			{
				$file_path = $repo_path_id . "/.hg/project_name.txt";
				file_put_contents($file_path, $project_name);
			}
		}
		
		return $repo_path;
	}
	
	
	
	/**
	 * Check repo path
	 */
	static function checkRepoPath($project_id)
	{
		$project = static::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			return false;
		}
		
		$repo_path_id = "/data/repo/id/" . $project->id;
		$repo_path = static::getRepoPath($project->type, $project->name);
		if (!file_exists($repo_path))
		{
			$base_repo_path = dirname($repo_path);
			@mkdir($base_repo_path, 0775, true);
			$cmd = "ln -s /data/repo/id/" . $project->id . " " . $repo_path;
			shell_exec($cmd);
		}
		
		/* Save project name */
		if ($project->type == "git")
		{
			$file_path = $repo_path_id . "/project_name.txt";
			file_put_contents($file_path, $project->name);
		}
		else if ($project->type == "hg")
		{
			$file_path = $repo_path_id . "/.hg/project_name.txt";
			file_put_contents($file_path, $project->name);
		}
		
		return true;
	}
	
	
	
	/**
	 * Rename project
	 */
	static function renameProject($project_id, $project_name_new)
	{
		$project = static::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			throw new \Exception("Project not found");
			return null;
		}
		if (!static::validateProjectName($project_name_new))
		{
			throw new \Exception("Project name must be valid");
			return null;
		}
		
		$project_name_new = static::filterProjectName($project_name_new);
		
		$repo_path_id = "/data/repo/id/" . $project->id;
		$repo_path_old = static::getRepoPath($project->type, $project->name);
		$repo_path_new = static::getRepoPath($project->type, $project_name_new);
		
		$project_name_new_arr = explode("/", $project_name_new);
		for ($i=1; $i<=count($project_name_new_arr); $i++)
		{
			$sub_project_name_new = implode("/", array_slice($project_name_new_arr, 0, $i));
			if ($sub_project_name_new == $project->name)
			{
				continue;
			}
			$project_item = static::findItem([
				["type", "=", $project->type],
				["name", "=", $sub_project_name_new],
				["id", "!=", $project->id],
			]);
			if ($project_item)
			{
				throw new \Exception("Project is already exists");
				return false;
			}
			if (static::isRepoPath("/data/repo/hg/" . $sub_project_name_new))
			{
				throw new \Exception("Project is already exists");
				return false;
			}
			if (static::isRepoPath("/data/repo/git/" . $sub_project_name_new))
			{
				throw new \Exception("Project is already exists");
				return false;
			}
		}
		
		/* Change symlinks */
		if (file_exists($repo_path_old))
		{
			@unlink($repo_path_old);
		}
		
		if (!file_exists($repo_path_new))
		{
			$base_repo_path_new = dirname($repo_path_new);
			@mkdir($base_repo_path_new, 0775, true);
			$cmd = "ln -s /data/repo/id/" . $project->id . " " . $repo_path_new;
			shell_exec($cmd);
		}
		
		/* Rename project in database */
		$project->name = $project_name_new;
		$project->save();
		
		/* Save project name */
		if ($project->type == "git")
		{
			$file_path = $repo_path_id . "/project_name.txt";
			file_put_contents($file_path, $project_name_new);
		}
		else if ($project->type == "hg")
		{
			$file_path = $repo_path_id . "/.hg/project_name.txt";
			file_put_contents($file_path, $project_name_new);
		}
		
		return $project;
	}
	
	
	
	/**
	 * Remove project
	 */
	static function removeProject($project_id)
	{
		$project = static::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			throw new \Exception("Project not found");
			return null;
		}
		
		$repo_path_id = "/data/repo/id/" . $project->id;
		$repo_path = static::getRepoPath($project->type, $project->name);
		
		/* Remove symlinks */
		if (file_exists($repo_path))
		{
			@unlink($repo_path);
		}
		
		/* Remove data */
		if (file_exists($repo_path_id))
		{
			$cmd = "rm -rf $repo_path_id";
			shell_exec($cmd);
		}
		
		$project->delete();
		
		return $project;
	}
	
	
	
	/**
	 * Setup users
	 */
	static function saveUsers($project_id, $new_users)
	{
		$project = static::findItem([
			"id" => $project_id,
		]);
		if (!$project)
		{
			return false;
		}
		$project_id = $project->id;
		
		/* Get users */
		$users = User::selectQuery()
			->where("is_deleted", "=", 0)
			->where("banned", "=", 0)
			->all()
		;
		
		/* Get groups */
		$groups = UserRoles::selectQuery()
			->where("is_deleted", "=", 0)
			->all()
		;
		
		$findUserOrGroup = function ($name) use ($users, $groups)
		{
			if ($name == "")
			{
				return null;
			}
			
			$item = null;
			$item_id = 0;
			$item_type = 1;
			$item_name = $name;
			$arr = $users;
			
			if ($name[0] == "@")
			{
				//$item_name = substr($name, 1);
				$item_type = 2;
				$arr = $groups;
			}
			
			foreach ($arr as $item)
			{
				if ($item_type == 1)
				{
					if ($item["login"] == $item_name)
					{
						return $item;
					}
				}
				else if ($item_type == 2)
				{
					if ($item["name"] == $item_name)
					{
						return $item;
					}
				}
			}
			
			return null;
		};
			
		/* Add user */
		foreach ($new_users as $new_user)
		{
			$item = $findUserOrGroup( $new_user["name"] );
			if ($item == null && $new_user["name"] != "@all")
			{
				continue;
			}
			
			$item_id = $item ? $item["id"] : 0;
			$item_type = 0;
			if ($item instanceof User) $item_type = 1;
			if ($item instanceof UserRoles) $item_type = 2;
			
			$project_user = ProjectUser::findOrCreate([
				"project_id" => $project_id,
				"name" => $new_user["name"],
			]);
			$project_user->item_id = $item_id;
			$project_user->item_type = $item_type;
			$project_user->value = $new_user["value"];
			$project_user->save();
		}
		
		/* Remove old users */
		$old_users = ProjectUser::selectQuery()
			->where("project_id", $project_id)
			->all()
		;
		foreach ($old_users as $old_user)
		{
			$find = false;
			foreach ($new_users as $new_user)
			{
				$item = $findUserOrGroup( $new_user["name"] );
				if ($item == null && $new_user["name"] != "@all")
				{
					continue;
				}
				
				if ($old_user["name"] == $new_user["name"])
				{
					$find = true;
					break;
				}
			}
			if (!$find)
			{
				$old_user->delete();
			}
		}
		
		return true;
	}
	
	
	
	/**
	 * Read users for project
	 */
	static function getUsers($project_id)
	{
		$project = Project::selectQuery()
			->where("id", "=", $project_id)
			->one()
		;
		if (!$project) return [];
			
		$items = ProjectUser::selectQuery()
			->where("project_id", "=", $project->id)
			->all(true)
		;
		return $items;
	}
	
	
}