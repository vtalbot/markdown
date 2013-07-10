<?php

namespace Vtalbot\Markdown;

use Illuminate\View\Engines\EngineInterface;
use Illuminate\Support\Contracts\RenderableInterface as Renderable;

class Markdown implements Renderable {

    /**
     * The Markdown environment instance.
     *
     * @var Environment
     */
    protected $environment;

    /**
     * The engine implementation.
     *
     * @var EngineInterface
     */
    protected $engine;

    /**
     * The name of the Markdown.
     *
     * @var string
     */
    protected $markdown;

    /**
     * The path to the Markdown file.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new Markdown instance.
     *
     * @param Environment     $environment
     * @param EngineInterface $engine
     * @param string          $markdown
     * @param string          $path
     *
     * @return Markdown
     */
    public function __construct(Environment $environment, EngineInterface $engine, $markdown, $path)
    {
        $this->environment = $environment;
        $this->engine = $engine;
        $this->markdown = $markdown;
        $this->path = $path;
    }

    /**
     * Get the string contents of the Markdown.
     *
     * @return string
     */
    public function render()
    {
        $env = $this->environment;
        $env->incrementRender();
        $contents = $this->getContents();
        $env->decrementRender();

        return $contents;
    }

    /**
     * Get the evaluated contents of the Markdown.
     *
     * @return string
     */
    protected function getContents()
    {
        return $this->engine->get($this->path);
    }

    /**
     * Get the Markdown environment instance.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Get the Markdown' rendering engine.
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Get the name of the Markdown.
     *
     * @return string
     */
    public function getName()
    {
        return $this->markdown;
    }

    /**
     * Get the path to the Markdown file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path to the Markdown.
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the string contents of the Markdown.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}