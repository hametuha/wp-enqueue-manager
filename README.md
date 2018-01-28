# wp-enqueue-manager
Bulk register WordPress assets.

## Installation

Use composer.

```
composer require hametuha/wp-enqueue-manager
```

## Usage

Write dependnecies in your assets(js and css) header as comment.
format is like `wpdeps=dependencies`. CSV ready.

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
// Register all js in folder.
// e.g. /assets/js/sample.js will be regsitered as 'my-sample'.
Hametuha\WpEnqueueManager::register_styles( __DIR__ . '/assets/, 'my-', '1.0.0' );
```

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

