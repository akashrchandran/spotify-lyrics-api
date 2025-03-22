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
    $reponse = json_encode(['error' => true, 'message' => 'url or trackid parameter is required!', 'usage' => 'https://github.com/akashrchandran/spotify-lyrics-api']);
    echo $reponse;
    return;
}
if ($url) {
    preg_match($re, $url, $matches, PREG_OFFSET_CAPTURE, 0);
    $trackid = $matches[1][0];
}
$spotify = new SpotifyLyricsApi\Spotify(getenv('SP_DC'));
$spotify->checkTokensExpire();
$reponse = $spotify->getLyrics(track_id: $trackid);
echo make_response($spotify, $reponse, $format);

function make_response($spotify, $response, $format)
{
    $json_res = json_decode($response, true);

    if ($json_res === null || !isset($json_res['lyrics'])) {
        http_response_code(404);
        return json_encode(['error' => true, 'message' => 'lyrics for this track is not available on spotify!']);
    }
    $lines = $format == 'lrc' ? $spotify->getLrcLyrics($json_res['lyrics']['lines']) : $json_res['lyrics']['lines'];
    if ($format == 'lrc') {
        $lines = $spotify->getLrcLyrics($json_res['lyrics']['lines']);
    } elseif ($format == 'srt') {
        $lines = $spotify->getSrtLyrics($json_res['lyrics']['lines']);
    } else {
        $lines =  $json_res['lyrics']['lines'];
    }
    $response = ['error' => false, 'syncType' => $json_res['lyrics']['syncType'], 'lines' => $lines];
    return json_encode($response);
}
