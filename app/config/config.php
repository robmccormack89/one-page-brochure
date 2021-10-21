<?php
/*
*
* Enable debug mode. Set to false for production
*
*/
$config['enable_debug_mode'] = true;

/*
*
* Choose whether to use http or https protocol
* True results in redirects to https
*
*/
$config['enable_https'] = false;

/*
*
* Set the $root variables, with http/https, depending on whether https or not is enabled & available on the server..
* The $root variable is used in the base of global url variables
*
*/
$root = ($config['enable_https'] && isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'];

/*
*
* Global url variables; urls that are useful throughout the app
*
*/
$config['base_url'] = $root;
$config['current_url'] = $root.$_SERVER['REQUEST_URI'];
$config['current_url_clean'] = strtok($root.$_SERVER['REQUEST_URI'], "?");
$config['url_params'] = $_GET;

/*
*
* Global path variables; paths that are useful throughout the app
*
*/
$config['app_path'] = $_SERVER['DOCUMENT_ROOT'];

/*
*
* Language & Charset
*
*/
$config['language'] = 'en-GB';
$config['charset'] = 'UTF-8';

/*
*
* Set these to enable php caching & minification
*
*/
$config['php_cache'] = true;
$config['php_minify'] = true;

/*
*
* Json data Locations
*
*/
$config['json_data'] = '../public/json/data.min.json';
$config['json_secret'] = '../public/json/_secret.json'; // this is blank
// $config['json_secret'] = '../public/json/secret.json'; // this is real but excluded in gitignore

/*
*
* Set the locations that twig will look for templates in
* First set the base location (relative), then set an array with subsequent folders to look in
*
*/
$config['twig_templates_base_location'] = '../app/views/';
$config['twig_templates_locations'] = array();

/*
*
* Basically, this is the place to 'register' new post types
* These global settings get used in various places throughout the app, particaulary for creating urls for different archives & links etc 
* Non-archived content_type 'page' is built-in & does not need to be added here
* At the moment, all archived content_types need to be registered here
* Also I could try to allow for new, non-archived content_types to also be registered here also, instead of just having pages built-in
* Likewise, I could try to allow for things like non-public content types, non-archived & non-singular types together etc, which would never need to be even routed
*
*/
$config['types'] = array();

return $config;