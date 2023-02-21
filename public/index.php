<?php

require('../vendor/autoload.php');

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
$trackid = $_GET['trackid'];
$url = $_GET['url'];
$typed = $_GET['format'];

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
$spotify = new SpotifyLyricsApi\Spotify();
$spotify->check_if_expire();
$reponse = $spotify->get_lyrics(track_id: $trackid);
echo make_reponse($reponse, $typed);

function make_reponse($response, $format)
{
	$temp = json_decode($response, true)['lyrics'];
	if (!$temp) {
		http_response_code(404);
		return json_encode(["error" => true, "message" => "lyrics for this track is not available on spotify!"]);
	}
	if ($format == 'lrc') {
		$lines = array();
		foreach ($temp['lines'] as $lists) {
			$lrctime = formatMS($lists['startTimeMs']);
			array_push($lines, ["timeTag" => $lrctime, "words" => $lists['words']]);
		}
		$response = ["error" => false, "syncType" => $temp["syncType"], "lines" => $lines];
	} else {
		$response = ["error" => false, "syncType" => $temp["syncType"], "lines" => $temp["lines"]];
	}
	return json_encode($response);
}

function formatMS($milliseconds)
{
	$lrc_timetag = sprintf('%02d:%02d.%02d', ($milliseconds / 1000) / 60, ($milliseconds / 1000) % 60, ($milliseconds % 1000) / 10);
	return $lrc_timetag;
}

?>