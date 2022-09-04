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

use TinyPHP\RenderContainer;
use TinyPHP\Route;
use TinyPHP\RouteList;


class DatabaseRoute extends Route
{
	
	/**
	 * Declare routes
	 */
	function routes(RouteList $routes)
	{
		$routes->addRoute([
			"url" => "/database/",
			"name" => "site:database",
			"method" => [$this, "actionDatabase"],
		]);
		
		$routes->addRoute([
			"url" => "/adminer/",
			"name" => "site:database:adminer",
			"method" => [$this, "actionAdminer"],
		]);
	}
	
	
	
	/**
	 * Action index
	 */
	function actionIndex()
	{
		/* Set result */
		$this->render("@app_admin/index.twig");
	}
	
	
	
	/**
	 * Action database
	 */
	function actionDatabase()
	{
		/* Set result */
		$this->render("@app_admin/database.twig");
	}
	
	
	
	/**
	 * Adminer
	 */
	function actionAdminer()
	{
		$file_path = BASE_PATH . "/admin/Templates/adminer-sqlite.php";
		
		@ob_start();
		$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		include $file_path;
		$content = ob_get_contents();
		@ob_end_clean();
		
		/* Set result */
		$this->setContent($content);
	}
	
}