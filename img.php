<?php
require 'common.php';

header("Content-type: image/jpeg");

$req = urldecode($_SERVER["QUERY_STRING"]);
$cache = $imgcache_dir . "/" . sha1($req) . ".jpg";
if (!file_exists($cache))
    create_img($content_dir . $req, $cache, 1100, 1100);

caching_headers($cache, filemtime($cache));
readfile($cache);

?>
