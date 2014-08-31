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
    <script src="common.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $('.serititle').click(function () {
            $(this).siblings('.sericontent').slideToggle();
            $(this).siblings('.sericontent').filter(':visible').css({'display': 'block'});
        });

        $('#bakkin-img').dblclick(function () {
            location.href='admin.php';
        });

        if (isMobile.any()) {
            applyMobileStyle();
        }
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

        $f = $series . "/" . $chapter . "/thumb.png";
        $icon = $iconcache_dir . "/" . sha1($f) . ".jpg";
        if (!file_exists($icon))
            create_img($content_dir . "/" . $f, $icon, 35, 35);

        echo "<img src='" . $icon . "' />" .
             "<span>" . $chapter . "</span></a>";
    }

    echo "</div>"; // sericontent
    echo "</div>"; // seri
}
?>

</body>
</html>

