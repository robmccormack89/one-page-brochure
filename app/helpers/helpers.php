<?php

function pathToURL($path) {
  
  $path = str_replace("C:/xampp/htdocs/robertmccormack.com", "", realpath($path));
  
  //Replace backslashes to slashes if exists, because no URL use backslashes
  $path = str_replace("\\", "/", realpath($path));

  //if the $path does not contain the document root in it, then it is not reachable
  $pos = strpos($path, $_SERVER['DOCUMENT_ROOT']);
  if ($pos === false) return false;

  //just cut the DOCUMENT_ROOT part of the $path
  return substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
  //Note: usually /images is the same with http://somedomain.com/images,
  //      So let's not bother adding domain name here.
}

function showAllParamFix(string $params_string) {
  if (strpos($params_string, 'show_all') !== false) {
    $params = str_replace("show_all=", "show_all", $params_string);
  } else {
    $params = $params_string;
  }
  return $params;
}

function queryParamsExists($params) {
  global $config;
  parse_str($params, $params_array);
  
  // type
  $_type = (array_key_exists('type', $params_array)) ? $params_array['type'] : false;
  if($_type){
    return true;
  }
  
  $_taxonomy = (array_key_exists('taxonomy', $params_array)) ? $params_array['taxonomy'] : false;
  if($_taxonomy){
    return true;
  }
  
  $_order = (array_key_exists('order', $params_array)) ? $params_array['order'] : false;
  if($_order){
    return true;
  }
  
  $_orderby = (array_key_exists('orderby', $params_array)) ? $params_array['orderby'] : false;
  if($_orderby){
    return true;
  }
  
  // taxes
  $taxes = array();
  foreach($config['types'] as $type){
    if(array_key_exists('taxonomies', $type)) {
      foreach($type['taxonomies'] as $tax => $value){
        $taxes[$tax] = $tax;
      }
    }
  }
  $result = array_intersect_key($params_array, $taxes);
  $_taxes = ($result) ? $result : false;
  if($_taxes){
    return true;
  }
  
  // search
  $_search = (array_key_exists('s', $params_array)) ? $params_array['s'] : false;
  if($_search){
    return true;
  }
  
  // year
  $_year = (array_key_exists('year', $params_array) && is_numeric($params_array['year'])) ? $params_array['year'] : false;
  $_month = (array_key_exists('month', $params_array) && is_numeric($params_array['month'])) ? $params_array['month'] : false;
  $_day = (array_key_exists('day', $params_array) && is_numeric($params_array['day'])) ? $params_array['day'] : false;
  if($_year || $_month || $_day) {
    return true;
  }
  
  // name
  $_name = (array_key_exists('name', $params_array)) ? $params_array['name'] : false;
  if($_name){
    return true;
  }
  
  // per_page
  $_per_page = (array_key_exists('per_page', $params_array) && is_numeric($params_array['per_page'])) ? $params_array['per_page'] : false;
  if($_per_page){
    return true;
  }
  
  // paged page
  $_page = (array_key_exists('p', $params_array) && is_numeric($params_array['p'])) ? $params_array['p'] : false;
  if($_page){
    return true;
  }
  
  // paged page
  $_show_all = (array_key_exists('show_all', $params_array)) ? true : false;
  if($_show_all){
    return true;
  }
  
  return false;
}

// string $key, // key of item to check against. e.g 'key' or 'items_key' or 'items_singular'
// string $value, // value of item to check against. e.g 'blog' or 'portfolio'
// string $return_key // key of the value to return. e.g 'items_route' 
function typeSettingByKey($key, $value, $return_key) {
  global $config;
  $data = '';
  foreach ($config['types'] as $type_setting) if ($type_setting[$key] == $value) {
    $data = $type_setting[$return_key];
  }
  return $data;
}

// get taxonomy setting. same as above just added type paramter to get a types taxonomy
function taxSettingByKey($type, $key, $value, $return) {
  global $config;
  $data = '';
  foreach ($config['types'][$type]['taxonomies'] as $tax_setting) if ($tax_setting[$key] == $value) {
    $data = $tax_setting[$return];
  }
  return $data;
}

// remove & replace the slashes in the requested string with hyphens for use as a file name. from given slug
function slugToFilename($slug) {
  // strip character/s from end of string
  $string = rtrim($slug, '/');
  // strip character/s from beginning of string
  $trimmed = ltrim($string, '/');
  
  $data = str_replace('/', '-', $trimmed);
  
  return $data;
}

// get objects in array using key->value
function getInArray(string $needle, array $haystack, string $column){
  $matches = [];
  foreach( $haystack as $item )  if( $item[ $column ] === $needle )  $matches[] = $item;
  return $matches;
}

// minify html, for use with ob_start
function minifyOutput($buffer) {
  $search = array('/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
  $replace = array('>','<','\\1');
  if (preg_match("/\<html/i",$buffer) == 1 && preg_match("/\<\/html\>/i",$buffer) == 1) {
    $buffer = preg_replace($search, $replace, $buffer);
  }
  return $buffer;
}