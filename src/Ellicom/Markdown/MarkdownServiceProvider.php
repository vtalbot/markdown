<?php

namespace Ellicom\Markdown;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use Ellicom\Markdown\Compilers\MarkdownCompiler;

class MarkdownServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['config']->package('ellicom/markdown', 'ellicom/markdown', 'ellicom/markdown');

        $this->registerRoutes();

        $this->registerEngineResolver();

        $this->registerMarkdownFinder();

        $this->registerEnvironment();
    }

    /**
     * Register routes to catch LESS request.
     *
     * @return void
     */
    public function registerRoutes()
    {
        $app = $this->app;

        foreach ($app['config']['ellicom/markdown::routes'] as $routes)
        {
            foreach ($app['config']['ellicom/markdown::extensions'] as $ext)
            {
                \Route::get($routes.'{file}.'.$ext, function($file) use ($routes)
                {
                    $markdown = \Markdown::make($routes.$file);
                    return \Response::make($markdown, 200, array('Content-Type' => 'text/html'));
                });
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

        $app['markdown.engine.resolver'] = $app->share(function($app) use ($me)
        {
            $resolver = new EngineResolver;

            foreach (array('markdown') as $engine)
            {
                $me->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the LESS engine implementation.
     *
     * @param  Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerMarkdownEngine($resolver)
    {
        $app = $this->app;

        $resolver->register('markdown', function() use ($app)
        {
            $cache = $app['path'].'/storage/markdown';

            if ( ! $app['files']->isDirectory($cache))
            {
                $app['files']->makeDirectory($cache);
            }

            $compiler = new MarkdownCompiler($app['files'], $cache);

            return new CompilerEngine($compiler, $app['files']);
        });
    }

    /**
     * Register the LESS finder implementation.
     *
     * @return void
     */
    public function registerMarkdownFinder()
    {
        $this->app['markdown.finder'] = $this->app->share(function($app)
        {
            $paths = $app['config']['ellicom/markdown::paths'];

            foreach ($paths as $key => $path)
            {
                $paths[$key] = $app['path'].$path;
            }

            return new FileViewFinder($app['files'], $paths, array('markdown', 'md'));
        });
    }

    /**
     * Register the LESS environment.
     *
     * @return void
     */
    public function registerEnvironment()
    {
        $me = $this;

        $this->app['markdown'] = $this->app->share(function($app) use ($me)
        {
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