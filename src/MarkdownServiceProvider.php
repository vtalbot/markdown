<?php

namespace VTalbot\Markdown;

use Config;
use Markdown;
use Route;
use Response;
use File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use VTalbot\Markdown\Compilers\MarkdownCompiler;

class MarkdownServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $path_config = __DIR__.'/../config/config.php';
        $publish_path_config = config_path('markdown.php');
        $this->publishes([$path_config => $publish_path_config,], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'markdown');
        $this->registerRoutes();
        $this->registerEngineResolver();
        $this->registerMarkdownFinder();
        $this->registerEnvironment();
    }

    /**
     * Register routes to catch Markdown request.
     *
     * @return void
     */
    public function registerRoutes()
    {
        if (Config::get('markdown.add_routes'))
        {
            foreach (Config::get('markdown.routes') as $routes)
            {
                foreach (Config::get('markdown.extensions') as $ext)
                {
                    Route::get($routes.'{file}.'.$ext, function($file) use ($routes)
                        {
                            $markdown = Markdown::make($routes.$file);
                            return Response::make($markdown, 200, array('Content-Type' => 'text/html'));
                        })->where('file', '.*');
                }
            }
        }
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        list($me, $app) = array($this, $this->app);

        $this->app->singleton('markdown.engine.resolver', function () use ($app, $me) {
          $resolver = new EngineResolver;
          $me->registerMarkdownEngine($resolver);

          return $resolver;
        });

    }

    /**
     * Register the Markdown engine implementation.
     *
     * @param  EngineResolver  $resolver
     * @return void
     */
    public function registerMarkdownEngine($resolver)
    {
        $app = $this->app;

        $resolver->register('markdown', function() use ($app)
            {
                $cache = storage_path().'/markdown';

                if ( ! File::isDirectory($cache))
                {
                    File::makeDirectory($cache);
                }

                $compiler = new MarkdownCompiler($app['files'], $cache);

                $compiler->setOptions(Config::get('markdown.options'));

                return new CompilerEngine($compiler, $app['files']);
            });
    }

    /**
     * Register the Markdown finder implementation.
     *
     * @return void
     */
    public function registerMarkdownFinder()
    {
      $app = $this->app;

      $this->app->singleton('markdown.finder', function () use ($app) {

        $paths = Config::get('markdown.paths');

        foreach ($paths as $key => $path)
        {
            $paths[$key] = app_path().$path;
        }

        return new FileViewFinder($app['files'], $paths, array('markdown', 'md'));
      });

    }

    /**
     * Register the Markdown environment.
     *
     * @return void
     */
    public function registerEnvironment()
    {
        //$me = $this;
        $app = $this->app;

        $this->app->singleton('markdown', function () use ($app) {
          $resolver = $app['markdown.engine.resolver'];

          $finder = $app['markdown.finder'];

          $events = $app['events'];

          $environment = new Environment($resolver, $finder, $events);

          $environment->setContainer($app);

          $environment->share('app', $app);

          return $environment;
    		});

    }

}
