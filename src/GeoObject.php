<?php

namespace Chelout\Geocoder;

/**
 * Class GeoObject.
 *
 * @license The MIT License (MIT)
 */
class GeoObject
{
    protected $addressHierarchy = [
        'Country' => ['AdministrativeArea'],
        'AdministrativeArea' => ['SubAdministrativeArea', 'Locality'],
        'SubAdministrativeArea' => ['Locality'],
        'Locality' => ['DependentLocality', 'Thoroughfare'],
        'DependentLocality' => ['DependentLocality', 'Thoroughfare'],
        'Thoroughfare' => ['Premise'],
        'Premise' => [],
    ];

    protected $data;

    protected $rawData;

    public function __construct(array $rawData)
    {
        $data = [
            'Address' => $rawData['metaDataProperty']['GeocoderMetaData']['text'],
            'Kind' => $rawData['metaDataProperty']['GeocoderMetaData']['kind'],
        ];
        array_walk_recursive(
            $rawData,
            function ($value, $key) use (&$data) {
                if (in_array(
                    $key,
                    [
                        'CountryName',
                        'CountryNameCode',
                        'AdministrativeAreaName',
                        'SubAdministrativeAreaName',
                        'LocalityName',
                        'DependentLocalityName',
                        'ThoroughfareName',
                        'PremiseNumber',
                    ]
                )) {
                    $data[$key] = $value;
                }
            }
        );
        if (isset($rawData['Point']['pos'])) {
            $pos = explode(' ', $rawData['Point']['pos']);
            $data['Longitude'] = (float) $pos[0];
            $data['Latitude'] = (float) $pos[1];
        }
        $this->data = $data;
        $this->rawData = $rawData;
    }

    public function __sleep()
    {
        return ['data'];
    }

    /**
     * Необработанные данные.
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Обработанные данные.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Широта в градусах. Имеет десятичное представление с точностью до семи знаков после запятой.
     *
     * @return float|null
     */
    public function getLatitude()
    {
        return isset($this->data['Latitude']) ? $this->data['Latitude'] : null;
    }

    /**
     * Долгота в градусах. Имеет десятичное представление с точностью до семи знаков после запятой.
     *
     * @return float|null
     */
    public function getLongitude()
    {
        return isset($this->data['Longitude']) ? $this->data['Longitude'] : null;
    }

    /**
     * Полный адрес
     *
     * @return string|null
     */
    public function getAddress()
    {
        return isset($this->data['Address']) ? $this->data['Address'] : null;
    }

    /**
     * Тип
     *
     * @return string|null
     */
    public function getKind()
    {
        return isset($this->data['Kind']) ? $this->data['Kind'] : null;
    }

    /**
     * Страна.
     *
     * @return string|null
     */
    public function getCountry()
    {
        return isset($this->data['CountryName']) ? $this->data['CountryName'] : null;
    }

    /**
     * Код страны.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return isset($this->data['CountryNameCode']) ? $this->data['CountryNameCode'] : null;
    }

    /**
     * Административный округ.
     *
     * @return string|null
     */
    public function getAdministrativeAreaName()
    {
        return isset($this->data['AdministrativeAreaName']) ? $this->data['AdministrativeAreaName'] : null;
    }

    /**
     * Область/край.
     *
     * @return string|null
     */
    public function getSubAdministrativeAreaName()
    {
        return isset($this->data['SubAdministrativeAreaName']) ? $this->data['SubAdministrativeAreaName'] : null;
    }

    /**
     * Населенный пункт
     *
     * @return string|null
     */
    public function getLocalityName()
    {
        return isset($this->data['LocalityName']) ? $this->data['LocalityName'] : null;
    }

    /**
     * @return string|null
     */
    public function getDependentLocalityName()
    {
        return isset($this->data['DependentLocalityName']) ? $this->data['DependentLocalityName'] : null;
    }

    /**
     * Улица.
     *
     * @return string|null
     */
    public function getThoroughfareName()
    {
        return isset($this->data['ThoroughfareName']) ? $this->data['ThoroughfareName'] : null;
    }

    /**
     * Номер дома.
     *
     * @return string|null
     */
    public function getPremiseNumber()
    {
        return isset($this->data['PremiseNumber']) ? $this->data['PremiseNumber'] : null;
    }

    /**
     * Полный адрес
     *
     * @return array
     */
    public function getFullAddressParts()
    {
        return array_unique(
            $this->parseLevel(
                $this->rawData['metaDataProperty']['GeocoderMetaData']['AddressDetails']['Country'],
                'Country'
            )
        );
    }

    /**
     * @param array  $level
     * @param string $levelName
     * @param array  $address
     *
     * @return array
     */
    protected function parseLevel(array $level, $levelName, &$address = [])
    {
        if (! isset($this->addressHierarchy[$levelName])) {
            return;
        }

        $nameProp = 'Premise' === $levelName ? 'PremiseNumber' : $levelName . 'Name';
        if (isset($level[$nameProp])) {
            $address[] = $level[$nameProp];
        }

        foreach ($this->addressHierarchy[$levelName] as $child) {
            if (! isset($level[$child])) {
                continue;
            }
            $this->parseLevel($level[$child], $child, $address);
        }

        return $address;
    }
}
