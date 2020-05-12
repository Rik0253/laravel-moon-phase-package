<?php

namespace Rik0253\Moonphase;

/**
 * This class gives the moon phase details
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/)
 * By Siddhanta Das
 * Licence: MIT
 */

class Moonphase
{

    /** @var int timestamp */
    protected $timestamp;

    /** @var float phase */
    protected $phase;

    /** @var float illumination */
    protected $illumination;

    /** @var float age */
    protected $age;

    /** @var float distance */
    protected $distance;

    /** @var float diameter */
    protected $diameter;

    /** @var float sun_distance */
    protected $sun_distance;

    /** @var float sun_diameter */
    protected $sun_diameter;

    /** @var float syn_month */
    protected $syn_month;

    /** @var array quarters */
    protected $quarters = false;

    /** @var string */
    protected $day;
    /** @var string */
    protected $month;
    /** @var integer */
    protected $year;

    /**
     * Constructor
     *
     * @param \DateTime|null $date
     */
    public function __construct($datetime = null)
    {
        
        if (is_null($datetime)) {
            $date = time();
            $this->day = date('d');
            $this->month = date('m');
            $this->year = date('Y');
        } else {
            $date = \Carbon\Carbon::parse($datetime)->timestamp;
            $this->day = \Carbon\Carbon::parse($datetime)->format('d');
            $this->month = \Carbon\Carbon::parse($datetime)->format('m');
            $this->year = \Carbon\Carbon::parse($datetime)->format('Y');
        }       
        $this->timestamp = $date;

        // Astronomical constants. 1980 January 0.0
        $epoch = 2444238.5;

        // Constants defining the Sun's apparent orbit
        $elonge = 278.833540; // Ecliptic longitude of the Sun at epoch 1980.0
        $elongp = 282.596403; // Ecliptic longitude of the Sun at perigee
        $eccent = 0.016718; // Eccentricity of Earth's orbit
        $sunsmax = 1.495985e8; // Semi-major axis of Earth's orbit, km
        $sunangsiz = 0.533128; // Sun's angular size, degrees, at semi-major axis distance

        // Elements of the Moon's orbit, epoch 1980.0
        $mmlong = 64.975464; // Moon's mean longitude at the epoch
        $mmlongp = 349.383063; // Mean longitude of the perigee at the epoch
        $mlnode = 151.950429; // Mean longitude of the node at the epoch
        $minc = 5.145396; // Inclination of the Moon's orbit
        $mecc = 0.054900; // Eccentricity of the Moon's orbit
        $mangsiz = 0.5181; // Moon's angular size at distance a from Earth
        $msmax = 384401; // Semi-major axis of Moon's orbit in km
        $mparallax = 0.9507; // Parallax at distance a from Earth
        $syn_month = 29.53058868; // Synodic month (new Moon to new Moon)

        $this->syn_month = $syn_month;

        // UNIX timstamp date, converting it to Julian
        $date = $date / 86400 + 2440587.5;

        // Calculation of the Sun's position
        $Day = $date - $epoch; // Date within epoch
        $N = $this->fixangle((360 / 365.2422) * $Day); // Mean anomaly of the Sun
        $M = $this->fixangle($N + $elonge - $elongp); // Convert from perigee co-ordinates to epoch 1980.0
        $Ec = $this->kepler($M, $eccent); // Solve equation of Kepler
        $Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
        $Ec = 2 * rad2deg(atan($Ec)); // True anomaly
        $Lambdasun = $this->fixangle($Ec + $elongp); // Sun's geocentric ecliptic longitude

        $F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent)); // Orbital distance factor
        $SunDist = $sunsmax / $F; // Distance to Sun in km
        $SunAng = $F * $sunangsiz; // Sun's angular size in degrees

        // Calculation of the Moon's position
        $ml = $this->fixangle(13.1763966 * $Day + $mmlong); // Moon's mean longitude
        $MM = $this->fixangle($ml - 0.1114041 * $Day - $mmlongp); // Moon's mean anomaly
        $MN = $this->fixangle($mlnode - 0.0529539 * $Day); // Moon's ascending node mean longitude
        $Ev = 1.2739 * sin(deg2rad(2 * ($ml - $Lambdasun) - $MM)); // Evection
        $Ae = 0.1858 * sin(deg2rad($M)); // Annual equation
        $A3 = 0.37 * sin(deg2rad($M)); // Correction term
        $MmP = $MM + $Ev - $Ae - $A3; // Corrected anomaly
        $mEc = 6.2886 * sin(deg2rad($MmP)); // Correction for the equation of the centre
        $A4 = 0.214 * sin(deg2rad(2 * $MmP)); // Another correction term
        $lP = $ml + $Ev + $mEc - $Ae + $A4; // Corrected longitude
        $V = 0.6583 * sin(deg2rad(2 * ($lP - $Lambdasun))); // Variation
        $lPP = $lP + $V; // True longitude
        $NP = $MN - 0.16 * sin(deg2rad($M)); // Corrected longitude of the node
        $y = sin(deg2rad($lPP - $NP)) * cos(deg2rad($minc)); // Y inclination coordinate
        $x = cos(deg2rad($lPP - $NP)); // X inclination coordinate

        $Lambdamoon = rad2deg(atan2($y, $x)) + $NP; // Ecliptic longitude
        $BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc)))); // Ecliptic latitude

        // Calculation of the phase of the Moon
        $MoonAge = $lPP - $Lambdasun; // Age of the Moon in degrees
        $MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2; // Phase of the Moon

        // Distance of moon from the centre of the Earth
        $MoonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc)));

        $MoonDFrac = $MoonDist / $msmax;
        $MoonAng = $mangsiz / $MoonDFrac; // Moon's angular diameter
        // $MoonPar = $mparallax / $MoonDFrac;                            // Moon's parallax

        // Store results
        $this->phase = $this->fixangle($MoonAge) / 360; // Phase (0 to 1)
        $this->illumination = $MoonPhase; // Illuminated fraction (0 to 1)
        $this->age = $syn_month * $this->phase; // Age of moon (days)
        $this->distance = $MoonDist; // Distance (kilometres)
        $this->diameter = $MoonAng; // Angular diameter (degrees)
        $this->sun_distance = $SunDist; // Distance to Sun (kilometres)
        $this->sun_diameter = $SunAng; // Sun's angular diameter (degrees)
    }

    /**
     * Fix angle
     *
     * @param float $a
     * @return float
     */
    protected function fixangle(float $a): float
    {
        return ($a - 360 * floor($a / 360));
    }

    /**
     * Kepler
     *
     * @param float $m
     * @param float $ecc
     * @return float
     */
    protected function kepler(float $m, float $ecc): float
    {
        // 1E-6
        $epsilon = 0.000001;
        $e = $m = deg2rad($m);

        do {
            $delta = $e - $ecc * sin($e) - $m;
            $e -= $delta / (1 - $ecc * cos($e));
        } while (abs($delta) > $epsilon);

        return $e;
    }
    /**
     * Calculates time  of the mean new Moon for a given base date.
     *     This argument K to this function is the precomputed synodic month index, given by:
     *     K = (year - 1900) * 12.3685
     *     where year is expressed as a year and fractional year.
     *
     * @param int   $date
     * @param float $k
     * @return float
     */
    protected function meanphase(int $date, float $k): float
    {
        // Time in Julian centuries from 1900 January 0.5
        $jt = ($date - 2415020.0) / 36525;
        $t2 = $jt * $jt;
        $t3 = $t2 * $jt;

        $nt1 = 2415020.75933 + $this->syn_month * $k
         + 0.0001178 * $t2
         - 0.000000155 * $t3
         + 0.00033 * sin(deg2rad(166.56 + 132.87 * $jt - 0.009173 * $t2));

        return $nt1;
    }

    /**
     * Given a K value used to determine the mean phase of the new moon and a
     *     phase selector (0.0, 0.25, 0.5, 0.75), obtain the true, corrected phase time.
     *
     * @param float $k
     * @param float $phase
     * @return float|null
     */
    protected function truephase(float $k, float $phase): ?float
    {
        $apcor = false;

        $k += $phase; // Add phase to new moon time
        $t = $k / 1236.85; // Time in Julian centuries from 1900 January 0.5
        $t2 = $t * $t; // Square for frequent use
        $t3 = $t2 * $t; // Cube for frequent use
        $pt = 2415020.75933// Mean time of phase
         + $this->syn_month * $k
         + 0.0001178 * $t2
         - 0.000000155 * $t3
         + 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2));

        $m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3; // Sun's mean anomaly
        $mprime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3; // Moon's mean anomaly
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3; // Moon's argument of latitude

        if ($phase < 0.01 || abs($phase - 0.5) < 0.01) {
            // Corrections for New and Full Moon
            $pt += (0.1734 - 0.000393 * $t) * sin(deg2rad($m))
             + 0.0021 * sin(deg2rad(2 * $m))
             - 0.4068 * sin(deg2rad($mprime))
             + 0.0161 * sin(deg2rad(2 * $mprime))
             - 0.0004 * sin(deg2rad(3 * $mprime))
             + 0.0104 * sin(deg2rad(2 * $f))
             - 0.0051 * sin(deg2rad($m + $mprime))
             - 0.0074 * sin(deg2rad($m - $mprime))
             + 0.0004 * sin(deg2rad(2 * $f + $m))
             - 0.0004 * sin(deg2rad(2 * $f - $m))
             - 0.0006 * sin(deg2rad(2 * $f + $mprime))
             + 0.0010 * sin(deg2rad(2 * $f - $mprime))
             + 0.0005 * sin(deg2rad($m + 2 * $mprime));

            $apcor = true;
        } else if (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01) {
            $pt += (0.1721 - 0.0004 * $t) * sin(deg2rad($m))
             + 0.0021 * sin(deg2rad(2 * $m))
             - 0.6280 * sin(deg2rad($mprime))
             + 0.0089 * sin(deg2rad(2 * $mprime))
             - 0.0004 * sin(deg2rad(3 * $mprime))
             + 0.0079 * sin(deg2rad(2 * $f))
             - 0.0119 * sin(deg2rad($m + $mprime))
             - 0.0047 * sin(deg2rad($m - $mprime))
             + 0.0003 * sin(deg2rad(2 * $f + $m))
             - 0.0004 * sin(deg2rad(2 * $f - $m))
             - 0.0006 * sin(deg2rad(2 * $f + $mprime))
             + 0.0021 * sin(deg2rad(2 * $f - $mprime))
             + 0.0003 * sin(deg2rad($m + 2 * $mprime))
             + 0.0004 * sin(deg2rad($m - 2 * $mprime))
             - 0.0003 * sin(deg2rad(2 * $m + $mprime));

            // First and last quarter corrections
            if ($phase < 0.5) {
                $pt += 0.0028 - 0.0004 * cos(deg2rad($m)) + 0.0003 * cos(deg2rad($mprime));
            } else {
                $pt += -0.0028 + 0.0004 * cos(deg2rad($m)) - 0.0003 * cos(deg2rad($mprime));
            }
            $apcor = true;
        }

        return $apcor ? $pt : null;
    }

    /**
     * Find time of phases of the moon which surround the current date. Five phases are found, starting and
     *     ending with the new moons which bound the current lunation.
     *
     * @return void
     */
    protected function phasehunt(): void
    {
        $sdate = $this->utc_to_julian($this->timestamp);
        $adate = $sdate - 45;
        $ats = $this->timestamp - 86400 * 45;
        $yy = (int) gmdate('Y', $ats);
        $mm = (int) gmdate('n', $ats);

        $k1 = floor(($yy + (($mm - 1) * (1 / 12)) - 1900) * 12.3685);
        $adate = $nt1 = $this->meanphase($adate, $k1);

        while (true) {
            $adate += $this->syn_month;
            $k2 = $k1 + 1;
            $nt2 = $this->meanphase($adate, $k2);

            // If nt2 is close to sdate, then mean phase isn't good enough, we have to be more accurate
            if (abs($nt2 - $sdate) < 0.75) {
                $nt2 = $this->truephase($k2, 0.0);
            }

            if ($nt1 <= $sdate && $nt2 > $sdate) {
                break;
            }

            $nt1 = $nt2;
            $k1 = $k2;
        }

        // Results in Julian dates
        $dates = [
            $this->truephase($k1, 0.0),
            $this->truephase($k1, 0.25),
            $this->truephase($k1, 0.5),
            $this->truephase($k1, 0.75),
            $this->truephase($k2, 0.0),
            $this->truephase($k2, 0.25),
            $this->truephase($k2, 0.5),
            $this->truephase($k2, 0.75),
        ];

        $this->quarters = [];
        foreach ($dates as $jdate) {
            // Convert to UNIX time
            $this->quarters[] = ($jdate - 2440587.5) * 86400;
        }
    }

    /**
     * UTC to Julian
     *
     * @param int $ts
     * @return float
     */
    protected function utc_to_julian(int $timestamp): float
    {
        return $timestamp / 86400 + 2440587.5;
    }

    /**
     * Get moon phase
     *
     * @return float
     */
    public function phase(): float
    {
        return $this->phase;
    }

    /**
     * Function to get Moon data
     *
     * @param string $property_name
     * @return int|float|array|null
     */
    public function getMoonData(string $property_name)
    {
        return $this->{$property_name} ?? null;
    }

    /**
     * Function to get Moon phase data
     *
     * @param string $name
     * @return float
     */
    public function getPhaseData(string $name): ?string
    {
        $phases = [
            'new_moon',
            'first_quarter',
            'full_moon',
            'last_quarter',
            'next_new_moon',
            'next_first_quarter',
            'next_full_moon',
            'next_last_quarter',
        ];

        if ($this->quarters === false) {
            $this->phasehunt();
        }

        return date('Y-m-d H:i:s',$this->quarters[array_flip($phases)[$name]]) ?? null;
    }

    /**
     * Get current phase name
     * There are eight phases, evenly split.
     * A "New Moon" occupies the 1/16th phases either side of phase = 0, and the rest follow from that.
     *
     * @return string
     */
    public function getPhaseName(): string
    {
        $names = [
            'New Moon',
            'Waxing Crescent',
            'First Quarter',
            'Waxing Gibbous',
            'Full Moon',
            'Waning Gibbous',
            'Third Quarter',
            'Waning Crescent',
            'New Moon',
        ];

        return $names[floor(($this->phase + 0.0625) * 8)];
    }


    /**
    * Implementation section of moon rise / set time 
    * It is taken from https://dxprog.com/entry/calculate-moon-rise-and-set-in-php
    * Some alteration is done by me to implement with the package.
    */

    /**
     * Calculates the moon rise/set for a given location and day of year
     */
    public function getMoonTimes($lat, $lon) {
    
        $utrise = $utset = 0;
        
        $timezone = (int)($lon / 15);
        $date = $this->modifiedJulianDate($this->month, $this->day, $this->year);
        $date -= $timezone / 24;
        $latRad = deg2rad($lat);
        $sinho = 0.0023271056;
        $sglat = sin($latRad);
        $cglat = cos($latRad);
        
        $rise = false;
        $set = false;
        $above = false;
        $hour = 1;
        $ym = $this->sinAlt($date, $hour - 1, $lon, $cglat, $sglat) - $sinho;
        
        $above = $ym > 0;
        while ($hour < 25 && (false == $set || false == $rise)) {
        
            $yz = $this->sinAlt($date, $hour, $lon, $cglat, $sglat) - $sinho;
            $yp = $this->sinAlt($date, $hour + 1, $lon, $cglat, $sglat) - $sinho;
            
            $quadout = $this->quad($ym, $yz, $yp);
            $nz = $quadout[0];
            $z1 = $quadout[1];
            $z2 = $quadout[2];
            $xe = $quadout[3];
            $ye = $quadout[4];
            
            if ($nz == 1) {
                if ($ym < 0) {
                    $utrise = $hour + $z1;
                    $rise = true;
                } else {
                    $utset = $hour + $z1;
                    $set = true;
                }
            }
            
            if ($nz == 2) {
                if ($ye < 0) {
                    $utrise = $hour + $z2;
                    $utset = $hour + $z1;
                } else {
                    $utrise = $hour + $z1;
                    $utset = $hour + $z2;
                }
            }
            
            $ym = $yp;
            $hour += 2.0;
        
        }
        // Convert to unix timestamps and return as an object
        $retVal = new \stdClass();
        $utrise = $this->convertTime($utrise);
        $utset = $this->convertTime($utset);
        $retVal->moonrise = $rise ? mktime($utrise['hrs'], $utrise['min'], 0, $this->month, $this->day, $this->year) : mktime(0, 0, 0, $this->month, $this->day + 1, $this->year);
        $retVal->moonset = $set ? mktime($utset['hrs'], $utset['min'], 0, $this->month, $this->day, $this->year) : mktime(0, 0, 0, $this->month, $this->day + 1, $this->year);
        return $retVal;
    
    }
    
    /**
     *  finds the parabola throuh the three points (-1,ym), (0,yz), (1, yp)
     *  and returns the coordinates of the max/min (if any) xe, ye
     *  the values of x where the parabola crosses zero (roots of the $this->quadratic)
     *  and the number of roots (0, 1 or 2) within the interval [-1, 1]
     *
     *  well, this routine is producing sensible answers
     *
     *  results passed as array [nz, z1, z2, xe, ye]
     */
    public function quad($ym, $yz, $yp) {

        $nz = $z1 = $z2 = 0;
        $a = 0.5 * ($ym + $yp) - $yz;
        $b = 0.5 * ($yp - $ym);
        $c = $yz;
        $xe = -$b / (2 * $a);
        $ye = ($a * $xe + $b) * $xe + $c;
        $dis = $b * $b - 4 * $a * $c;
        if ($dis > 0) {
            $dx = 0.5 * sqrt($dis) / abs($a);
            $z1 = $xe - $dx;
            $z2 = $xe + $dx;
            $nz = abs($z1) < 1 ? $nz + 1 : $nz;
            $nz = abs($z2) < 1 ? $nz + 1 : $nz;
            $z1 = $z1 < -1 ? $z2 : $z1;
        }

        return array($nz, $z1, $z2, $xe, $ye);
        
    }

    /**
     *  this rather mickey mouse function takes a lot of
     *  arguments and then returns the sine of the altitude of the moon
     */
    public function sinAlt($mjd, $hour, $glon, $cglat, $sglat) {
        
        $mjd += $hour / 24;
        $t = ($mjd - 51544.5) / 36525;
        $objpos = $this->minimoon($t);

        $ra = $objpos[1];
        $dec = $objpos[0];
        $decRad = deg2rad($dec);
        $tau = 15 * ($this->lmst($mjd, $glon) - $ra);

        return $sglat * sin($decRad) + $cglat * cos($decRad) * cos(deg2rad($tau));

    }

    /**
     *  returns an angle in degrees in the range 0 to 360
     */
    public function degRange($x) {
        $b = $x / 360;
        $a = 360 * ($b - (int)$b);
        $retVal = $a < 0 ? $a + 360 : $a;
        return $retVal;
    }

    public function lmst($mjd, $glon) {
        $d = $mjd - 51544.5;
        $t = $d / 36525;
        $lst = $this->degRange(280.46061839 + 360.98564736629 * $d + 0.000387933 * $t * $t - $t * $t * $t / 38710000);
        return $lst / 15 + $glon / 15;
    }

    /**
     * takes t and returns the geocentric ra and dec in an array mooneq
     * claimed good to 5' (angle) in ra and 1' in dec
     * tallies with another approximate method and with ICE for a couple of dates
     */
    public function minimoon($t) {
            
        $p2 = 6.283185307;
        $arc = 206264.8062;
        $coseps = 0.91748;
        $sineps = 0.39778;
        
        $lo = $this->frac(0.606433 + 1336.855225 * $t);
        $l = $p2 * $this->frac(0.374897 + 1325.552410 * $t);
        $l2 = $l * 2;
        $ls = $p2 * $this->frac(0.993133 + 99.997361 * $t);
        $d = $p2 * $this->frac(0.827361 + 1236.853086 * $t);
        $d2 = $d * 2;
        $f = $p2 * $this->frac(0.259086 + 1342.227825 * $t);
        $f2 = $f * 2;
        
        $sinls = sin($ls);
        $sinf2 = sin($f2);
        
        $dl = 22640 * sin($l);
        $dl += -4586 * sin($l - $d2);
        $dl += 2370 * sin($d2);
        $dl += 769 * sin($l2);
        $dl += -668 * $sinls;
        $dl += -412 * $sinf2;
        $dl += -212 * sin($l2 - $d2);
        $dl += -206 * sin ($l + $ls - $d2);
        $dl += 192 * sin($l + $d2);
        $dl += -165 * sin($ls - $d2);
        $dl += -125 * sin($d);
        $dl += -110 * sin($l + $ls);
        $dl += 148 * sin($l - $ls);
        $dl += -55 * sin($f2 - $d2);
        
        $s = $f + ($dl + 412 * $sinf2 + 541 * $sinls) / $arc;
        $h = $f - $d2;
        $n = -526 * sin($h);
        $n += 44 * sin($l + $h);
        $n += -31 * sin(-$l + $h);
        $n += -23 * sin($ls + $h);
        $n += 11 * sin(-$ls + $h);
        $n += -25 * sin(-$l2 + $f);
        $n += 21 * sin(-$l + $f);
        
        $L_moon = $p2 * $this->frac($lo + $dl / 1296000);
        $B_moon = (18520.0 * sin($s) + $n) / $arc;
        
        $cb = cos($B_moon);
        $x = $cb * cos($L_moon);
        $v = $cb * sin($L_moon);
        $w = sin($B_moon);
        $y = $coseps * $v - $sineps * $w;
        $z = $sineps * $v + $coseps * $w;
        $rho = sqrt(1 - $z * $z);
        $dec = (360 / $p2) * atan($z / $rho);
        $ra = (48 / $p2) * atan($y / ($x + $rho));
        $ra = $ra < 0 ? $ra + 24 : $ra;
        
        return array($dec, $ra);
        
    }

    /**
     *  returns the $this->fractional part of x as used in $this->minimoon and minisun
     */
    public static function frac($x) {
        $x -= (int)$x;
        return $x < 0 ? $x + 1 : $x;
    }

    /**
     * Takes the day, month, year and hours in the day and returns the
     * modified julian day number defined as mjd = jd - 2400000.5
     * checked OK for Greg era dates - 26th Dec 02
     */
    public function modifiedJulianDate($month, $day, $year) {
        
        if ($month <= 2) {
            $month += 12;
            $year--;
        }
        
        $a = 10000 * $year + 100 * $month + $day;
        $b = 0;
        if ($a <= 15821004.1) {
            $b = -2 * (int)(($year + 4716) / 4) - 1179;
        } else {
            $b = (int)($year / 400) - (int)($year / 100) + (int)($year / 4);
        }

        $a = 365 * $year - 679004;
        return $a + $b + (int)(30.6001 * ($month + 1)) + $day;
        
    }

    /**
     * Converts an hours decimal to hours and minutes
     */
    public function convertTime($hours) {

        $hrs = (int)($hours * 60 + 0.5) / 60.0;
        $h = (int)($hrs);
        $m = (int)(60 * ($hrs - $h) + 0.5);
        return array('hrs'=>$h, 'min'=>$m);
        
    }


}
