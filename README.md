# Markdown Compiler for Laravel 4 (Illuminate)

### Installation

Add to your composer.json those lines

    "require": {
        "vtalbot/markdown": "1.*"
    }

Run `php artisan config:publish vtalbot/markdown`

Then edit `config.php` in `app/packages/vtalbot/markdown` to your needs.

Add `'VTalbot\Markdown\MarkdownServiceProvider',` to `providers` in `app/config/app.php`
and `'Markdown' => 'VTalbot\Markdown\Facades\Markdown',` to `aliases` in `app/config/app.php`

### Usage

    http://domain.name/test.md

If `test.md` doesn't exists in the `public` directory, it will search for `test.md` in `app/markdown` directory.
If found, compile it if needed and return the result.

    Markdown::make('file-in-markdown-directory');
