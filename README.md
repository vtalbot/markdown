# Markdown Compiler for Laravel 4 (Illuminate)

[![Build Status](https://travis-ci.org/Ellicom/markdown.png)](https://travis-ci.org/Ellicom/markdown)

### Installation

Run `php artisan config:publish ellicom/markdown`

Then edit `config.php` in `app/packages/ellicom/markdown` to your needs.

Add `'Ellicom\Markdown\MarkdownServiceProvider',` to `providers` in `app/config/app.php`
and `'Markdown' => 'Ellicom\Markdown\Facades\Markdown',` to `aliases` in `app/config/app.php`

### Usage

    http://domain.name/test.md

If `test.md` doesn't exists in the `public` directory, it will search for `test.md` in `app/markdown` directory.
If found, compile it if needed and return the result.

    Markdown::make('file-in-markdown-directory');

### Todo

Add tests.