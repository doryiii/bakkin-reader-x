<?php

/* ====================== constants ===============================*/
const CONTENT_DIR = "content";

/* ==================== helper funcs ==============================*/

function normal_dir($d, $base) {
    return $d != "." && $d != ".." && is_dir($base . "/" . $d);
}
function list_subdirs($dir) {
    // only returns normal directories (no ., no .., no file)
    return array_values(array_filter(scandir($dir),
                                     function($f) use($dir) {
                                         return normal_dir($f, $dir); 
                                     }));
}

function startsWith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}
function sanitize($str) {
    if ($str == ".." || startsWith($str, "../") || endsWith($str, "/..") || strpos($str, '/../') !== false)
        exit(1);
    return $str;
}

function prefixDir($dir) {
    return CONTENT_DIR . "/" . $dir;
}
function ifExist($file) {
    return file_exists($file) ? $file : "";
}

/* ========================= main =================================*/
/* TODO: One potential optimization here is to load each series only
 * when queried, instead of loading all series like this.
 * TODO: Another one is probably to load the list of chapter pages
 * only when needed.
 * This works for Bakkin, since we only have a couple of series,
 * but obviously wouldn't work for bigger manga reader sites */
function getList() {
    $series_dirs = list_subdirs(CONTENT_DIR);

    $series = [];

    foreach ($series_dirs as $series_dir) {
        $series_info = file(prefixDir($series_dir . "/SERIESINFO"));
        
        $all_volumes = list_subdirs(prefixDir($series_dir));
        $volumes = [];
        $last_chapter = null;
        $last_chapter_name = null;
        $last_chapter_time = 0;
        foreach ($all_volumes as $volume) {
            $volume_dir = $series_dir . "/" . $volume;

            $chapters = [];
            $all_chapters = list_subdirs(prefixDir($volume_dir));
            foreach ($all_chapters as $chapter) {
                $chapter_dir = $volume_dir . "/" . $chapter;
                $chapter_info = file(prefixDir($chapter_dir . "/CHAPTERINFO"));
                if (filemtime(prefixDir($chapter_dir)) > $last_chapter_time) {
                    $last_chapter_time = filemtime(prefixDir($chapter_dir));
                    $last_chapter_name = $chapter_info[0];
                    $last_chapter = $chapter_dir;
                }

                $chapter_files = scandir(prefixDir($chapter_dir));
                $chapter_pages = array_filter(
                    $chapter_files,
                    function($f) use($chapter_dir) {
                        return is_file(prefixDir($chapter_dir . "/" . $f)) &&
                               (endsWith($f, ".png") || endsWith($f, ".jpg"));
                    });
                $chapter_pages = array_values(array_map(
                    function($d) use($chapter_dir){
                        return prefixDir($chapter_dir . "/" . $d);},
                    $chapter_pages));

                array_push($chapters, [
                    "dir" => $chapter_dir,
                    "name" => $chapter_info[0] ? trim($chapter_info[0]) : $chapter,
                    "thumb" => $chapter_pages[0],
                    "pages" => $chapter_pages
                ]);
            }
            
            array_push($volumes, [
                "dir" => $volume_dir,
                "name" => $volume,
                "thumb" => ifExist(prefixDir($volume_dir . "/thumb.png")),
                "chapters" => $chapters
            ]);
            
        }
        
        array_push($series, [
            "dir" => $series_dir,
            "name" => trim($series_info[0]),
            "author" => trim($series_info[1]),
            "thumb" => end($volumes)["thumb"],
            "latest" => $last_chapter,
            "latest_name" => $last_chapter_name,
            "latest_time" => date("Y-m-d", $last_chapter_time),
            "volumes" => $volumes
        ]);
    }
    
    return $series;
}


/* ========================= output ===============================*/
header('Content-Type: application/json');
$ret = getList();
echo json_encode($ret);

?>
