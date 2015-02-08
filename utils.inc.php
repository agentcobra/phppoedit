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

function numeric_html_encode( $text )
{
/*
  $res = "";
  $tlen = strlen($text);
  for ( $i=0; $i<$tlen; $i++ )
  {
    $c = $text[$i];
    if (ord($c)>=ord('A') && ord($c)<=ord('z'))
      $res .= $c;
    else
      $res .= '&#' . ord($c) . ';';
  }
  return $res;
  */

  $text = htmlentities($text, ENT_QUOTES, 'utf-8');
  $res = "";
  $tlen = strlen($text);
  for ( $i=0; $i<$tlen; $i++ )
  {
    $c = $text[$i];
    if (ord($c)<=32)
      $res .= '&#' . ord($c) . ';';
    else
      $res .= $c;
  }
  return $res;
}

function xhtml_encode( $text )
{
  return htmlspecialchars($text, ENT_QUOTES);
}

function print_encoded( $text )
{
  print xhtml_encode( $text );
}

// Requires two colors in format "RRGGBB" and a floating
// point multiplier [0, 1]. Returns blended color in "RRGGBB" format.
function blend_colors( $a, $b, $mul )
{
    return sprintf("%02X%02X%02X",
        round( hexdec(substr($a, 0, 2)) * $mul + hexdec(substr($b, 0, 2)) * (1-$mul)),
        round( hexdec(substr($a, 2, 2)) * $mul + hexdec(substr($b, 2, 2)) * (1-$mul)),
        round( hexdec(substr($a, 4, 2)) * $mul + hexdec(substr($b, 4, 2)) * (1-$mul)));
}

// Borrowed from "charl_le at alcor dot concordia dot ca" in PHP docs
function my_glob ($pattern)
{
    $path_parts = pathinfo ($pattern);
    $pattern = '^' . str_replace (array ('*',  '?'), array ('(.+)', '(.)'), $path_parts['basename'] . '$');
    $dir = opendir ($path_parts['dirname']);
    while ($file = readdir ($dir)) {
        if ($file != "." && $file != ".." && ereg ($pattern, $file)) $result[] = "{$path_parts['dirname']}/$file";
    }
    closedir ($dir);

    // my changes here
    if ( isset($result) )
        return $result;

    return (array) null;
}

// Outputs a bunch of HTTP headers that should prevent caching
// of the result
function forceNoCache()
{
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
  header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
  header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");                          // HTTP/1.0
}

function debug($var){
 echo '<pre>'.print_r($var,TRUE).'</pre>';   
}
?>