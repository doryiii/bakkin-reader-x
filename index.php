<?php
require "common.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="masonry.pkgd.min.js"></script>
    <script src="common.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.serititle').click(function () {
            $(this).siblings('.sericontent').filter(':visible').css({'display': 'block'});
            $(this).siblings('.sericontent').toggle(0, function () {
                $('#sericontainer').masonry();
            });
        });

        $('#bakkin-img').dblclick(function () {
            location.href='admin.php';
        });
    });
    </script>
</head>
<body>
<div id='pagetitle'><span id='apptitle'>Bakkin Reader X</span><span id='appsubtitle'><a href="/">By Dory</a></span></div>
<div id='banner'>
<img src='img/titleb_1.png' />
<img src='img/titleb_2.png' />
<img id='bakkin-img' src='img/titleb_3.png' />
<img src='img/titleb_4.png' />
<img src='img/titleb_5.png' />
<img src='img/titleb_6.png' />
</div>
<div style='clear:both;'></div>
<div id='sericontainer' class="js-masonry" data-masonry-options='{ "columnWidth": ".gridsizer", "itemSelector": ".seri", "transitionDuration": "0", "gutter": 15 }'>
<div class='gridsizer'>aaa</div>

<?php
$all_series = list_subdirs($content_dir);

foreach ($all_series as $series) {
    $series_dir = $content_dir . "/" . $series;

    echo "<div class='seri'>";
    echo "<div class='serititle' data-state='expanded'>" . preg_replace("/^\d* *(.*)$/", "$1", $series) . "</div>";
    echo "<div class='sericontent' style=''>";

    $all_chapters = list_subdirs($series_dir);
    foreach ($all_chapters as $chapter) {
        $chapter_dir = $series_dir . "/" . $chapter;

        echo "<a class='chaplink' href='chapter.php?" .
             tourl($series . "/" . $chapter) . "'>";
        echo "<table><tr><td>";

        $f = $series . "/" . $chapter . "/thumb.png";
        if (file_exists($content_dir . "/" . $f)) {
            $icon = $iconcache_dir . "/" . sha1($f) . ".jpg";
            if (!file_exists($icon))
                create_img($content_dir . "/" . $f, $icon, 35, 35);
            echo "<img src='" . $icon . "' />" . "</td><td>";
        }
        echo "<span>" . $chapter . "</span></td></tr></table></a>";
    }

    echo "</div>"; // sericontent
    echo "</div>"; // seri
}
?>

</div><!--sericontainer-->
</body>
</html>

