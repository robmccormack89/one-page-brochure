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
$config['php_cache'] = false;
$config['php_minify'] = false;

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

/*
*
* Blog registration
*
*/

$config['types']['shop'] = array(
  'key'  => 'shop', // used as main key/main archive url
  'items' => 'products', // used as product items key/in singular urls
  'single' => 'product', // used as singular key such as in queries
  'per_page' => 7,
  'meta' => array(
    'title'  => 'My Shop', // MainIndexArchive title
    'description' => 'Something more descriptive goes here...', // MainIndexArchive description
    'meta_title' => '',
    'meta_description' => '',
  ),
);
$config['types']['shop']['taxes_in_meta'] = array('categories', 'tags');
$config['types']['shop']['taxonomies'] = array();
$config['types']['shop']['taxonomies']['categories'] = array(
  'key'  => 'categories', // used as main key/archive url
  'single'  => 'category', // used as singular key such as in queries
  'meta' => array(
    'title'  => 'Categories', // CollectionArchive title
    'description' => 'Handshake release assets validation metrics first mover advantage ownership prototype', // CollectionArchive description
    'meta_title' => '',
    'meta_description' => '',
  ),
);
$config['types']['shop']['taxonomies']['tags'] = array(
  'key'  => 'tags',
  'single'  => 'tag',
  'meta' => array(
    'title'  => 'Tags',
    'description' => 'Handshake release assets validation metrics first mover advantage ownership prototype',
    'meta_title' => '',
    'meta_description' => '',
  ),
);

$config['types']['the_hero_banners'] = array(
  'key'  => 'the_hero_banners', // used as main key/main archive url
  'items' => 'hero_banners', // used as product items key/in singular urls
  'single' => 'hero_banner', // used as singular key such as in queries
  'per_page' => 7,
  'meta' => array(
    'title'  => 'My Shop', // MainIndexArchive title
    'description' => 'Something more descriptive goes here...', // MainIndexArchive description
    'meta_title' => '',
    'meta_description' => '',
  ),
);

$config['types']['the_highlight_blocks'] = array(
  'key'  => 'the_highlight_blocks', // used as main key/main archive url
  'items' => 'highlight_blocks', // used as product items key/in singular urls
  'single' => 'highlight_block', // used as singular key such as in queries
  'per_page' => 7,
  'meta' => array(
    'title'  => 'My Shop', // MainIndexArchive title
    'description' => 'Something more descriptive goes here...', // MainIndexArchive description
    'meta_title' => '',
    'meta_description' => '',
  ),
);

$config['types']['the_banner_blocks'] = array(
  'key'  => 'the_banner_blocks', // used as main key/main archive url
  'items' => 'banner_blocks', // used as product items key/in singular urls
  'single' => 'banner_block', // used as singular key such as in queries
  'per_page' => 7,
  'meta' => array(
    'title'  => 'My Shop', // MainIndexArchive title
    'description' => 'Something more descriptive goes here...', // MainIndexArchive description
    'meta_title' => '',
    'meta_description' => '',
  ),
);

return $config;