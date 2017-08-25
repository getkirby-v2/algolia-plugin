<?php 

/**
 * Kirby Algolia Plugin
 * 
 * @author Lukas Bestle <lukas@getkirby.com>
 */

/**
 * Autoloader for all Kirby Algolia classes and dependencies
 */
load([
  'kirby\\algolia'          => __DIR__ . DS . 'lib' . DS . 'algolia.php',
  'kirby\\algolia\\results' => __DIR__ . DS . 'lib' . DS . 'algolia' . DS . 'results.php',
  
  // Official Algolia PHP API client
  'algoliasearch\\client'   => __DIR__ . DS . 'vendor' . DS . 'algolia-client' . DS . 'algoliasearch.php'
]);

/**
 * Helper function that returns a Kirby\Algolia instance
 *
 * @return Algolia
 */
function algolia() {
  return Kirby\Algolia::instance();
}

// Register the Panel widget
if(c::get('algolia.widget', true) && function_exists('panel')) {
  kirby()->set('widget', 'algolia', __DIR__ . DS . 'widgets' . DS . 'algolia');
  
  // Register the route for the widget
  panel()->routes([
    [
      'pattern' => 'widgets/algolia/index',
      'method'  => 'GET',
      'filter'  => 'auth',
      'action'  => function() {
        algolia()->index();
        
        panel()->notify(':)');
        panel()->redirect('/');
      }
    ]
  ]);
}

/**
 * Panel hooks
 * 
 * Every page change is automatically synced to Algolia
 * Automatic indexing can be disabled with the algolia.autoindex option
 */

if(c::get('algolia.autoindex', true)) {
  kirby()->hook('panel.page.create', function($page) {
    return algolia()->insertPage($page);
  });

  kirby()->hook('panel.page.update', function($page) {
    return algolia()->updatePage($page);
  });

  kirby()->hook('panel.page.delete', function($page) {
    return algolia()->deletePageRecursive($page);
  });

  kirby()->hook('panel.page.sort', function($page) {
    return algolia()->updatePage($page);
  });

  kirby()->hook('panel.page.hide', function($page) {
    return algolia()->updatePage($page);
  });

  kirby()->hook('panel.page.move', function($newPage, $oldPage) {
    return algolia()->movePage($oldPage, $newPage);
  });
}
