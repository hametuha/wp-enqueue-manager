# wp-enqueue-manager

Bulk register WordPress assets with specified folder structure.
Good shortcut for your theme development.

[![Travis CI master branch](https://travis-ci.org/hametuha/wp-enqueue-manager.svg?branch=master)](https://travis-ci.org/hametuha/wp-enqueue-manager)

## Installation

Use composer.

```
composer require hametuha/wp-enqueue-manager
```

## Usage

Write dependnecies in your assets(js and css) header as comment.
format is like `wpdeps=dependencies`. CSV ready.

This notation will be used for the `deps` argument of `wp_register_script`[(doc)](https://developer.wordpress.org/reference/functions/wp_register_script/) and `wp_register_style`[(doc)](https://developer.wordpress.org/reference/functions/wp_register_style/).

Operations should be done in `init` hook or before. Write codes in your `functions.php`.

### Javascript

Header file should be like below:

```js
/*!
 * wpdeps=jquery,thicbox
 */
jQuery(document).ready(function($){
  // Do something.
});
```

**NOTICE:** If you use autoprefixer or minify tools, be careful about cleaning up comments.


Then, register them all from your theme or plugin.

```php
// Register all js in folder.
// e.g. /assets/js/sample.js will be regsitered as 'my-sample'.
Hametuha\WpEnqueueManager::register_js( __DIR__ . '/assets/, 'my-', '1.0.0' );
```

### Stylesheet

Same as javascript, regsier

```css
/*!
 * wpdeps=bootstrap
 */
body{
   background-color: red;
}
```

And, run `register_styles`.

```php
// Register all css in folder.
// e.g. /assets/css/sample.css will be regsitered as 'my-sample'.
Hametuha\WpEnqueueManager::register_styles( __DIR__ . '/assets/, 'my-', '1.0.0' );
```

### Versionning

If you are a theme or plugin author, it's proper to pass the version of your theme/plugin.
Lazy authors may just pass `null` or skip the argument. Then the file modified time will be used as version string.

## Localization

For Javascript localization, you can bulk register localization vars.

```
Hametuha\WpEnqueueManager::register_js_var_files( __DIR__ . '/l10n );
```

File name equals js handle name. Camelized handle name should be var name. PHP files should return var array.

For example, if you put `my-sample.php` below in `l10n` directory.

```php
<?php
// Avoide direct load.
defined( 'ABSPATH' ) || die();
// Return JS vars.
return [
	'label' => 'This is my var!',
];
```

Registered infromation are below:

- Handle: **my-sample**
- Var name: **MySample**
- Var: returned array of file.

So, you can refer PHP variables from JS like this:

```js
$('.button').on('click', function(){
	alert(MySample.label);
	// => This is my var!
};
```

## License

MIT.