<?php

namespace Evand\Recommenderir;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;

/**
 * Class Client
 * @package Evand\Recommenderir
 */
class Client
{
    use Validator;
    use Helpers;

    /**
     * @var array
     */
    protected $options = array (
        'base_uri' => 'http://example.com',
        'user_agent' => 'php-recommenderir (https://github.com/evandhq/php-recommenderir)',
        'connect_timeout' => 30,
        'timeout' => 30
    );

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->client = new GuzzleClient(array_merge($this->options, $options));
    }

    /**
     * @param int    $userId
     * @param string $item
     * @param int    $value
     *
     * @return bool
     */
    public function ingest($userId, $item, $value = 0)
    {

        $this->isInt($userId, 'userId');
        $this->haveString($item, 'item');
        $this->validateValue($value);

        try {
            $this->client->get('/ingest', [
                'query' => [
                    'id' => $userId,
                    'url' => "'" . $item . "'",
                    'value' => $value
                ]
            ]);
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param array $items
     *
     * @return bool
     */
    public function forgetItems(array $items)
    {
        if (empty($items)) {
            throw new InvalidArgumentException('items is empty!');
        }

        $this->validateArray($items, 'haveString', 'items');

        try {
            $this->client->get('/forget/' . implode('/', $items));
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function forgetItemsList()
    {
        try {
            $response    = $this->client->get('/forget?list');
            $itemsString = $response->getBody()->getContents();

            if (empty($itemsString)) {
                return [];
            }

            $items = explode(PHP_EOL, $itemsString);
            $items = array_filter($items);
            $items = array_unique($items);
        } catch (ClientException $e) {
            return [];
        }

        return $items;
    }

    /**
     * @param array $items
     *
     * @return bool
     */
    public function rememberItems(array $items)
    {
        if (empty($items)) {
            throw new InvalidArgumentException('items is empty!');
        }

        $this->validateArray($items, 'haveString', 'items');

        try {
            $this->client->get('/forget/' . implode('/', $items) . '?remember=1');
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param       $item
     * @param array $terms
     * @param bool  $overwrite
     *
     * @return bool
     */
    public function addTerms($item, array $terms, $overwrite = false)
    {

        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');
        $this->haveString($item, 'item');

        try {
            $overwrite = $overwrite === true ? '?overwrite' : '';
            $this->client->get('/termItemAdd/' . $item . '/' . implode('/', $terms) . $overwrite);
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param       $item
     * @param array $terms
     *
     * @return bool
     */
    public function removeTerms($item, array $terms)
    {
        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');
        $this->haveString($item, 'item');

        try {
            $this->client->get('/termItemRemove/' . $item . '/' . implode('/', $terms));
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $item
     *
     * @return array
     */
    public function termsList($item)
    {
        $this->haveString($item, 'item');

        try {
            $response = $this->client->get('/termItemList/' . $item);

            $terms = $this->json_decode_recommender($response->getBody()->getContents());
            if ($terms === null) {
                return [];
            }
        } catch (ClientException $e) {
            return [];
        }

        return $terms->$item;
    }

    /**
     * @param            $userId
     * @param null       $howMany
     * @param bool|false $dither
     *
     * @return array|mixed
     */
    public function recommend($userId, $howMany = null, $dither = false)
    {
        $this->isInt($userId, 'userId');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            if ($dither === true) {
                $queryString['query']['dither'] = '';
            }

            $response = $this->client->get('/recommend/' . $userId, $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param array      $userIds
     * @param null       $howMany
     * @param bool|false $dither
     *
     * @return array|mixed
     */
    public function recommendToGroup(array $userIds, $howMany = null, $dither = false)
    {
        if (empty($userIds)) {
            throw new InvalidArgumentException('userIds is empty!');
        }

        $this->validateArray($userIds, 'isInt', 'userIds');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            if ($dither === true) {
                $queryString['query']['dither'] = '';
            }

            $response = $this->client->get('/recommendToGroup/' . implode('/', $userIds), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param array $items
     * @param null  $howMany
     *
     * @return array|mixed
     */
    public function similarity(array $items, $howMany = null)
    {
        if (empty($items)) {
            throw new InvalidArgumentException('items is empty!');
        }

        $this->validateArray($items, 'haveString', 'items');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/similarity/' . implode('/', $items), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param       $item
     * @param array $items
     *
     * @return array
     */
    public function similarityToItem($item, array $items)
    {
        $this->haveString($item);

        if (empty($items)) {
            throw new InvalidArgumentException('items is empty!');
        }

        $this->validateArray($items, 'haveString', 'items');

        try {
            $response = $this->client->get('/similarityToItem/' . $item . '/' . implode('/', $items));

            $similarityString = $response->getBody()->getContents();

            if (empty($similarityString)) {
                return [];
            }

            $similarities = explode(PHP_EOL, $similarityString);
            $similarities = array_filter($similarities);

            $results = [];
            $index   = 0;
            foreach ($items as $item) {
                $results[$item] = $similarities[$index];
                $index++;
            }

            return $results;
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param      $userId
     * @param      $item
     * @param null $howMany
     *
     * @return array|mixed
     */
    public function because($userId, $item, $howMany = null)
    {
        $this->isInt($userId);
        $this->haveString($item);

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/because/' . $userId . '/' . $item, $queryString);

            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param null $howMany
     *
     * @return array|mixed
     */
    public function mostPopularItems($howMany = null)
    {
        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/mostPopularItems', $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param null $howMany
     *
     * @return array|mixed
     */
    public function termMostPopularItems($howMany = null)
    {
        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/termMostPopularItems', $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param array      $items
     * @param null       $howMany
     * @param bool|false $guess
     *
     * @return array|mixed
     */
    public function termSimilarity(array $items, $howMany = null, $guess = false)
    {
        if (empty($items)) {
            throw new InvalidArgumentException('items is empty!');
        }

        $this->validateArray($items, 'haveString', 'items');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            if ($guess === true) {
                $queryString['query']['guess'] = '';
            }

            $response = $this->client->get('/termSimilarity/' . implode('/', $items), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param       $userId
     * @param array $terms
     * @param null  $howMany
     *
     * @return array|mixed
     */
    public function termBasedRecommend($userId, array $terms, $howMany = null)
    {
        $this->isInt($userId);

        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/termBasedRecommend/' . $userId . '/' . implode('/', $terms), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param array $terms
     * @param null  $howMany
     *
     * @return array|mixed
     */
    public function termBasedMostPopularItems(array $terms, $howMany = null)
    {

        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/termBasedMostPopularItems/' . implode('/', $terms), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param       $item
     * @param array $terms
     * @param null  $howMany
     *
     * @return array|mixed
     */
    public function termBasedSimilarity($item, array $terms, $howMany = null)
    {
        $this->haveString($item);

        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');

        try {

            $queryString = [];
            if (is_numeric($howMany) and $howMany > 0) {
                $queryString['query']['howMany'] = $howMany;
            }

            $response = $this->client->get('/termBasedSimilarity/' . $item . '/' . implode('/', $terms), $queryString);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param array $terms
     *
     * @return array|mixed
     */
    public function termNeighborhood(array $terms)
    {

        if (empty($terms)) {
            throw new InvalidArgumentException('terms is empty!');
        }

        $this->validateArray($terms, 'haveString', 'terms');

        try {
            $response = $this->client->get('/termNeighborhood/' . implode('/', $terms));
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param $userId
     *
     * @return array|mixed
     */
    public function latestTouchedItems($userId)
    {
        $this->isInt($userId);

        try {
            $response = $this->client->get('/latestTouchedItems/' . $userId);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param $userId
     *
     * @return array|mixed
     */
    public function latestTouchedTerms($userId)
    {
        $this->isInt($userId);

        try {
            $response = $this->client->get('/latestTouchedTerms/' . $userId);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param $userId
     *
     * @return array|mixed
     */
    public function userFrequentlyTouchedItems($userId)
    {
        $this->isInt($userId);

        try {
            $response = $this->client->get('/userFrequentlyTouchedItems/' . $userId);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }

    /**
     * @param $item
     *
     * @return array
     */
    public function itemFrequentlyVisitors($item)
    {
        $this->haveString($item, 'item');

        try {
            $response = $this->client->get('/itemFrequentlyVisitors/' . $item);

            $users = $this->json_decode_recommender($response->getBody()->getContents());
            if ($users === null) {
                return [];
            }
        } catch (ClientException $e) {
            return [];
        }

        return $users->$item;
    }

    /**
     * @param $userId
     *
     * @return array|mixed
     */
    public function currentMood($userId)
    {
        $this->isInt($userId);

        try {
            $response = $this->client->get('/currentMood/' . $userId);
            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return [];
        }
    }
}
