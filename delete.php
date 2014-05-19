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
if ($secret != $fixed_secret || count(explode("/", $chap_dir)) != 3) {
    echo "Error: wrong secret or bad chapter name";
    exit(1);
}

delTree($chap_dir);

echo "Done!<br />";

?>
</body>
</html>


