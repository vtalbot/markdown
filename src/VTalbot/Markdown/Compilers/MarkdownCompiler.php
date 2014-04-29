<?php

namespace VTalbot\Markdown\Compilers;

use Illuminate\Filesystem;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Michelf\Markdown;
use Michelf\MarkdownExtra;

class MarkdownCompiler extends Compiler implements CompilerInterface {

    /**
     * Options for PHP Markdown.
     *
     * @var array
     */
    protected $options;

    /**
     * Compile the Markdown at the given path
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {
        $markdown = $this->createParser();
        $contents = $markdown->transform(file_get_contents($path));

        if ( ! is_null($this->cachePath))
        {
            $this->files->put($this->getCompiledPath($path), $contents);
        }
    }

    /**
     * Compile the Markdown from the given string
     * and returns it
     *
     * @param  string  $str
     * @return string
     */
    public function string($str)
    {
        $markdown = $this->createParser();
        $contents = $markdown->transform($str);

        return $contents;
    }

    /**
     * Create a new parser with the options.
     *
     * @return MarkdownExtra|Markdown
     */
    protected function createParser()
    {
        if ($this->options['use_extra'])
        {
            $parser = new MarkdownExtra;
        }
        else
        {
            $parser = new Markdown;
        }

        foreach ($this->options as $key => $value)
        {
            if (property_exists($parser, $key))
            {
                $parser->$key = $value;
            }
        }

        return $parser;
    }

    /**
     * Set the options for PHP Markdown.
     *
     * @param  array  $options
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

}
