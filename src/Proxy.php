<?php

namespace Chelout\Geocoder;

use GuzzleHttp\Client as GuzzleClient;
use InvalidArgumentException;
use Predis\Client as RedisClient;

class Proxy
{
    protected $options = [
        /*
         * Формат экспорта [обязательный параметр]
         *
         * Доступны следующие форматы:
         * plain - текстовый список в формате ip:port
         * csv - csv таблица
         * php - сериализованный php массив
         * js - json формат
         * xml - xml формат
         */
        'out' => 'php',
        /*
         * Двухбуквенный код страны, один или несколько.
         * Пример: "RU", "UAUS"
         */
        'country' => '',
        /*
         * Максимально допустимая задержка.
         * Прокси, задержка у которых превышает данный параметр (время в мс), не будут показаны.
         */
        'maxtime' => '500',
        /*
         * Показать только прокси с заданными портами.
         * Пример: "25,80-500,8080"
         */
        'ports' => '',
        /*
         * Тип прокси.
         * Используются следующие коды:
         * HTTP - h
         * HTTPS - s
         * SOCKS4 - 4
         * SOCKS5 - 5
         */
        'type' => 'h',
        /*
         * Анонимность.
         * Используются следующие коды:
         * Нет - 1 (удалённый сервер знает ваш IP, и знает, что вы используете прокси)
         * Низкая - 2 (удалённый сервер не знает ваш IP, но знает, что вы используете прокси)
         * Средняя - 3 (удалённый сервер знает, что вы используете прокси, и думает, что знает ваш IP, но он не ваш (это обычно многосетевые прокси, показывающие удалённому серверу входящий интерфейс как REMOTE_ADDR))
         * Высокая - 4 (удалённый сервер не знает ваш IP, и у него нет прямых доказательств, что вы используете прокси (заголовков из семейства прокси-информации))
         */
        'anon' => '3',
        /*
         * Минимально желаемый аптайм прокси (1-100)
         */
        'uptime' => '95',
        /*
         * Для работы без авторизации на сайте, добавьте к ссылке свой код
         */
        'code' => '',
    ];

    /**
     * @var \GuzzleHttp\Client
     */
    public $guzzle;

    /**
     * @var \Predis\Client
     */
    public $redis;

    public function __construct()
    {
        $this->redis = new RedisClient;

        if (! $proxies = $this->getCache('proxies')) {
            $proxies = $this
                ->loadProxies();

            $this->setCache('proxies', $proxies);
        }

        $this->proxies = $proxies;
    }

    protected function initGuzzle(GuzzleClient $guzzle = null)
    {
        $this->guzzle = $guzzle ?: new GuzzleClient([
            'base_uri' => 'https://hidemy.name/api/proxylist.txt',
            'http_errors' => false,
        ]);

        return $this;
    }

    protected function loadProxies(array $options = [])
    {
        $this->initGuzzle();

        $response = $this->guzzle->request('GET', '', [
            'query' => array_filter($options + $this->options),
        ]);

        if (200 == $response->getStatusCode()) {
            $data = unserialize($response->getBody()->getContents());

            return array_map(function ($proxy) {
                return $proxy['host'] . ':' . $proxy['port'];
            }, $data);
        } else {
            die('Error: ' . $body);
        }
    }

    protected function setCache($key, $value, $seconds = 3600)
    {
        $this->redis->set($key, json_encode($value), 'EX', $seconds);
    }

    protected function getCache($key)
    {
        $value = $this->redis->get($key);

        if (! is_null($value)) {
            return json_decode($value, true);
        }

        return false;
    }

    public function get($key = null)
    {
        if (is_null($key)) {
            return $this->proxies;
        }

        if (! array_key_exists($key, $this->proxies)) {
            throw new InvalidArgumentException('Key is invalid.');
        }

        return $this->proxies[$key];
    }

    public function count()
    {
        return count($this->proxies);
    }

    public function random()
    {
        return $this->get(
            array_rand($this->get())
        );
    }

    public function remove($value)
    {
        if ($key = array_search($value, $this->proxies)) {
            unset($this->proxies[$key]);

            $this->setCache('proxies', $this->proxies);
        }
    }
}
