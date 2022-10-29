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
<h1 align="center">
Spotify Lyrics API
</h1>

<div align="center">

[![shield made-with-php](https://img.shields.io/badge/PHP->=%208.1-white?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
 
</div>
 
 <div align="center">

A Rest API for fetching lyrics from Spotify which is powered by Musixmatch. Commandline version is available [akashrchandran/syrics](https://github.com/akashrchandran/syrics).
 
</div>
 
## Fetching Lyrics
> For now it only supports track id or link.

### Using GET Requests
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
https://spotify-lyric-api.herokuapp.com/?trackid=5f8eCNwTlr0RJopE9vQ6mB
```
__Using url__

```
https://spotify-lyric-api.herokuapp.com/?url=https://open.spotify.com/track/5f8eCNwTlr0RJopE9vQ6mB?autoplay=true
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
https://spotify-lyric-api.herokuapp.com/?trackid=5f8eCNwTlr0RJopE9vQ6mB&format=lrc
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
### Error Messages

__When trackid and url both are not given__ (400 Bad Request)

error response:
```json
{
    "error": true,
    "message": "url or trackid parameter is required!"
}
```

__When no lyrics found on spotify for given track__ (400 Not Found)

error response:
```json
{
    "error": true,
    "message": "lyrics for this track is not available on spotify!"
}
```

## Deployment

> Want to host your own version of this API, But first you need SP_DC cookie from spotify

__Find SP_DC__

You will find the detailed guide [here](https://github.com/akashrchandran/syrics/wiki/Finding-sp_dc).

__Heroku__


[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://dashboard.heroku.com/new?template=https://github.com/akashrchandran/spotify-lyrics-api)

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
php -S localhost:8000
```
now open your browser and type `localhost:8080` and you should see the program running.
## Credits

• [Me](https://akashrchandran.in)
  -> For everything.