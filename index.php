<?php
require 'spotify.php';
error_reporting(0);
header("Content-Type: application/json");

$trackid = $_GET['trackid'];

if (! $trackid) {
	http_response_code(400);
	$reponse = json_encode(["error" => true, "message" => "trackid parameter is required!"]);
	echo $reponse;
	return;
}
$spotify = new Spotify();
$spotify -> check_if_expire();
$reponse = $spotify -> get_lyrics(track_id: $trackid);
?>