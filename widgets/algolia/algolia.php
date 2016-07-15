<?php

namespace Kirby;

// Kirby dependencies
use Tpl;

/**
 * Algolia indexing widget
 * 
 * @author Lukas Bestle <lukas@getkirby.com>
 * @license MIT
 * @link https://getkirby.com
 */

return array(
	'title' => [
		'text'       => 'Search Index',
		'compressed' => false
	],
	'options' => [
		[
			'text' => 'Manual Refresh',
			'icon' => 'refresh',
			'link' => purl('widgets/algolia/index')
		]
	],
	'html' => function() {
		$count = algolia()->objectCount();
		return tpl::load(__DIR__ . DS . 'algolia.html.php', compact('count'));
	}
);
