<?php

use Chelout\Geocoder\Geocoder;

// use Chelout\Geocoder\Geocoder;

require 'vendor/autoload.php';

$flats = [
    [
        'lat' => '55.85575',
        'lon' => '37.644073',
    ],
    [
        'lat' => '55.827403',
        'lon' => '38.264566',
    ],
    [
        'lat' => '55.702054',
        'lon' => '37.93466',
    ],
    [
        'lat' => '55.721255',
        'lon' => '37.902931',
    ],
    [
        'lat' => '55.55333',
        'lon' => '38.251316',
    ],
    [
        'lat' => '55.583709',
        'lon' => '37.739249',
    ],
    [
        'lat' => '55.49111',
        'lon' => '36.033304',
    ],
    [
        'lat' => '55.687406',
        'lon' => '37.907054',
    ],
    [
        'lat' => '55.710139',
        'lon' => '37.888944',
    ],
    [
        'lat' => '55.541576',
        'lon' => '37.722334',
    ],
    [
        'lat' => '55.788793',
        'lon' => '37.451339',
    ],
    [
        'lat' => '55.583699',
        'lon' => '37.739217',
    ],
    [
        'lat' => '55.558208',
        'lon' => '37.695501',
    ],
    [
        'lat' => '55.631519',
        'lon' => '37.516368',
    ],
    [
        'lat' => '55.860241',
        'lon' => '38.495711',
    ],
    [
        'lat' => '55.882621',
        'lon' => '37.419467',
    ],
    [
        'lat' => '55.707491',
        'lon' => '37.928632',
    ],
    [
        'lat' => '55.684681',
        'lon' => '37.901314',
    ],
    [
        'lat' => '55.683392',
        'lon' => '37.843786',
    ],
    [
        'lat' => '54.890071',
        'lon' => '38.060184',
    ],
    [
        'lat' => '54.889914',
        'lon' => '38.060161',
    ],
    [
        'lat' => '54.890106',
        'lon' => '38.060229',
    ],
    [
        'lat' => '54.890388',
        'lon' => '38.060165',
    ],
    [
        'lat' => '54.890281',
        'lon' => '38.060192',
    ],
    [
        'lat' => '54.890244',
        'lon' => '38.060162',
    ],
];

$i = 0;
foreach ($flats as $flat) {
    $geocoder = new Geocoder();

    $geocoder->setPoint($flat['lon'], $flat['lat']);

    if (false !== $geocoder->load()) {
        $response = $geocoder->getResponse();
        if ($response->getFoundCount()) {
            $geoObjects = $response->getData()['response']['GeoObjectCollection']['featureMember'];
            foreach ($geoObjects as $geoObject) {
                $kind = $geoObject['GeoObject']['metaDataProperty']['GeocoderMetaData']['kind'];
                $name = $geoObject['GeoObject']['name'];
                // dump($kind . ' - ' . $name);
            }
            ++$i;
        }
    } else {
        dump('Error');
    }
}
dump($i);

// Можно искать по точке
// $geocoder->setPoint(37.614006, 55.756994);

// Или можно икать по адресу
// $geocoder->setQuery('Московская область, Химки, улица Горшина, 2');
// $geocoder->setQuery('Москва, Тверская, 1');

if (false !== $geocoder->load()) {
    $response = $geocoder->getResponse();
    if ($response->getFoundCount()) {
        $metaData = $response->getData()['response']['GeoObjectCollection']['metaDataProperty'];
        $geoObjects = $response->getData()['response']['GeoObjectCollection']['featureMember'];
        foreach ($geoObjects as $geoObject) {
            $kind = $geoObject['GeoObject']['metaDataProperty']['GeocoderMetaData']['kind'];
            $name = $geoObject['GeoObject']['name'];
            dump($kind . ' - ' . $name);
            $data[$kind] = $name;
            // dump($geoObject['GeoObject']['name']);
        }
    }
}
die();

// $response = $geocoder->getResponse();
// $metaData = $response->getData()['response']['GeoObjectCollection']['metaDataProperty'];
// $geoObjects = $response->getData()['response']['GeoObjectCollection']['featureMember'];
// foreach ($geoObjects as $geoObject) {
//     $kind = $geoObject['GeoObject']['metaDataProperty']['GeocoderMetaData']['kind'];
//     $name = $geoObject['GeoObject']['name'];
//     dump($kind . ' - ' . $name);
//     $data[$kind] = $name;
//     // dump($geoObject['GeoObject']['name']);
// }
// // dump($rawData);
// die();

// $object = $response->getFirst();
// dump($object->getAddress()); // вернет адрес
// dump($object->getLatitude()); // широта
// dump($object->getLongitude()); // долгота
// dump($object->getData()); // необработанные данные
// // dump($object->getRawData());
// die();

// dump($response->getFoundCount()); // кол-во найденных адресов
// dump($response->getQuery()); // исходный запрос
// dump($response->getLatitude()); // широта для исходного запроса
// dump($response->getLongitude()); // долгота для исходного запроса

$collection = $response->getList();
foreach ($collection as $item) {
    // dump($item->getAddress()); // вернет адрес
    // dump($item->getLatitude()); // широта
    // dump($item->getLongitude()); // долгота
    // dump($item->getData()); // необработанные данные
    dump($item->getKind());
    dump($item->getFullAddressParts());
}
