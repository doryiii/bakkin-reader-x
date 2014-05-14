<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - Cache Status</title>
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" /></head>
    <style>
        body {
            font-size: small;
        }
        table {
            border-collapse:collapse;
        }
        td, th {
            border: 1px solid #555555;
        }
        .old {
            color: #888888;
            font-style: italic;
        }
    </style>
<body>
<?php
require "common.php";

$gen_thumb = ($_SERVER["QUERY_STRING"] == 'gen_thumb');
$gen_all = ($_SERVER["QUERY_STRING"] == 'gen_all');

// Show the warning
if ($gen_thumb || $gen_all) {
    echo "<p>This table may be cut off due to script being terminated (php has a time limit to run)." .
         "Check <a href='?'>the full list</a> to see if all thumbnails are generated or not.</p>";

// Show the size
} else {
    $io = popen ( '/usr/bin/du -sh ' . $thumbcache_dir, 'r' );
    $size = fgets ( $io, 4096);
    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
    pclose ( $io );
    echo '<p>Thumbnail cache: ' . $f . ' => Size: <strong>' . $size . "</strong></p>";

    $io = popen ( '/usr/bin/du -sh ' . $imgcache_dir, 'r' );
    $size = fgets ( $io, 4096);
    $size = substr ( $size, 0, strpos ( $size, "\t" ) );
    pclose ( $io );
    echo '<p>Resized images cache: ' . $f . ' => Size: <strong>' . $size . "</strong></p>";
}

echo "<table>";
$all_series = scandir($content_dir); // sorts alphabetically by default
foreach ($all_series as $series) {
    if (!normal_dir($series, $content_dir)) continue;
    $series_dir = $content_dir . "/" . $series;

    $all_chapters = scandir($series_dir);
    foreach ($all_chapters as $chapter) {
        if (!normal_dir($chapter, $series_dir)) continue;
        $chap_dir = $series_dir . "/" . $chapter;


        $fs = scandir($chap_dir);
        $page_files = array_filter($fs, function($f) use($chap_dir) {
            return is_file($chap_dir . "/" . $f) && ($f != "thumb.png"); });
        $icon_files = array_filter($fs, function($f) use($chap_dir) {
            return is_file($chap_dir . "/" . $f) && ($f == "thumb.png"); });


        $i = 0;
        foreach ($icon_files as $file) {
            $f = $series . "/" . $chapter . "/" . $file;
            echo "<tr><td>" . $series . "</td><td>" . $chapter . "</td><td>" . $file . "</td><td>";
            $icon = $iconcache_dir . "/" . sha1($f) . ".jpg";
            if (file_exists($icon)) {
                echo "<span class='old'>" . $icon . "</span>";
            } else if ($gen_thumb || $gen_all) {
                create_img($content_dir . "/" . $f, $icon, 80, 100);
                echo "<span class='new'>" . $icon . "</span>";
            }
            echo "</td><td>";
            echo "</td></tr>";
        }

        foreach ($page_files as $file) {
            $f = $series . "/" . $chapter . "/" . $file;

            echo "<tr><td>" . $series . "</td><td>" . $chapter . "</td><td>" . $file . "</td><td>";
            $thumb = $thumbcache_dir . "/" . sha1($f) . ".jpg";
            if (file_exists($thumb)) {
                echo "<span class='old'>" . $thumb . "</span>";
            } else if ($gen_thumb || $gen_all) {
                create_img($content_dir . "/" . $f, $thumb, 80, 100);
                echo "<span class='new'>" . $thumb . "</span>";
            }

            echo "</td><td>";
            $img = $imgcache_dir . "/" . sha1($f) . ".jpg";
            if (file_exists($img)) {
                echo "<span class='old'>" . $img . "</span>";
            } else if ($gen_all || ($gen_thumb && $i==0)) {
                create_img($content_dir . "/" . $f, $img, 1100, 1100);
                echo "<span class='new'>" . $img . "</span>";
            }

            echo "</td></tr>";

            $i++;
        }
    }
}

echo "</table>";

?>
</body>
</html>

