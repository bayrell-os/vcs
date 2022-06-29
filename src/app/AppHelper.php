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

namespace App;


class AppHelper
{
	
	/**
	 * Validate project name
	 */
	static function validateProjectName($project_name)
	{
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
	 * Check if folder is project of the type
	 */
	static function isProjectFolder($type, $folder)
	{
		if ($type == "hg")
		{
			if (!is_dir($folder)) return false;
			if (!is_dir($folder . "/.hg")) return false;
			return true;
		}
		if ($type == "git")
		{
			if (!is_dir($folder)) return false;
			if (!file_exists($folder . "/config")) return false;
			return true;
		}
		return false;
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
	 * Get project name by path
	 */
	static function getProjectNameByPath($type, $folder)
	{
		$project_name = "";
		if ($type == "hg")
		{
			if (strpos($folder, "/data/repo/hg/") !== 0) return "";
			$project_name = substr($folder, strlen("/data/repo/hg/"));
		}
		if ($type == "git")
		{
			if (strpos($folder, "/data/repo/git/") !== 0) return "";
			$project_name = substr($folder, strlen("/data/repo/git/"));
		}
		if (!static::validateProjectName($project_name)) return $project_name;
		return $project_name;
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
	 * Scan projects in folder
	 */
	static function scanProjects($type, $folder, $level)
	{
		if ($level >= 3) return [];
		
		$projects = [];
		
		$res = scandir($folder, SCANDIR_SORT_ASCENDING);
		foreach ($res as $name)
		{
			if ($name == "." or $name == "..") continue;
			
			$path = $folder . "/" . $name;
			
			if (static::isProjectFolder($type, $path))
			{
				$projects[] =
				[
					"type" => $type,
					"name" => static::getProjectNameByPath($type, $path),
					"path" => $path,
				];
			}
			else
			{
				$projects = array_merge($projects, static::scanProjects($type, $path, $level + 1));
			}
		}
		
		return $projects;
	}
	
	
	
	/**
	 * Returns project list
	 */
	static function getProjectsList()
	{
		$projects = [];
		$projects = array_merge($projects, static::scanProjects("hg", "/data/repo/hg", 0));
		$projects = array_merge($projects, static::scanProjects("git", "/data/repo/git", 0));
		
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
		
		return $projects;
	}
	
	
	
	/**
	 * Setup users
	 */
	static function projectSaveUsers($type, $project_name, $users)
	{
		$repo_path = static::getRepoPath($type, $project_name);
		
		if ($repo_path == "" || !is_dir($repo_path)) return;
		
		/* Get users.json file path */
		$user_path = $repo_path;
		if ($type == "git") $user_path .= "/users.json";
		if ($type == "hg") $user_path .= "/.hg/users.json";
		$user_path = preg_replace("/\/+$/", "", $user_path);
		
		file_put_contents($user_path, json_encode($users));
	}
	
	
	
	/**
	 * Read users for project
	 */
	static function projectGetUsers($type, $project_name)
	{
		$repo_path = static::getRepoPath($type, $project_name);
		
		if ($repo_path == "" || !is_dir($repo_path)) return [];
		
		/* Get users.json file path */
		$user_path = $repo_path;
		if ($type == "git") $user_path .= "/users.json";
		if ($type == "hg") $user_path .= "/.hg/users.json";
		$user_path = preg_replace("/\/+$/", "", $user_path);
		
		$users = [];
		if (file_exists($user_path))
		{
			$content = file_get_contents($user_path);
			$users = json_decode($content, true);
			if (!$users) $users = [];
		}
		
		return $users;
	}
	
}