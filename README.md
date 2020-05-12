# A Laravel Package class for calculating the phase of the Moon and you can get the moon rise and set time also.

MoonPhase is a PHP class for calculating the phase of the Moon, and other related variables. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).
Moon Rise/Set time is based on (https://dxprog.com/entry/calculate-moon-rise-and-set-in-php) .

## Installation

composer require rik0253/moon-phase


## Usage

Create an instance of the class by 'new Moonphase('YYYY-MM-DD');', supplying a date for when you want to determine the moon phase (if you don't then the current time will be used). You can then use the following class functions to access the properties of the object:

 - `phase()`: get the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
 - `getMoonData('illumination')`: get the illuminated fraction of the Moon (0 = New, 1 = Full).
 - `getMoonData('age')`: get the age of the Moon, in days.
 - `getMoonData('distance')`: get the distance of the Moon from the centre of the Earth (kilometres).
 - `getMoonData('diameter')`: get the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
 - `getMoonData('sundistance')`: get the distance to the Sun (kilometres).
 - `getMoonData('sundiameter')`: get the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
 - `getPhaseData('new_moon')`: get the time of the last New Moon (DateTime).
 - `getPhaseData('next_new_moon')`: get the time of the next New Moon (DateTime).
 - `getPhaseData('full_moon')`: get the time of the Full Moon in the current lunar cycle (DateTime).
 - `getPhaseData('next_full_moon')`: get the time of the next Full Moon in the current lunar cycle (DateTime).
 - `getPhaseData('first_quarter')`: get the time of the first quarter in the current lunar cycle (DateTime).
 - `getPhaseData('next_first_quarter')`: get the time of the next first quarter in the current lunar cycle (DateTime).
 - `getPhaseData('last_quarter')`: get the time of the last quarter in the current lunar cycle (DateTime).
 - `getPhaseData('next_last_quarter')`: get the time of the next last quarter in the current lunar cycle (DateTime).
 - `getPhaseName()`: get the phase name.

## New Usage

Now you can get the moon rise and set time in unix timestamp. You just need to access the property of the object:
 - `getMoonTimes('latitude','logitude')`: get the time of moon rise and moon set (Unix timestamp).


### Example

	//create an instance of the class, and use the current time
	$moon = new Moonphase('1990-04-21');
	$age = round($moon->get('age'), 1);
	$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
	$distance = round($moon->get('distance'), 2);
	$next = $moon->getPhaseData('next_new_moon');
	$phaseName = $moon->getPhaseName();
	echo "The moon is currently $age days old, and is therefore $stage. ";
	echo "It is $distance km from the centre of the Earth. ";
	echo "The next new moon is at $next.";
	//To get the moon rise/set time
	$moonTime = getMoonTimes(22.5655,88.3653);
	echo "Moon rise at ".date('Y-m-d H:i',strtotime($moonTime->moonrise));	
	echo "Moon set at ".date('Y-m-d H:i',strtotime($moonTime->moonset));	



## Help

For bugs/enhancements, feel free to either raise an issue or pull request in GitHub, or [contact me](das.sidd89@gmail.com).