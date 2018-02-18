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

  header( "Content-Type: text/html; charset=UTF-8");

  require_once("config.inc");
  require_once("utils.inc");
  require_once("poparse.inc");

  forceNoCache();

?>
<!DOCTYPE html>
<html>

<head>
  <title>Translate '<?php print_encoded($project_name);  ?>' - PHPPOEdit</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
<h1><?php print_encoded($project_name);  ?> translations</h1>

<p style="border: 1px gray dotted;">
    NOTE! This system <em>requires</em> UTF-8 character encoding.
    If you don't see some Japanese text (or plain squares) here: "日本語",
    adjust your browser settings.
</p>

<?php

  $pot_files = array_map(
    create_function('$t', 'return str_replace("pot/", "", $t);'),
    my_glob( "pot/*.pot" ));

  $lang_dirs = array_map(
    create_function('$t', 'return str_replace("po/", "", $t);'),
    my_glob( "po/*", GLOB_ONLYDIR ));

  print "<p>Languages:</p><ul>";
  foreach( $lang_dirs as $l )
  {
    printf ("<li><a href='#%s'>%s</a></li>\n", xhtml_encode($l), xhtml_encode(ucfirst($l)));
  }
  print "</ul>";


  foreach( $lang_dirs as $l )
  {
    print "<a name='" . htmlentities($l) . "' /><h2>" . xhtml_encode(ucfirst($l)) . "</h2>\n";
  }
?>
  <table border="1">
  <tr>
    <td><strong>Filename</strong></td>
    <td><strong>Translated</strong></td>
    <td><strong>..of which fuzzy</strong></td>
    <td><strong>total strings</strong></td>
  </tr>
<?php

    foreach( $pot_files as $pot ):
      $parsed_pot = parse_po(file( "pot/$pot" ));

      ?><tr><?php
      $po = "po/$l/" . str_replace('.pot', '.po', $pot);
      if ( file_exists( $po )):
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


        printf ("<td><a href='editpo.php?file=%s'><em>%s</em></a></td><td style='color: #%s'>%0.2f%%</td><td style='color: #%s'>%0.2f%%</td><td>%d</td>",
          htmlentities($po),
          str_replace('.pot', '.po', $pot),
          blend_colors( "008000", "FF0000", ($total>0) ? ($translated/$total) : 1 ),
          ($total>0) ? ($translated*100/$total) : 100,
          blend_colors( "008000", "FF0000", ($translated>0) ? (1-$fuzzy/$translated) : 1 ),
          ($translated>0) ? ($fuzzy*100/$translated) : 0,
          $total);
      else: ?>
        <strong>MISSING FILE <?= xhtml_encode($po); ?></strong>
      <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </table>

<hr/>
<p>Powered by <a href="http://iki.fi/elonen/code/phppoedit/">Phppoedit</a>.</p>

</body>
</html>