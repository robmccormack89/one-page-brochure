<?php
namespace Rmcc;

class ArchiveController extends CoreController {
  
  public function __construct($type = null, $posts_per_page = 7) {
    parent::__construct();
    $this->type = $type;
    $this->posts_per_page = $posts_per_page;
    $this->posts_per_page = typeSettingByKey('key', $this->type, 'per_page');
    $this->paged = $this->setPaged();
    $this->init(); // init some globals
  }
  
  private function setPaged() {
    if($this->posts_per_page == false) return false;
    return true;
  }
  
  private function init() {
    global $_context;
    $_context = array(
      'archive' => 'Archive',
      'type' => $this->type,
      'page' => 1,
      'per_page' => $this->posts_per_page,
      'paged' => $this->paged,
    );
  }
  
  public function querySite($params) {
    
    global $_context;

    /*
    *
    * set the _context archive
    *
    */
    $_context['archive'] = 'SiteQuery';
    
    /*
    *
    * parse the params string into an array (the params have been filtered for relevant ones only in routes)
    *
    */
    parse_str($params, $params_array);
    
    /*
    *
    * set the type based on the type given in routes.
    * this will be fed into the query string; type= filtering is not supposed to be used on MainIndexArchives as they are already a 'type'
    *
    */
    // $params_array['type'] = typeSettingByKey('key', $this->type, 'single');
    
    /*
    *
    * set the pagination values in the params array
    *
    */
    if(isset($params_array['p'])) $_context['page'] = $params_array['p'];
    if(isset($params_array['show_all'])) $_context['paged'] = false;
    if(isset($params_array['per_page'])) $_context['per_page'] = $params_array['per_page'];
    if(!isset($params_array['per_page'])) $params_array['per_page'] = $_context['per_page'];
    
    /*
    *
    * rebuild the params array into a query string
    *
    */
    $pre_params = http_build_query($params_array);
    
    /*
    *
    * comma-separated items in the string: commas get changed into '%2C' after http_build_query
    * this changes fixes this.
    * cosmetic really
    *
    */
    $pre_params = str_replace("%2C", ",", $pre_params);
    
    /*
    *
    * when show_all does't have a value, it ends up with an = sign at the end after http_build_query
    * this code just removes the = from the show_all param
    * cosmetic really
    *
    */
    $new_params = showAllParamFix($pre_params);
    
    /*
    *
    * _context->string_params is what the query will be running off. so we set it here to out rebuilt string above
    *
    */
    $_context['string_params'] = $new_params;
    
    /*
    *
    * finally, set the archive obj context for twig to render
    *
    */
    $context['archive'] = (new ArchiveModel())->getQueriedArchive();
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function queryMainIndexArchive($params) {
    
    global $_context;

    /*
    *
    * set the _context archive
    *
    */
    $_context['archive'] = 'MainIndexArchive';
    
    /*
    *
    * parse the params string into an array (the params have been filtered for relevant ones only in routes)
    *
    */
    parse_str($params, $params_array);
    
    /*
    *
    * set the type based on the type given in routes.
    * this will be fed into the query string; type= filtering is not supposed to be used on MainIndexArchives as they are already a 'type'
    *
    */
    $params_array['type'] = typeSettingByKey('key', $this->type, 'single');
    
    /*
    *
    * set the pagination values in the params array
    *
    */
    if(isset($params_array['p'])) $_context['page'] = $params_array['p'];
    if(isset($params_array['show_all'])) $_context['paged'] = false;
    if(isset($params_array['per_page'])) $_context['per_page'] = $params_array['per_page'];
    if(!isset($params_array['per_page'])) $params_array['per_page'] = $_context['per_page'];
    
    /*
    *
    * rebuild the params array into a query string
    *
    */
    $pre_params = http_build_query($params_array);
    
    /*
    *
    * comma-separated items in the string: commas get changed into '%2C' after http_build_query
    * this changes fixes this.
    * cosmetic really
    *
    */
    $pre_params = str_replace("%2C", ",", $pre_params);
    
    /*
    *
    * when show_all does't have a value, it ends up with an = sign at the end after http_build_query
    * this code just removes the = from the show_all param
    * cosmetic really
    *
    */
    $new_params = showAllParamFix($pre_params);
    
    /*
    *
    * _context->string_params is what the query will be running off. so we set it here to out rebuilt string above
    *
    */
    $_context['string_params'] = $new_params;
    
    /*
    *
    * finally, set the archive obj context for twig to render
    *
    */
    $context['archive'] = (new ArchiveModel())->getQueriedArchive();
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function getMainIndexArchive() {
    global $_context;
    // set some global variables related to the current context
    $_context['archive'] = 'MainIndexArchive';
    // set the archive obj context for twig to render
    $context['archive'] = (new ArchiveModel())->getArchive();
    
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function queryTaxTermArchive($params, $tax, $term) {
    
    global $_context;
    
    /*
    *
    * set the _context archive
    *
    */
    $_context['archive'] = 'TaxTermArchive';
    
    /*
    *
    * parse the params string into an array (the params have been filtered for relevant ones only in routes)
    *
    */
    parse_str($params, $params_array);
    
    /*
    *
    * set the type & tax => term based on the data given in routes.
    * this will be fed into the query string...
    *
    */
    $params_array['type'] = typeSettingByKey('key', $this->type, 'single');
    $params_array[$tax] = $term;
    
    /*
    *
    * add tax & term to the _context array
    *
    */
    $_context['tax'] = $tax;
    $_context['term'] = $term;
    
    /*
    *
    * set the pagination values in the params array
    *
    */
    if(isset($params_array['p'])) $_context['page'] = $params_array['p'];
    if(isset($params_array['show_all'])) $_context['paged'] = false;
    if(isset($params_array['per_page'])) $_context['per_page'] = $params_array['per_page'];
    if(!isset($params_array['per_page'])) $params_array['per_page'] = $_context['per_page'];
    
    /*
    *
    * rebuild the params array into a query string
    *
    */
    $pre_params = http_build_query($params_array);
    
    /*
    *
    * comma-separated items in the string: commas get changed into '%2C' after http_build_query
    * this changes fixes this.
    * cosmetic really
    *
    */
    $pre_params = str_replace("%2C", ",", $pre_params);
    
    /*
    *
    * when show_all does't have a value, it ends up with an = sign at the end after http_build_query
    * this code just removes the = from the show_all param
    * cosmetic really
    *
    */
    $new_params = showAllParamFix($pre_params);
    
    
    /*
    *
    * _context->string_params is what the query will be running off. so we set it here to out rebuilt string above
    *
    */
    $_context['string_params'] = $new_params;
    
    // set the archive obj context for twig to render
    $context['archive'] = (new ArchiveModel())->getQueriedArchive();
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function getTaxTermArchive($tax, $term) {
    global $_context;
    // set some global variables related to the current context
    $_context['archive'] = 'TaxTermArchive';
    $_context['tax'] = $tax;
    $_context['term'] = $term;
    // set the archive obj context for twig to render
    $context['archive'] = (new ArchiveModel())->getTermArchive();
    
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function queryTaxCollectionArchive($params, $tax) {
    
    global $_context;

    /*
    *
    * set the _context archive
    *
    */
    $_context['archive'] = 'TaxCollectionArchive';
    
    /*
    *
    * parse the params string into an array (the params have been filtered for relevant ones only in routes)
    *
    */
    parse_str($params, $params_array);
    
    /*
    *
    * set the type & tax => term based on the data given in routes.
    * this will be fed into the query string...
    *
    */
    $params_array['taxonomy'] = taxSettingByKey($this->type, 'key', $tax, 'single');
    
    /*
    *
    * add tax & term to the _context array
    *
    */
    $_context['tax'] = $tax;
    
    /*
    *
    * set the pagination values in the params array
    *
    */
    if(isset($params_array['p'])) $_context['page'] = $params_array['p'];
    if(isset($params_array['show_all'])) $_context['paged'] = false;
    if(isset($params_array['per_page'])) $_context['per_page'] = $params_array['per_page'];
    if(!isset($params_array['per_page'])) $params_array['per_page'] = $_context['per_page'];
    
    /*
    *
    * rebuild the params array into a query string
    *
    */
    $pre_params = http_build_query($params_array);
    
    /*
    *
    * comma-separated items in the string: commas get changed into '%2C' after http_build_query
    * this changes fixes this.
    * cosmetic really
    *
    */
    $pre_params = str_replace("%2C", ",", $pre_params);
    
    /*
    *
    * when show_all does't have a value, it ends up with an = sign at the end after http_build_query
    * this code just removes the = from the show_all param
    * cosmetic really
    *
    */
    $new_params = showAllParamFix($pre_params);
    
    /*
    *
    * _context->string_params is what the query will be running off. so we set it here to out rebuilt string above
    *
    */
    $_context['string_params'] = $new_params;   
      
    // set the archive obj context for twig to render
    $context['archive'] = (new ArchiveModel())->getQueriedTaxonomyArchive();
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  public function getTaxCollectionArchive($tax) {
    global $_context;
    // set some global variables related to the current context
    $_context['archive'] = 'TaxCollectionArchive';
    $_context['tax'] = $tax;
    // set the archive obj context for twig to render
    $context['archive'] = (new ArchiveModel())->getTaxonomyArchive();
    
    $context['context'] = $_context;
    if(isset($context['archive']['title'])) {
      $this->render($context);
    } else {
      $this->error();
    }
  }
  
  protected function render($context) {
    
    global $_context;
    
    $_type = (isset($_context['type'])) ? $_context['type'] : null;
    $_tax = (isset($_context['tax']) && isset($_context['type'])) ? $_context['tax'] : null;
    $_term = (isset($_context['term']) && isset($_context['tax'])) ? $_context['term'] : null;
    
    // TaxTermArchive
    if($_context['archive'] = 'TaxTermArchive' && $this->twig->getLoader()->exists($_tax.'-'.$_term.'.twig')) {
      $this->templateRender($_tax.'-'.$_term.'.twig', $context); // // categories-news.twig
      exit();
    }
    
    // TaxCollectionArchive
    elseif($_context['archive'] = 'TaxCollectionArchive' && $this->twig->getLoader()->exists($_type.'-'.$_tax.'.twig')) {
      $this->templateRender($_type.'-'.$_tax.'.twig', $context); // blog-categories.twig
      exit();
    }
    
    // MainIndexArchive
    elseif($_context['archive'] = 'MainIndexArchive' && $this->twig->getLoader()->exists($_type.'.twig')) {
      $this->templateRender($_type.'.twig', $context); // blog.twig
      exit();
    }
    
    else {
      $this->templateRender('archive.twig', $context);
    }
    
  }
  
}