<?php

// REQUIRE php7.0-xml

function gracenote_register($client_id, $client_tag) {

$url = "https://c".$client_id.".web.cddbp.net/webapi/xml/1.0/";

$query = <<< EOD
<QUERIES>
  <QUERY CMD="REGISTER">
    <CLIENT>$client_id-$client_tag</CLIENT>
  </QUERY>
</QUERIES>
EOD;

$context = stream_context_create(
  array(
    'http' => array(
    'method' => "POST",
    'header' => "Content-type: application/x-www-form-urlencoded\r\n"
      . "Content-Length: " . strlen($query) . "\r\n",
    'content' => $query)
  )
);

$ret = file_get_contents($url, false, $context);
$xml = new SimpleXMLElement($ret);

if ((string)$xml->RESPONSE['STATUS'] == "OK") {
  return (string)$xml->RESPONSE->USER;
} else {
  return false;
}

}


function gracenote_album_toc($client_id, $client_tag, $user_id, $toc) {

$url = "https://c".$client_id.".web.cddbp.net/webapi/xml/1.0/";
$all_toc = implode(" ", $toc);

$query = <<< EOD
<QUERIES>
  <AUTH>
    <CLIENT>$client_id-$client_tag</CLIENT>
    <USER>$user_id</USER>
  </AUTH>
  <QUERY CMD="ALBUM_TOC">
    <MODE>SINGLE_BEST</MODE>
    <TOC>
      <OFFSETS>$all_toc</OFFSETS>
  </TOC>
</QUERY>
</QUERIES>
EOD;

$context = stream_context_create(
  array(
    'http' => array(
    'method' => "POST",
    'header' => "Content-type: application/x-www-form-urlencoded\r\n"
      . "Content-Length: " . strlen($query) . "\r\n",
    'content' => $query)
  )
);
    
$ret = file_get_contents($url, false, $context);
$xml = new SimpleXMLElement($ret);

if ((string)$xml->RESPONSE['STATUS'] == "NO_MATCH") {
  return false;
} elseif ((string)$xml->RESPONSE['STATUS'] == "OK") {
  return gracenote_xml_parse($xml);
}

}


function gracenote_album_fetch($client_id, $client_tag, $user_id, $id) {

$url = "https://c".$client_id.".web.cddbp.net/webapi/xml/1.0/";

$query = <<< EOD
<QUERIES>
  <AUTH>
    <CLIENT>$client_id-$client_tag</CLIENT>
    <USER>$user_id</USER>
  </AUTH>
  <QUERY CMD="ALBUM_FETCH">
    <GN_ID>$id</GN_ID>
  </QUERY>
</QUERIES>
EOD;

$context = stream_context_create(
  array(
    'http' => array(
    'method' => "POST",
    'header' => "Content-type: application/x-www-form-urlencoded\r\n"
      . "Content-Length: " . strlen($query) . "\r\n",
    'content' => $query)
  )
);

$ret = file_get_contents($url, false, $context);
$xml = new SimpleXMLElement($ret);

if ((string)$xml->RESPONSE['STATUS'] == "OK") {
  return gracenote_xml_parse($xml);
} else {
  return false;
}

}


function gracenote_xml_parse($xml) {

$albums = array();
foreach ($xml->RESPONSE->ALBUM as $album) {

$tracks = array();
foreach ($album->TRACK as $track) {
$tracks[] = array(
(string)$track->TRACK_NUM,
(string)$track->GN_ID,
(string)$track->ARTIST,
(string)$track->TITLE,
array((string)$track->GENRE, (string)$track->GENRE['NUM'], (string)$track->GENRE['ID'])
);
}

$albums[] = array(
(string)$album->GN_ID,
(string)$album->ARTIST,
(string)$album->TITLE,
(string)$album->PKG_LANG,
(string)$album->DATE,
array((string)$album->GENRE, (string)$album->GENRE['NUM'], (string)$album->GENRE['ID']),
(string)$album->TRACK_COUNT,
$tracks
);

}

return $albums;

}

?>
