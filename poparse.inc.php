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


/**
 * @param $txt
 * @return string
 */
function decode_po_quotes($txt)
{
    return stripcslashes(preg_replace('/"[^"]*$/', '', preg_replace('/^[^"]*"/', '', $txt)));
}

/**
 * @param $lines
 * @return array
 */
function parse_po($lines)
{
    $res = [];
    $last_line_type = "comment";

    $empty_entry = [
        "comments" => [],
        "autocomments" => [],
        "sources" => [],
        "flags" => [],
        "msgid" => "",
        "msgstr" => ""
    ];
    $cur = $empty_entry;

    $lineno = 0;
    foreach ($lines as $line) {
        $lineno++;
        $line = trim($line);

        if (strlen($line)) {
            if (preg_match('/^#/', $line)) {
                if ($last_line_type != "comment") {
                    if (strlen($cur["msgid"]) || strlen(join($cur["comments"]))) {
                        $res[] = $cur;
                    }
                    $cur = $empty_entry;
                }

                if (preg_match('/^#:/', $line)) {
                    $cur["sources"][] = trim(substr($line, 2));
                } else {
                    if (preg_match('/^#,/', $line)) {
                        $cur["flags"] = preg_split('/[#, \t]+/', $line, -1, PREG_SPLIT_NO_EMPTY);
                    } else {
                        if (preg_match('/^#[.]/', $line)) {
                            $cur["autocomments"][] = trim(substr($line, 2));
                        } else {
                            $cur["comments"][] = trim(substr($line, 2));
                        }
                    }
                }

                $last_line_type = "comment";
            } else {
                if (preg_match('/^msgid[ \t]+["]*/', $line)) {
                    if ($last_line_type != "comment") {
                        if (strlen($cur["msgid"]) || strlen($cur["comments"])) {
                            $res[] = $cur;
                        }
                        $cur = $empty_entry;
                    }
                    $cur["msgid"] = decode_po_quotes(substr($line, 6));
                    $last_line_type = "msgid";
                } else {
                    if (preg_match('/^msgstr[ \t]+["]*/', $line)) {
                        if ($cur === false) {
                            $cur = $empty_entry;
                        }

                        $cur["msgstr"] = decode_po_quotes($line);
                        $last_line_type = "msgstr";
                    } else {
                        if (preg_match('/^"/', $line)) {
                            if (!preg_match('/msg(id|str)/', $last_line_type)) {
                                print "Warning: syntax error in PO-file on line $lineno \n";
                            } else {
                                $cur[$last_line_type] .= decode_po_quotes($line);
                            }
                        } else {
                            print "Warning: malformed line $lineno in PO-file.\n";
                        }
                    }
                }
            }
        }
    }
    if (strlen($cur["msgid"]) || strlen($cur["comments"])) {
        $res[] = $cur;
    }

    $res2 = array();
    foreach ($res as $r) {
        $res2[md5($r["msgid"])] = $r;
    }

    return $res2;
}

/**
 * @param $txt
 * @return string
 */
function slash_and_split_lines($txt)
{
    $res = "";
    // Split the string if it's very long
    $broken_lines = array();
    while (mb_strpos($txt, "\n", 0, 'UTF-8') !== false) {
        $i = mb_strpos($txt, "\n", 0, 'UTF-8');
        $broken_lines[] = mb_substr($txt, 0, $i + 1, 'UTF-8');
        $txt = mb_substr($txt, $i + 1, mb_strlen($txt, 'UTF-8'), 'UTF-8');
    }
    if (mb_strlen($txt, 'UTF-8')) {
        $broken_lines[] = $txt;
    }

    foreach ($broken_lines as $line) {
        $l = sprintf("\"%s\"\n", addcslashes($line, "\"\0..\37\\"));
        if (mb_strlen($l, 'UTF-8') > 65) {
            $l = "";
            while (mb_strlen($line, 'UTF-8') > 0) {
                $l .= "\"" . addcslashes(mb_substr($line, 0, 65, 'UTF-8'), "\"\0..\37\\") . "\"\n";
                $line = mb_substr($line, 65, mb_strlen($line, 'UTF-8'), 'UTF-8');
            }
        }
        $res .= $l;
    }

    if (count($broken_lines) == 0) {
        return "\"\"\n";
    }

    if (substr_count($res, "\n") > 1) // mb_substr_count >= PHP 4.3
    {
        $res = "\"\"\n" . $res;
    }

    return $res;
}

/**
 * @param $entries
 * @return string
 */
function unparse_po($entries)
{
    $res = "";
    while (list($k, $e) = each($entries)) {
        foreach ($e["comments"] as $l) {
            if (strlen($l)) {
                $res .= "# " . $l . "\n";
            }
        }

        foreach ($e["sources"] as $l) {
            if (strlen($l)) {
                $res .= "#: " . $l . "\n";
            }
        }

        foreach ($e["autocomments"] as $l) {
            if (strlen($l)) {
                $res .= "#. " . $l . "\n";
            }
        }

        if (count($e["flags"]) > 0) {
            $res .= "#, " . join(", ", $e["flags"]) . "\n";
        }

        $res .= sprintf("msgid %s", slash_and_split_lines($e["msgid"]));
        $res .= sprintf("msgstr %s", slash_and_split_lines($e["msgstr"]));

        $res .= "\n";
    }

    return $res;
}

?>