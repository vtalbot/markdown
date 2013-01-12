<?php

namespace Ellicom\Markdown;

use Closure;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;

class Environment {

    /**
     * The engine implementation.
     *
     * @var Illuminate\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The Markdown finder implementation.
     * 
     * @var Illuminate\View\ViewFinderInterface
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var Illuminate\Events\Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
     *
     * @var Illuminate\Container
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
     * Create a new Markdown environment instance.
     *
     * @param  Illuminate\View\Engines\EngineResolver  $engines
     * @param  Illuminate\View\ViewFinderInterface  $finder
     * @param  Illuminate\Events\Dispatcher  $events
     * @return void
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
     * @return Ellicom\Markdown\Markdown
     */
    public function make($markdown)
    {
        $path = $this->finder->find($markdown);

        return new Markdown($this, $this->getEngineFromPath($path), $markdown, $path);
    }

    /**
     * Get the appropriate Markdown engine for the given path.
     *
     * @param  string  $path
     * @return Illuminate\View\Engines\EngineInterface
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
     * @return Illuminate\View\Engines\EngineResolver
     */
    public function getEngineResolver()
    {
        return $this->engines;
    }

    /**
     * Get the Markdown finder instance.
     *
     * @return Illuminate\View\FileViewFinder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Get the event dispatcher instance.
     *
     * @return Illuminate\Events\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->events;
    }

    /**
     * Get the IoC container instance.
     *
     * @return Illuminate\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  Illuminate\Container  $container
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

}