<?php
// these helpers are specifically related to getting json data
// any generalized functions that get json data
use Rmcc\Json;

function getPostsCountFromATerm($type, $tax, $term) {
  global $config;
  $q = new Json($config['json_data']);
  $_types = typeSettingByKey('key', $type, 'items');
  $posts = $q->from('site.content_types.'.$type.'.'.$_types)
  ->where($tax, 'any', $term)
  ->get();
  
  return $posts->count();
}

// getting the TermTitleFromSlug for setPostsTeaseTerms() in PostsModel
// in the case of post teases or posts singulars, we will only ever know the slugs of taxonomy terms that a post has
// this function is to get the title of a term based on its slug.
// the first argument is the content type. e.g 'blog'
// the second argument is the taxonomy name. e.g 'categories'
// the third argument is the taxonomy term slug
function getTermTitleFromSlug($type, $tax, $slug) {
  global $config;
  $q = new Json($config['json_data']);
  $term = $q->from('site.content_types.'.$type.'.taxonomies.'.$tax)
  ->where('slug', '=', $slug)
  ->first();
  return $term['title'];
}

function term_title_from_slug($type, $tax, $slug) {
  global $config;
  $q = new Json($config['json_data']);
  $term = $q->from('site.content_types.'.$type.'.taxonomies.'.$tax)
  ->where('slug', '=', $slug)
  ->first();
  return $term['title'];
}