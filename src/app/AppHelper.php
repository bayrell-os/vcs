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
	 * Check if folder is project of the type
	 */
	static function isProject($type, $folder)
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
	 * Get project name
	 */
	static function getProjectName($type, $folder)
	{
		if ($type == "hg")
		{
			if (strpos($folder, "/data/repo/hg/") !== 0) return "";
			return substr($folder, strlen("/data/repo/hg/"));
		}
		if ($type == "git")
		{
			if (strpos($folder, "/data/repo/git/") !== 0) return "";
			return substr($folder, strlen("/data/repo/git/"));
		}
		return "";
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
			
			if (static::isProject($type, $path))
			{
				$projects[] =
				[
					"type" => $type,
					"name" => static::getProjectName($type, $path),
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
	
	
}