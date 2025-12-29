<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SpotifyLyricsApi\Spotify;
use SpotifyLyricsApi\SpotifyException;

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
    echo json_encode(['error' => true, 'message' => 'url or trackid parameter is required!', 'usage' => 'https://github.com/akashrchandran/spotify-lyrics-api']);
    return;
}
if ($url) {
    preg_match($re, $url, $matches, PREG_OFFSET_CAPTURE, 0);
    $trackid = $matches[1][0];
}

try {
    $spotify = new Spotify(getenv('SP_DC'));
    $spotify->checkTokenExpire();
    $lyricsData = $spotify->getLyrics(track_id: $trackid);
    echo make_response($spotify, $lyricsData, $format);
} catch (SpotifyException $e) {
    $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($statusCode);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

function make_response($spotify, $lyricsData, $format)
{
    $json_res = $lyricsData;
    if ($format == 'lrc') {
        $lines = $spotify->getLrcLyrics($json_res['lyrics']['lines']);
    } elseif ($format == 'srt') {
        $lines = $spotify->getSrtLyrics($json_res['lyrics']['lines']);
    } elseif ($format == 'raw') {
        $lines = $spotify->getRawLyrics($json_res['lyrics']['lines']);
    } else {
        $lines =  $json_res['lyrics']['lines'];
    }
    $response = ['error' => false, 'syncType' => $json_res['lyrics']['syncType'], 'lines' => $lines];
    return json_encode($response);
}
