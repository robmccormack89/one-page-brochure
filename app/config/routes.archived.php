<?php
namespace Rmcc;

global $config;

foreach($config['types'] as $key => $value) {
  $items = typeSettingByKey('key', $key , 'items');
  $router->get('/'.$key.'/', function() use ($key) {
    $params = parse_url($_SERVER['REQUEST_URI']);
    if (isset($params['query']) && queryParamsExists($params['query'])) {
      parse_str($params['query'], $params_array);
      if($_SERVER['REQUEST_URI'] === '/'.$key.'?p=1' || $_SERVER['REQUEST_URI'] === '/'.$key.'/?p=1'){
        header('Location: /'.$key, true, 301);
        exit();
      }
      (new ArchiveController($key))->queryMainIndexArchive($params['query']);
    } else {
      Cache::cacheServe(function() use ($key){ 
        (new ArchiveController($key))->getMainIndexArchive();
      });
    }
  });
  $router->get('/'.$key.'/'.$items.'/{slug}', function($slug) use ($key) {
    Cache::cacheServe(function() use ($key, $slug) { 
      (new SingleController($key, $slug))->getSingle();
    });
  });
  foreach($value['taxes_in_meta'] as $tax) {
    $router->get('/'.$key.'/'.$tax.'/{term}/', function($term) use ($key, $tax){
       $params = parse_url($_SERVER['REQUEST_URI']);
       if (isset($params['query']) && queryParamsExists($params['query'])) {
         parse_str($params['query'], $params_array);
         if($_SERVER['REQUEST_URI'] === '/'.$key.'/'.$tax.'/'.$term.'?p=1' || $_SERVER['REQUEST_URI'] === '/'.$key.'/'.$tax.'/'.$term.'/?p=1'){
           header('Location: /'.$key.'/'.$tax.'/'.$term, true, 301); // redirect requests for page one of paged archive to main archive
           exit();
         }
         (new ArchiveController($key))->queryTaxTermArchive($params['query'], $tax, $term);
       } else {
         Cache::cacheServe(function() use ($key, $tax, $term) { 
           (new ArchiveController($key))->getTaxTermArchive($tax, $term);
         });
       } 
    });
    $router->get('/'.$key.'/'.$tax.'/', function() use ($key, $tax) {
      $params = parse_url($_SERVER['REQUEST_URI']);
      if (isset($params['query']) && queryParamsExists($params['query'])) {
        parse_str($params['query'], $params_array);
        if($_SERVER['REQUEST_URI'] === '/'.$key.'/'.$tax.'?p=1' || $_SERVER['REQUEST_URI'] === '/'.$key.'/'.$tax.'/?p=1'){
          header('Location: /'.$key.'/'.$tax, true, 301);
          exit();
        }
        (new ArchiveController($key))->queryTaxCollectionArchive($params['query'], $tax);
      } else {
        Cache::cacheServe(function() use ($key, $tax){ 
          (new ArchiveController($key))->getTaxCollectionArchive($tax);
        });
      }
    });
  }
}