<?php

namespace VTalbot\Markdown;

use Closure;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewFinderInterface;
use Illuminate\Routing\Route;

class Environment {

    /**
     * The engine implementation.
     *
     * @var EngineResolver
     */
    protected $engines;

    /**
     * The Markdown finder implementation.
     *
     * @var ViewFinderInterface
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
     *
     * @var Container
     */
    protected $container;

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = array();

    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    protected $extensions = array('markdown' => 'markdown', 'md' => 'markdown');

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Handle markdown file not found.
     *
     * @var Closure|string
     */
    protected $notFoundHandler;

    /**
     * Create a new Markdown environment instance.
     *
     * @param EngineResolver      $engines
     * @param ViewFinderInterface $finder
     * @param Dispatcher          $events
     *
     * @return Environment
     */
    public function __construct(EngineResolver $engines, ViewFinderInterface $finder, Dispatcher $events)
    {
        $this->engines = $engines;
        $this->finder = $finder;
        $this->events = $events;
        $this->share('__env', $this);
    }

    /**
     * Get a evaluated Markdown contents for the given Markdown.
     *
     * @param  string  $markdown
     * @return Markdown
     */
    public function make($markdown)
    {
        try
        {
            $path = $this->finder->find($markdown);
            return new Markdown($this, $this->getEngineFromPath($path), $markdown, $path);
        }
        catch (\InvalidArgumentException $e)
        {
            if ( ! is_null($this->notFoundHandler))
            {
                if ($this->notFoundHandler instanceof Closure)
                {
                    return call_user_func($this->notFoundHandler, $markdown);
                }
                else
                {
                    $handler = explode('@', $this->notFoundHandler);

                    if (class_exists($class = $handler[0]))
                    {
                        $controller = new $class;

                        $action = isset($handler[1]) ? $handler[1] : 'index';

                        return $controller->callAction($action, array($markdown));
                    }
                }
            }

            throw $e;
        }
    }

    /**
     * Get a evaluated Markdown contents for the given string.
     *
     * @param  string  $markdown
     * @return string
     */
    public function string($markdown)
    {
        $resolver = app('markdown.engine.resolver');
        $compiler = $resolver->resolve('markdown')->getCompiler();
        return $compiler->string($markdown);
    }

    /**
     * Get the appropriate Markdown engine for the given path.
     *
     * @param  string  $path
     * @return EngineInterface
     */
    protected function getEngineFromPath($path)
    {
        $engine = $this->extensions[$this->getExtension($path)];

        return $this->engines->resolve($engine);
    }

    /**
     * Get the extension used by the Markdown file.
     *
     * @param  string  $path
     * @return string
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return array_first($extensions, function($key, $value) use ($path)
            {
                return ends_with($path, $value);
            });
    }

    /**
     * Add piece of shared data to the environment
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function share($key, $value)
    {
        $this->shared[$key] = $value;
    }

    /**
     * Increment the rendering counter.
     *
     * @return void
     */
    public function incrementRender()
    {
        $this->renderCount++;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender()
    {
        $this->renderCount--;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering()
    {
        return $this->renderCount == 0;
    }

    /**
     * Add a location to the array of Markdown locations.
     *
     * @param  string  $location
     * @return void
     */
    public function addLocation($location)
    {
        $this->finder->addLocation($location);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function addNamespace($namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);
    }

    /**
     * Register a valid Markdown extension and its engine.
     *
     * @param  string   $extension
     * @param  string   $engine
     * @param  Closure  $resolver
     * @return void
     */
    public function addExtension($extension, $engine, $resolver = null)
    {
        $this->finder->addExtension($extension);

        if (isset($resolver))
        {
            $this->engines->register($engine, $resolver);
        }

        $this->extensions[$extension] = $engine;
    }

    /**
     * Get the engine resolver instance.
     *
     * @return EngineResolver
     */
    public function getEngineResolver()
    {
        return $this->engines;
    }

    /**
     * Get the Markdown finder instance.
     *
     * @return FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Get the IoC container instance.
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  Container  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Set the handler for markdown file not found.
     *
     * @param  Closure|string
     * @return void
     */
    public function setNotFoundHandler($notFoundHandler)
    {
        $this->notFoundHandler = $notFoundHandler;
    }

}
