<?php
namespace Rmcc;

class QueryTermsModel {
  
  /*
  *
  * Examples of inputs into this class
  *
  */
  protected static $_exampleString = 'taxonomy=category&orderby=title&order=desc&per_page=1&p=1';
  protected static $_exampleArray = array(
    // the query stuff...
    'taxonomy' => 'category', // REQUIRED
    'orderby' => 'title', // title, slug: title is default
    'order' => 'DESC', // ASC DESC: ASC is default
    // the pagination stuff...
    'per_page' => 3,
    'p' => 1,
    // 'show_all' => true
  );
      
  /*
  *
  * Properties based on https://developer.wordpress.org/reference/classes/wp_query/#properties
  *
  */
  public function __construct($args) {
    $this->args = $args;
    $this->query = $this->getString(); // Holds the query string that was passed to the query object
    $this->query_vars = $this->getArray(); // An associative array containing the dissected $query: an array of the query variables and their respective values.
    $this->queried_object = $this->getQueriedObject(); // Can hold information on the requested category, author, post or Page, archive etc.,.
    $this->terms = $this->getTerms(); // Gets filled with the requested posts
    $this->term_count = $this->getTermsPerPage(); // The number of posts being displayed. per page
    $this->found_terms = $this->getTermsCount(); // The total number of posts found matching the current query parameters
    $this->max_num_pages = $this->getTermsMaxPages(); // The total number of pages. Is the result of $found_posts / $posts_per_page
    // $this->init();
  }
  public function init() {}
  
  /*
  *
  * The Queried Object (archive meta)
  *
  */
  private function getQueriedObject() {
    $data = $this->getCollectionMeta();
    return $data;
  }
  // get the term archive meta
  private function getCollectionMeta() {
    global $_context;
    $the_type = $_context['type'];
    global $config;
    if($this->taxonomyKey() && taxSettingByKey($_context['type'], 'single', $this->taxonomyKey(), 'key')) {
      // get taxonomy meta from $config
      $_tax = taxSettingByKey($_context['type'], 'single', $this->taxonomyKey(), 'key');
      $data = $config['types'][$the_type]['taxonomies'][$_tax]['meta'];
    } else {
      // get base meta if no tax key
      $q = new Json($config['json_data']);
      $data = $q->from('site.content_types.'.$_context['type'].'.meta')->get();
    }
    return $data;
  }
  
  /*
  *
  * Set $this->query & $this->query_vars
  *
  */
  private function getString() {
    $data = '';
    if(is_string($this->args)) {
      $data = $this->args;
    }
    return $data;
  }
  private function getArray() {
    
    if(!empty($this->getString())){
      $data = $this->paramsToArgs();
    }
    
    if(is_array($this->args)) {
      $data = $this->args;
    }
    
    return $data;
  }
  
  /*
  *
  * String Params dissection
  *
  */
  private function paramsDissect() {
    $thestring = $this->getString();
    $string_array = parse_str($thestring, $output);
    return $output;
  }
  private function paramsToArgs() {
    
    $new_args_array = array();
    
    if($this->taxonomyParam()){
      $new_args_array['taxonomy'] = $this->taxonomyParam();
    }
    
    if($this->orderbyParam()){
      $new_args_array['orderby'] = $this->orderbyParam();
    }
    
    if($this->orderParam()){
      $new_args_array['order'] = $this->orderParam();
    }

    if($this->perPageParam()){
      $new_args_array['per_page'] = $this->perPageParam();
    }
    
    if($this->pagedParam()){
      $new_args_array['p'] = $this->pagedParam();
    }
    
    if($this->showAllParam()){
      $new_args_array['show_all'] = true;
    }
    
    return $new_args_array;
  }
  
  /*
  *
  * Posts Stuff
  *
  */
  private function getTermsQuery() {
    
    global $_context;
    global $config;
    
    /*
    *
    * 1. set the initial location
    *
    */
    $q = new Json($config['json_data']);
    
    $terms = new Json();
    
    if($this->taxonomyKey() && taxSettingByKey($_context['type'], 'single', $this->taxonomyKey(), 'key')) {
      $_tax = taxSettingByKey($_context['type'], 'single', $this->taxonomyKey(), 'key');
      $terms = $q->from('site.content_types.'.$_context['type'].'.taxonomies.'.$_tax);
    }
    
    if($terms->exists()) {
      if($this->orderbyKey()) {
        $terms = new Json($terms);
        $orderby_query = $this->orderbyKey();
        if($this->orderKey() == 'DESC' || $this->orderKey() == 'desc') {
          $terms = $terms->sortBy($orderby_query, 'desc');
        } else {
          $terms = $terms->sortBy($orderby_query);
        }
      } else {
        $terms = new Json($terms);
        $orderby_query = $this->orderbyKey();
        if($this->orderKey() == 'DESC' || $this->orderKey() == 'desc') {
          $terms = $terms->sortBy('title', 'desc');
        } else {
          $terms = $terms->sortBy('title');
        }
      }
    }

    $terms = $terms->get();
    $count = $terms->count();
    
    /*
    *
    * If is paged, then paged stuff....
    *
    */
    if($this->isPaged() && $terms->exists()){
      $terms = new Json($terms);
      $paged_terms = $terms->chunk($this->getTermsPerPage());
      $terms = $this->getPagedTerms($paged_terms); // returns an array
    }
    
    /*
    *
    * **. This is the last stage before return.
    *
    * If the $posts exists now as a Json object, whether filled or empty or null,
    * we should convert it to a normal array using Json->toArray(),
    * and then get the $count off of that using php count.
    *
    * This is more reliable as the posts(or lack of) can be checked on the other side as a standard array rather than a Json object,
    * which is harder to check against. e.g: if($posts) or if(count($posts) > 2) etc....
    *
    */
    if(is_object($terms)) $terms = $terms->toArray(); // If $posts is a Json object, convert it to an array
    
    // oh yeah, if $posts count is more than 0, set the post tease data to the $posts....
    $terms = ($count > 0) ? $this->setTermTeaseData($terms) : null;
    
    /*
    *
    * Finally we return the $posts & the $count variables.
    * $posts should be an array, either filled with posts or empty,
    * and $count should be an integer, either 0 or more...
    *
    */
    return array('posts' => $terms, 'count' => $count);
  }
  private function getTermsCount() {
    $data = $this->getTermsQuery();
    return $data['count'];
  }
  public function getTerms() {
    $data = $this->getTermsQuery();
    return $data['posts'];
  }
  
  /*
  *
  * Posts Tease Stuff
  *
  */
  private function setTermTeaseData($terms) {
    $linked_terms = $this->setTermTeaseLinkData($terms);
    return $linked_terms;
  }
  private function setTermTeaseLinkData($terms) {
    foreach ($terms as $term) {
      $term['link'] = '/'.$term['type'].'/'.$term['tax'].'/'.$term['slug'];
      $term['excerpt'] = $term['description'];
      $term['posts_count'] = getPostsCountFromATerm($term['type'], $term['tax'], $term['slug']);
      $data[] = $term;
    }
    return $data;
  }
  
  
  
  /*
  *
  * Paged stuff
  *
  */
  private function isPaged() {
    if(!$this->showAllKey()) return true;
  }
  private function getPagedTerms($terms) {
    $data = false;
    
    $p = $this->pagedKey();
  
    $offset = $p ? $p - 1 : 0;
    
    if (!isset($terms[$offset])) $terms[$offset] = null;
  
    $data = $terms[$offset];
    
    return $data;
  }
  
  /*
  *
  * Various string params to check for. If string, string -> array
  *
  */
  private function taxonomyParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('taxonomy', $string_args)) return $string_args['taxonomy'];
    return false;
  }
  private function orderbyParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('orderby', $string_args)) return $string_args['orderby'];
    return false;
  }
  private function orderParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('order', $string_args)) return $string_args['order'];
    return false;
  }
  private function perPageParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('per_page', $string_args)) return $string_args['per_page'];
    return false;
  }
  private function pagedParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('p', $string_args)) return $string_args['p'];
    return false;
  }
  private function showAllParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('show_all', $string_args)) return true;
    return false;
  }
  
  /*
  *
  * Various keys to check for. If array
  *
  */
  private function taxonomyKey() {
    if($this->query_vars && array_key_exists('taxonomy', $this->query_vars)) return $this->query_vars['taxonomy'];
    return false;
  }
  private function orderbyKey() {
    if($this->query_vars && array_key_exists('orderby', $this->query_vars)) return $this->query_vars['orderby'];
    return false;
  }
  private function orderKey() {
    if($this->query_vars && array_key_exists('order', $this->query_vars)) return $this->query_vars['order'];
    return false;
  }
  private function perPageKey() {
    if($this->query_vars && array_key_exists('per_page', $this->query_vars)) return $this->query_vars['per_page'];
    return false;
  }
  private function pagedKey() {
    if($this->query_vars && array_key_exists('p', $this->query_vars)) return $this->query_vars['p'];
    return false;
  }
  private function showAllKey() {
    if($this->query_vars && array_key_exists('show_all', $this->query_vars)) return $this->query_vars['show_all'];
    return false;
  }
  
  /*
  *
  * Properties Configuration
  *
  */
  private function getTermsPerPage() {
    $per_page = $this->perPageKey() ? $this->perPageKey() : 4;
    return $per_page;
  }
  private function getTermsMaxPages() {
    $max_pages = $this->found_terms / $this->term_count;
    return ceil($max_pages);
  }
  
}