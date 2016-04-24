## php-recommenderir
A simple Object Oriented wrapper for [Recommender.ir](http://recommender.ir/) API, written with PHP5.

## Installation
```
compsoer require evandhq/php-recommenderir
```
or add this line to your composer.json file
```
"evandhq/php-recommenderir" : "dev-master"
```

## Basic usage
```php
require_once 'vendor/autoload.php';

$client = new Evand\Recommenderir\Client(['base_uri' => 'http://example.com']);
$client->ingest(12345, 'product1', 200);
```

## Available methods

* ingest
* forgetItems
* forgetItemsList
* rememberItems
* addTerms
* removeTerms
* termsList
* recommend
* recommendToGroup
* similarity
* similarityToItem
* because
* mostPopularItems
* termMostPopularItems
* termSimilarity
* termBasedRecommend
* termBasedMostPopularItems
* termBasedSimilarity
* termNeighborhood
* latestTouchedItems
* latestTouchedTerms
* userFrequentlyTouchedItems
* itemFrequentlyVisitors
* currentMood

Feel free to open an issue and send a pull request. 
