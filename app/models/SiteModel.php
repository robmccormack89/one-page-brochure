<?php
namespace Rmcc;

//  a singleton class, used for representation of single objects. see https://phpenthusiast.com/blog/the-singleton-design-pattern-in-php
class SiteModel {

  private static $instance = null;
  private $q;

  private function __construct() {
    global $config;
    $q = new Json($config['json_data']);
    $this->q = $q->from('site.meta')->get();
  }
  
  public static function init() {
    if(!self::$instance) {
      self::$instance = new SiteModel();
    }
    return self::$instance;
  }
  
  public function getSite() {
    return $this->q;
  }
}