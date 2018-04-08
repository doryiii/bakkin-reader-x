<?php

/* Copyright (c) 2018 Dory
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

date_default_timezone_set('America/Los_Angeles');

/* ====================== constants ===============================*/
const CONTENT_DIR = "manga";
const CACHE_DIR = "caches";
const THUMB_WIDTH = 100;
const THUMB_HEIGHT = 100;
const IMG_WIDTH = 1800;
const IMG_HEIGHT = 1500;
const COVER_WIDTH = 350;
const COVER_HEIGHT = 350;

/* ==================== helper funcs ==============================*/

function create_img($orig, $dest, $width, $height) {
    list($orig_width, $orig_height) = getimagesize($orig);

    // Make sure to keep image ratio
    $ratio = $orig_width/$orig_height;
    if ($width/$height > $ratio) {
        $width = $height * $ratio;
    } else {
        $height = $width / $ratio;
    }

    // Load the original image and create an img obj for the thumbnail
    if (endsWith($orig, ".png")) {
        $img_orig = imagecreatefrompng($orig);
    } elseif (endsWith($orig, ".jpg") || endsWith($orig, ".jpeg")) {
        $img_orig = imagecreatefromjpeg($orig);
    }

    // Only downsize, don't expand
    if ($width < $orig_width || $height < $orig_height) {
        $img_thumb = imagecreatetruecolor($width, $height);
        imagecopyresampled($img_thumb, $img_orig, 0, 0, 0, 0,
        $width, $height, $orig_width, $orig_height);
    } else {
        $img_thumb = $img_orig;
    }

    imageinterlace($img_thumb, true);
    imagejpeg($img_thumb, $dest, 85);
}

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

function endsWith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function dirOf($file) {
    return CONTENT_DIR . "/" . $file;
}

function ifExist($file) {
    return file_exists($file) ? $file : "";
}

function genPreview($file, $dir, $prefix, $max_width, $max_height) {
    if (!file_exists(dirOf($file)))
        return null;
    $out_file = $dir . "/" . dirname($file) . "/" .
                $prefix . basename($file) . ".jpg";

    if (!file_exists($out_file)) {
        if (!file_exists(dirname($out_file)))
            mkdir(dirname($out_file), 0777, true);
        create_img(dirOf($file), $out_file, $max_width, $max_height);
    }
    return $out_file;
}
function thumbOf($file) {
    return genPreview($file, CACHE_DIR, "thumb_", THUMB_WIDTH, THUMB_HEIGHT);
}
function imgOf($file) {
    return genPreview($file, CACHE_DIR, "img_", IMG_WIDTH, IMG_HEIGHT);
}
function coverOf($file) {
    return genPreview($file, CACHE_DIR, "cover_", COVER_WIDTH, COVER_HEIGHT);
}

/* ========================= main =================================*/
/* NOTE: One potential optimization here is to load each series only
 * when queried, instead of loading all series like this.
 * This works for Bakkin, since we only have a couple of series,
 * but obviously wouldn't work for bigger manga reader sites
 */
function getList() {
    $series_dirs = list_subdirs(CONTENT_DIR);

    $series = [];

    foreach ($series_dirs as $series_dir) {
        $series_info = file(dirOf($series_dir . "/SERIESINFO"));

        $all_volumes = list_subdirs(dirOf($series_dir));
        $volumes = [];
        $last_chapter = null;
        $last_volume = null;
        $last_chapter_name = null;
        $last_chapter_time = 0;
        foreach ($all_volumes as $volume) {
            $volume_dir = $series_dir . "/" . $volume;
            $volume_info = file(dirOf($volume_dir . "/VOLUMEINFO"));

            $chapters = [];
            $all_chapters = list_subdirs(dirOf($volume_dir));
            foreach ($all_chapters as $chapter) {
                $chapter_dir = $volume_dir . "/" . $chapter;
                $chapter_info = file(dirOf($chapter_dir . "/CHAPTERINFO"));
                $chapter_name = $chapter_info[0] ? trim($chapter_info[0]) : $chapter;
                if (filemtime(dirOf($chapter_dir)) > $last_chapter_time) {
                    $last_chapter_time = filemtime(dirOf($chapter_dir));
                    $last_chapter_name = $chapter_name;
                    $last_chapter = $chapter;
                    $last_volume = $volume;
                }

                $chapter_files = scandir(dirOf($chapter_dir));
                $chapter_pages = array_values(array_filter(
                    $chapter_files,
                    function($f) use($chapter_dir) {
                        return is_file(dirOf($chapter_dir . "/" . $f)) &&
                               (endsWith($f, ".png") || endsWith($f, ".jpg")) &&
                               $f != "thumb.png";
                    }));
                $chapter_page_links = array_values(array_map(
                    function($d) use($chapter_dir) {
                        return imgOf($chapter_dir . "/" . $d);
                    }, $chapter_pages));
                $chapter_thumbs = array_values(array_map(
                    function($d) use($chapter_dir) {
                        return thumbOf($chapter_dir . "/" . $d);
                    }, $chapter_pages));

                array_push($chapters, [
                    "dir" => $chapter,
                    "name" => $chapter_name,
                    "thumb" => ifExist(dirOf($chapter_dir . "/thumb.png")) ?
                                (dirOf($chapter_dir . "/thumb.png")) : "",
                    "pages" => $chapter_page_links,
                    "thumbs" => $chapter_thumbs,
                ]);
            }

            array_push($volumes, [
                "dir" => $volume,
                "name" => $volume_info ? trim($volume_info[0]) : $volume,
                "thumb" => coverOf($volume_dir . "/thumb.png"),
                "thumb_large" => imgOf($volume_dir . "/thumb.png"),
                "chapters" => $chapters
            ]);

        }

        $series[$series_dir] = [
            "dir" => $series_dir,
            "name" => $series_info[0] ? trim($series_info[0]) : $series_dir,
            "author" => trim($series_info[1]),
            "status" => trim($series_info[2]),
            "buy_from" => trim($series_info[3]),
            "buy_link" => trim($series_info[4]),
            "thumb" => end($volumes)["thumb"],
            "latest_vol" => $last_volume,
            "latest_chap" => $last_chapter,
            "latest_name" => trim($last_chapter_name),
            "latest_time" => date("Y-m-d", $last_chapter_time),
            "volumes" => $volumes
        ];
    }

    return $series;
}


/* ========================= output ===============================*/
header('Content-Type: application/json');
$ret = getList();
echo json_encode($ret);

?>
