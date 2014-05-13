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

$fixed_secret = "doryrocks";

function delTree($dir) {
    // Don't delete the entire application by accident!
    if ($dir == "") return false;

    $files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    } 
    return rmdir($dir); 
} 

$secret = $_POST["secret"];
$series = $_POST["series"];
$chapter = $_POST["chapter"];
$tmpfile = $_FILES["file"]["tmp_name"];

if ($_FILES["file"]["error"] > 0 || $secret != $fixed_secret) {
    echo "Error: Either file upload error or wrong secret";
    exit(1);
}

$ret = 0;
$tmp_dir = "";
$chap_dir = $content_dir."/".$series."/".$chapter;
if (!mkdir($chap_dir, 0755)) exit(0);

exec("/bin/mktemp -d --tmpdir='".$chap_dir."'", $tmp_dir, $ret);
if ($ret != 0) exit(0);
$tmp_dir = $tmp_dir[0];

exec("/usr/bin/unzip '".$tmpfile."' -d '".$tmp_dir."'");
exec("/usr/bin/find '".$tmp_dir."' -type f \( -iname '*.png' -o ".
     "-iname '*.jpg' -o -iname '*.jpeg' \) -exec mv '{}' '".
     $chap_dir."' \;");
delTree($tmp_dir);

echo "Done!<br />";
echo "<a href='chapter.php?/".$series."/".$chapter."'>&gt; Link to the chapter</a>";

?>
</body>
</html>


