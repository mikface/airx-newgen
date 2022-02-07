<?php

declare(strict_types=1);

namespace App\Airline\Enum;

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

    /** @return array<string, string> */
    public function getInfo() : array
    {
        return match ($this) {
            self::RYANAIR => ['fullName' => 'Ryanair DAC', 'iata' => 'FR', 'icao' => 'RYR'],
            self::BUZZ => ['fullName' => 'Buzz', 'iata' => 'RR', 'icao' => 'RYS'],
            self::LAUDA => ['fullName' => 'Laudamotion GmbH', 'iata' => 'OE', 'icao' => 'LDM'],
            self::MALTA_AIR => ['fullName' => 'Malta air', 'iata' => 'AL', 'icao' => 'MAY'],
            self::RYANAIR_UK => ['fullName' => 'Ryanair UK', 'iata' => 'RK', 'icao' => 'RUK'],
            self::WIZZAIR => ['fullName' => 'Wizz Air Hungary Ltd.', 'iata' => 'W6', 'icao' => 'WZZ'],
            self::WIZZAIR_UAE => ['fullName' => 'Wizz Air Abu Dhabi', 'iata' => '5W', 'icao' => 'WAZ'],
            self::WIZZAIR_UK => ['fullName' => 'Wizz Air UK Ltd.', 'iata' => 'W9', 'icao' => 'WUK'],
            self::EASYJET => ['fullName' => 'EasyJet UK', 'iata' => 'U2', 'icao' => 'EZY'],
            self::EASYJET_EU => ['fullName' => 'EasyJet Europe', 'iata' => 'EC', 'icao' => 'EJU'],
            self::EASYJET_CH => ['fullName' => 'EasyJet Switzerland', 'iata' => 'DS', 'icao' => 'EZS'],
            self::VUELING => ['fullName' => 'Vueling S.A.', 'iata' => 'VY', 'icao' => 'VLG'],
            self::VOLOTEA => ['fullName' => 'Volotea', 'iata' => 'V7', 'icao' => 'VOE']
        };
    }
    }
