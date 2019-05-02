# hiscore-api
An api to retrieve highscores from retroPie mame2003.  This is a modification of https://github.com/CrazySpence/hiscore-php/, but in api form.

# install apache/php
https://www.raspberrypi.org/documentation/remote-access/web-server/apache.md

# install the api
```
cd /var/www/html/
sudo git clone https://github.com/kmcrawford/hiscore-api.git
```

# mame2003 hi scores location

By default this will use `/home/pi/RetroPie/roms/mame-libretro/mame2003/hi/` if you have your games in another loction please set the environment variable `HISCORE_LOCATION` to the path of your highscores.


# view api
http://retropie.local/hiscore-api/

# credit:
https://github.com/CrazySpence/hiscore-php/ <br>
http://forum.arcadecontrols.com/index.php/topic,83614.0.html

# example output

```
{
	"games": [{
		"scoreData": {
			"score": [42650, 40440, 28200, 27090, 26240],
			"initials": ["KEN", "CSC", "AAA", "KEN", "KEN"],
			"top": 42650
		},
		"game": "galaga"
	}, {
		"scoreData": {
			"score": 26440
		},
		"game": "mspacman"
	}, {
		"scoreData": {
			"score": 4190
		},
		"game": "pacman"
	}, {
		"scoreData": {
			"scores": [{
				"score": 10400,
				"scoreWithLeadingZeros": "010400",
				"initials": "   "
			}, {
				"score": 7650,
				"scoreWithLeadingZeros": "007650",
				"initials": "   "
			}, {
				"score": 6900,
				"scoreWithLeadingZeros": "006900",
				"initials": "KEN"
			}, {
				"score": 6100,
				"scoreWithLeadingZeros": "006100",
				"initials": "   "
			}, {
				"score": 5950,
				"scoreWithLeadingZeros": "005950",
				"initials": "   "
			}]
		},
		"game": "dkong"
	}]
}
```
