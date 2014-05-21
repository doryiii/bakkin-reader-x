<?php
require "common.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - Viewer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes">
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="imagesloaded.pkgd.min.js"></script>
    <script src="common.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        mainimg = $('#pageview img');
        mainlink = $('#pageview img');
        thumbboxes = $('a.thumbbox');
        mainlinkclicked = false;

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
                // Autoscroll to top of img if on mobile (also see below)
                if (isMobile.any() && mainlinkclicked) {
                    $('html, body').animate({scrollTop: $('#pageview').offset().top}, 200);
                    mainlinkclicked = false;
                }
                // Preloading stuffs here
                var newid = parseInt(location.hash.replace('#', '')) + 1;
                if (newid <= thumbboxes.length) {
                    $('#preloader img').attr('src', $('.thumbbox[href="#' + newid + '"] img').attr('data-src'));
                }
            });
            mainimg.attr('src', $('.thumbbox[href="#' + id + '"] img').attr('data-src'));

            // Autoscroll to top page only if not mobile
            if (!isMobile.any())
                $('html, body').animate({scrollTop: $('html').offset().top}, 200);
        }
        if (location.hash.length <= 0)
            location.hash = '1';
        else
            window.onhashchange();

        // Handles clicking on the current image (advances to next img)
        mainlink.click(function() {
            mainlinkclicked = true;
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
            if (e.shiftKey || e.ctrlKey || e.altKey || e.metaKey)
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

        // Switch to mobile CSS if on mobile device
        if (isMobile.any()) {
            applyMobileStyle();
        }
    });
    </script>
</head>
<body>

<div id='preloader'><img/></div>

<div id='titlebar'>
<?php
$chap = sanitize(urldecode($_SERVER["QUERY_STRING"]));
$dir_explode = array_filter(explode("/", $chap));
$series = $dir_explode[0];
$cur_chap = $dir_explode[1];

$icon_f = $series . "/" . $cur_chap . "/thumb.png";
$icon = $iconcache_dir . "/" . sha1($icon_f) . ".jpg";
if (!file_exists($icon))
    create_img($content_dir . "/" . $icon_f, $icon, 35, 35);

echo "<span id='chaptitle'>" . $series . "</span>";
echo "<img id='chapicon' src='" . $icon . "'/>";
echo "<span id='chapchapter'>" . $cur_chap . "</span>";
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
        create_img($content_dir . "/" . $f, $thumb,
                   $max_thumb_width, $max_thumb_height);

    $img = $imgcache_dir . "/" . sha1($f) . ".jpg";
    $imgurl = file_exists($img) ? $img : "img.php?" . $f;

    echo "<a href='#" . $i . "' class='thumbbox' data-id='". $i . "'>";
    echo "<img class='thumb' src='" . $thumb .
         "' data-src='" . $imgurl . "'/>";
    echo "</a>";
    $i++;
}
?>
</div>

<div id='navbar'>
<a class='navbtn' id='back' href='./'>^ Home</a>
<a class='navbtn' id='togglethumbbar'>Toggle thumbnails</a>
<?php

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

