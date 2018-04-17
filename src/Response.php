<?php

namespace Chelout\Geocoder;

/**
 * Class Response.
 *
 * @license The MIT License (MIT)
 */
class Response
{
    /**
     * @var \Chelout\Geocoder\GeoObject[]
     */
    protected $list = [];

    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
            foreach ($data['response']['GeoObjectCollection']['featureMember'] as $entry) {
                $this->list[] = new \Chelout\Geocoder\GeoObject($entry['GeoObject']);
            }
        }
    }

    /**
     * Исходные данные.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \Chelout\Geocoder\GeoObject[]
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return null|GeoObject
     */
    public function getFirst()
    {
        if (count($this->list)) {
            return $this->list[0];
        }
    }

    /**
     * Возвращает исходный запрос
     *
     * @return string|null
     */
    public function getQuery()
    {
        $result = null;
        if (isset($this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'])) {
            $result = $this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['request'];
        }

        return $result;
    }

    /**
     * Кол-во найденных результатов.
     *
     * @return int
     */
    public function getFoundCount()
    {
        $result = null;
        if (isset($this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'])) {
            $result = (int) $this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'];
        }

        return $result;
    }

    /**
     * Широта в градусах. Имеет десятичное представление с точностью до семи знаков после запятой.
     *
     * @return float|null
     */
    public function getLatitude()
    {
        $result = null;
        if (isset($this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['Point']['pos'])) {
            list(, $latitude) = explode(' ', $this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['Point']['pos']);
            $result = (float) $latitude;
        }

        return $result;
    }

    /**
     * Долгота в градусах. Имеет десятичное представление с точностью до семи знаков после запятой.
     *
     * @return float|null
     */
    public function getLongitude()
    {
        $result = null;
        if (isset($this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['Point']['pos'])) {
            list($longitude) = explode(' ', $this->data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['Point']['pos']);
            $result = (float) $longitude;
        }

        return $result;
    }
}
