<?php

namespace Chelout\Geocoder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Geocoder.
 *
 * @license The MIT License (MIT)
 *
 * @see http://api.yandex.ru/maps/doc/geocoder/desc/concepts/About.xml
 */
class Geocoder
{
    /**
     * Дом
     */
    const KIND_HOUSE = 'house';

    /**
     * Улица.
     */
    const KIND_STREET = 'street';

    /**
     * Станция метро.
     */
    const KIND_METRO = 'metro';

    /**
     * Район города.
     */
    const KIND_DISTRICT = 'district';

    /**
     * Населенный пункт (город/поселок/деревня/село/...).
     */
    const KIND_LOCALITY = 'locality';

    /**
     * Русский (по умолчанию).
     */
    const LANG_RU = 'ru-RU';

    /**
     * Украинский.
     */
    const LANG_UA = 'uk-UA';

    /**
     * Белорусский.
     */
    const LANG_BY = 'be-BY';

    /**
     * Американский английский.
     */
    const LANG_US = 'en-US';

    /**
     * Британский английский.
     */
    const LANG_BR = 'en-BR';

    /**
     * Турецкий (только для карты Турции).
     */
    const LANG_TR = 'tr-TR';

    /**
     * Фильтры гео-кодирования.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * @var \GuzzleHttp\Client
     */
    public $client;

    /**
     * @var \Yandex\Geo\Response|null
     */
    protected $response;

    /**
     * @var \Chelout\Geocoder\Proxy
     */
    protected $proxy;

    /**
     * Число попыток соединения с Yandex.
     *
     * @var int
     */
    protected $tries = 10;

    /**
     * @param null|\Chelout\Geocoder\Proxy $proxy
     * @param null|\GuzzleHttp\Client      $client
     */
    public function __construct(Proxy $proxy = null, Client $client = null)
    {
        $this->proxy = $proxy ?: new Proxy;

        $this->tries = $this->proxy->count();

        $this->client = $client ?: new Client([
            'base_uri' => 'https://geocode-maps.yandex.ru/1.x/',
            // 'proxy' => ($proxy ?: new Proxy)->random(),
            'timeout' => 2.0,
            // 'http_errors' => false,
        ]);

        $this->clear();
    }

    public function load(array $options = [])
    {
        do {
            $proxy = $this->proxy->random();

            try {
                // dump($this->tries . ': ' . $proxy);

                $response = $this->client->request('GET', '', [
                    'query' => $this->filters,
                    'proxy' => $proxy,
                    // 'verify' => false,
                ]);

                $this->response = new Response(
                    json_decode((string) $response->getBody(), true)
                );

                dump($this->tries . ': ' . $proxy);

                return $this;
            } catch (ClientException $e) {
                // $this->proxy->remove($proxy);

                // if ($e->hasResponse()) {
                //     dump('Exception: ' . $e->getResponse()->getStatusCode(), $e->getMessage());
                // } else {
                //     dump('Exception: ' . $e->getMessage());
                // }
            } catch (RequestException $e) {
                // $this->proxy->remove($proxy);

                // if ($e->hasResponse()) {
                //     dump('Exception: ' . $e->getResponse()->getStatusCode(), $e->getMessage());
                // } else {
                //     dump('Exception: ' . $e->getMessage());
                // }
            }
            // } catch (ClientException $e) {
            //     $this->proxy->remove($proxy);
            //     dump('ClientException: ' . $e->getResponse()->getStatusCode());
            // } catch (RequestException $e) {
            //     if ($e->hasResponse()) {
            //         dump('RequestException: ' . $e->getResponse()->getStatusCode(), $e->getMessage());
            //     } else {
            //         dump('Exception: ' . $e->getMessage());
            //     }
            // }

            --$this->tries;
        } while ((! isset($response) || 200 != $response->getStatusCode()) && $this->tries > 0);

        return false;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Очистка фильтров гео-кодирования.
     *
     * @return self
     */
    public function clear()
    {
        $this->filters = [
            'format' => 'json',
        ];

        // указываем явно значения по-умолчанию
        $this
            ->setLang(self::LANG_RU)
            ->setOffset(0)
            ->setLimit(10);
//            ->useAreaLimit(false);

        $this->response = null;

        return $this;
    }

    /**
     * Гео-кодирование по координатам
     *
     * @see http://api.yandex.ru/maps/doc/geocoder/desc/concepts/input_params.xml#geocode-format
     *
     * @param float $longitude Долгота в градусах
     * @param float $latitude  Широта в градусах
     *
     * @return self
     */
    public function setPoint($longitude, $latitude)
    {
        $longitude = (float) $longitude;
        $latitude = (float) $latitude;
        $this->filters['geocode'] = sprintf('%F,%F', $longitude, $latitude);

        return $this;
    }

    /**
     * Географическая область поиска объекта.
     *
     * @param float      $lengthLng Разница между максимальной и минимальной долготой в градусах
     * @param float      $lengthLat Разница между максимальной и минимальной широтой в градусах
     * @param null|float $longitude Долгота в градусах
     * @param null|float $latitude  Широта в градусах
     *
     * @return self
     */
    public function setArea($lengthLng, $lengthLat, $longitude = null, $latitude = null)
    {
        $lengthLng = (float) $lengthLng;
        $lengthLat = (float) $lengthLat;
        $this->filters['spn'] = sprintf('%f,%f', $lengthLng, $lengthLat);
        if (! empty($longitude) && ! empty($latitude)) {
            $longitude = (float) $longitude;
            $latitude = (float) $latitude;
            $this->filters['ll'] = sprintf('%f,%f', $longitude, $latitude);
        }

        return $this;
    }

    /**
     * Позволяет ограничить поиск объектов областью, заданной self::setArea().
     *
     * @param bool $areaLimit
     *
     * @return self
     */
    public function useAreaLimit($areaLimit)
    {
        $this->filters['rspn'] = $areaLimit ? 1 : 0;

        return $this;
    }

    /**
     * Гео-кодирование по запросу (адрес/координаты).
     *
     * @param string $query
     *
     * @return self
     */
    public function setQuery($query)
    {
        $this->filters['geocode'] = (string) $query;

        return $this;
    }

    /**
     * Вид топонима (только для обратного геокодирования).
     *
     * @param string $kind
     *
     * @return self
     */
    public function setKind($kind)
    {
        $this->filters['kind'] = (string) $kind;

        return $this;
    }

    /**
     * Максимальное количество возвращаемых объектов (по-умолчанию 10).
     *
     * @param int $limit
     *
     * @return self
     */
    public function setLimit($limit)
    {
        $this->filters['results'] = (int) $limit;

        return $this;
    }

    /**
     * Количество объектов в ответе (начиная с первого), которое необходимо пропустить.
     *
     * @param int $offset
     *
     * @return self
     */
    public function setOffset($offset)
    {
        $this->filters['skip'] = (int) $offset;

        return $this;
    }

    /**
     * Предпочитаемый язык описания объектов.
     *
     * @param string $lang
     *
     * @return self
     */
    public function setLang($lang)
    {
        $this->filters['lang'] = (string) $lang;

        return $this;
    }

    /**
     * Ключ API Яндекс.Карт
     *
     * @see http://api.yandex.ru/maps/form.xml
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->filters['key'] = (string) $token;

        return $this;
    }
}
