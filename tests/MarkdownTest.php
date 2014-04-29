<?php

use Mockery as m;
use VTalbot\Markdown\Compilers\MarkdownCompiler;

class MarkdownTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }

    public function testTransformString()
    {
        $compiler = new MarkdownCompiler($this->getFiles(), __DIR__);
        $compiler->setOptions(array('use_extra' => false));
        $this->assertEquals('<h1>Hello World</h1>'.PHP_EOL, $compiler->string('#Hello World'));
        $this->assertEquals('<p><code>foo</code></p>'.PHP_EOL, $compiler->string('`foo`'));
    }

    public function testTransformFile()
    {
        $compiler = new MarkdownCompiler($this->getFiles(), __DIR__);
        $compiler->setOptions(array('use_extra' => false));
        $this->assertEquals('<h1>Hello World</h1>'.PHP_EOL, $compiler->string(file_get_contents(__DIR__.'/fixtures/foo.md')));
        $this->assertEquals('<p><code>class Foo extends Bar {}</code></p>'.PHP_EOL, $compiler->string(file_get_contents(__DIR__.'/fixtures/code.md')));
    }

    public function testTransformCodeToPre()
    {
        $compiler = new MarkdownCompiler($this->getFiles(), __DIR__);
        $compiler->setOptions(array('use_extra' => true, 'code_attr_on_pre' => true));
        $this->assertEquals('<pre><code>class Foo extends Bar {}'.PHP_EOL.'</code></pre>'.PHP_EOL, $compiler->string(file_get_contents(__DIR__.'/fixtures/code.md')));
    }

    protected function getFiles()
    {
        return m::mock('Illuminate\Filesystem\Filesystem');
    }

}

