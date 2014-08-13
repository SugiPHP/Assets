SugiPHP Asset Manager
=====================

[![Build Status](https://scrutinizer-ci.com/g/SugiPHP/Assets/badges/build.png?b=master)](https://scrutinizer-ci.com/g/SugiPHP/Assets/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SugiPHP/Assets/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SugiPHP/Assets/?branch=master)

SugiPHP\Assets simplifies use of a well know asset management
framework for PHP [Assetic](https://github.com/kriswallsmith/assetic).

CssPacker
---------

Packs and minifies CSS stylesheet files. It can process LESS files as
well.

```php
$config = array(
	"input_path"  => "/path/to/your/assets",
	"output_path" => "/path/to/webroot/css",
	"debug"       => true
);
$css = new CssPacker($config);
// add several files atones
$css->add(array("reset.css", "common.css"));
// add one file
$css->add("pages/index.css");
// add a file not from the default input path:
$css->add("/absolute/path/to/stylesheet.css");

// In your template file:
<link rel="stylesheet" href="/css/<?php echo $css->pack(); ?>" />

// This will pack all assets in one and minify* them. Then the result
// will be saved in a file. Return a filename.
// *Minification will only be done if "debug" configuration option is
// FALSE. This makes debugging easier.

// If you want to get the contents only and not saving it in a file:
<style type="text/css">
<?php echo $css->pack(false); ?>
</style>

```

JsPacker
--------

JsPacker works in a same way as a CssPacker. Enjoy!
