<?php
require 'common.php';

header("Content-type: image/jpeg");

$req = sanitize(urldecode($_SERVER["QUERY_STRING"]));
$cache = $imgcache_dir . "/" . sha1($req) . ".jpg";
if (!file_exists($cache))
    create_img($content_dir . "/" . $req, $cache,
               $max_img_width, $max_img_height);

caching_headers($cache, filemtime($cache));
readfile($cache);

?>
