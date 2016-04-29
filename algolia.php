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
    return algolia()->deletePage($page);
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
