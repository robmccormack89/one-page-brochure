<?php
namespace Rmcc;
use PHPMailer\PHPMailer\PHPMailer;

class CoreController {
  
  public function __construct() {
    
    global $config;

    // twig configs
    $loader = new \Twig\Loader\FilesystemLoader($config['twig_templates_locations'], $config['twig_templates_base_location']);
    $loader->prependPath('/');
    
    $_environ = ['cache' => '../app/cache/compilation'];
    // $_environ = ['cache' => false];
    // error reporting
    if($config['enable_debug_mode']) {
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL);
      $_environ['debug'] = true;
    } else {
      error_reporting(0);
      ini_set('display_errors', 0);
      ini_set('display_startup_errors', 0);
    }
    $this->twig = new \Twig\Environment($loader, $_environ);
    $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    
    // remove query params from a given string/url, added to twig
    $strtokparams = new \Twig\TwigFilter('strtokparams', function ($string) {
      return strtok($string);
    });
    $this->twig->addFilter($strtokparams);
    
    // resize filter added to twig
    $resize = new \Twig\TwigFilter('resize', function ($src, $w, $h = null, $crop = 'default') {
      return self::resize($src, $w, $h, $crop);
    });
    $this->twig->addFilter($resize);
    
    // get_terms function added to twig
    $get_terms = new \Twig\TwigFunction('get_terms', function ($tax) {
      $args = array(
        'taxonomy' => $tax,
        'show_all' => true
      );
      $terms_obj = new QueryTermsModel($args);
      return $terms_obj->terms;
    });
    $this->twig->addFunction($get_terms);
    
    // twig globals: Site, Author & Configs
    $this->twig->addGlobal('app', AppModel::init()->getApp());
    $this->twig->addGlobal('site', SiteModel::init()->getSite());
    $this->twig->addGlobal('author', AuthorModel::init()->getAuthor());
    $this->twig->addGlobal('config', $config);
    
    // menus
    $main_menu = new MenuModel('main-menu');
    $main_menu_chunked = array_chunk($main_menu->menu_items, ceil(count($main_menu->menu_items) / 2));
    $main_menu_first = $main_menu_chunked[0];
    $main_menu_second = $main_menu_chunked[1];
    $this->twig->addGlobal('main_menu', $main_menu);
    $this->twig->addGlobal('main_menu_first', $main_menu_first);
    $this->twig->addGlobal('main_menu_second', $main_menu_second);
    
    // url globals
    $this->twig->addGlobal('base_url', $config['base_url']);
    $this->twig->addGlobal('current_url', $config['current_url']);
    $this->twig->addGlobal('current_url_no_params', $config['current_url_clean']);
    $this->twig->addGlobal('get', $config['url_params']);
  }
  
  public static function resize($src, $w, $h, $crop) {
    if (!is_numeric($w) && is_string($w)) return $src;
    // if (!file_exists($src)) return $src; // need to check
    $path = parse_url($src, PHP_URL_PATH);
    $full_path = $_SERVER['DOCUMENT_ROOT'].$path;
    $op = new Resize($w, $h, $crop);
    return pathToURL(self::_resize($full_path, $op));
  }

  private static function _resize($src, $op) {
    $file_extension = substr(strrchr($src, '.'), 1);
    $file_name = basename($src, '.'.$file_extension);
    $destination_path = $op->filename($file_name, $file_extension);
    $dir = dirname($src).'/';
    $destination = $dir.$destination_path;
    if(!file_exists($destination)) {
      $op->run($src, $destination);
    }
    return $destination;
  }

  public function error() {
    echo $this->twig->render('404.twig');
  }
  
  protected function templateRender($template, $context) {
    Cache::cacheRender($this->twig->render($template, $context));
  }
  
}