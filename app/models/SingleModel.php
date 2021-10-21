<?php
namespace Rmcc;

class SingleModel {
  
  /*
  *
  * This class is used to create singular objects like pages, posts, projects etc.
  * Create a new SingleModel object with $type & $slug properties.
  * The $single property of the created object contains all the data for the singular item
  *
  */
  public function __construct() {
    global $_context;
    global $config;
    $this->type = $_context['type']; // e.g 'page' or 'blog' or 'portfolio'
    $this->slug = $_context['slug']; // e.g 'about'. this will usually come from the request unless setting for specific pages
    // the $key property is used for locating the singular data based on the given $type
    $this->key = (isset($config['types'][$this->type]['items'])) ? $config['types'][$this->type]['items'] : null;
    $this->single = $this->getSinglular(); // this property then contains all our data
  }
  
  // get the singular object
  private function getSinglular() {
    global $config;
    
    $q = new Json($config['json_data']);
    $single = $q->from($this->getSinglularLocation())
    ->where('slug', '=', $this->slug)
    ->first();
    
    if($single['type'] !== 'page') {
      $type_key = typeSettingByKey('single', $single['type'], 'key'); // returns 'blog' or 'portfolio'
      $taxonomies = (isset($config['types'][$type_key]['taxes_in_meta'])) ? $config['types'][$type_key]['taxes_in_meta'] : null;
      if($taxonomies) {
        foreach($taxonomies as $tax) {
          if(isset($single[$tax])){
            $terms = $single[$tax];
            foreach ($terms as &$term) {
              $term = array(
                'link' => '/'.$type_key.'/'.$tax.'/'.$term,
                'slug' => $term,
                'title' => term_title_from_slug($type_key, $tax, $term)
              );
            }
            $single[$tax] = null;
            $new_post[$tax] = $terms;
            $single['taxonomies'] = $new_post;
          }
        }
      }
    }
    
    return $single;
  }
  
  // this method sets the location of the data based on $type & $key.
  // this is used mainly to differenciate between archived & non-archived singular objects when getting data
  private function getSinglularLocation() {
    $data = ($this->type == 'page') ? 'site.content_types.'.$this->type : 'site.content_types.'.$this->type.'.'.$this->key;
    return $data;
  }
  
}