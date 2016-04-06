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

        $this->isInt($userId);
        $this->haveString($item);
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

        $this->validateArray($items, 'haveString');

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

        $this->validateArray($items, 'haveString');

        try {
            $this->client->get('/forget/' . implode('/', $items) . '?remember=1');
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param       $item
     * @param array $tags
     *
     * @return bool
     */
    public function addTags($item, array $tags)
    {

        if (empty($tags)) {
            throw new InvalidArgumentException('tags is empty!');
        }

        $this->validateArray($tags, 'haveString');
        $this->haveString($item);

        try {
            $this->client->get('/termItemAdd/' . $item . '/' .implode('/', $tags));
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param       $item
     * @param array $tags
     *
     * @return bool
     */
    public function removeTags($item, array $tags)
    {
        if (empty($tags)) {
            throw new InvalidArgumentException('tags is empty!');
        }

        $this->validateArray($tags, 'haveString');
        $this->haveString($item);

        try {
            $this->client->get('/termItemRemove/' . $item . '/' .implode('/', $tags));
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
    public function tagsList($item)
    {
        $this->haveString($item);

        try {
            $response = $this->client->get('/termItemList/' . $item);
            $tags = $this->toArray($item, $response->getBody()->getContents());
            if(empty($tags)) {
                return [];
            }

        } catch (ClientException $e) {
            return [];
        }

        return $tags[$item];

    }

    /**
     * @param $item
     * @param $string
     *
     * @return array
     */
    private function toArray($item, $string) {

        if(empty($string) or $string === null) {
            return [];
        }

        $array = [];
        $string = '$array = ' . str_replace("\"$item\":", "\"$item\"=>", $string) . ';';
        eval($string);

        return $array;
    }
}