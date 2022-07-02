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
use App\Models\UserGroup;
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
	 * Setup users
	 */
	static function saveUsers($type, $project_name, $new_users)
	{
		/* Get project id */
		$project_id = 0;
		$project = static::findOrCreate([
			"type" => $type,
			"name" => $project_name,
		]);
		if ($project->isNew())
		{
			$project->save();
		}
		$project_id = $project->id;
		
		/* Get users */
		$users = User::selectQuery()
			->where("is_deleted", "=", 0)
			->where("banned", "=", 0)
			->all();
			
		/* Get groups */
		$groups = UserGroup::selectQuery()
			->where("is_deleted", "=", 0)
			->all();
		
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
				$item_name = substr($name, 1);
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
			if ($item instanceof UserGroup) $item_type = 2;
			
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
		
	}
	
	
	
	/**
	 * Read users for project
	 */
	static function getUsers($type, $project_name)
	{
		$project = Project::selectQuery()
			->where("type", "=", $type)
			->where("name", "=", $project_name)
			->one();
		
		if (!$project) return [];
			
		$items = ProjectUser::selectQuery()
			->where("project_id", "=", $project->id)
			->all(true);
		
		return $items;
	}
	
	
}