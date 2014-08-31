<?php
require "common.php";
$all_series = list_subdirs($content_dir);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <script src="common.js"></script>
    <style>
        form {
            border: 1px solid #888888;
            padding: 5px;
            float: left;
            margin: 5px;
            max-width: 400px;
        }
        input[type=text], input[type=password] {
            width: 240px;
        }
    </style>
    <script>
    $(document).ready(function() {
        $('#upload input[type="submit"]').click(function() {
            if ($('input[name="secret"]').val() == "" ||
                $('input[name="chapter"]').val() == "" ||
                $('input[name="file"]').val() == "") {
                alert("All fields need to be filled in");
                return false;
            }
            $("#upload").submit();
        });

        $('#delete input[type="submit"]').click(function() {
            if (!window.confirm("Are you sure you want to delete \"" +
                                $('#delete select').val() + "\"?"))
                return false;
        });
    });
    </script>
</head>
<body>
    <form id='upload' action='upload.php' method="post" enctype="multipart/form-data">
        <table>
        <tr><td>Secret</td><td><input type='password' name='secret' /></td></tr>
        <tr><td>Series Name</td><td>
        <select name='series'>
            <?php
            foreach ($all_series as $series)
                echo "<option value='".$series."' />".$series."</option>";
            ?>
        </select>
        </td></tr>
        <tr><td>Chapter Name</td><td><input type='text' name='chapter' /></td></tr>
        <tr><td>Zip file (no rar!)</td><td><input type='file' name='file' /></td></tr>
        <tr><td></td><td><input type='submit' value='Upload' /></td></tr>
        <tr><td colspan=2>Zip file format: PNG or JPG, with a thumb.png for the chapter thumbnail.
        No 2 file can have the same name (they will overwrite each other).</td></tr>
        </table>
    </form>

    <form id='delete' action='delete.php' method='post' enctype='multipart/form-data'>
        <table>
        <tr><td>Secret</td><td><input type='password' name='secret' /></td></tr>
        <tr><td>Chapter</td><td>
        <select name='del_chapter'>
            <?php
            foreach ($all_series as $series) {
                foreach (list_subdirs($content_dir . "/" . $series) as $chapter) {
                    echo "<option value='".$series."/".$chapter."'>".
                         $series."/".$chapter."</option>";
                }
            }
            ?>
        </select>
        </td></tr>
        <tr><td></td><td>
        <input type='submit' value='Delete' /></td></tr>
        </table>
    </form>

    <form>
    <table>
    <tr><td><a href='cache.php?gen_thumb'>&gt; Generate all thumbnails</a><br /></td></tr>
    <tr><td><a href='cache.php?gen_all'>&gt; Cache all resized images</a><br /></td></tr>
    <tr><td><a href='cache.php'>&gt; View cache status</a><br /></td></tr>
    </table>
    </form>
</body>
</html>

