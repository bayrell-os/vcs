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

namespace App\Admin\Console;

use App\Docker;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UsersInGroups;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TinyPHP\Bus;
use TinyPHP\Utils;


class UsersUpdate extends Command
{
	protected static $defaultName = 'users:update';

	protected function configure(): void
	{
		$this
			// the short description
			->setDescription('Update users')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('Update users')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		/* Call api */
		$res = Bus::call
		(
			"/cloud_os/bus/users/dump/",
			[
			]
		);
		
		/* If success */
		if ($res->isSuccess())
		{
			$users = isset($res->result["users"]) ? $res->result["users"] : [];
			$groups = isset($res->result["groups"]) ? $res->result["groups"] : [];
			$users_in_groups = isset($res->result["users_in_groups"]) ?
				$res->result["users_in_groups"] : [];
			
			User::sync($users);
			UserGroup::sync($groups);
			UsersInGroups::sync($users_in_groups);
		}
		
		return Command::SUCCESS;
	}
}