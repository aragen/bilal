<?php

namespace App\Helpers\PrayerTimes\Methods;

use GeniusTS\PrayerTimes\Methods\Method;

class Kemenag extends Method
{
    /**
     * @var array<float>
     */
    protected $angles = [
        'fajr' => 20.0,
        'isha' => 18.0,
    ];
    /**
     * @var float
     */
    protected $interval = 0;
    /**
     * @var string
     */
    protected $name = 'kemenag';
    /**
     * Get fajr angle
     *
     * @return float
     */
    public function fajrAngle(): float
    {
        return $this->angles['fajr'];
    }
    /**
     * Get isha angle
     *
     * @return float
     */
    public function ishaAngle(): float
    {
        return $this->angles['isha'];
    }
    /**
     * Get Isha interval
     *
     * @return mixed
     */
    public function ishaInterval(): int
    {
        return $this->interval;
    }
    /**
     * Get method name
     *
     * @return mixed
     */
    public function name()
    {
        return $this->name;
    }
}