<?php

// phpcs:ignoreFile
/** FIXME - cs ignore */
declare(strict_types=1);

namespace App\Airline\Enum;

use App\Airline\Value\AirlineInfo;

enum Airline: string
{
    case RYANAIR = 'Ryanair';
    case BUZZ = 'Buzz';
    case LAUDA = 'Lauda';
    case MALTA_AIR = 'Malta air';
    case RYANAIR_UK = 'Ryanair UK';
    case WIZZAIR = 'Wizzair';
    case WIZZAIR_UAE = 'Wizzair UAE';
    case WIZZAIR_UK = 'Wizzair UK';
    case EASYJET = 'Easyjet';
    case EASYJET_CH = 'Easyjet CH';
    case EASYJET_EU = 'Easyjet EU';
    case VUELING = 'Vueling';
    case VOLOTEA = 'Volotea';

    public function getInfo() : AirlineInfo
    {
        return match ($this) {
            self::RYANAIR => new AirlineInfo('Ryanair DAC', 'FR', 'RYR'),
            self::BUZZ =>  new AirlineInfo('Buzz', 'RR', 'RYS'),
            self::LAUDA =>  new AirlineInfo('Laudamotion GmbH', 'OE', 'LDM'),
            self::MALTA_AIR =>  new AirlineInfo('Malta air', 'AL', 'MAY'),
            self::RYANAIR_UK =>  new AirlineInfo('Ryanair UK', 'RK', 'RUK'),
            self::WIZZAIR =>  new AirlineInfo('Wizz Air Hungary Ltd.', 'W6', 'WZZ'),
            self::WIZZAIR_UAE =>  new AirlineInfo('Wizz Air Abu Dhabi', '5W', 'WAZ'),
            self::WIZZAIR_UK =>  new AirlineInfo('Wizz Air UK Ltd.', 'W9', 'WUK'),
            self::EASYJET =>  new AirlineInfo('EasyJet UK', 'U2', 'EZY'),
            self::EASYJET_EU =>  new AirlineInfo('EasyJet Europe', 'EC', 'EJU'),
            self::EASYJET_CH =>  new AirlineInfo('EasyJet Switzerland', 'DS', 'EZS'),
            self::VUELING =>  new AirlineInfo('Vueling S.A.', 'VY', 'VLG'),
            self::VOLOTEA =>  new AirlineInfo('Volotea', 'V7', 'VOE'),
        };
    }
    }
