<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - New chapter</title>
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" /></head>
</style>
<body>
<div>If you don't see "Done!" message after this, something went wrong.</div>
<?php
require "common.php";

$sec_read = file($secret_file, FILE_IGNORE_NEW_LINES);
$fixed_secret = $sec_read[0];

$secret = $_POST["secret"];
$chapter = sanitize($_POST["del_chapter"]);
$chap_dir = $content_dir."/".$chapter;

echo "Trying to delete ".$chap_dir."<br/>";
if ($secret != $fixed_secret) { echo "ERROR: Wrong secret"; exit(1); }
if (count(explode("/", $chap_dir)) != 3) { echo "ERROR: Bad chapter name"; exit(1); }

// Step 1: delete the caches
echo "Start deleting cached resizes...<br />".
$fs = scandir($chap_dir);

$all_files = array_filter($fs, function($f) use($chap_dir) {
    return is_file($chap_dir . "/" . $f); });
echo "There are ".count($all_files)." files to delete.<br />";

foreach ($all_files as $file) {
    $f = $chapter . "/" . $file;
    echo "Processing ".$f."...<br />";

    $imgcache = $imgcache_dir . "/" . sha1($f) . ".jpg";
    $iconcache = $iconcache_dir . "/" . sha1($f) . ".jpg";
    $thumbcache = $thumbcache_dir . "/" . sha1($f) . ".jpg";

    if (file_exists($imgcache)) unlink($imgcache);
    if (file_exists($iconcache)) unlink($iconcache);
    if (file_exists($thumbcache)) unlink($thumbcache);
}

// Step 2: actually delete the chapter
echo "Actually deleting the original files...<br />";
delTree($chap_dir);

echo "Done!<br />";

?>
</body>
</html>


