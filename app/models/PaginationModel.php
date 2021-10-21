<?php
namespace Rmcc;

class PaginationModel {
  
  public function __construct(int $count) {
    $this->count = $count;
  }
  
  // if $count is greater than $posts_per_page, return the pagination data, else return null
  public function getPagination() {
    $data = $this->setPaginationData();
    return $data;
  }
  
  protected function setPaginationLink($new_part) {
    $current_url_parsed = parse_url($_SERVER['REQUEST_URI']);
    if(isset($current_url_parsed['query'])){
      parse_str($current_url_parsed['query'], $queryArray);
      $queryArray['p'] = $new_part;
      $newQueryStr = http_build_query($queryArray);
      $newQueryStr = str_replace("%2C", ",", $newQueryStr);
      $url = '?'.$newQueryStr;
    } else {
      $url = '?p='.$new_part;
    }
    return $url;
  }
  
  // set the pagination data
  protected function setPaginationData() {
    
    // step 1 - setup. data is blank. paged is the archive paging route. if req page is blank, set it to 1
    $data[] = '';
    
    // parse the url
    $current_url_parsed = parse_url($_SERVER['REQUEST_URI']);
    
    // step 2 - if has next|prev, set the links data, available at .next & .prev
    if ($this->hasNextPage()) {
      $nexturl = $this->setPaginationLink(($GLOBALS['_context']['page'] + 1));
      $data['next'] = $nexturl;
    };

    if ($this->hasPrevPage()) {
      $prevurl = $this->setPaginationLink(($GLOBALS['_context']['page'] - 1));
      $data['prev'] = $prevurl;
    };
    
    // step 3 - all posts count divided by posts per page, rounded up to the highest integer
    $rounded = ceil($this->count / $GLOBALS['_context']['per_page']);
    
    // step 4 - set the pagination pata. will be available at .pages
    $output = [];
    for ($i=0; $i < $rounded; $i++) {
    
      $offset = $i+1;
      
      // set the active class if req page matches
      $class = "not-active";
      if ($offset == $GLOBALS['_context']['page']) $class = "uk-active";
      
      $offseturl = $this->setPaginationLink($offset);
      // setting the data
      $output[] = array(
        'link' => $offseturl, 
        'title' => $offset,
        'class' => $class,
      );
    }
    
    // available at .pages
    $data['pages'] = $output;
    
    // step 5 - results count html
    $page_offset = $GLOBALS['_context']['page'] - 1;
    $addifier = $page_offset * $GLOBALS['_context']['per_page'];
    $result_start = $addifier + 1;
    $result_end = $GLOBALS['_context']['page'] * $GLOBALS['_context']['per_page'];
    
    if($result_end > $this->count) {
      $result_end = $this->count;
    }
    
    if($GLOBALS['_context']['per_page'] == 1) {
      $results_text = 'Showing Page '.$GLOBALS['_context']['page'].' of '.$this->count.' results';
    }
    
    if($GLOBALS['_context']['per_page'] > 1 && $GLOBALS['_context']['per_page'] < $this->count && $GLOBALS['_context']['paged'] == TRUE) {
      if($result_end > $result_start) {
        $results_text = 'Showing '.$result_start.'-'.$result_end.' of '.$this->count.' results';
      } else {
        $results_text = 'Showing '.$result_end.' of '.$this->count.' results';
      }
    }
    
    if($GLOBALS['_context']['per_page'] >= $this->count || $GLOBALS['_context']['paged'] == FALSE) {
      $results_text = 'Showing all '.$this->count.' results';
    }
    
    $data['results'] = $results_text;
    
    $data['results_count'] = $this->count;
    
    // step 6 - return it all
    return $data;
  }
  
  // conditionals for pagination
  protected function hasNextPage() {
    if(!$GLOBALS['_context']['paged'] || $GLOBALS['_context']['page'] >= $this->count / $GLOBALS['_context']['per_page']) {
      return false;
    }
    return true;
  }  
  protected function hasPrevPage() {
    if(!$GLOBALS['_context']['paged'] || $GLOBALS['_context']['page'] <= 1) {
      return false;
    }
    return true;
  }
  
}