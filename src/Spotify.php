<?php

namespace SpotifyLyricsApi;

/**
 * Class Spotify
 *
 * This class is responsible for interacting with the Spotify API.
 */
class Spotify
{
    private $token_url = 'https://open.spotify.com/get_access_token?reason=transport&productType=web_player';
    private $lyrics_url = 'https://spclient.wg.spotify.com/color-lyrics/v2/track/';
    private $sp_dc;
    private $cache_file;

    /**
     * Spotify constructor.
     *
     * @param string $sp_dc The Spotify Data Controller (sp_dc) cookie value.
     */
    function __construct($sp_dc)
    {
        $this->cache_file = sys_get_temp_dir() . '/spotify_token.json';
        $this->sp_dc = $sp_dc;
    }

    /**
     * Retrieves an access token from the Spotify and stores it in a file.
     * The file is stored in the working directory.
     */
    function getToken(): void
    {
        $sp_dc = $this->sp_dc;
        if (!$sp_dc)
            throw new SpotifyException('Please set SP_DC as a environmental variable.');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36',
            'App-platform: WebPlayer',
            'content-type: text/html; charset=utf-8',
            "cookie: sp_dc=$sp_dc;"
        ));
        curl_setopt($ch, CURLOPT_URL, $this->token_url);
        $result = curl_exec($ch);
        $token_json = json_decode($result, true);
        if (!$token_json || $token_json['isAnonymous'])
            throw new SpotifyException('The SP_DC set seems to be invalid, please correct it!');
        $token_file = fopen($this -> cache_file, 'w') or die('Unable to open file!');;
        fwrite($token_file, $result);
    }

    /**
     * Checks if the access token is expired and retrieves a new one if it is.
     * The function invokes getToken if the token is expired or the cache file is not found.
     */
    function checkTokenExpire(): void
    {
        $check = file_exists($this->cache_file);
        if ($check) {
            $json = file_get_contents($this->cache_file);
            $timeleft = json_decode($json, true)['accessTokenExpirationTimestampMs'];
            $timenow = round(microtime(true) * 1000);
        }
        if (!$check || $timeleft < $timenow) {
            $this->getToken();
        }
    }

    /**
     * Retrieves the lyrics of a track from the Spotify.
     * @param string $track_id The Spotify track id.
     * @return string The lyrics of the track in JSON format.
     */
    function getLyrics($track_id): string
    {
        $json = file_get_contents($this -> cache_file);
        $token = json_decode($json, true)['accessToken'];
        $formated_url = $this->lyrics_url . $track_id . '?format=json&market=from_token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36',
            'App-platform: WebPlayer',
            "authorization: Bearer $token"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $formated_url);
        $result = curl_exec($ch);
        return $result;
    }

    /*
    * It changes the format of the lyrics from milisecond to LRC format.
    * @param array $lyrics The lyrics of the track in JSON format.
    * @return array The lyrics of the track in LRC format.
    */
    function getLrcLyrics($lyrics): array
    {
        $lrc = array();
        foreach ($lyrics as $lines) {
            $lrctime = $this->formatMS($lines['startTimeMs']);
            array_push($lrc, ['timeTag' => $lrctime, 'words' => $lines['words']]);
        }
        return $lrc;
    }

    /**
     * Helper fucntion for getLrcLyrics to change miliseconds to [mm:ss.xx]
     * @param int $milliseconds The time in miliseconds.
     * @return string The time in [mm:ss.xx] format.
     */
    function formatMS($milliseconds): string
    {   
        $th_secs = intdiv($milliseconds, 1000);
        $lrc_timetag = sprintf('%02d:%02d.%02d', intdiv($th_secs , 60), $th_secs % 60, intdiv(($milliseconds % 1000), 10));
        return $lrc_timetag;
    }
}
