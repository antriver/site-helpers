<?php

namespace Tmd\LaravelSite\Providers;

use Auth;
use Config;
use DB;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use PDO;
use Tmd\LaravelPasswordUpdater\PasswordHasher;
use Tmd\LaravelSite\Libraries\Debug\QueryLogger;
use Tmd\LaravelSite\Libraries\Laravel\Auth\DatabaseSessionGuard;
use Tmd\LaravelSite\Libraries\Laravel\Auth\RepositoryUserProvider;
use Tmd\LaravelSite\Repositories\Interfaces\UserRepositoryInterface;
use Validator;

class LaravelSiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        include_once dirname(__DIR__).'/Libraries/helpers.php';

        Auth::provider(
            'repository',
            function (Container $app) {
                return new RepositoryUserProvider(
                    $app->make(UserRepositoryInterface::class),
                    $app->make(PasswordHasher::class)
                );
            }
        );

        Auth::extend(
            'database-session',
            function (Container $app, $name, array $config) {
                return new DatabaseSessionGuard(
                    app('auth')::createUserProvider($config['provider']),
                    $app->make(Request::class)
                );
            }
        );

        $this->registerQueryLogger();
        DB::connection()->getPdo()->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    private function registerQueryLogger()
    {
        if (Config::get('app.log_queries')) {
            new QueryLogger();
        }
    }
}
