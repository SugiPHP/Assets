SugiPHP Asset Manager
=====================

SugiPHP\Assets simplifies use of a well know asset management 
framework for PHP [Assetic](https://github.com/kriswallsmith/assetic).

CssPacker
---------

Packs and minifies CSS stylesheet files. It can process LESS files as 
well.

```
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

// pack, minify, save a file and get the filename:
// minifications will only be done if "debug" configuration option is
// FALSE. This makes debugging easier.
$filename = $css->pack();

// In your template file:
<link rel="stylesheet" href="/css/<?php echo $filename; ?>" />

// If you want to get the contents only and not saving it in a file:
<style type="text/css">
<?php echo $css->pack(false); ?> 
</style>
```

JsPacker
--------

JsPacker works in a same way as a CssPacker. Enjoy!
