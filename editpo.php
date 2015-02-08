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
  header( "accept-charset: utf-8" );

  require_once("config.inc.php");
  require_once("utils.inc.php");
  require_once("poparse.inc.php");
  require_once("htpasswd.inc.php");

  forceNoCache();

  // Remember the password
  if ( isset($_POST["pass"] ))
    setcookie("editpo_pass", $_POST["pass"]);
  else if ( isset($_COOKIE["editpo_pass"] ))
    $_POST["pass"] = $_COOKIE["editpo_pass"];

  print '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

  $filename = trim($_GET["file"]);

  // Take extra care not to open a security hole with
  // user-passed file names.
  $rp = realpath($filename);
  if ( !is_string($rp) ||
       strpos( realpath($filename), realpath('po/')) !== 0 ||
       !preg_match('/^po\\/[^.\\/]+\\/[^\\/]+[.]po$/', $filename) ||
       strpos($filename, '..') !== False )
    die( "Non-allowed filename; only the PO files in the correct directory are accepted.");

  $dir = preg_replace('/\\/[^\\/]*$/', '', $filename);
  $user = preg_replace('/^.*\\//', '', $dir);

  $red_color = "FF6060";
  $blue_color = "9090FF";
  $yellow_color = "F0F000";
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>'<?php print_encoded($filename);  ?>' - PHPPOEdit</title>
  <!--
    これはUTF-8 としてこのファイルを確認するためにソフトウェアを助ける模造のテキストである。
    Tämä on testitekstiä, joka toivottavasti auttaa ohjelmia tunnistamaan tekstin UTF8:ksi.
    This is some test text that hopefully helps browsers to detect this as UTF8.
    -->
</head>

<body>
<h1>Edit '<?php print_encoded($filename);  ?>'</h1>

<?php

  function strip_post( $post_key )
  {
    global $_POST;
    return stripslashes(str_replace("\r", "", $_POST[$post_key]));
  }

  function explode_nl( $txt )
  {
    if ( strlen($txt))
      return explode("\n", $txt);
    else
      return array();
  }

  function explode_post( $post_key )
  {
    return explode_nl(strip_post( $post_key ));
  }

  if ( isset($_POST["save"]))
  {
    $pass_array = load_htpasswd();
    $error = False;
    if ( !isset($_POST["pass"]))
      $error = "No password given!";
    else if ( !isset($pass_array[$user]))
      $error = sprintf("No such username '%s'", $user);
    else if ( !test_htpasswd( $pass_array,  $user, $_POST["pass"] ))
      $error = sprintf("Incorrect password for user '%s'", $user);
    if ( $error !== False )
    {
        printf ("<strong>%s</strong>", htmlentities($error));
        print "<br>Click 'back' and try again.";
        die;
    }


    $ids = array();
    foreach( $_POST as $k => $v )
      if ( substr( $k, 0, 5 ) == "msgid" )
        $ids[] = preg_replace( '/^.*_/', '', $k );

    $entries = array();
    foreach( $ids as $k => $v )
    {
      $flags = preg_split('/[ ,]+/', $_POST["otherflags_$v"], -1, PREG_SPLIT_NO_EMPTY);

      if ( !in_array('fuzzy', $flags) && isset($_POST["fuzzy_$v"]) && $_POST["fuzzy_$v"])
        $flags[] = "fuzzy";

      $entries[] = array(
        "comments" => explode_post("comments_$v"),
        "autocomments" => explode_nl(base64_decode($_POST["autocomments_$v"])),
        "sources" => explode_nl(base64_decode($_POST["sources_$v"])),
        "flags" => $flags,
        "msgid" => base64_decode($_POST["msgid_$v"]),
        "msgstr" => strip_post("msgstr_$v"));
    }

    $new_file = unparse_po($entries);

  $error = False;
  if (!is_writable($filename))
    $error = $error = "File is not writable ($filename)";
  else
  {
    if (!$fp = fopen($filename, 'w'))
          $error = "Cannot open file ($filename)";
     else
        if (fwrite($fp, $new_file) === False)
          $error = "Cannot write to file ($filename)";
  }
  if ( $error !== False )
  {
    printf( "The file could not be written: '%s'.<br />" .
            "This is a bug in system configuration. Please report.", $error );
    die;
  }
  fclose($fp);
?>
  <div style="height: 100%; border: 1px gray dotted; color: green;">
    <strong>Modifications saved.</strong>
  </div>
<?php
  }
?>

  <p>
    <a href="./">Back to main page</a>
  </p>

  <div style="border: 1px gray dotted;">
  <table>
    <tr><td colspan="2"><strong>Legend:</strong></td></tr>
    <tr>
      <td width="16" style="background: #<?php print $blue_color ?>;">&nbsp;</td>
      <td>completed translation</td>
    </tr>
    <tr>
      <td width="16" style="background: #<?php print $red_color ?>;">&nbsp;</td>
      <td>missing translation</td>
    </tr>
    <tr>
      <td width="16" style="background: #<?php print $yellow_color ?>;">&nbsp;</td>
      <td>fuzzy/uncertain translation (e.g. automatically translated)</td>
    </tr>
  </table>

  <div class="alert alert-info" role="alert">
    NOTE! This system <em>requires</em> UTF-8 character encoding.
    If you don't see some Japanese text here: "日本語",
    adjust your browser settings.
  </div>

  </div>
<p> </p>

<?php

  if ( strpos($filename, "../") !== False || $filename[0] == "/" || !preg_match('/.*[.]po$/', $filename))
  {
    print '<strong>Forbidden</strong>';
    die;
  }

  $parsed = parse_po(file( $filename ));

  function print_field_if_nonempty( $entry_id, $label, $content, $http_parm, $rows, $force, $propo )
  {
    if ( is_array($content))
      $content = join( "\n", $content );
    if ( $force || strlen($content))
    {
?>
  <tr>
    <td><strong><?php print_encoded( $label ); ?></strong></td>
    <?php
      if ( $propo )
        print "<td>";
      else
        print "<td style='background: #E0E0E0;'>";
      if ( $rows > 0 )
      {
        printf( '<textarea wrap="off" cols="60" rows="%d" name="%s_%s">%s</textarea>'."\n", $rows, $http_parm, $entry_id, xhtml_encode($content));
      }
      else
      {
        printf( '<input type="hidden" name="%s_%s" value="%s">'."\n", $http_parm, $entry_id, base64_encode( $content ));
        if ( $propo )
          print nl2br(xhtml_encode($content));
        else
          print "<code>" . nl2br(xhtml_encode($content)) . "</code>";
      }
        ?></td>
  </tr>
<?php
    }
  }

  print '<div class="alert alert-info" role="alert">TRANSLATIONS: NOTE! You can ignore the first box (translation info, meta data etc).</div>';

  print '<form action="' . htmlentities($_SERVER["REQUEST_URI"]) . '" method="post">' . "\n";
  print '<input type="hidden" name="save" value="1">' . "\n";

  foreach( $parsed as $checksum => $e )
  {
    $flags = $e["flags"];
    $fuzzy = ( in_array('fuzzy', $e["flags"]));
    foreach( $flags as $k => $v )
      if ( $v == "fuzzy" )
        unset($flags[$k]);

    $bgcolor = $red_color;
    if ( strlen($e["msgstr"]))
      $bgcolor = $blue_color;
    if ( $fuzzy )
      $bgcolor = $yellow_color;

    print "<table style='background: #$bgcolor;' border='1'>\n";
    print_field_if_nonempty( $checksum, "from", $e["sources"], "sources", 0, false, true );
    print_field_if_nonempty( $checksum, "note", $e["autocomments"], "autocomments", 0, false, true );
    if ($e["msgid"]=="")
    {
      print "<input type='hidden' name='msgid_$checksum' value=''>\n";
      print_field_if_nonempty( $checksum, "translation info", $e["comments"], "comments", 6, true, true );
      print_field_if_nonempty( $checksum, "meta data", $e["msgstr"], "msgstr", 6, true, false );
    }
    else
    {
      print_field_if_nonempty( $checksum, "text", $e["msgid"], "msgid", 0, false, false );
      print_field_if_nonempty( $checksum, "translation", $e["msgstr"], "msgstr", 6, true, false );
      print_field_if_nonempty( $checksum, "comments", $e["comments"], "comments", 2, true, true );
    }

?>
  <tr>
    <td><strong>flags</strong></td>
    <td><?php
          if ( count($flags))
            print join( ", ", $flags) . ", ";
          printf( '<input type="hidden" name="otherflags_%s" value="%s">'."\n", $checksum, numeric_html_encode( join( ", ", $flags) ));
        ?>
        <input type="checkbox" value="1" name="fuzzy_<?php print $checksum; ?>" <?php if ( $fuzzy ) print "checked"; ?>> fuzzy
     </td>
   </tr>
<?php
    print "</table>\n";
  }
?>

  Password (for user '<?php print htmlentities($user); ?>'): <input type="password" value="<?php if (isset($_POST["pass"])) print $_POST["pass"]; ?>" name="pass" /><br />
  <input type="submit" class="btn btn-primary" value="Save all">
  </form>

</body>
</html>
