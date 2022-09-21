<?php
require 'spotify.php';
error_reporting(0);
header("Content-Type: application/json");

$trackid = $_GET['trackid'];
$url = $_GET['url'];
$typed = $_GET['format'];

$re = '~[\bhttps://open.\b]*spotify[\b.com\b]*[/:]*track[/:]*([A-Za-z0-9]+)~';

if (! $trackid && ! $url) {
	http_response_code(400);
	$reponse = json_encode(["error" => true, "message" => "url or trackid parameter is required!", "usage" => "https://github.com/akashrchandran/spotify-lyrics-api"]);
	echo $reponse;
	return;
}
if ($url) {
	preg_match($re, $url, $matches, PREG_OFFSET_CAPTURE, 0);
	$trackid = $matches[1][0];
}
$spotify = new Spotify();
$spotify -> check_if_expire();
$reponse = $spotify -> get_lyrics(track_id: $trackid);
echo make_reponse($reponse, $typed);

function make_reponse($response, $format)
{	
	$temp = json_decode($response, true)['lyrics'];
	if (! $temp) {
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
	}
	else {
		$response = ["error" => false, "syncType" => $temp["syncType"], "lines" => $temp["lines"]];
	}
	return json_encode($response);
}

function formatMS($milliseconds) {
	if ($milliseconds == 0) {
		return '00:00.00';
	}
	$seconds = floor($milliseconds / 1000);
    $minutes = floor($seconds / 60);
    $centi = $milliseconds % 1000;
    $seconds = $seconds % 60;
    $minutes = $minutes % 60;
    $format = '%02u:%02u.%03u';
    $time = sprintf($format, $minutes, $seconds, $centi);
    return rtrim($time, '0');
}
?>