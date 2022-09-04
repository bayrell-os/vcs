<?php

/*!
 *  Bayrell Cloud OS
 *
 *  (c) Copyright 2020 - 2022 "Ildar Bikmamatov" <support@bayrell.org>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      https://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace App;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\UsersInGroups;


class Auth extends \TinyPHP\Auth
{
	protected $groups = [];
	
	
	/**
	 * Returns groups
	 */
	function getGroups()
	{
		return $this->groups;
	}
	
	
	
	/**
	 * Check if user in the group
	 */
	function hasGroup($group_name)
	{
		return in_array($group_name, $this->groups);
	}
	
	
	
	/**
	 * Check if user is admin
	 */
	function isAdmin()
	{
		return $this->hasGroup("admin");
	}
	
	
	
	/**
	 * Init auth
	 */
	function init($params = [])
	{
		if ($this->initialized) return;
		
		parent::init($params);
		
		$login = $this->getLogin();
		if ($login == "") return;
		
		/* Find user */
		$user = User::selectQuery()
			->where("login", "=", $login)
			->where("is_deleted", "=", 0)
			->where("banned", "=", 0)
			->one()
		;
		if (!$user)
		{
			return;
		}
		
		/* Get users groups */
		$groups = UserGroup::selectQuery()
			->fields("t.*")
			->innerJoin(
				UsersInGroups::getTableName(),
				"users_in_groups",
				"users_in_groups.group_id = t.id"
			)
			->where("users_in_groups.user_id", "=", $user->id)
			->where("users_in_groups.is_deleted", "=", 0)
			->where("t.is_deleted", "=", 0)
			->all(true)
		;
		
		$groups = array_map(
			function($item){ return $item["name"]; },
			$groups
		);
		
		$this->groups = $groups;
	}
	
}