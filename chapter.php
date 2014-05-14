<?php
require "common.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - Viewer</title>
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="imagesloaded.pkgd.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        mainimg = $('#pageview img');
        mainlink = $('#pageview img');
        thumbboxes = $('a.thumbbox');
        mainlinkclicked = false;
        arrowkeyused = false;

        // onhashchange == new image requested
        window.onhashchange = function() {
            var id = 1;
            if (location.hash.length > 0)
                id = location.hash.replace('#', '');
            $('a.thumbbox[data-sel="yes"]').removeAttr('data-sel');
            $('a.thumbbox[href="' + location.hash + '"]').attr('data-sel', 'yes');
            mainlink.attr('data-current', id);
            $('#pagenum').text("pg. " + id);
            
            mainimg.attr('src', '');
            imagesLoaded(mainimg, function() {
                // Preloading stuffs here
                var newid = parseInt(location.hash.replace('#', '')) + 1;
                if (newid <= thumbboxes.length) {
                    $('#preloader img').attr('src', $('.thumbbox[href="#' + newid + '"] img').attr('data-src'));
                }
            });
            mainimg.attr('src', $('.thumbbox[href="#' + id + '"] img').attr('data-src'));
        }
        if (location.hash.length <= 0)
            location.hash = '1';
        else
            window.onhashchange();

        // Handles clicking on the current image (advances to next img)
        mainlink.click(function() {
            newid = parseInt(location.hash.replace('#', '')) + 1;
            if (newid <= thumbboxes.length) {
                location.hash = newid;
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
            if (event.shiftKey || event.ctrlKey || event.altKey || event.metaKey)
                return true;

            if (e.keyCode == 37) {
                if (id > 1)
                    location.hash = (id - 1).toString();
                else if ($('#prev').length > 0)
                    location.href = $('#prev').attr('data-lastpage');
                else
                    alert("First page");
                return false;
            } else if (e.keyCode == 39) {
                if (newid <= thumbboxes.length)
                    location.hash = newid;
                else if ($('#next').length > 0)
                    location.href = $('#next').attr('href');
                else
                    alert("End");
                return false;
            }
            return true;
        });

        // Show/hide thumbnail bar
        $('#togglethumbbar').click(function() {
            $('#thumbbar').slideToggle();
        });
    });
    </script>
</head>
<body>

<div id='preloader'><img/></div>

<div id='titlebar'>
<?php
$chap = sanitize(urldecode($_SERVER["QUERY_STRING"]));
$dir_explode = array_filter(explode("/", $chap));
echo "<span id='chaptitle'>" . $dir_explode[0] . "</span>";
echo "<span id='chapchapter'>" . $dir_explode[1] . "</span>";
?>
<span id='pagenum'></span>
</div>

<div id='thumbbar' style='display:none;'>
<?php

$chap_dir = $content_dir . "/" . $chap;
$fs = scandir($chap_dir);

$all_files = array_filter($fs, function($f) use($chap_dir) {
    return is_file($chap_dir . "/" . $f) && ($f != "thumb.png"); });

$i = 1;
foreach ($all_files as $file) {
    $f = $chap . "/" . $file;

    $thumb = $thumbcache_dir . "/" . sha1($f) . ".jpg";
    if (!file_exists($thumb))
        create_img($content_dir . "/" . $f, $thumb, 80, 100);
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
<a class='navbtn' id='togglethumbbar'>Show/hide thumbnails</a>
<?php

$cur_chap = array_pop($dir_explode);
$series = implode("/", $dir_explode);
$series_dir = $content_dir . "/" . $series;
$all_chapters = scandir($series_dir);

$prev = "";
$next = "";

// Look for the current one in the list, and deduce prev/next chapter
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
    $prev_dir = $content_dir . "/" . $series . "/" . $prev;
    $prev_fs = scandir($prev_dir);
    $prev_files = array_filter($prev_fs, function($f) use($prev_dir) {
        return is_file($prev_dir . "/" . $f) && ($f != "thumb.png"); });

    $url = "chapter.php?" . tourl($series . "/" . $prev);
    $last_prev_url = $url . "#" . count($prev_files);
    echo "<a class='navbtn' id='prev' href='" . $url . "' data-lastpage='" .
         $last_prev_url . "'>&lt;&lt; " . $prev . "</a>";
}

if ($next != "") {
    $url = "chapter.php?" . tourl($series . "/" . $next);
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

