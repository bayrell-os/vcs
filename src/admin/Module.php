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

namespace App\Admin;


class Module extends \App\Module
{
	
	/**
	 * Register hooks
	 */
	static function register_hooks()
	{
		parent::register_hooks();
		add_chain("twig_loader", static::class, "twig_loader");
	}
	
	
	
	/**
	 * Register entities
	 */
	static function register_entities()
	{
		$app = app();
		$app->addEntity(\App\Admin\Console\UsersUpdate::class);
		$app->addEntity(\App\Admin\Routes\DefaultRoute::class);
		$app->addEntity(\App\Admin\Routes\ProjectRoute::class);
		$app->addEntity(\App\Admin\Routes\DatabaseRoute::class);
	}
	
	
	
	/**
	 * Request before
	 */
	static function request_before($res)
	{
		$res->container->add_breadcrumb(
			$res->container->base_url . "/",
			"Main"
		);
	}
	
	
	
	/**
	 * Method not found
	 */
	static function method_not_found($res)
	{
		$container = $res->container;
	}
	
	
	
	/**
	 * Twig loader
	 */
	static function twig_loader($res)
	{
		$obj = $res["obj"];
		$obj->registerTemplatePath(\App\Module::class);
	}
	
	
	
	/**
	 * Create App
	 */
	static function createApp()
	{
		/* Create app */
		$app = create_app_instance();
		
		/* Add modules */
		$app->addModule(static::class);
		$app->addModule(\TinyORM\Module::class);
		
		/* Run app */
		$app->init();
		return $app;
	}
	
}