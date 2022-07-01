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


class Module
{
	/**
	 * Register hooks
	 */
	static function register_hooks()
	{
		add_chain("init_app", static::class, "init_app");
		add_chain("init_di_defs", static::class, "init_di_defs", CHAIN_LAST);
		add_chain("register_entities", static::class, "register_entities", CHAIN_LAST);
		add_chain("request_before", static::class, "request_before");
		add_chain("method_not_found", static::class, "method_not_found");
		add_chain("routes", static::class, "routes");
		add_chain("base_url", static::class, "base_url");
		add_chain("bus_gateway", static::class, "bus_gateway");
		add_chain("twig_opt", static::class, "twig_opt");
	}
	
	
	
	/**
	 * Init app
	 */
	static function init_app()
	{
	}
	
	
	
	/**
	 * Init defs
	 */
	static function init_di_defs($res)
	{
		$defs = $res->defs;
		
		/* Setup default db connection */
		$defs["db_connection"] = \DI\create(\TinyORM\SQLiteConnection::class);
		
		/* Connect to database */
		$defs["connectToDatabase"] =
			function ()
			{
				$conn = make("db_connection");
				$conn->database = "/data/db/vcs.db";
				
				/* Connect */
				$conn->connect();
				
				if (!$conn->isConnected())
				{
					echo "Error: " . $conn->connect_error . "\n";
					exit(1);
				}
				
				$db_list = app("db_connection_list");
				$db_list->add("default", $conn);
				
				// Set journal_mode wal
				$conn->query("PRAGMA journal_mode = WAL;");
				
				call_chain("connectToDatabase", ["conn"=>$conn]);
			};
		
		$res->defs = $defs;
	}
	
	
	
	/**
	 * Register entities
	 */
	static function register_entities()
	{
		$app = app();
		
		$is_debug = env("APP_DEBUG");
		if ($is_debug)
		{
			// $this->addEntity(\App\Api\Test::class);
		}
		
		/* Add routes */
		$app->addEntity(\App\Routes\DefaultRoute::class);
		$app->addEntity(\App\Routes\ProjectRoute::class);
		$app->addEntity(\App\Routes\SettingsRoute::class);
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
	 * Routes
	 */
	static function routes($res)
	{
		// var_dump( $res->route_container->routes );
	}
	
	
	
	/**
	 * Base url
	 */
	static function base_url($res)
	{
		$res["base_url"] = $res->request->server->get('HTTP_X_ROUTE_PREFIX', '');
	}
	
	
	
	/**
	 * Twig opt
	 */
	static function twig_opt($res)
	{
		$twig_opt = $res["twig_opt"];
		$twig_opt["cache"] = "/data/php/cache/twig";
		$res["twig_opt"] = $twig_opt;
	}
	
	
	
	/**
	 * Bus gateway
	 */
	static function bus_gateway($res)
	{
		$gateway = $res["project"];
		if ($gateway == "cloud_os")
		{
			$res["gateway"] = "http://" . env("CLOUD_OS_GATEWAY") . "/api/bus/";
		}
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