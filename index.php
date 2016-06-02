<?php
require("gracenote.lib.php");

//////////////////////////////////////////////////

$client_id = "12345678";
$client_tag = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";

//////////////////////////////////////////////////

// Get Param
if(isset($_POST['cmd']) && isset($_POST['hello']) && isset($_POST['proto'])){
$cmd = $_POST['cmd'];
$hello = $_POST['hello'];
$proto = $_POST['proto'];

} elseif(isset($_GET['cmd']) && isset($_GET['hello']) && isset($_GET['proto'])){
$cmd = $_GET['cmd'];
$hello = $_GET['hello'];
$proto = $_GET['proto'];

} else {
// No Param
print("no param");
die();

}

$user_id = gracenote_register($client_id, $client_tag);
if ($user_id == false) {
print("not register");
die();
}

//$cmd = "cddb query 6A0A4E07 7 0 18540 37041 55542 74029 160921 179417 2638";
//$hello = "test example.com ExactAudioCopy v0.99pb6";
//$proto = "6";

$cmd = explode(" ", $cmd);
$hello = explode(" ", $hello);

$cmd_cddb = array_shift($cmd);
$cmd_mode = array_shift($cmd);

if ($cmd_mode == "query") {
// Query Command
$cmd_discid = array_shift($cmd);
$cmd_track = array_shift($cmd);
$cmd_seconds = array_pop($cmd);

// Lead-Out Calc (1 Seconds -> 75 Frame)
array_push($cmd, $cmd_seconds * 75);

// Pregap Offset Adjust (+150 Frame)
if ($cmd[0] == 0) {
  foreach ($cmd as &$value) {
    $value = $value + 150;
  }
}

} elseif ($cmd_mode = "read") {
// Read Command
$cmd_cat = array_shift($cmd);
$cmd_discid = array_shift($cmd);
}


if ($cmd_mode == "query") {
// Query Command
$ret = gracenote_album_toc($client_id, $client_tag, $user_id, $cmd);

if ($ret) {
// match
print("211 Found inexact matches, list follows (until terminating `.')\n");
foreach ($ret as $album) {
 print("Misc ".$album[0]." ".$album[1]);
 if ($album[2]) {
  print(" / ".$album[2]."\n");
 } else {
  print("\n");
 }
}
print(".\n");

} else {
print("202 No match for disc ID ".$cmd_discid.".");
}

} elseif ($cmd_mode == "read") {
// Read Command
$ret = gracenote_album_fetch($client_id, $client_tag, $user_id, $cmd_discid);

if ($ret) {
$album = $ret[0];
print("210 Misc ".$album[0]." CD database entry follows (until terminating `.')\n");
print("DISCID=".$album[0]."\n");
print("DTITLE=".$album[1]);
 if ($album[2]) {
  print(" / ".$album[2]."\n");
 } else {
  print("\n");
 }
print("DYEAR=".$album[4]."\n");
print("DGENRE=".$album[5][0]."\n");
foreach ($album[7] as $key => $song) {
print("TTITLE".$key."=");
if ($song[2]){
print($song[2]." / ".$song[3]."\n");
} else {
print($song[3]."\n");
}
}
print("EXTD=\n");
foreach ($album[7] as $key => $song) {
print("EXTT".$key."=\n");
}
print("PLAYORDER=\n");
print(".\n");

} else {
print("401 Misc ".$cmd_discid." No such CD entry in database.");
}

}
?>
