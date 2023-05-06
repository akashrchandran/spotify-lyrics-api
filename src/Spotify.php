<?php

namespace SpotifyLyricsApi;

class Spotify
 {
    private $token_url = 'https://open.spotify.com/get_access_token?reason=transport&productType=web_player';
    private $lyrics_url = 'https://spclient.wg.spotify.com/color-lyrics/v2/track/';
    /**
    * Retrieves an access token from the Spotify and stores it in a file.
    * The file is stored in the working directory.
    */

    function getToken(): void
 {
        $sp_dc = 'AQB0khNFpwXip80j-Yme5BZpQ6z3cqMPNbvF1JcRBHKDwdO_TmjpXZQ36JWkw2jA_30LIYf7z4Igj3wXqRs9OVeWl6cEx7bk2KLwwiP3dxL5M74PGwTPBOT-w02lyazIwF9OX487EkGQFRfQxnF-6-7lZqi5_g-Q';
        //getenv( 'SP_DC' );
        if ( !$sp_dc )
        throw new SpotifyException( 'Please set SP_DC as a environmental variable.' );
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_TIMEOUT, 600 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36',
            'App-platform: WebPlayer',
            'content-type: text/html; charset=utf-8',
            "cookie: sp_dc=$sp_dc;"
        ) );
        curl_setopt($ch, CURLOPT_URL, $this->token_url);
        $result = curl_exec( $ch );
        $token_json = json_decode( $result, true );
        if ( !$token_json || $token_json[ 'isAnonymous' ] )
        throw new SpotifyException('The SP_DC set seems to be invalid, please correct it!' );
        $token_file = fopen('.cache', 'w' ) or die( 'Unable to open file!' );
        ;
        fwrite( $token_file, $result );
    }

    function checkTokenExpire()
 {
        $check = file_exists( '.cache' );
        if ( $check ) {
            $json = file_get_contents( '.cache' );
            $timeleft = json_decode( $json, true )[ 'accessTokenExpirationTimestampMs' ];
            $timenow = round( microtime( true ) * 1000 );
        }
        if ( !$check || $timeleft < $timenow ) {
            $this->getToken();
        }
    }

    function getLyrics( $track_id ): string
 {
        $json = file_get_contents( 'config.json' );
        $token = json_decode( $json, true )[ 'accessToken' ];
        $formated_url = $this->lyrics_url . $track_id . '?format=json&market=from_token';

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36',
            'App-platform: WebPlayer',
            "authorization: Bearer $token"
        ) );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $ch, CURLOPT_URL, $formated_url );
        $result = curl_exec( $ch );
        return $result;
    }

    function getLrcLyrics( $lyrics ): array
 {
        $lrc = array();
        foreach ( $lyrics as $lines ) {
            $lrctime = $this -> formatMS( $lines[ 'startTimeMs' ] );
            array_push( $lrc, [ 'timeTag' => $lrctime, 'words' => $lines[ 'words' ] ] );
        }
        return $lrc;
    }

    function formatMS( $milliseconds ): string
 {
        $lrc_timetag = sprintf( '%02d:%02d.%02d', ( $milliseconds / 1000 ) / 60, ( $milliseconds / 1000 ) % 60, ( $milliseconds % 1000 ) / 10 );
        return $lrc_timetag;
    }
}

?>