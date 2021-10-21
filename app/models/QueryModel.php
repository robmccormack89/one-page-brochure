<?php
namespace Rmcc;

class QueryModel {
  
  /*
  *
  * Examples of inputs into this class
  *
  */
  protected static $_exampleString = 'type=post&categories=news,media&tags=css,javascript&s=lorem&year=2021&month=4&day=4&name=lorem&per_page=3&p=1&show_all';
  protected static $_exampleArray = array(
    // working. takes string & content type singular label (post, project etc...)
    'type' => 'post',
    
    // working. takes array with relation key & sub-arrays
    'tax_query' => array(
      // relation is required for now as there are checks based the iteration of the first array in this sequence, which should be 2nd when relation exists
      'relation' => 'AND', // working: 'AND', 'OR'. Deaults to 'AND'.
      array(
        'taxonomy' => 'category', // working. takes taxonomy string
        'terms' => array('news', 'media'), // working. takes array
        'operator' => 'AND' // 'AND', 'IN', 'NOT IN'. 'IN' is default. AND means posts must have all terms in the array. In means just one.
      ),
      array(
        'taxonomy' => 'tag',
        'terms' => array('css', 'javascript'),
        'operator' => 'AND'
      ),
    ),
    
    // working. takes string & searches in title/excerpt for matches
    's' => 'lorem',
    
    // working. takes array with date arguments.
    'date' => array(
      'year'  => 2021,
      'month' => 4,
      'day'   => 4,
    ),
    
    // working. takes string & searches in slugs for matches
    'name' => 'sed',
    
    'orderby' => 'title', // title, slug: title is default
    'order' => 'DESC', // ASC DESC: ASC is default
    
    // the pagination stuff. seems to be working...
    'per_page' => 3,
    'p' => 1,
    'show_all' => true
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
    $this->posts = $this->getPosts(); // Gets filled with the requested posts
    $this->post_count = $this->getPostsPerPage(); // The number of posts being displayed. per page
    $this->found_posts = $this->getPostsCount(); // The total number of posts found matching the current query parameters
    $this->max_num_pages = $this->getPostsMaxPages(); // The total number of pages. Is the result of $found_posts / $posts_per_page
    // $this->init();
  }
  public function init() {
    print_r($this->found_posts);
  }
  
  /*
  *
  * The Queried Object (archive meta)
  *
  */
  private function getQueriedObject() {
    global $_context;
    $data = $this->getBaseMeta();
    if(isset($_context['type']) && !isset($_context['term'])) $data = $this->getArchiveMeta();
    if(isset($_context['term'])) $data = $this->getTermMeta();
    if(!isset($_context['type']) && $this->typeParam()){
      $data['title'] = 'Query: '.$this->typeParam();
    }
    return $data;
  }
  // Site Meta
  private function getBaseMeta() {
    global $config;
    $q = new Json($config['json_data']);
    $data = $q->from('site.meta')->get();
    return $data;
  }
  // Type Meta
  private function getArchiveMeta() {
    global $_context;
    $the_type = $_context['type'];
    global $config;
    $data = $config['types'][$the_type]['meta'];
    return $data;
  }
  // Term Meta
  private function getTermMeta() {
    global $_context;
    $the_type = $_context['type'];
    global $config;
    if($_context['term']) {
      $q = new Json($config['json_data']);
      $data = $q->from('site.content_types.'.$_context['type'].'.taxonomies.'.$_context['tax'])
      ->where('slug', '=', $_context['term'])->first();
    } else {
      $data = $config['types'][$the_type]['meta'];
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
    $thestring = $this->query;
    $string_array = parse_str($thestring, $output);
    return $output;
  }
  private function paramsToArgs() {
    
    $new_args_array = array();
    
    if($this->typeParam()){
      $new_args_array['type'] = $this->typeParam();
    }
    
    if($this->typeParam()){
      if($this->taxParams()){
        $new_args_array['tax_query']['relation'] = 'AND';
        foreach($this->taxParams() as $tax => $value){
          $type = typeSettingByKey('single', $this->typeParam(), 'key');
          $new_args_array['tax_query'][] = array(
            'taxonomy' => taxSettingByKey($type, 'key', $tax, 'single'),
            'terms' => explode(',', $value),
            'operator' => 'AND'
          );
        }
      }
    }
    
    if($this->orderbyParam()){
      $new_args_array['orderby'] = $this->orderbyParam();
    }
    
    if($this->orderParam()){
      $new_args_array['order'] = $this->orderParam();
    }
    
    if($this->searchParam()){
      $new_args_array['s'] = $this->searchParam();
    }
    
    if($this->yearParam() || $this->monthParam() || $this->dayParam()){
      if($this->yearParam()){
        $new_args_array['date']['year'] = $this->yearParam();
      }
      if($this->monthParam()){
        $new_args_array['date']['month'] = $this->monthParam();
      }
      if($this->dayParam()){
        $new_args_array['date']['day'] = $this->dayParam();
      }
    }
    
    if($this->nameParam()){
      $new_args_array['name'] = $this->nameParam();
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
  private function getPostsQuery() {
    global $config;
    /*
    *
    * 1. set the initial location
    *
    */
    $q = new Json($config['json_data']);
    
    /*
    *
    * 2. get all single posts from all content types
    * This serves as our start query, minus all other queries, we return all posts
    * $posts is set with the posts, and per each modifying query below, we query off $posts & then reset it with the new results
    *
    */
    $content_types = $q->find('site.content_types')->toArray(); // all content types as an array
    $new_content_types = array(); // creating new array by looping thru each of the content types
    foreach($content_types as $key => $value) {
      $types_array = $q->copy()->reset()->find('site.content_types.'.$key.'.'.typeSettingByKey('key', $key, 'items'))->toArray();
      if($key == 'page') $types_array = $q->copy()->reset()->find('site.content_types.'.$key)->toArray();
      $new_content_types[] = $types_array;
    }
    //  finally we merge all the posts from the different content types together in a new Json object using Json->collect() & php's array_merge()
    $posts = (new Json())->collect(array_merge(...$new_content_types));
    
    /*
    *
    * If type key exists, we need to modifiy $posts according to that
    *
    */
    if($this->typeKey()) {
      
      if($this->typeKey() == 'page') {
        $_location = 'site.content_types.page'; // if given type is page, set $_location based on this. 
      } else {
        // else set $_location based on the registered content types using the given type
        $type_plural = typeSettingByKey('single', $this->typeKey(), 'items'); // returns 'posts' or 'projects', or null...
        $type_archive = typeSettingByKey('single', $this->typeKey(), 'key'); // returns 'blog' or 'portfolio', or null...
        if($type_plural && $type_archive) $_location = 'site.content_types.'.$type_archive.'.'.$type_plural;
      };
    
      // if $_location indeed exists, query posts using it, else set $posts as empty Json object
      // if the initial type results in a faulty location, we want the result to be empty so this makes sense
      $posts = ($_location) ? $q->copy()->reset()->from($_location) : new Json();
    }
    
    /*
    *
    * If search key exists & $posts also exists....
    *
    * remember $posts is still in a Json obj at this point, so $posts->exists() works
    * we only want to continue the query with $posts existing & in the right format
    * if $posts doesnt exist, the query is not processed then. which makes sense
    *
    */
    if($this->searchKey() && $posts->exists()) {
      // $posts = $posts->copy(); // this seems not working or bugged here...
      $posts = new Json($posts); // create a new json object, this seems working fine...
      $search_query = $this->searchKey();
      $posts = $posts
      ->where(function($query) use ($search_query) {
        $query->where('excerpt', 'truematch', '(?i)'.$search_query);
        $query->orWhere('title', 'truematch', '(?i)'.$search_query);
      });
    }

    /*
    *
    * If date key/s exists & $posts also exists....
    *
    */
    if($this->dateKey() && $posts->exists()) {
      
      $posts = new Json($posts); // create the new Json posts object to query against with the previous posts
    
      $_year = $this->yearKey();
      $_month = $this->monthKey();
      $_day = $this->dayKey();
    
      // year
      if($_year){
        $posts = $posts
        ->where(function($query) use ($_year) {
          $query->where('date_time', 'year', $_year);
        });
      }
    
      // month
      if($_month){
        $posts = $posts
        ->where(function($query) use ($_month) {
          $query->where('date_time', 'month', $_month);
        });
      }
    
      // day
      if($_day){
        $posts = $posts
        ->where(function($query) use ($_day) {
          $query->where('date_time', 'day', $_day);
        });
      }
    
    }
    
    /*
    *
    * If tax query key/s exists & $posts also exists....
    *
    */
    if($this->taxQueryKey() && $posts->exists()) {
      if($this->relationKey() == 'OR') {
    
        $taxes_i = 0;
        foreach($this->taxQueryKey() as $key => $value){
    
          if (is_array($value)) {
            $taxes_iterator = ++$taxes_i;
            if(array_key_exists('taxonomy', $value)) $tax = taxSettingByKey($type_archive, 'single', $value['taxonomy'], 'key');
            if(array_key_exists('terms', $value)) {
              if(array_key_exists('operator', $value)) $op = $value['operator'];  
    
              $terms = $value['terms'];
    
              if ($taxes_iterator == 1) {
    
                $posts = $posts
                ->where(function($query) use ($terms, $tax, $op) {
                  $first_query_i = 0;
                  foreach($terms as $term){
                    $first_query_iterator = ++$first_query_i;
                    switch ($op) {
                      case 'AND':
                        $query->where($tax, 'any', $term); 
                        break;
                      case 'NOT IN':
                        $query->where($tax, 'notany', $term);
                        break;
                      default:
                        $first_query_iterator == 1 ? $query->where($tax, 'any', $term) : $query->orWhere($tax, 'any', $term);
                    }
                  }
                });
    
              } else {
    
                $new_posts = $posts;
                $posts = $new_posts
                ->orWhere(function($query) use ($terms, $tax, $op) {
                  $next_query_i = 0;
                  foreach($terms as $term){
                    $next_query_iterator = ++$next_query_i;
                    switch ($op) {
                      case 'AND':
                        $query->where($tax, 'any', $term);
                        break;
                      case 'NOT IN':
                        $query->where($tax, 'notany', $term); 
                        break;
                      default:
                        $first_query_iterator == 1 ? $query->where($tax, 'any', $term) : $query->orWhere($tax, 'any', $term);
                    }
                  }
                });
    
              }
            }
          }
        }
    
      } else {
    
        foreach ($this->taxQueryKey() as $key => $value) if (is_array($value)) {
          if(array_key_exists('taxonomy', $value)) $tax = taxSettingByKey($type_archive, 'single', $value['taxonomy'], 'key');
          if(array_key_exists('terms', $value)) {
            if(array_key_exists('operator', $value)) $op = $value['operator'];
    
            $posts = new Json($posts);
            $terms = $value['terms'];
    
            if(is_array($value['terms'])){
              $posts = $posts
              ->where(function($query) use ($terms, $tax, $op) {
                $first_query_i = 0;
                foreach($terms as $term){
                  $first_query_iterator = ++$first_query_i;
                  switch ($op) {
                    case 'AND':
                      $query->where($tax, 'any', $term);
                      break;
                    case 'NOT IN':
                      $query->where($tax, 'notany', $term); 
                      break;
                    default:
                      $first_query_iterator == 1 ? $query->where($tax, 'any', $term) : $query->orWhere($tax, 'any', $term);
                  }
                }
              })
              ->get();
            } else {
              $posts = $first_query
              ->where(function($query) use ($terms, $tax) {
                $query->where($tax, 'any', $terms); 
              })
              ->get();
            }
    
          }
        }
    
      }
    }
    
    /*
    *
    * If name key/s exists & $posts also exists....
    *
    */
    if($this->nameKey() && $posts->exists()) {
      $posts = new Json($posts);
      $name_query = $this->nameKey();
      $posts = $posts
      ->where(function($query) use ($name_query) {
        $query->where('slug', 'truematch', '(?i)'.$name_query);
      });
    }
    
    // ordering
    if($posts->exists()) {
      if($this->orderbyKey()) {
        $posts = new Json($posts);
        $orderby_query = $this->orderbyKey();
        if($this->orderKey() == 'DESC' || $this->orderKey() == 'desc') {
          $posts = $posts->sortBy($orderby_query, 'desc');
        } else {
          $posts = $posts->sortBy($orderby_query);
        }
      } else {
        $posts = new Json($posts);
        $orderby_query = $this->orderbyKey();
        if($this->orderKey() == 'DESC' || $this->orderKey() == 'desc') {
          $posts = $posts->sortBy('title', 'desc');
        } elseif($this->orderKey() == 'ASC' || $this->orderKey() == 'asc') {
          $posts = $posts->sortBy('title');
        } else {
          $posts = $posts; // default when no orderby or no order. appears as entered into the json
        }
      }
    }
    
    /*
    *
    * we need to get() the $posts BEFORE paged stuff
    * we need to set the latest $count BEFORE paged stuff
    *
    */
    $posts = $posts->get();
    $count = $posts->count();
    
    /*
    *
    * If is paged, then paged stuff....
    *
    */
    if($this->isPaged() && $posts->exists()){
      $posts = new Json($posts);
      $paged_posts = $posts->chunk($this->getPostsPerPage());
      $posts = $this->getPagedPosts($paged_posts); // returns an array
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
    if(is_object($posts)) $posts = $posts->toArray(); // If $posts is a Json object, convert it to an array
    
    // oh yeah, if $posts count is more than 0, set the post tease data to the $posts....
    $posts = ($count > 0) ? $this->setPostsTeaseData($posts) : null;
    
    /*
    *
    * Finally we return the $posts & the $count variables.
    * $posts should be an array, either filled with posts or empty,
    * and $count should be an integer, either 0 or more...
    *
    */
    return array('posts' => $posts, 'count' => $count);
  }
  private function getPostsCount() {
    $data = $this->getPostsQuery();
    return $data['count'];
  }
  public function getPosts() {
    $data = $this->getPostsQuery();
    return $data['posts'];
  }
  
  /*
  *
  * Posts Tease Stuff
  *
  */
  private function setPostsTeaseData($posts) {
    $linked_posts = $this->setPostTeaseLinkData($posts);
    $termed_posts = $this->setPostTeaseTermData($linked_posts);
    return $termed_posts;
  }
  private function setPostTeaseLinkData($posts) {
    $data = null;
    if($posts){
      foreach ($posts as $post) {
        if(isset($post['type']) && $post['type'] == 'page'){
          if($post['slug'] == 'index') {
            $post['link'] = '/';
          } else {
            $post['link'] = '/'.$post['slug'];
          }
        } else {
          $post['link'] = '/'.typeSettingByKey('single', $post['type'], 'key').'/'.typeSettingByKey('single', $post['type'], 'items').'/'.$post['slug'];
        }
        $data[] = $post;
      }
    }
    return $data;
  }
  private function setPostTeaseTermData($posts) {
    global $config;
    $data = null;
    if($posts){
      foreach ($posts as $post) {
        if($post['type'] !== 'page') {
          $type_key = typeSettingByKey('single', $post['type'], 'key'); // returns 'blog' or 'portfolio'
          $taxonomies = (isset($config['types'][$type_key]['taxes_in_meta'])) ? $config['types'][$type_key]['taxes_in_meta'] : null;
          if($taxonomies) {
            foreach($taxonomies as $tax) {
              if(isset($post[$tax])){
                $terms = $post[$tax];
                foreach ($terms as &$term) {
                  $term = array(
                    'link' => '/'.$type_key.'/'.$tax.'/'.$term,
                    'slug' => $term,
                    'title' => term_title_from_slug($type_key, $tax, $term)
                  );
                }
                $post[$tax] = null;
                $new_posts[$tax] = $terms;
                $post['taxonomies'] = $new_posts;
              } else {
                $post['taxonomies'] = null;
              }
            }
          }
        } else {
          $post = $post;
        }
        $data[] = $post;
      }
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
  private function getPagedPosts($posts) {
    $data = false;
    
    $p = $this->pagedKey();
  
    $offset = $p ? $p - 1 : 0;
    
    if (!isset($posts[$offset])) $posts[$offset] = null;
  
    $data = $posts[$offset];
    
    return $data;
  }
  
  /*
  *
  * Various string params to check for. If string, string -> array
  *
  */
  private function typeParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('type', $string_args)) return $string_args['type'];
    return false;
  }
  private function taxParams() {
    global $config;
    $string_args = $this->paramsDissect();
    $taxes = array();
    foreach($config['types'] as $type){
      if(array_key_exists('taxonomies', $type)) {
        foreach($type['taxonomies'] as $tax => $value){
          $taxes[$tax] = $tax;
        }
      }
    }
    $result = array_intersect_key($string_args, $taxes);
    if($result) return $result;
    return false;
  }
  private function searchParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('s', $string_args)) return $string_args['s'];
    return false;
  }
  private function nameParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('name', $string_args)) return $string_args['name'];
    return false;
  }
  private function yearParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('year', $string_args)) return $string_args['year'];
    return false;
  }
  private function monthParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('month', $string_args)) return $string_args['month'];
    return false;
  }
  private function dayParam() {
    $string_args = $this->paramsDissect();
    if(array_key_exists('day', $string_args)) return $string_args['day'];
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
  private function typeKey() {
    if($this->query_vars && array_key_exists('type', $this->query_vars)) return $this->query_vars['type'];
    return false;
  }
  private function taxQueryKey() {
    if($this->query_vars && array_key_exists('tax_query', $this->query_vars) && $this->typeKey()) return $this->query_vars['tax_query'];
    return false;
  }
  private function relationKey() {
    $tax_query_array = $this->taxQueryKey();
    if($tax_query_array){
      if(array_key_exists('relation', $tax_query_array)) {
        return $tax_query_array['relation'];
      }
    }
    return false;
  }
  private function searchKey() {
    if($this->query_vars && array_key_exists('s', $this->query_vars)) return $this->query_vars['s'];
    return false;
  }
  private function nameKey() {
    if($this->query_vars && array_key_exists('name', $this->query_vars)) return $this->query_vars['name'];
    return false;
  }
  private function dateKey() {
    if($this->query_vars && array_key_exists('date', $this->query_vars)) return $this->query_vars['date'];
    return false;
  }
  private function yearKey() {
    $date = $this->dateKey();
    if(array_key_exists('year', $date)) return $date['year'];
    return false;
  }
  private function monthKey() {
    $date = $this->dateKey();
    if(array_key_exists('month', $date)) return $date['month'];
    return false;
  }
  private function dayKey() {
    $date = $this->dateKey();
    if(array_key_exists('day', $date)) return $date['day'];
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
  private function getPostsPerPage() {
    $per_page = $this->perPageKey() ? $this->perPageKey() : 4;
    return $per_page;
  }
  private function getPostsMaxPages() {
    $max_pages = $this->found_posts / $this->post_count;
    return ceil($max_pages);
  }
  
}