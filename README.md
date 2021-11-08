# khorsa/asset-manager

Utility classes to organize assets in website.

## Installation

You can add this library as local by downloading zip archive, or through composer:

```
composer require flexycms/asset-manager
```

If you only need this library during development, for instance to run your project's test suite, then you should add it as a development-time dependency:

```
composer require --dev flexycms/asset-manager
```

## Usage

### Basic usage

```php
$DR = $_SERVER['DOCUMENT_ROOT'];
$assets = new Flexycms\AssetManager\AssetManager();

$assets->scripts()->setCompiledFile($DR . "/assets/js/.compiled.js");
$assets->scripts()->addFile([
	$DR . "/assets/js/module1.js",
	$DR . "/assets/js/module2.js",
	$DR . "/assets/js/module3.js",
]);

$assets->styles()->addFile([
	$DR . "/assets/css/styles1.scss",
	$DR . "/assets/css/styles2.css",
	$DR . "/assets/css/styles3.css",
]);

$scriptsArray = $assets->scripts()->getRefs();
$stylesArray = $assets->styles()->getRefs();
```


### AssetManager usage
```php
$assets = new Flexycms\AssetManager\AssetManager();

$scriptManager = $assets->scripts(); // get script manager
$styleManager = $assets->styles(); // get styles manager
```

### ScriptManager usage

```php
$assets = new Flexycms\AssetManager\AssetManager();
$DR = $_SERVER['DOCUMENT_ROOT'];    // /var/www for example

/**
Set compiled file
*/
$assets->scripts()->setCompiledFile($DR . "/assets/js/.compiled.js");

/**
Add one JS file
*/
$assets->scripts()->addFile($DR . "/assets/js/main_script.js");

/**
Add JS files as array
*/
$assets->scripts()->addFile([
    $DR . "/assets/js/script_1.js",
    $DR . "/assets/js/script_2.js",
    $DR . "/assets/js/script_3.js",
]);

/**
Add file and disallow combine it with compiled file
(It work with array too)
*/
$assets->scripts()->addFile($DR . "/assets/js/as_is_file.js", false);


/**
Add all JS files in directory, except "ignored.js" 
Files in directory: "a.js", "b.js", "c.js", "ignored.js"
*/
$assets->scripts()->addIgnoreFile($DR . "/assets/js/modules/ignored.js");
$assets->scripts()->addDir($DR . "/assets/js/modules/");

/**
Add external JS
*/
$assets->scripts()->addFile("https://example.com/libs/external_js_file.js");

/**
Get result files
*/
$result = $assets->scripts()->get();
/**
$result = [
    "/var/www/assets/js/.compiled.js",
    "/var/www/assets/js/as_is_file.js",
    "https://example.com/libs/external_js_file.js",
]

".compiled.js" will contains compressed content of previously added files:
/var/www/assets/js/main_script.js
/var/www/assets/js/script_1.js,
/var/www/assets/js/script_2.js,
/var/www/assets/js/script_3.js,
/var/www/assets/js/modules/a.js,
/var/www/assets/js/modules/b.js,
/var/www/assets/js/modules/c.js,
*/


/**
Get refs for files
*/
$assets->scripts()->getRefs();
/**
$result = [
    "/assets/js/.compiled.js",
    "/assets/js/as_is_file.js",
    "https://example.com/libs/external_js_file.js",
]
*/
```

### StyleManager usage
```php
$assets = new Flexycms\AssetManager\AssetManager();
$DR = $_SERVER['DOCUMENT_ROOT'];    // /var/www for example

/**
Add CSS or SCSS file
*/
$assets->styles()->addFile($DR . "/assets/css/fonts/roboto.css");

/**
Add CSS and SCSS files as array
*/
$assets->styles()->addFile([
	$DR . "/assets/css/styles.scss",
	$DR . "/assets/css/images.css",
]);

/**
Add external style
*/
$assets->styles()->addFile("https://example.com/libs/external_css_file.css");


/**
Disable inline sourcemap generation (for prod environment for example)
*/
$assets->styles()->disableSourcemap();

/**
Enable inline sourcemap generation
*/
$assets->styles()->enableSourcemap();

/**
Get result files
*/
$result = $assets->scripts()->get();
/**
$result = [
    "/var/www/assets/css/fonts/roboto.css",
    "/var/www/assets/css/styles.min.css",
    "/var/www/assets/css/images.css",
    "https://example.com/libs/external_css_file.css",
]

styles.min.css is a result of processing styles.scss and will contain sourcemap block at the end of file (look ::enableSourcemap())

*/

/**
Get refs for files
*/
$assets->scripts()->getRefs();
/**
$result = [
    "/assets/css/fonts/roboto.css",
    "/assets/css/styles.min.css",
    "/assets/css/images.css",
    "https://example.com/libs/external_css_file.css",
]
*/
```
For work with CSS modules use SCSS @import directive inside one of your SCSS files