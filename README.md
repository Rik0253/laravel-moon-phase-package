# A Laravel Package class for calculating the phase of the Moon.

MoonPhase is a PHP class for calculating the phase of the Moon, and other related variables. It is based on [Moontool for Windows](http://www.fourmilab.ch/moontoolw/).

## Installation



## Usage

Create an instance of the class by 'new Moonphase('YYYY-MM-DD');', supplying a date for when you want to determine the moon phase (if you don't then the current time will be used). You can then use the following class functions to access the properties of the object:

 - `phase()`: the terminator phase angle as a fraction of a full circle (i.e., 0 to 1). Both 0 and 1 correspond to a New Moon, and 0.5 corresponds to a Full Moon.
 - `getMoonData('illumination')`: the illuminated fraction of the Moon (0 = New, 1 = Full).
 - `getMoonData('age')`: the age of the Moon, in days.
 - `getMoonData('distance')`: the distance of the Moon from the centre of the Earth (kilometres).
 - `getMoonData('diameter')`: the angular diameter subtended by the Moon as seen by an observer at the centre of the Earth (degrees).
 - `getMoonData('sundistance')`: the distance to the Sun (kilometres).
 - `getMoonData('sundiameter')`: the angular diameter subtended by the Sun as seen by an observer at the centre of the Earth (degrees).
 - `getPhaseData('new_moon')`: the time of the last New Moon (UNIX timestamp).
 - `getPhaseData('next_new_moon')`: the time of the next New Moon (UNIX timestamp).
 - `getPhaseData('full_moon')`: the time of the Full Moon in the current lunar cycle (UNIX timestamp).
 - `getPhaseData('next_full_moon')`: the time of the next Full Moon in the current lunar cycle (UNIX timestamp).
 - `getPhaseData('first_quarter')`: the time of the first quarter in the current lunar cycle (UNIX timestamp).
 - `getPhaseData('next_first_quarter')`: the time of the next first quarter in the current lunar cycle (UNIX timestamp).
 - `getPhaseData('last_quarter')`: the time of the last quarter in the current lunar cycle (UNIX timestamp).
 - `getPhaseData('next_last_quarter')`: the time of the next last quarter in the current lunar cycle (UNIX timestamp).
 - `getPhaseName()`: the [phase name](http://aa.usno.navy.mil/faq/docs/moon_phases.php).

### Example

	// create an instance of the class, and use the current time
	$moon = new Moonphase('1990-04-21');
	$age = round($moon->get('age'), 1);
	$stage = $moon->phase() < 0.5 ? 'waxing' : 'waning';
	$distance = round($moon->get('distance'), 2);
	$next = gmdate('G:i:s, j M Y', $moon->get_phase('next_new_moon'));
	echo "The moon is currently $age days old, and is therefore $stage. ";
	echo "It is $distance km from the centre of the Earth. ";
	echo "The next new moon is at $next.";

## Help

For bugs/enhancements, feel free to either raise an issue or pull request in GitHub, or [contact me](das.sidd89@gmail.com).