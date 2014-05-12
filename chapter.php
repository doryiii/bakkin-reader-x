<?php
require "common.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="imagesloaded.pkgd.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        mainimg = $('#pageview img');
        mainlink = $('#pageview img');
        thumbboxes = $('a.thumbbox');
        mainlinkclicked = false;

        // onhashchange == new image requested
        window.onhashchange = function() {
            var id = 0;
            if (location.hash.length > 0)
                id = location.hash.replace('#', '');
            
            mainimg.attr('src', '');
            imagesLoaded(mainimg, function() {
                $('a.thumbbox[data-sel="yes"]').removeAttr('data-sel');
                $('a.thumbbox[href="' + location.hash + '"]').attr('data-sel', 'yes');
                if (mainlinkclicked) {
                    $('#navbar')[0].scrollIntoView(true);
                    mainlinkclicked = false;
                }

                // Preloading stuffs here
                var newid = parseInt(location.hash.replace('#', '')) + 1;
                if (newid < thumbboxes.length) {
                    $('#preloader img').attr('src', $('.thumbbox[href="#' + newid + '"] img').attr('data-src'));
                }
            });
            mainimg.attr('src', $('.thumbbox[href="#' + id + '"] img').attr('data-src'));
            mainlink.attr('data-current', id);

        }
        if (location.hash.length <= 0)
            location.hash = '0';
        else
            window.onhashchange();

        // Handles clicking on the current image (advances to next img)
        mainlink.click(function() {
            newid = parseInt(location.hash.replace('#', '')) + 1;
            if (newid < thumbboxes.length) {
                location.hash = newid;
                mainlinkclicked = true;
            } else if ($('#next').length > 0) {
                location.href = $('#next').attr('href');
            } else {
                alert("End");
            }

            return false;
        });

        // Reload the images if any of them failed
        var imgLoad = imagesLoaded('img');
        imgLoad.on('progress', function(inst, image) {
            if (!image.isLoaded) {
                var origsrc = image.img.src;
                image.img.src = '';
                setTimeout(function() {image.img.src = origsrc;}, 1000);
            }
        });
        imgLoad.on('always', function() {
        });

        // Handle arrow keys
        $(document).keydown(function(e){
            var id = parseInt(location.hash.replace('#', ''));
            var newid = id + 1;
            if (e.keyCode == 37) {
                if (id > 0)
                    location.hash = (id - 1).toString();
                else if ($('#prev').length > 0)
                    location.href = $('#prev').attr('href');
                else
                    alert("First page");
            } else if (e.keyCode == 39) {
                if (newid < thumbboxes.length)
                    location.hash = newid;
                else if ($('#next').length > 0)
                    location.href = $('#next').attr('href');
                else
                    alert("End");
            } else {
                return true;
            }
            return false;
        });
    });
    </script>
</head>
<body>

<div id='preloader'><img/></div>

<div id='titlebar'>
<?php
$chap = urldecode($_SERVER["QUERY_STRING"]);
$dir_explode = array_filter(explode("/", $chap));
echo "<span id='chaptitle'>" . $dir_explode[1] . "</span>";
echo "<span id='chapchapter'>" . $dir_explode[2] . "</span>";
?>
</div>

<div id='thumbbar'>
<?php

$chap = urldecode($_SERVER["QUERY_STRING"]);
$chap_dir = $content_dir . $chap;
$fs = scandir($chap_dir);

$all_files = array_filter($fs, function($f) use($chap_dir) {
    return is_file($chap_dir . "/" . $f) && ($f != "thumb.png"); });

$i = 0;
foreach ($all_files as $file) {
    $f = $chap . "/" . $file;

    $thumb = $thumbcache_dir . "/" . sha1($f) . ".jpg";
    if (!file_exists($thumb))
        create_img($content_dir . $f, $thumb, 80, 100);
    $thumburl = $thumb;

    $img = $imgcache_dir . "/" . sha1($f) . ".jpg";
    $imgurl = file_exists($img) ? $img : "img.php?" . $f;

    echo "<a href='#" . $i . "' class='thumbbox' data-id='". $i . "'>";
    echo "<img class='thumb' src='" . $thumburl .
         "' data-src='" . $imgurl . "'/>";
    echo "</a>";
    $i++;
}
?>
</div>

<div id='navbar'>
<a class='navbtn' id='back' href='<?php echo $script_path; ?>'>^ Back home</a>
<?php

$dir_explode = array_filter(explode("/", $chap));
$cur_chap = array_pop($dir_explode);
$series = implode("/", $dir_explode);
$series_dir = $content_dir . "/" . $series;
$all_chapters = scandir($series_dir);

$prev = "";
$next = "";

for ($i=0; $i<count($all_chapters); $i++) {
    $chapter = $all_chapters[$i];
    if (!normal_dir($chapter, $series_dir)) continue;

    if ($chapter == $cur_chap)
        break;
    $prev = $chapter;
}
if ($i < count($all_chapters) - 1)
    $next = $all_chapters[$i+1];

if ($prev != "") {
    $url = "chapter.php?/" . tourl($series . "/" . $prev);
    echo "<a class='navbtn' id='prev' href='" . $url . "'>&lt;&lt; " . $prev . "</a>";
}

if ($next != "") {
    $url = "chapter.php?/" . tourl($series . "/" . $next);
    echo "<a class='navbtn' id='next' href='" . $url . "'>" . $next . " &gt;&gt;</a>";
}

?>
</div>

<div id='pageview'>
    <img src='' />
    <span></span>
</div>

</body>
</html>

