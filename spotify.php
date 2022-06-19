<?php

class Spotify
{
	private $sp_dc;
	private $spotify_url = 'https://open.spotify.com/';
	function __construct($sp_dc)
	{
		$this->sp_dc = $sp_dc;
	}

	function get_token($sp_dc)
	{
		echo $sp_dc;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.0.0 Safari/537.36",
			"app-platform: WebPlayer", 
			"Cookie: sp_dc = $sp_dc"
		));
		curl_setopt($ch, CURLOPT_URL, 'https://open.spotify.com/');
		$result = curl_exec($ch);
		$re = '~<script id="config" data-testid="config" type="application/json">(\S+)</script>~m';
		preg_match_all($re, $result, $matches, PREG_SET_ORDER, 0);
		$token_json = $matches[0][1];
		$token_file = fopen("config.json", "w") or die("Unable to open file!");;
		fwrite($token_file, $token_json);
		echo $token_json;
	}
}

$new = new Spotify('trial');
$new->get_token('trial');
