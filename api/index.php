<?php

require_once __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$trackid = $_GET['trackid'] ?? null;
$url = $_GET['url'] ?? null;
$format = $_GET['format'] ?? null;

$re = '~[\bhttps://open.\b]*spotify[\b.com\b]*[/:]*track[/:]*([A-Za-z0-9]+)~';

if (!$trackid && !$url) {
	http_response_code(400);
	$reponse = json_encode(["error" => true, "message" => "url or trackid parameter is required!", "usage" => "https://github.com/akashrchandran/spotify-lyrics-api"]);
	echo $reponse;
	return;
}
if ($url) {
	preg_match($re, $url, $matches, PREG_OFFSET_CAPTURE, 0);
	$trackid = $matches[1][0];
}
$spotify = new SpotifyLyricsApi\Spotify(getenv( 'SP_DC' ));
$spotify->checkTokenExpire();
$reponse = $spotify -> getLyrics(track_id: $trackid);
echo make_reponse($reponse, $format);

function make_reponse($response, $format)
{
	global $spotify;
	$temp = json_decode($response, true)['lyrics'];
	if (!$temp) {
		http_response_code(404);
		return json_encode(["error" => true, "message" => "lyrics for this track is not available on spotify!"]);
	}
	if ($format == 'lrc') {
		$lines = $spotify -> getLrcLyrics($temp["lines"]);
		$response = ["error" => false, "syncType" => $temp["syncType"], "lines" => $lines];
	} else {
		$response = ["error" => false, "syncType" => $temp["syncType"], "lines" => $temp["lines"]];
	}
	return json_encode($response);
}

?>
