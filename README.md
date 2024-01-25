<!--
 Copyright (C) 2022 Akash R Chandran
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as
 published by the Free Software Foundation, either version 3 of the
 License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.
 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<div align="center">
 
![spotify-lyrics-api](https://github.com/akashrchandran/spotify-lyrics-api/assets/78685510/12000a6d-ee53-41c4-9c69-98bc2559c5ed)

</div>

---
 
<div align="center">

A Rest API for fetching lyrics from Spotify which is powered by Musixmatch. Commandline version is available [akashrchandran/syrics](https://github.com/akashrchandran/syrics).
 
</div>

> [!WARNING]
> This project is probably against Spotify TOS. Use at your own risks.
 
> [!NOTE]
> Changed this project into a template repository, deploy your own version. If you need help, don't hesitate to open an issue.
 # Install using Composer
```
composer require akashrchandran/spotify-lyrics-api
```
# Fetching Lyrics


> For now it only supports track id or link.


## Using GET Requests
> You have to use query paramters to send data

__Available Parameters:__

| Parameter      | Default value                                               | Type             | Description                                                                                                                                    |
| -------------- | ----------------------------------------------------------- | ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| `trackid `       | None                                             | String           | The trackid from spotify.                                                                                            |
| `url`       | None                                              | String           | The url of the track from spotify.                                                                                            |
| `format`        | `"id3"`                                                  | String           | The format of lyrics required. It has 2 options either `id3` or `lrc`. |

> You must specify either __trackid__ or __url__, otherwise it will retuen error.

### Examples

__Using trackid__

```
http://localhost:8080/?trackid=5f8eCNwTlr0RJopE9vQ6mB
```
__Using url__

```
http://localhost:8080/?url=https://open.spotify.com/track/5f8eCNwTlr0RJopE9vQ6mB?autoplay=true
```
response:

```json
{
    "error": false,
    "syncType": "LINE_SYNCED",
    "lines": [
        {
            "startTimeMs": "960",
            "words": "One, two, three, four",
            "syllables": [],
            "endTimeMs": "0"
        },
        {
            "startTimeMs": "4020",
            "words": "Ooh-ooh, ooh-ooh-ooh",
            "syllables": [],
            "endTimeMs": "0"
        }
    ]
}
```
__Changing format to lrc__
```
http://localhost:8080/?trackid=5f8eCNwTlr0RJopE9vQ6mB&format=lrc
```
response:

```json
{
    "error": false,
    "syncType": "LINE_SYNCED",
    "lines": [
        {
            "timeTag": "00:00.96",
            "words": "One, two, three, four"
        },
        {
            "timeTag": "00:04.02",
            "words": "Ooh-ooh, ooh-ooh-ooh"
        }
    ]
}
```

### Responses
> Different Responses given out by the API, are listed here.

If any error occurs the value of the error key will be set to `true` else `false`
```JSON
"error": false //no error occured
```
Most of the lyrics are time synced or have timetags and some aren't time synced or have timetags. To differentiate between synced and unsynced we have key `syncType`.
```JSON
"syncType": "LINE_SYNCED"
```
> Musixmatch supports Line synced and Word synced type of timed lyrics. Line Synced is the timetag is given till which the line is sang and the word synced lyrics time specifed when the word comes up in the song. For now Spotify only supports line synced. Maybe they would support word synced in the future :/.

__LINE Synced__
```JSON
{
    "error": false,
    "syncType": "LINE_SYNCED",
    "lines": [
        {
            "timeTag": "00:00.96",
            "words": "One, two, three, four"
        },
        {
            "timeTag": "00:04.02",
            "words": "Ooh-ooh, ooh-ooh-ooh"
        }
    ]
}
```
__NOT Synced or Unsynced__
> Note the `timeTags` is set to `00:00.00`.
```JSON
{
    "error": false,
    "syncType": "UNSYNCED",
    "lines": [
        {
            "timeTag": "00:00.00",
            "words": "jaane nahin denge tuje"
        },
        {
            "timeTag": "00:00.00",
            "words": "chaahe tujh ko rab bulaa le, hum naa rab se darane waale"
        }
    ]
}
```
### Error Messages

__When trackid and url both are not given__ (400 Bad Request)

error response:
```json
{
    "error": true,
    "message": "url or trackid parameter is required!"
}
```

__When no lyrics found on spotify for given track__ (404 Not Found)

error response:
```json
{
    "error": true,
    "message": "lyrics for this track is not available on spotify!"
}
```

# Using as package
> Install using `composer require akashrchandran/spotify-lyrics-api`.

Include the package's autoloader file in your PHP code and call class `Spotify()`.

```PHP
<?php
require('./vendor/autoload.php');

$spotify = new SpotifyLyricsApi\Spotify("SP_DC here");
$spotify->checkTokenExpire();
$reponse = $spotify -> getLyrics(track_id: "1418IuVKQPTYqt7QNJ9RXN");
?>
```


![image](https://user-images.githubusercontent.com/78685510/236701387-23165a0e-1c94-474d-aea1-41d8b8464f66.png)


# Deployment

> Want to host your own version of this API, But first you need SP_DC cookie from spotify

__Finding SP_DC__

You will find the detailed guide [here](https://github.com/akashrchandran/syrics/wiki/Finding-sp_dc).

__Heroku__


[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://dashboard.heroku.com/new?template=https://github.com/akashrchandran/spotify-lyrics-api)

__Vercel__

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Fakashrchandran%2Fspotify-lyrics-api%2Ftree%2Fmain&env=SP_DC&envDescription=Want%20to%20host%20your%20own%20version%20of%20this%20API%2C%20then%20you'll%20first%20need%20SP_DC%20cookie%20from%20spotify.&envLink=https%3A%2F%2Fgithub.com%2Fakashrchandran%2Fsyrics%2Fwiki%2FFinding-sp_dc&project-name=spotify-lyrics-api&repository-name=spotify-lyrics-api&redirect-url=https%3A%2F%2Fgithub.com%2Fakashrchandran%2Fspotify-lyrics-api%23examples)


__Run locally__

> use git to clone the repo to your local machine or you can download the latest [zip](https://github.com/akashrchandran/spotify-lyrics-api/archive/refs/heads/main.zip) file and extract it.

*You need to have PHP installed on you machine to run this program.*

__Enter into the folder via terminal__
```sh
cd spotify-lyrics-api
```

__Set SP_DC token as environment variable temprorily__
```sh
export SP_DC=[token here and remove the square brackets]
```

__Start the server__
```
php -S localhost:8000 api/index.php
```
now open your browser and type `localhost:8080` and you should see the program running.
# Credits

• [Me](https://akashrchandran.in)
  -> For everything.
