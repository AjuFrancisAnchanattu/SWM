<?php
/*
 * Modify the following variables as needed
 */
/*
 $clientIP = null; // provide client IP optionally
// $clientIP = $_SERVER['REMOTE_ADDR'];
$host = "[REPLACE-WITH-HOST-IP]"; // your ip/host
$url= "http://".$host.":1935/";
$stream = "[replace-with-app-name]/[replace-with-stream-name]"; // your stream
$start = time();
$end = strtotime("+30 minutes"); //time() + $validity;
$secret = "[replace-with-secret]"; // your secret
$tokenName = "[replace-with-token-name]";
RIGHT URL WOULD BE IN THIS FORMAT http://192.168.1.2:1935/vodTest1/_definst_/vodtest1/smil:bigbuckbunny.smil/playlist.m3u8?myTokenPrefixhash=TgJft5hsjKyC5Rem_EoUNP7xZvxbqVPhhd0GxIcA2oo=
*/

$clientIP = null; // provide client IP optionally
// $clientIP = $_SERVER['REMOTE_ADDR'];
$host = "10.19.199.7"; // your ip/host
$url= "http://".$host.":1935/";
$stream = "DeskCamera/DeskCamera.stream"; // your stream
$start = time();
$validity = 1000; // validity in seconds
$end = time() + $validity;
$secret = "8d7734cdf503af5c"; // your secret
$tokenName = "wowzatoken";


//$params = array("{$tokenName}starttime=".$start, "{$tokenName}endtime=".$end, $secret);
$params = array($secret);

if(!is_null($clientIP)){
        $params[] = $clientIP;
}
sort($params);

$string4Hashing = $stream."?";
foreach($params as $entry){
        $string4Hashing .= $entry."&";
}

$string4Hashing = preg_replace("/(\&)$/","", $string4Hashing);

$hash = hash('sha256', $string4Hashing, true); // generate the hash string

echo $string4Hashing;
echo "<br>";

$base64Hash = strtr(base64_encode($hash), '+/', '-_'); // Base64 encode the hashed string


//$playbackURL = $url.$stream."/playlist.m3u8?".$tokenName."starttime=".$start."&".$tokenName."endtime=".$end."&".$tokenName."hash=".$base64Hash;
$playbackURL = $url.$stream."/playlist.m3u8?".$tokenName."hash=".$base64Hash;

//echo $base64Hash;
//echo "<br>";
echo $playbackURL;
//echo "<br>";

echo "<video width='100%' height='200' controls autoplay>";
echo "<source src='$playbackURL' type='application/x-mpegURL'>";
//echo "<source src='http://10.19.199.7:1935/DeskCamera/DeskCamera.stream/playlist.m3u8' type='application/x-mpegURL'>";
//echo "<source src='http://10.19.199.7:1935/vod/mp4:sample.mp4/playlist.m3u8' type='application/x-mpegURL'>";
echo "</video>"; 

?>
</video>