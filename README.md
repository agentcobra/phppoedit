# phppoedit
PHPPOEDIT 1.0

This is a light-weight PHP based PO-file (gettext-file) editor.
How to install:

 + Copy the files to a website
 + Copy your .pot files to the pot/ directory
 + Create one directory per target languge to the po/ directory
 + Copy the .pot files into po/ directories as empty .po files
   and make sure PHP has permissions to write into them
 + Create .htpasswd with the htpasswd command and add
   one user per language to it (use the language directory name
   as a username)
 + Enjoy.



The MIT License (MIT)
Copyright (c) 2004-2006 by Jarno Elonen <elonen@iki.fi> - 2015 by agentcobra <agentcobra@free.fr>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.