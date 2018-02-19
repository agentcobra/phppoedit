<?php
/**
 * Copyright (C) 2004-2006 Jarno Elonen <elonen@iki.fi>
 * Copyright (C) 2015-2018 Agentcobra <agentcobra@free.fr>
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - The name of the author may not be used to endorse or promote products derived
 *   from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ''AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

require_once("config.inc.php");
require_once("utils.inc.php");
require_once("poparse.inc.php");

forceNoCache();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Translate '<?php print_encoded($project_name); ?>' - PHPPOEdit</title>
</head>

<body>

<h1><?php print_encoded($project_name); ?> translations</h1>

<div class="alert alert-info" role="alert">
    NOTE! This system <em>requires</em> UTF-8 character encoding.
    If you don't see some Japanese text (or plain squares) here: "日本語",
    adjust your browser settings.
</div>
<?php

$pot_files = array_map(function ($t) {
    return str_replace("pot/", "", $t);
},
    my_glob("pot/*.pot"));

$lang_dirs = array_map(
    function ($t) {
        return str_replace("po/", "", $t);
    },
    my_glob("po/*", GLOB_ONLYDIR));
?>
<h2>Languages:</h2>
<ul class="nav justify-content-center">
    <?php foreach ($lang_dirs as $l) : ?>
        <li class="nav-item">
            <?php printf("<a class='nav-link' href='#%s'>%s</a>\n", xhtml_encode($l), xhtml_encode(ucfirst($l))); ?>
        </li>
    <?php endforeach; ?>
</ul>
<div class="container">
    <?php
    foreach ($lang_dirs

    as $l)
    {
    print '<h3 id="' . htmlentities($l) . '">' . xhtml_encode(ucfirst($l)) . '</h3>';

    ?>
    <table class="table table-bordered table-hover table-striped">
        <thead>
        <tr>
            <th><?php _("Filename"); ?></th>
            <th>Translated</th>
            <th>..of which fuzzy</th>
            <th>total strings</th>
        </tr>
        </thead>
        <tbody>
        <?php

        foreach ($pot_files as $pot) {
            $parsed_pot = parse_po(file("pot/$pot"));

            print '<tr>';
            $po = "po/$l/" . str_replace('.pot', '.po', $pot);
            if (file_exists($po)) {
                $parsed_po = parse_po(file($po));
                $total = 0;
                $translated = 0;
                $fuzzy = 0;
                foreach ($parsed_pot as $checksum => $entry) {
                    if (strlen($entry["msgid"])) {
                        $total++;
                        if (isset($parsed_po[$checksum])) {
                            if (in_array('fuzzy', $parsed_po[$checksum]["flags"])) {
                                $fuzzy++;
                                $translated++;
                            } else {
                                if (strlen($parsed_po[$checksum]["msgstr"])) {
                                    $translated++;
                                }
                            }
                        }
                    }
                }
                debug($pot);
                printf("<td><a href='editpo.php?file=%s'><em>%s</em></a></td><td style='color: #%s'>%0.2f%%</td><td style='color: #%s'>%0.2f%%</td><td>%d</td>",
                    htmlentities($po),
                    str_replace('.pot', '.po', $pot),
                    blend_colors("008000", "FF0000", ($total > 0) ? ($translated / $total) : 1),
                    ($total > 0) ? ($translated * 100 / $total) : 100,
                    blend_colors("008000", "FF0000", ($translated > 0) ? (1 - $fuzzy / $translated) : 1),
                    ($translated > 0) ? ($fuzzy * 100 / $translated) : 0,
                    $total);
            } else {
                print '<div class="alert alert-warning" role="alert">MISSING FILE ' . xhtml_encode($po) . '</div>';
            }
            print '</tr>';
        }
        print "</tbody></table>\n";
        }
        ?>
</div><!--/.container-->
<footer class="well">
    <p>Powered by <a href="http://iki.fi/elonen/code/phppoedit/">Phppoedit</a>.</p>
</footer>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>
