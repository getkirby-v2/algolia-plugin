<?php 

namespace Kirby\Algolia;

// Kirby dependencies
use Collection;
use Obj;
use Pagination;

/**
 * Kirby Algolia Results Class
 * 
 * @author Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 * @link https://getkirby.com
 */
class Results extends Collection {
  // Result metadata
  protected $totalCount;
  protected $processingTime;
  protected $query;
  protected $params;
  
  /**
   * Class constructor
   *
   * @param array $results Returned data from an Algolia search operation
   */
  public function __construct($results) {
    // Defaults in case the results are invalid
    $defaults = array(
      'hits'             => array(),
      'page'             => 0,
      'nbHits'           => 0,
      'nbPages'          => 0,
      'hitsPerPage'      => 20,
      'processingTimeMS' => 0,
      'query'            => '',
      'params'           => ''
    );
    $results = array_merge($defaults, $results);
    
    // Convert the hits to Obj objects
    $hits = array_map(function($hit) {
      return new Obj($hit);
    }, $results['hits']);
    
    // Get metadata from the results
    // Algolia uses zero based page indexes while Kirby's pagination starts at 1
    $page           = $results['page'] + 1;
    $totalCount     = $results['nbHits'];
    $hitsPerPage    = $results['hitsPerPage'];
    $processingTime = $results['processingTimeMS'];
    $query          = $results['query'];
    $params         = $results['params'];
    
    // Store the results
    parent::__construct($hits);
    $this->totalCount     = $totalCount;
    $this->processingTime = $processingTime;
    $this->query          = $query;
    $this->params         = $params;
    
    // Paginate the collection
    $pagination = new Pagination($totalCount, $hitsPerPage, compact('page'));
    $this->paginate($pagination);
  }
  
  /**
   * Returns the total count of results for the search query
   * $results->count() returns the count of results on the current pagination page
   *
   * @return int
   */
  public function totalCount() {
    return $this->totalCount;
  }
  
  /**
   * Returns the Algolia server processing time in ms
   *
   * @return int
   */
  public function processingTime() {
    return $this->processingTime;
  }
  
  /**
   * Returns the search query
   *
   * @return string
   */
  public function query() {
    return $this->query;
  }
  
  /**
   * Returns the Algolia search parameter string
   * Useful when debugging search requests
   *
   * @return string
   */
  public function params() {
    return $this->params;
  }
}
