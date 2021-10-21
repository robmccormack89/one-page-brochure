<?php
namespace Rmcc;

// creation of Menu obj requires $slug. E.g new MenuModel('main-menu')
// Menu obj is returned with 3 properties: $slug, $title, $menu_items
// menus created therefore dont need to call getMenu(), this function is protected anyway. Menu obj should be enough
class MenuModel {
  
  public $slug;
  public $title;
  public $menu_items;
  
  public function __construct($slug) {
    $this->slug = $slug;
    $this->title = $this->getMenu()['title'];
    $this->menu_items = $this->getMenu()['menu_items'];
  }
  
  // get a menu via its slug
  private function getMenu() {
    global $config;
    $q = new Json($config['json_data']);
    $data = $q->from('site.menus')->where('slug', '=', $this->slug)->first();
    $data['menu_items'] = self::setMenuItemsClasses($data['menu_items']);

    return $data;
  }
  
  // traverse a given set of menu items, and add active classes if link is found in REQUEST_URI
  private static function setMenuItemsClasses($menu_items) {
    foreach ($menu_items as $k => &$item) {
      if ($_SERVER['REQUEST_URI'] == $menu_items[$k]['link']) {
        $menu_items[$k]['class'] = 'uk-active';
      }
      if(isset($menu_items[$k]['children'])){
        foreach ($menu_items[$k]['children'] as $key => &$value) {
          if ($_SERVER['REQUEST_URI'] == $value['link']) {
            $value['class'] = 'uk-active';
          }
        }
      }
    }
    return($menu_items);
  }
  
}