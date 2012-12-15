<?php

namespace Ellicom\Markdown\Compilers;

use Illuminate\Filesystem;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use dflydev\markdown\MarkdownParser;

class MarkdownCompiler extends Compiler implements CompilerInterface {

    /**
     * Compile the Markdown at the given path
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path)
    {
        $markdown = new MarkdownParser();
        $contents = $markdown->transformMarkdown(file_get_contents($path));

        if ( ! is_null($this->cachePath))
        {
            $this->files->put($this->getCompiledPath($path), $contents);
        }
    }
}