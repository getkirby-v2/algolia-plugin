# Kirby Algolia Plugin

This plugin integrates with the [Algolia service](https://www.algolia.com) to index and search through your Kirby website.

## What is Algolia?

Algolia is a hosted search engine for websites and apps. After providing it with the structured content of your website, it will create an optimized database of the content that will be very fast to search through via their API.

Using Algolia requires an [account](https://www.algolia.com/users/sign_up). Free accounts are available, but paid accounts allow you to use the service without displaying the Algolia logo next to the search results. The [pricing](https://www.algolia.com/pricing) mostly depends on the number of records (Kirby pages) you want to index, the change frequency of these records and the number of search queries per month. The "Starter" plan should be enough for most Kirby websites.

You can read more about Algolia on their [website](https://www.algolia.com) and in their [documentation](https://www.algolia.com/doc).

## Requirements

This plugin requires Kirby 2.3 or later and an account at Algolia.

## Installation

To install the plugin, please put it in the `site/plugins` directory and add the necessary configuration options (see below) to your `site/config.php`.

## Configuration

### API credentials

After [creating an Algolia account](https://www.algolia.com/users/sign_up), you can find your API credentials in the Algolia dashboard on the "API keys" page. The values you need are the "Application ID" and the "Write API Key":

```php
c::set('algolia.app', 'ABCDEFGHIJ');
c::set('algolia.key', '12345678901234567890123456789012');
```

### Search index

Once setup, the plugin will upload your Kirby pages to an Algolia index. An index is like a database table that is optimized for searching.
You need to create one index per site in the [Algolia dashboard](https://www.algolia.com/dashboard) and set its name like this:

```php
c::set('algolia.index', 'myindex');
```

The plugin also uses a temporary index when re-indexing the site manually. This temporary index is created automatically when needed.
The name of that index is set to `{{algolia.index}}_temp` by default, but if you need to, you can change this value like this:

```php
c::set('algolia.index.temp', 'myindex_temp');
```

### Indexing options

#### Template specific settings

This is the most important indexing option that allows you to define which pages and which fields of the pages should be uploaded and indexed.
You can define these rules for each template. Every template that is not in the list will be ignored:

```php
c::set('algolia.templates', array(
  // Simple definition if you don't need a filter and don't want to add fields (see below)
  'contact',
  
  // Complex definition that allows to customize the behavior
  'project' => array(
    // A filter function can decide whether a specific page should be indexed or not
    'filter' => function($page) {
      return $page->isVisible() && $page->indexable()->bool();
    },
    
    // List of fields to send to Algolia
    // Extends the algolia.fields option (see below)
    'fields' => array(
      // Simple string values
      'title',
      'text',
      
      // Define a field method to transform the field
      'featured' => 'bool',
      
      // Field method with arguments
      'tags' => array('split', ','),
      
      // Custom value using a function
      'image' => function($page) {
        // Returns the URL of the first image of the page
        // You should always make sure to return something here, even if there are no images, to avoid errors
        $image = $page->images()->first();
        return ($image)? $image->url() : null;
      }
    )
  )
));
```

#### Default fields

Every page in the Algolia index will additionally contain an `url` and `intendedTemplate` field that you can later use to display the results.
You can override this behavior or add additional fields to this list by using the `algolia.fields` option:

```php
c::set('algolia.fields', array('url', 'intendedTemplate', 'title', 'text'));
```

#### Automatic indexing

The Algolia plugin contains Panel hooks that will update the index on every page change in the Panel.
Every time you create, update, rename, delete, hide or sort a page, the changes will immediately be reflected in the Algolia index.

If you only want to use manual indexing, you can disable the automatic indexing with the following option:

```php
c::set('algolia.autoindex', false);
```

#### Indexing widget

The Algolia plugin includes a Panel widget that allows Panel users to manually index the site. You can disable this widget with the following option:

```php
c::set('algolia.widget', false);
```

### Search options

Algolia has [many search options](https://www.algolia.com/doc/php#full-text-search-parameters) to fine-tune the search results. You can set these in your configuration like this:

```php
c::set('algolia.search.options', array(
  'typoTolerance' => false,
  'hitsPerPage'   => 50
));
```

Alternatively, you can also set the options in the display settings of the index in the Algolia dashboard.

## Indexing

The plugin will automatically update the Algolia index with changes you make in the Panel.
If you don't use the Panel or have a custom deployment strategy, you can instead manually index the whole site:

```php
algolia()->index();
```

This will create a new temporary index, upload all indexable pages and replace the main index with the temporary index.
Please note that manual indexing will use roughly as many Algolia "Operations" as you have indexable pages each time you call the `index` method. The amount of included/free "Operations" per month depends on your Algolia plan.

There is also a Panel widget for this that is enabled by default.

## Search

The plugin also provides a method to query/search the index from the backend. It is generally recommended to use [Algolia's JavaScript library](https://www.algolia.com/doc/javascript#quick-start) to avoid the round-trip to your server, but you should also have a server-side fallback results page, which you can implement using the `search` method:

```php
$results = algolia()->search($query, $page = 1, $options = array());
```

The `$options` array can be used to override your default values in the `algolia.search.options` option.

**Note**: The `$page` parameter starts at `1` while Algolia uses "zero based" pagination (where the parameter starts at `0`). The plugin converts between these formats automatically to allow you to use Kirby's collection pagination.

### Getting metadata about the results

Algolia returns metadata together with the results. You can get this data from the results collection:

```php
// Total count of results
echo 'There are ' . $results->totalCount() . ' results.';

// Algolia server processing time in ms
echo 'Processing time: ' . $results->processingTime();

// Search query
echo 'You searched for ' . esc($results->query()) . '.';

// Algolia search parameter string
// Useful when debugging search requests
echo 'Search params: ' . $results->params();
```

### Example

*Adapted from the [Kirby search example](https://getkirby.com/docs/solutions/search).*

**`site/controllers/search.php`**

```php
<?php

return function($site, $pages, $page) {

  $query = get('q');
  $page  = param('page');
  
  if($query) {
    $results    = algolia()->search($query, $page);
    $pagination = $results->pagination();
  } else {
    $results    = array();
    $pagination = null;
  }

  return compact('results', 'pagination', 'query');

};
```

**`site/templates/search.php`**

```php
<?php snippet('header') ?>

<form>
  <input type="search" name="q" value="<?php echo esc($query) ?>">
  <input type="submit" value="Search">
</form>

<ul>
  <?php foreach($results as $result): ?>
  <li>
    <a href="<?php echo $result->url() ?>">
      <?php echo html($result->title()) ?>
    </a>
  </li>
  <?php endforeach ?>
</ul>

<?php if($pagination && $pagination->hasPages()): ?>
<nav class="pagination">

  <?php if($pagination->hasNextPage()): ?>
  <a class="next" href="<?php echo $pagination->nextPageURL() ?>">&lsaquo; next page</a>
  <?php endif ?>

  <?php if($pagination->hasPrevPage()): ?>
  <a class="prev" href="<?php echo $pagination->prevPageURL() ?>">previous page &rsaquo;</a>
  <?php endif ?>

</nav>
<?php endif ?>

<?php snippet('footer') ?>
```

## License

<http://www.opensource.org/licenses/mit-license.php>

## Author

Lukas Bestle <https://getkirby.com>
