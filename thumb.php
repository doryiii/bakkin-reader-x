<?php
require 'common.php';

header("Content-type: image/jpeg");

$req = urldecode($_SERVER["QUERY_STRING"]);
$thumb = $thumbcache_dir . "/" . sha1($req) . ".jpg";
if (!file_exists($thumb))
    create_img($content_dir . $req, $thumb, 80, 100);

caching_headers($thumb, filemtime($thumb));
readfile($thumb);

?>
