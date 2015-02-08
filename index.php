<?php
// Copyright (C) 2004-2006 Jarno Elonen <elonen@iki.fi>
//
// Redistribution and use in source and binary forms, with or without modification,
// are permitted provided that the following conditions are met:
//
// * Redistributions of source code must retain the above copyright notice, this
//   list of conditions and the following disclaimer.
// * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation
//   and/or other materials provided with the distribution.
// * The name of the author may not be used to endorse or promote products derived
//   from this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE AUTHOR ''AS IS'' AND ANY EXPRESS OR IMPLIED
// WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
// AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR
// BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
// LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
// ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
// NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
// EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


require_once("config.inc");
require_once("utils.inc");
require_once("poparse.inc");

forceNoCache();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Translate '<?php print_encoded($project_name);  ?>' - PHPPOEdit</title>

        <!-- Bootstrap -->
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
        <style>
            footer.well{
                margin-bottom: 0;
            }
        </style>
    </head>

    <body>
        <h1><?php print_encoded($project_name);  ?> translations</h1>

        <div class="alert alert-info" role="alert">
            NOTE! This system <em>requires</em> UTF-8 character encoding.
            If you don't see some Japanese text (or plain squares) here: "日本語",
            adjust your browser settings.
        </div>
        <?php

$pot_files = array_map(
    create_function('$t', 'return str_replace("pot/", "", $t);'),
    my_glob( "pot/*.pot" ));

$lang_dirs = array_map(
    create_function('$t', 'return str_replace("po/", "", $t);'),
    my_glob( "po/*", GLOB_ONLYDIR ));
        ?>
        <h2>Languages:</h2>
        <ul class="list-inline">
            <?php
foreach( $lang_dirs as $l )
{
    printf ("<li><a href='#%s'>%s</a></li>\n", xhtml_encode($l), xhtml_encode(ucfirst($l)));
}
            ?>
        </ul>
        <div class="container">
            <?php
foreach( $lang_dirs as $l )
{
    print '<h3 id="'. htmlentities($l) .'">' . xhtml_encode(ucfirst($l)) . '</h3>';

            ?>
            <table class="table table-bordered table-hover table-striped">
               <thead>
                <tr>
                    <th>Filename</th>
                    <th>Translated</th>
                    <th>..of which fuzzy</th>
                    <th>total strings</th>
                </tr>
                </thead>
                <tbody>
                <?php

    foreach( $pot_files as $pot )
    {
        $parsed_pot = parse_po(file( "pot/$pot" ));

        print '<tr>';
        $po = "po/$l/" . str_replace('.pot', '.po', $pot);
        if ( file_exists( $po ))
        {
            $parsed_po = parse_po(file( $po ));
            $total = 0; $translated = 0; $fuzzy = 0;
            foreach( $parsed_pot as $checksum => $entry )
            {
                if ( strlen($entry["msgid"] ))
                {
                    $total++;
                    if ( isset($parsed_po[$checksum]))
                    {
                        if ( in_array('fuzzy', $parsed_po[$checksum]["flags"]))
                        {
                            $fuzzy++;
                            $translated++;
                        }
                        else if ( strlen($parsed_po[$checksum]["msgstr"]))
                            $translated++;
                    }
                }
            }
            debug($pot);
            printf ("<td><a href='editpo.php?file=%s'><em>%s</em></a></td><td style='color: #%s'>%0.2f%%</td><td style='color: #%s'>%0.2f%%</td><td>%d</td>",
                    htmlentities($po),
                    str_replace('.pot', '.po', $pot),
                    blend_colors( "008000", "FF0000", ($total>0) ? ($translated/$total) : 1 ),
                    ($total>0) ? ($translated*100/$total) : 100,
                    blend_colors( "008000", "FF0000", ($translated>0) ? (1-$fuzzy/$translated) : 1 ),
                    ($translated>0) ? ($fuzzy*100/$translated) : 0,
                    $total);
        }
        else
        {
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

            <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
            <!-- Include all compiled plugins (below), or include individual files as needed -->
            <!-- Latest compiled and minified JavaScript -->
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
            </body>
        </html>
