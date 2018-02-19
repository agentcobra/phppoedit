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

define("HTPASSWDFILE", ".htpasswd");

/**
 * Loads htpasswd file into an array of form
 * Array( username => crypted_pass, ... )
 * @return array
 */
function load_htpasswd()
{
    if (!file_exists(HTPASSWDFILE)) {
        return Array();
    }

    $res = Array();
    foreach (file(HTPASSWDFILE) as $l) {
        $array = explode(':', $l);
        $user = $array[0];
        $pass = chop($array[1]);
        $res[$user] = $pass;
    }
    return $res;
}

/**
 * Saves the array given by load_htpasswd
 * Returns true on success, false on failure
 * @param $pass_array
 * @return bool
 */
function save_htpasswd($pass_array)
{
    $result = true;

    ignore_user_abort(true);
    $fp = fopen(HTPASSWDFILE, "w+");
    if (flock($fp, LOCK_EX)) {
        while (list($u, $p) = each($pass_array)) {
            fputs($fp, "$u:$p\n");
        }
        flock($fp, LOCK_UN); // release the lock
    } else {
        trigger_error("Could not save (lock) .htpasswd", E_USER_WARNING);
        $result = false;
    }
    fclose($fp);
    ignore_user_abort(false);
    return $result;
}

/**
 * Generates a htpasswd compatible crypted password string.
 * @param $pass
 * @return string
 */
function rand_salt_crypt($pass)
{
    $salt = "";
    mt_srand((double)microtime() * 1000000);
    for ($i = 0; $i < CRYPT_SALT_LENGTH; $i++) {
        $salt .= substr("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789./", mt_rand() & 63, 1);
    }
    return crypt($pass, $salt);
}

/**
 * Generates a htpasswd compatible sha1 password hash
 * @param $pass
 * @return string
 */
function rand_salt_sha1($pass)
{
    mt_srand((double)microtime() * 1000000);
    $salt = pack("CCCC", mt_rand(), mt_rand(), mt_rand(), mt_rand());
    return "{SSHA}" . base64_encode(pack("H*", sha1($pass . $salt)) . $salt);
}

/**
 * Generate a SHA1 password hash *without* salt
 * @param $pass
 * @return string
 */
function non_salted_sha1($pass)
{
    return "{SHA}" . base64_encode(pack("H*", sha1($pass)));
}

/**
 * Returns true if the user exists and the password matches, false otherwise
 * @param $pass_array
 * @param $user
 * @param $pass
 * @return bool
 */
function test_htpasswd($pass_array, $user, $pass)
{
    if (!isset($pass_array[$user])) {
        return false;
    }
    $crypted = $pass_array[$user];

    // Determine the password type
    // TODO: Support for MD5 Passwords
    if (substr($crypted, 0, 6) == "{SSHA}") {
        $ohash = base64_decode(substr($crypted, 6));
        return substr($ohash, 0, 20) == pack("H*", sha1($pass . substr($ohash, 20)));
    } else {
        if (substr($crypted, 0, 5) == "{SHA}") {
            return (non_salted_sha1($pass) == $crypted);
        } else {
            return crypt($pass, substr($crypted, 0, CRYPT_SALT_LENGTH)) == $crypted;
        }
    }
}

/**
 * Internal test
 */
function internal_unit_test()
{
    $pwds = Array(
        "Test" => rand_salt_crypt("testSecret!"),
        "fish" => rand_salt_crypt("sest Ticret"),
        "Generated" => "/uieo1ANOvsdA",
        "Generated2" => "Q3cbHUBgm7aYk"
    );

    assert(test_htpasswd($pwds, "Test", "testSecret!"));
    assert(!test_htpasswd($pwds, "Test", "wrong pass"));
    assert(test_htpasswd($pwds, "fish", "sest Ticret"));
    assert(!test_htpasswd($pwds, "fish", "wrong pass"));
    assert(test_htpasswd($pwds, "Generated", "withHtppasswdCmd"));
    assert(!test_htpasswd($pwds, "Generated", ""));
    assert(test_htpasswd($pwds, "Generated2", ""));
    assert(!test_htpasswd($pwds, "Generated2", "this is wrong too"));
}

?>