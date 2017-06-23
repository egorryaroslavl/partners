<?php

	namespace Egorryaroslavl\Partners;

	use Illuminate\Support\ServiceProvider;

	class PartnersServiceProvider extends ServiceProvider
	{

		public function boot()
		{
			$this->loadViewsFrom( __DIR__ . '/views', 'partners' );
			$this->loadRoutesFrom( __DIR__ . '/routes.php' );
			$this->publishes( [ __DIR__ . '/views' => resource_path( 'views/admin/partners' ) ], 'partners' );
			$this->publishes( [ __DIR__ . '/config/partners.php' => config_path( '/admin/partners.php' ) ], 'partners' );
			$this->publishes( [
				__DIR__ . '/migrations/2017_21_02_025121_create_partners_table.php' => base_path( 'database/migrations/2017_21_02_025121_create_partners_table.php' )
			], '' );


		}

		public function register()
		{

			$this->app->make( 'Egorryaroslavl\Partners\PartnersController' );
			$this->mergeConfigFrom( __DIR__ . '/config/partners.php', 'partners' );
		}

	}