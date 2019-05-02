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

