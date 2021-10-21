<?php
namespace Rmcc;

//  a singleton class, used for representation of single objects. see https://phpenthusiast.com/blog/the-singleton-design-pattern-in-php
class AppModel {

  private static $instance = null;
  private $data;

  private function __construct() {
    global $config;
    $data = new Json($config['json_data']);
    $data = $data->toArray($data);
    $title = $data['app_title'];
    $name = $data['app_name'];
    $description = $data['app_description'];
    $url = $data['app_url'];
    $this->data = array(
      'title' => $title,
      'name' => $name,
      'description' => $description,
      'url' => $url
    );
  }
  
  public static function init() {
    if(!self::$instance) {
      self::$instance = new AppModel();
    }
    return self::$instance;
  }
  
  public function getApp() {
    return $this->data;
  }
}