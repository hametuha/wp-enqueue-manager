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

```js
/*!
 * wpdeps=jquery,thicbox
 */
jQuery(document).ready(function($){
  // Do something.
});
```

```css
/*!
 * wpdeps=bootstrap
 */
body{
   background-color: red;
}
```

Then, register them all from your theme or plugin.

```php
add_action( 'init', function() {
	// Register all js in folder.
	// e.g. /assets/js/sample.js will be regsitered as 'my-sample'.
	Hametuha\WpEnqueueManager::register_js( __DIR__ . '/assets/, 'my-', '1.0.0' );
} );

```