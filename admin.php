<!DOCTYPE html>
<html>
<head>
    <title>Bakkin Reader X - Admin</title>
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" type="text/css" href="reader.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
    <style>
        table {
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
        $('input[value="Upload"]').click(function() {
            if ($('input[name="secret"]').val() == "" ||
                $('input[name="chapter"]').val() == "" ||
                $('input[name="file"]').val() == "") {
                alert("All fields need to be filled in");
                return false;
            }
        });
    });
    </script>
</head>
<body>
    <form action='upload.php' method="post" enctype="multipart/form-data">
    <table>
    <tr><td>Secret</td><td><input type='password' name='secret' /></td></tr>
    <tr><td>Series Name</td><td>
        <input type='radio' name='series' id='r_1' value='1 Yuru Yuri' checked/><label for='r_1'>1 Yuru Yuri</label><br/>
        <input type='radio' name='series' id='r_2' value='2 Oomuro-ke' /><label for='r_2'>2 Oomuro-ke</label><br/>
        <input type='radio' name='series' id='r_3' value='3 Yuyushiki' /><label for='r_3'>3 Yuyushiki</label><br/>
        <input type='radio' name='series' id='r_4' value='4 Reset!' /><label for='r_4'>4 Reset!</label><br/>
        <input type='radio' name='series' id='r_5' value='Miscellaneous' /><label for='r_5'>Miscellaneous</label><br/>
    </td></tr>
    <tr><td>Chapter Name</td><td><input type='text' name='chapter' /></td></tr>
    <tr><td>Zip file</td><td><input type='file' name='file' /></td></tr>
    <tr><td></td><td><input type='submit' value='Upload' /></td></tr>
    <tr><td colspan=2>Zip file format: PNG or JPG, with a thumb.png for the chapter thumbnail.
    No 2 file can have the same name (they will overwrite each other).</td></tr>
    </table>
    </form>

    <table>
    <tr><td><a href='cache.php?gen_thumb'>&gt; Generate all thumbnails</a><br /></td></tr>
    <tr><td><a href='cache.php?gen_all'>&gt; Cache all resized images</a><br /></td></tr>
    <tr><td><a href='cache.php'>&gt; View cache status</a><br /></td></tr>
    </table>
</body>
</html>

