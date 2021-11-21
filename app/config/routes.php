<?php
namespace Rmcc;
use \Bramus\Router\Router as Router;

/*
*
* SETUP
*
*/

$router = new Router;
$router->setBasePath('/');

/*
*
* HTTPS REDIRECT (IF ENABLED)
*
*/

if ($config['enable_https']) {
  if ($_SERVER['HTTPS'] != 'on') {
    $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: ' . $url, true, 301);
    exit();
  }
}

/*
*
* HOMEPAGE
*
*/

$router->get('/', function() {
  Cache::cacheServe(function() { 
    (new SingleController('page', 'index'))->getSingle();
  });
});

// contact form post route, Homepage!
$router->post('/', function() {
  (new SingleController('page', 'index'))->getContact();
});

/*
*
* ARCHIVED ROUTES (content_types defined in $config['types'])
*
*/

// include('routes.archived.php');

/*
*
* PAGES ROUTES
*
*/

// contact form post route, contact page!
$router->post('/contact', function() {
  (new SingleController('page', 'contact'))->getContact();
});

$router->get('/{slug}', function($slug) {
  Cache::cacheServe(function() use ($slug) { 
    (new SingleController('page', $slug))->getSingle();
  });
});


// 404 error route. in most cases 404 errors will be rendered rather than routed, see CoreController->error()
$router->set404(function() {
  header('HTTP/1.1 404 Not Found');
  echo '<hr>Error 404';
});

// go
$router->run();