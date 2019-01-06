<?php

$router->group(["prefix" => "/api/praytimes"], function() use ($router){
    $router->get('/calculate', 'PrayerTimesController@calculate');
});
