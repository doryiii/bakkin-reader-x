## Introduction

Bakkin Reader X is a lightweight web-based manga viewer, suitable for use by
scanlation groups with not too many (< 50) full manga series. It is currently
used by by Bakkin: https://bakkin.moe/reader

Some features of this reader that distinguishes it from others (i.e. why Dory
wrote this thing):

* Fast. First page loading is fast; jumping around is fast;
viewing things is fast; viewing on old computer is fast.
* Small code size and easy-to-maintain code, with clear delineation of M/V/C.
The webserver returns only a JSON structure.
* On-line image scaling to save bandwidth without having to resize by hand.
* Better use of mobile screen real-estate.
* Minimal dependencies and no DB required.

## Install

Bakkin Reader X requires PHP 5+ with GD.

Drop the files into a web directory, drop your manga into the right directory
structure below, edit the top of *main.php* to configure resolutions, and
that's it.

### Manga directory structure

A basic setup will simply consist of mangas inside the *manga/* directory,
organized series/volumes/chapters/pages.png. To add metadata, read on.

    /srv/http/root/of/your/reader/here/
    |-- main.php
    |-- index.html
    |-- caches/
    `-- manga/
        `-- YuruYuri
            |-- SERIESINFO
            `-- v01
                |-- VOLUMEINFO
                |-- c001
                |   |-- CHAPTERINFO
                |   |-- 001.png
                |   |-- ...
                |   `-- 013.png
                `-- c002
                    `-- ...
**SERIESINFO** is a cleartext file with information about the series.
All fields are optional. The file itself is also optional:

    <series name>
    <series author>
    <series status>
    <where to buy>
    <link to where to buy>

For example:

    Yuru Yuri
    Namori
    Ongoing
    Amazon.co.jp
    https://www.amazon.co.jp/s/field-keywords=%E3%82%86%E3%82%8B%E3%82%86%E3%82%8A

**VOLUMEINFO** and **CHAPTERINFO** are files containing a single line with a
custom volume/chapter title. These files are optional. If not provided, the
directory name ("v01", "c001" etc.) will be used instead. They are just there
to make directory names pretty.

The *manga/* directory does not have to be exposed by the web server.

### Initializing the cache

The script maintains a cache of resized images and thumbnails in the *caches/*
directory, which should be writable by the PHP or webserver process.

The cache entries for all new pages will be created when the site is loaded.
Which means that at the beginning right after installation, the script will
go and resizes all images, which it will probably not have enough time to
finish, given most PHP installation limit execution time to 30s or so.

To get around that, you can either:

1. SSH into the server and run something along the line of
    `sudo -u httpd php main.php`
2. Run that command on your local computer before uploading the populated
*caches/* directory to the server with FTP
3. Sit there and keep refreshing the browser. When the site is loaded, the
script would be processing images until it runs out of allocated execution
time, which will make the page timeout. Keep refreshing until all the images
are processed.

## Considerations

This web app can be modified to load a single series at a time, on demand,
to help with larger group with more series to show, and that should not be
hard. However, that is outside the scope of Bakkin.

## License

[AGPL-3.0](https://www.gnu.org/licenses/agpl-3.0.en.html). This license is
chosen so that anyone using this would also make their modifications available.
