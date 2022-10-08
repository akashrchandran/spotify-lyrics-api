<?php

class Spotify
{
	private $spotify_url = 'https://open.spotify.com/';
	private $lyrics_url = 'https://spclient.wg.spotify.com/color-lyrics/v2/track/';
	function get_token()
	{
		$sp_dc = getenv('SP_DC');
		if (!$sp_dc)
			throw new Exception("Please set SP_DC as a environmental variable.");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36",
			"App-platform: WebPlayer",
			"content-type: text/html; charset=utf-8",
			"cookie: sp_dc=$sp_dc;"
		));
		curl_setopt($ch, CURLOPT_URL, $this->spotify_url);
		$result = curl_exec($ch);
		$re = '~<script id="session" data-testid="session" type="application/json">(\S+)</script>~m';
		preg_match_all($re, $result, $matches, PREG_SET_ORDER, 0);
		$token_json = $matches[0][1];
		if (! $token_json)
			throw new Exception("The SP_DC set seems to be invalid, please correct it!");
		$token_file = fopen("config.json", "w") or die("Unable to open file!");;
		fwrite($token_file, $token_json);
	}

	function check_if_expire()
	{
		$check = file_exists("config.json");
		if ($check) {
			$json = file_get_contents("config.json");
			$timeleft = json_decode($json, true)['accessTokenExpirationTimestampMs'];
			$timenow = round(microtime(true) * 1000);
		}
		if (!$check || $timeleft < $timenow) {
			$this->get_token();
		}
	}

	function get_lyrics($track_id)
	{
		$json = file_get_contents('config.json');
		$token = json_decode($json, true)['accessToken'];
		$formated_url = $this->lyrics_url . $track_id . '?format=json&market=from_token';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36",
			"App-platform: WebPlayer",
			"authorization: Bearer $token"
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $formated_url);
		$result = curl_exec($ch);
		return $result;
	}
}
