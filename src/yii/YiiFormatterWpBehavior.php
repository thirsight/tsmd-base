<?php

namespace {
    /**
     * Set the mbstring internal encoding to a binary safe encoding when func_overload
     * is enabled.
     *
     * When mbstring.func_overload is in use for multi-byte encodings, the results from
     * strlen() and similar functions respect the utf8 characters, causing binary data
     * to return incorrect lengths.
     *
     * This function overrides the mbstring encoding to a binary-safe encoding, and
     * resets it to the users expected encoding afterwards through the
     * `reset_mbstring_encoding` function.
     *
     * It is safe to recursively call this function, however each
     * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
     * of `reset_mbstring_encoding()` calls.
     *
     * @since wordpress 3.7.0
     *
     * @see reset_mbstring_encoding()
     *
     * @staticvar array $encodings
     * @staticvar bool  $overloaded
     *
     * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
     *                    Default false.
     */
    function mbstring_binary_safe_encoding($reset = false) {
        static $encodings = array();
        static $overloaded = null;

        if (is_null($overloaded))
            $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);

        if (false === $overloaded)
            return;

        if (!$reset) {
            $encoding = mb_internal_encoding();
            array_push($encodings, $encoding);
            mb_internal_encoding('ISO-8859-1');
        }

        if ($reset && $encodings) {
            $encoding = array_pop($encodings);
            mb_internal_encoding($encoding);
        }
    }

    /**
     * Reset the mbstring internal encoding to a users previously set encoding.
     *
     * @see mbstring_binary_safe_encoding()
     *
     * @since wordpress 3.7.0
     */
    function reset_mbstring_encoding() {
        mbstring_binary_safe_encoding(true);
    }

    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *       has Bytes Sequences with a maximum length of 4.
     *
     * @author bmorel at ssi dot fr (modified)
     * @since wordpress 1.2.1
     *
     * @param string $str The string to be checked
     * @return bool True if $str fits a UTF-8 model, false otherwise.
     */
    function seems_utf8($str) {
        mbstring_binary_safe_encoding();
        $length = strlen($str);
        reset_mbstring_encoding();
        for ($i=0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n=1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n=2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n=3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n=4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n=5; // 1111110b
            else return false; // Does not match any model
            for ($j=0; $j<$n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }
}

namespace tsmd\base\yii {
    use Yii;

    /**
     * @see wordpress wp-includes/formatting.php
     *
     * @author Haisen <thirsight@gmail.com>
     * @since 1.0
     */
    class YiiFormatterWpBehavior extends \yii\base\Behavior
    {
        /**
         * Sanitizes a filename, replacing whitespace with dashes.
         *
         * Removes special characters that are illegal in filenames on certain
         * operating systems and special characters requiring special escaping
         * to manipulate at the command line. Replaces spaces and consecutive
         * dashes with a single dash. Trims period, dash and underscore from beginning
         * and end of filename.
         *
         * @since 2.1.0
         *
         * @param string $filename The filename to be sanitized
         * @return string The sanitized filename
         */
        public function sanitizeFilename($filename)
        {
            $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0));
            /**
             * Filter the list of characters to remove from a filename.
             *
             * @since 2.8.0
             *
             * @param array $special_chars Characters to remove.
             */
            $filename = preg_replace("#\x{00a0}#siu", ' ', $filename);
            $filename = str_replace($special_chars, '', $filename);
            $filename = str_replace(array('%20', '+'), '-', $filename);
            $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
            $filename = trim($filename, '.-_');

            // Split the filename into a base and extension[s]
            $parts = explode('.', $filename);

            // Return if only one extension
            if (count($parts) <= 2) {
                return $filename;
            }

            // Process multiple extensions
            $filename = array_shift($parts);
            $extension = array_pop($parts);
            $mimes = 'jpg|jpeg|jpe|gif|png|bmp|tiff|tif|ico|asf|asx|wmv|wmx|wm|avi|divx|flv|mov|qt|mpeg|mpg|mpe|mp4|m4v|ogv|webm|mkv|3gp|3gpp|3g2|3gp2|txt|asc|c|cc|h|srt|csv|tsv|ics|rtx|css|htm|html|vtt|dfxp|mp3|m4a|m4b|ra|ram|wav|ogg|oga|mid|midi|wma|wax|mka|rtf|js|pdf|swf|class|tar|zip|gz|gzip|rar|7z|exe|psd|xcf|doc|pot|pps|ppt|wri|xla|xls|xlt|xlw|mdb|mpp|docx|docm|dotx|dotm|xlsx|xlsm|xlsb|xltx|xltm|xlam|pptx|pptm|ppsx|ppsm|potx|potm|ppam|sldx|sldm|onetoc|onetoc2|onetmp|onepkg|oxps|xps|odt|odp|ods|odg|odc|odb|odf|wp|wpd|key|numbers|pages';

            /*
             * Loop over any intermediate extensions. Postfix them with a trailing underscore
             * if they are a 2 - 5 character long alpha string not in the extension whitelist.
             */
            foreach ((array) $parts as $part) {
                $filename .= '.' . $part;

                if (preg_match("/^[a-zA-Z]{2,5}\d?$/", $part)) {
                    if (stripos("|{$mimes}|", "|{$part}|") === false) {
                        $filename .= '_';
                    }
                }
            }
            $filename .= '.' . $extension;

            return $filename;
        }

        /**
         * Sanitizes a username, stripping out unsafe characters.
         *
         * Removes tags, octets, entities, and if strict is enabled, will only keep
         * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
         * raw username (the username in the parameter), and the value of $strict as
         * parameters for the 'sanitize_user' filter.
         *
         * @since 2.0.0
         *
         * @param string $username The username to be sanitized.
         * @param bool   $strict   If set limits $username to specific characters. Default false.
         * @return string The sanitized username, after passing through filters.
         */
        public function sanitizeUsername($username, $strict = false)
        {
            $username = $this->stripTags($username);
            $username = $this->removeAccents($username);
            // Kill octets
            $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
            $username = preg_replace('/&.+?;/', '', $username); // Kill entities

            // If strict, reduce to ASCII for max portability.
            if ($strict) {
                $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
            }

            $username = trim($username);
            // Consolidate contiguous whitespace
            $username = preg_replace('|\s+|', ' ', $username);

            return $username;
        }

        /**
         * Sanitizes a string key.
         *
         * Keys are used as internal identifiers. Lowercase alphanumeric characters, dashes and underscores are allowed.
         *
         * @since 3.0.0
         *
         * @param string $key String key
         * @return string Sanitized key
         */
        public function sanitizeKey($key)
        {
            $key = strtolower($key);
            $key = preg_replace('/[^a-z0-9_\-]/', '', $key);

            return $key;
        }

        /**
         * Sanitizes a title, or returns a fallback title.
         *
         * Specifically, HTML and PHP tags are stripped. Further actions can be added
         * via the plugin API. If $title is empty and $fallback_title is set, the latter
         * will be used.
         *
         * @since 1.0.0
         *
         * @param string $title          The string to be sanitized.
         * @param string $fallback_title Optional. A title to use if $title is empty.
         * @param string $context        Optional. The operation for which the string is sanitized
         * @return string The sanitized string.
         */
        public function sanitizeTitle($title, $fallback_title = '', $context = 'save')
        {
            if ('save' == $context) {
                $title = $this->removeAccents($title);
            }
            $title = $this->sanitizeTitleWithDashes($title, $context);

            if ('' === $title || false === $title) {
                $title = $fallback_title;
            }

            return $title;
        }

        /**
         * Sanitizes a title, replacing whitespace and a few other characters with dashes.
         *
         * Limits the output to alphanumeric characters, underscore (_) and dash (-).
         * Whitespace becomes a dash.
         *
         * @since 1.2.0
         *
         * @param string $title   The title to be sanitized.
         * @param string $context Optional. The operation for which the string is sanitized.
         * @return string The sanitized title.
         */
        public function sanitizeTitleWithDashes($title, $context = 'display')
        {
            $title = strip_tags($title);
            // Preserve escaped octets.
            $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
            // Remove percent signs that are not part of an octet.
            $title = str_replace('%', '', $title);
            // Restore octets.
            $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

            if (seems_utf8($title)) {
                if (function_exists('mb_strtolower')) {
                    $title = mb_strtolower($title, 'UTF-8');
                }
                $title = $this->utf8URIEncode($title, 200);
            }

            $title = strtolower($title);
            $title = preg_replace('/&.+?;/', '', $title); // kill entities
            $title = str_replace('.', '-', $title);

            if ('save' == $context) {
                // Convert nbsp, ndash and mdash to hyphens
                $title = str_replace(array('%c2%a0', '%e2%80%93', '%e2%80%94'), '-', $title);

                // Strip these characters entirely
                $title = str_replace(array(
                    // iexcl and iquest
                    '%c2%a1', '%c2%bf',
                    // angle quotes
                    '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
                    // curly quotes
                    '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
                    '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
                    // copy, reg, deg, hellip and trade
                    '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
                    // acute accents
                    '%c2%b4', '%cb%8a', '%cc%81', '%cd%81',
                    // grave accent, macron, caron
                    '%cc%80', '%cc%84', '%cc%8c',
                ), '', $title);

                // Convert times to x
                $title = str_replace('%c3%97', 'x', $title);
            }

            $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
            $title = preg_replace('/\s+/', '-', $title);
            $title = preg_replace('|-+|', '-', $title);
            $title = trim($title, '-');

            return $title;
        }

        /**
         * Sanitizes an HTML classname to ensure it only contains valid characters.
         *
         * Strips the string down to A-Z,a-z,0-9,_,-. If this results in an empty
         * string then it will return the alternative value supplied.
         *
         * @todo Expand to support the full range of CDATA that a class attribute can contain.
         *
         * @since 2.8.0
         *
         * @param string $class    The classname to be sanitized
         * @param string $fallback Optional. The value to return if the sanitization ends up as an empty string.
         * 	Defaults to an empty string.
         * @return string The sanitized value
         */
        public function sanitizeHtmlClass($class, $fallback = '')
        {
            //Strip out any % encoded octets
            $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', $class);

            //Limit to A-Z,a-z,0-9,_,-
            $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

            if ('' == $sanitized && $fallback) {
                return $this->sanitizeHtmlClass($fallback);
            }

            return $sanitized;
        }

        /**
         * Sanitize a mime type
         *
         * @since 3.1.3
         *
         * @param string $mimeType Mime type
         * @return string Sanitized mime type
         */
        public function sanitizeMimeType($mimeType)
        {
            $sanitized = preg_replace('/[^-+*.a-zA-Z0-9\/]/', '', $mimeType);

            return $sanitized;
        }

        /**
         * Converts lone & characters into `&#038;` (a.k.a. `&amp;`)
         *
         * @since 0.71
         *
         * @param string $content String of characters to be converted.
         * @return string Converted string.
         */
        public function convertChars($content)
        {
            if (strpos($content, '&') !== false) {
                $content = preg_replace('/&([^#])(?![a-z1-4]{1,8};)/i', '&#038;$1', $content);
            }
            return $content;
        }

        /**
         * Converts invalid Unicode references range to valid range.
         *
         * @since 4.3.0
         *
         * @param string $content String with entities that need converting.
         * @return string Converted string.
         */
        public function convertInvalidEntities($content)
        {
            $htmltranswinuni = array(
                '&#128;' => '&#8364;', // the Euro sign
                '&#129;' => '',
                '&#130;' => '&#8218;', // these are Windows CP1252 specific characters
                '&#131;' => '&#402;',  // they would look weird on non-Windows browsers
                '&#132;' => '&#8222;',
                '&#133;' => '&#8230;',
                '&#134;' => '&#8224;',
                '&#135;' => '&#8225;',
                '&#136;' => '&#710;',
                '&#137;' => '&#8240;',
                '&#138;' => '&#352;',
                '&#139;' => '&#8249;',
                '&#140;' => '&#338;',
                '&#141;' => '',
                '&#142;' => '&#381;',
                '&#143;' => '',
                '&#144;' => '',
                '&#145;' => '&#8216;',
                '&#146;' => '&#8217;',
                '&#147;' => '&#8220;',
                '&#148;' => '&#8221;',
                '&#149;' => '&#8226;',
                '&#150;' => '&#8211;',
                '&#151;' => '&#8212;',
                '&#152;' => '&#732;',
                '&#153;' => '&#8482;',
                '&#154;' => '&#353;',
                '&#155;' => '&#8250;',
                '&#156;' => '&#339;',
                '&#157;' => '',
                '&#158;' => '&#382;',
                '&#159;' => '&#376;'
            );
            if (strpos($content, '&#1') !== false) {
                $content = strtr($content, $htmltranswinuni);
            }
            return $content;
        }

        /**
         * Balances tags of string using a modified stack.
         *
         * @since 2.0.4
         *
         * @author Leonard Lin <leonard@acm.org>
         * @license GPL
         * @copyright November 4, 2001
         * @version 1.1
         * @todo Make better - change loop condition to $text in 1.2
         * @internal Modified by Scott Reilly (coffee2code) 02 Aug 2004
         *		1.1  Fixed handling of append/stack pop order of end text
         *			 Added Cleaning Hooks
         *		1.0  First Version
         *
         * @param string $text Text to be balanced.
         * @return string Balanced text.
         */
        public function balanceTags($text)
        {
            $tagstack = array();
            $stacksize = 0;
            $tagqueue = '';
            $newtext = '';
            // Known single-entity/self-closing tags
            $single_tags = array('area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source');
            // Tags that can be immediately nested within themselves
            $nestable_tags = array('blockquote', 'div', 'object', 'q', 'span');

            // WP bug fix for comments - in case you REALLY meant to type '< !--'
            $text = str_replace('< !--', '<    !--', $text);
            // WP bug fix for LOVE <3 (and other situations with '<' before a number)
            $text = preg_replace('#<([0-9]{1})#', '&lt;$1', $text);

            while (preg_match("/<(\/?[\w:]*)\s*([^>]*)>/", $text, $regex)) {
                $newtext .= $tagqueue;

                $i = strpos($text, $regex[0]);
                $l = strlen($regex[0]);

                // clear the shifter
                $tagqueue = '';
                // Pop or Push
                if (isset($regex[1][0]) && '/' == $regex[1][0]) { // End Tag
                    $tag = strtolower(substr($regex[1],1));
                    // if too many closing tags
                    if ($stacksize <= 0) {
                        $tag = '';
                        // or close to be safe $tag = '/' . $tag;
                    }
                    // if stacktop value = tag close value then pop
                    elseif ($tagstack[$stacksize - 1] == $tag) { // found closing tag
                        $tag = '</' . $tag . '>'; // Close Tag
                        // Pop
                        array_pop($tagstack);
                        $stacksize--;
                    } else { // closing tag not at top, search for it
                        for ($j = $stacksize-1; $j >= 0; $j--) {
                            if ($tagstack[$j] == $tag) {
                                // add tag to tagqueue
                                for ($k = $stacksize-1; $k >= $j; $k--) {
                                    $tagqueue .= '</' . array_pop($tagstack) . '>';
                                    $stacksize--;
                                }
                                break;
                            }
                        }
                        $tag = '';
                    }
                } else { // Begin Tag
                    $tag = strtolower($regex[1]);

                    // Tag Cleaning

                    // If it's an empty tag "< >", do nothing
                    if ('' == $tag) {
                        // do nothing
                    }
                    // ElseIf it presents itself as a self-closing tag...
                    elseif (substr($regex[2], -1) == '/') {
                        // ...but it isn't a known single-entity self-closing tag, then don't let it be treated as such and
                        // immediately close it with a closing tag (the tag will encapsulate no text as a result)
                        if (! in_array($tag, $single_tags))
                            $regex[2] = trim(substr($regex[2], 0, -1)) . "></$tag";
                    }
                    // ElseIf it's a known single-entity tag but it doesn't close itself, do so
                    elseif (in_array($tag, $single_tags)) {
                        $regex[2] .= '/';
                    }
                    // Else it's not a single-entity tag
                    else {
                        // If the top of the stack is the same as the tag we want to push, close previous tag
                        if ($stacksize > 0 && !in_array($tag, $nestable_tags) && $tagstack[$stacksize - 1] == $tag) {
                            $tagqueue = '</' . array_pop($tagstack) . '>';
                            $stacksize--;
                        }
                        $stacksize = array_push($tagstack, $tag);
                    }

                    // Attributes
                    $attributes = $regex[2];
                    if (! empty($attributes) && $attributes[0] != '>')
                        $attributes = ' ' . $attributes;

                    $tag = '<' . $tag . $attributes . '>';
                    //If already queuing a close tag, then put this tag on, too
                    if (!empty($tagqueue)) {
                        $tagqueue .= $tag;
                        $tag = '';
                    }
                }
                $newtext .= substr($text, 0, $i) . $tag;
                $text = substr($text, $i + $l);
            }

            // Clear Tag Queue
            $newtext .= $tagqueue;

            // Add Remaining text
            $newtext .= $text;

            // Empty Stack
            while($x = array_pop($tagstack))
                $newtext .= '</' . $x . '>'; // Add remaining tags to close

            // WP fix for the bug with HTML comments
            $newtext = str_replace("< !--","<!--",$newtext);
            $newtext = str_replace("<    !--","< !--",$newtext);

            return $newtext;
        }

        /**
         * Properly strip all HTML tags including script and style
         *
         * This differs from strip_tags() because it removes the contents of
         * the `<script>` and `<style>` tags. E.g. `strip_tags('<script>something</script>')`
         * will return 'something'. stripTags will return ''
         *
         * @since 2.9.0
         *
         * @param string $string        String containing HTML tags
         * @param bool   $removeBreaks Optional. Whether to remove left over line breaks and white space chars
         * @return string The processed string.
         */
        public function stripTags($string, $removeBreaks = false)
        {
            $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
            $string = strip_tags($string);

            if ($removeBreaks) {
                $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
            }

            return trim($string);
        }

        /**
         * @param $string
         * @return mixed
         */
        public function stripEmptyTags($string)
        {
            $tags = array('div', 'p', 'span');
            $tags = implode('|', $tags);
            return preg_replace('#<(?:' . $tags . ')>\s*(?:\s*<br[^>]*?>\s*)*\s*</(?:' . $tags . ')>#i', '', $string);
        }

        /**
         * Encode the Unicode values to be used in the URI.
         *
         * @since 1.5.0
         *
         * @param string $utf8String
         * @param int    $length Max  length of the string
         * @return string String with Unicode encoded for URI.
         */
        public function utf8URIEncode($utf8String, $length = 0)
        {
            $unicode = '';
            $values = array();
            $num_octets = 1;
            $unicode_length = 0;

            mbstring_binary_safe_encoding();
            $string_length = strlen($utf8String);
            reset_mbstring_encoding();

            for ($i = 0; $i < $string_length; $i++) {
                $value = ord($utf8String[ $i ]);

                if ($value < 128) {
                    if ($length && ($unicode_length >= $length))
                        break;
                    $unicode .= chr($value);
                    $unicode_length++;
                } else {
                    if (count($values) == 0) {
                        if ($value < 224) {
                            $num_octets = 2;
                        } elseif ($value < 240) {
                            $num_octets = 3;
                        } else {
                            $num_octets = 4;
                        }
                    }

                    $values[] = $value;

                    if ($length && ($unicode_length + ($num_octets * 3)) > $length)
                        break;
                    if (count($values) == $num_octets) {
                        for ($j = 0; $j < $num_octets; $j++) {
                            $unicode .= '%' . dechex($values[ $j ]);
                        }

                        $unicode_length += $num_octets * 3;

                        $values = array();
                        $num_octets = 1;
                    }
                }
            }

            return $unicode;
        }

        /**
         * Converts all accent characters to ASCII characters.
         *
         * If there are no accent characters, then the string given is just returned.
         *
         * @since 1.2.1
         *
         * @param string $string Text that might have accent characters
         * @return string Filtered string with replaced "nice" characters.
         */
        public function removeAccents($string)
        {
            if (!preg_match('/[\x80-\xff]/', $string))
                return $string;

            if (seems_utf8($string)) {
                $chars = array(
                    // Decompositions for Latin-1 Supplement
                    chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
                    chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
                    chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
                    chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
                    chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
                    chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
                    chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
                    chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
                    chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
                    chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
                    chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
                    chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
                    chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
                    chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
                    chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
                    chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
                    chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
                    chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
                    chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
                    chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
                    chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
                    chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
                    chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
                    chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
                    chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
                    chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
                    chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
                    chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
                    chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
                    chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
                    chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
                    chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
                    // Decompositions for Latin Extended-A
                    chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
                    chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
                    chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
                    chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
                    chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
                    chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
                    chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
                    chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
                    chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
                    chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
                    chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
                    chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
                    chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
                    chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
                    chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
                    chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
                    chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
                    chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
                    chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
                    chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
                    chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
                    chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
                    chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
                    chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
                    chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
                    chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
                    chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
                    chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
                    chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
                    chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
                    chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
                    chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
                    chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
                    chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
                    chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
                    chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
                    chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
                    chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
                    chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
                    chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
                    chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
                    chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
                    chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
                    chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
                    chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
                    chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
                    chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
                    chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
                    chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
                    chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
                    chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
                    chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
                    chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
                    chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
                    chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
                    chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
                    chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
                    chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
                    chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
                    chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
                    chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
                    chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
                    chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
                    chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
                    // Decompositions for Latin Extended-B
                    chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
                    chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
                    // Euro Sign
                    chr(226).chr(130).chr(172) => 'E',
                    // GBP (Pound) Sign
                    chr(194).chr(163) => '',
                    // Vowels with diacritic (Vietnamese)
                    // unmarked
                    chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
                    chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
                    // grave accent
                    chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
                    chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
                    chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
                    chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
                    chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
                    chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
                    chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
                    // hook
                    chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
                    chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
                    chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
                    chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
                    chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
                    chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
                    chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
                    chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
                    chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
                    chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
                    chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
                    chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
                    // tilde
                    chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
                    chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
                    chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
                    chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
                    chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
                    chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
                    chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
                    chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
                    // acute accent
                    chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
                    chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
                    chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
                    chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
                    chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
                    chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
                    // dot below
                    chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
                    chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
                    chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
                    chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
                    chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
                    chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
                    chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
                    chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
                    chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
                    chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
                    chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
                    chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
                    // Vowels with diacritic (Chinese, Hanyu Pinyin)
                    chr(201).chr(145) => 'a',
                    // macron
                    chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
                    // acute accent
                    chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
                    // caron
                    chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
                    chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
                    chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
                    chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
                    chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
                    // grave accent
                    chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
                );

                // Used for locale-specific rules
                $locale = Yii::$app->language;

                if ('de_DE' == $locale || 'de_DE_formal' == $locale) {
                    $chars[ chr(195).chr(132) ] = 'Ae';
                    $chars[ chr(195).chr(164) ] = 'ae';
                    $chars[ chr(195).chr(150) ] = 'Oe';
                    $chars[ chr(195).chr(182) ] = 'oe';
                    $chars[ chr(195).chr(156) ] = 'Ue';
                    $chars[ chr(195).chr(188) ] = 'ue';
                    $chars[ chr(195).chr(159) ] = 'ss';
                } elseif ('da_DK' === $locale) {
                    $chars[ chr(195).chr(134) ] = 'Ae';
                    $chars[ chr(195).chr(166) ] = 'ae';
                    $chars[ chr(195).chr(152) ] = 'Oe';
                    $chars[ chr(195).chr(184) ] = 'oe';
                    $chars[ chr(195).chr(133) ] = 'Aa';
                    $chars[ chr(195).chr(165) ] = 'aa';
                }

                $string = strtr($string, $chars);
            } else {
                $chars = array();
                // Assume ISO-8859-1 if not UTF-8
                $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                    .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                    .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                    .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                    .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                    .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                    .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                    .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                    .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                    .chr(252).chr(253).chr(255);

                $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

                $string = strtr($string, $chars['in'], $chars['out']);
                $double_chars = array();
                $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
                $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
                $string = str_replace($double_chars['in'], $double_chars['out'], $string);
            }

            return $string;
        }

        /**
         * Replaces double line-breaks with paragraph elements.
         *
         * A group of regex replaces used to identify text formatted with newlines and
         * replace double line-breaks with HTML paragraph tags. The remaining line-breaks
         * after conversion become <<br />> tags, unless $br is set to '0' or 'false'.
         *
         * @since 0.71
         *
         * @param string $pee The text which has to be formatted.
         * @param bool   $br  Optional. If set, this will convert all remaining line-breaks
         *                    after paragraphing. Default true.
         * @return string Text which has been converted into correct paragraph tags.
         */
        public function autoP($pee, $br = true)
        {
            $pre_tags = array();

            if (trim($pee) === '')
                return '';

            // Just to make things a little easier, pad the end.
            $pee = $pee . "\n";

            /*
             * Pre tags shouldn't be touched by autop.
             * Replace pre tags with placeholders and bring them back after autop.
             */
            if (strpos($pee, '<pre') !== false) {
                $pee_parts = explode('</pre>', $pee);
                $last_pee = array_pop($pee_parts);
                $pee = '';
                $i = 0;

                foreach ($pee_parts as $pee_part) {
                    $start = strpos($pee_part, '<pre');

                    // Malformed html?
                    if ($start === false) {
                        $pee .= $pee_part;
                        continue;
                    }

                    $name = "<pre wp-pre-tag-$i></pre>";
                    $pre_tags[$name] = substr($pee_part, $start) . '</pre>';

                    $pee .= substr($pee_part, 0, $start) . $name;
                    $i++;
                }

                $pee .= $last_pee;
            }
            // Change multiple <br>s into two line breaks, which will turn into paragraphs.
            $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);

            $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

            // Add a single line break above block-level opening tags.
            $pee = preg_replace('!(<' . $allblocks . '[\s/>])!', "\n$1", $pee);

            // Add a double line break below block-level closing tags.
            $pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);

            // Standardize newline characters to "\n".
            $pee = str_replace(array("\r\n", "\r"), "\n", $pee);

            // Find newlines in all elements and add placeholders.
            $pee = $this->replaceHtmlTags($pee, array("\n" => " <!-- wpnl --> "));

            // Collapse line breaks before and after <option> elements so they don't get autop'd.
            if (strpos($pee, '<option') !== false) {
                $pee = preg_replace('|\s*<option|', '<option', $pee);
                $pee = preg_replace('|</option>\s*|', '</option>', $pee);
            }

            /*
             * Collapse line breaks inside <object> elements, before <param> and <embed> elements
             * so they don't get autop'd.
             */
            if (strpos($pee, '</object>') !== false) {
                $pee = preg_replace('|(<object[^>]*>)\s*|', '$1', $pee);
                $pee = preg_replace('|\s*</object>|', '</object>', $pee);
                $pee = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee);
            }

            /*
             * Collapse line breaks inside <audio> and <video> elements,
             * before and after <source> and <track> elements.
             */
            if (strpos($pee, '<source') !== false || strpos($pee, '<track') !== false) {
                $pee = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee);
                $pee = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee);
                $pee = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee);
            }

            // Remove more than two contiguous line breaks.
            $pee = preg_replace("/\n\n+/", "\n\n", $pee);

            // Split up the contents into an array of strings, separated by double line breaks.
            $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);

            // Reset $pee prior to rebuilding.
            $pee = '';

            // Rebuild the content as a string, wrapping every bit with a <p>.
            foreach ($pees as $tinkle) {
                $pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
            }

            // Under certain strange conditions it could create a P of entirely whitespace.
            $pee = preg_replace('|<p>\s*</p>|', '', $pee);

            // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
            $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);

            // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
            $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

            // In some cases <li> may get wrapped in <p>, fix them.
            $pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);

            // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
            $pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
            $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);

            // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
            $pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);

            // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
            $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

            // Optionally insert line breaks.
            if ($br) {
                // Replace newlines that shouldn't be touched with a placeholder.
                $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', function ($matches) {
                    return str_replace( "\n", "<WPPreserveNewline />", $matches[0] );
                }, $pee);

                // Normalize <br>
                $pee = str_replace(array('<br>', '<br/>'), '<br />', $pee);

                // Replace any new line characters that aren't preceded by a <br /> with a <br />.
                $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);

                // Replace newline placeholders with newlines.
                $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
            }

            // If a <br /> tag is after an opening or closing block tag, remove it.
            $pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);

            // If a <br /> tag is before a subset of opening or closing block tags, remove it.
            $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
            $pee = preg_replace("|\n</p>$|", '</p>', $pee);

            // Replace placeholder <pre> tags with their original content.
            if (!empty($pre_tags))
                $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);

            // Restore newlines in all elements.
            if (false !== strpos($pee, '<!-- wpnl -->')) {
                $pee = str_replace(array(' <!-- wpnl --> ', '<!-- wpnl -->'), "\n", $pee);
            }

            return $pee;
        }

        /**
         * Replace characters or phrases within HTML elements only.
         *
         * @since 4.2.3
         *
         * @param string $haystack The text which has to be formatted.
         * @param array $replace_pairs In the form array('from' => 'to', ...).
         * @return string The formatted text.
         */
        protected function replaceHtmlTags($haystack, $replace_pairs)
        {
            // Find all elements.
            $textarr = preg_split($this->getHtmlSplitRegex(), $haystack, -1, PREG_SPLIT_DELIM_CAPTURE);
            $changed = false;

            // Optimize when searching for one item.
            if (1 === count($replace_pairs)) {
                // Extract $needle and $replace.
                foreach ($replace_pairs as $needle => $replace);

                // Loop through delimiters (elements) only.
                for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                    if (false !== strpos($textarr[$i], $needle)) {
                        $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                        $changed = true;
                    }
                }
            } else {
                // Extract all $needles.
                $needles = array_keys($replace_pairs);

                // Loop through delimiters (elements) only.
                for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
                    foreach ($needles as $needle) {
                        if (false !== strpos($textarr[$i], $needle)) {
                            $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                            $changed = true;
                            // After one strtr() break out of the foreach loop and look at next element.
                            break;
                        }
                    }
                }
            }

            if ($changed) {
                $haystack = implode($textarr);
            }

            return $haystack;
        }

        /**
         * Retrieve the regular expression for an HTML element.
         *
         * @since 4.4.0
         *
         * @return string The regular expression
         */
        protected function getHtmlSplitRegex()
        {
            static $regex;

            if (! isset($regex)) {
                $comments =
                    '!'           // Start of comment, after the <.
                    . '(?:'         // Unroll the loop: Consume everything until --> is found.
                    .     '-(?!->)' // Dash not followed by end of comment.
                    .     '[^\-]*+' // Consume non-dashes.
                    . ')*+'         // Loop possessively.
                    . '(?:-->)?';   // End of comment. If not found, match all input.

                $cdata =
                    '!\[CDATA\['  // Start of comment, after the <.
                    . '[^\]]*+'     // Consume non-].
                    . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
                    .     '](?!]>)' // One ] not followed by end of comment.
                    .     '[^\]]*+' // Consume non-].
                    . ')*+'         // Loop possessively.
                    . '(?:]]>)?';   // End of comment. If not found, match all input.

                $escaped =
                    '(?='           // Is the element escaped?
                    .    '!--'
                    . '|'
                    .    '!\[CDATA\['
                    . ')'
                    . '(?(?=!-)'      // If yes, which type?
                    .     $comments
                    . '|'
                    .     $cdata
                    . ')';

                $regex =
                    '/('              // Capture the entire match.
                    .     '<'           // Find start of element.
                    .     '(?'          // Conditional expression follows.
                    .         $escaped  // Find end of escaped element.
                    .     '|'           // ... else ...
                    .         '[^>]*>?' // Find end of normal element.
                    .     ')'
                    . ')/';
            }

            return $regex;
        }

        /**
         * Convert text equivalent of smilies to images.
         *
         * Will only convert smilies if the option 'use_smilies' is true and the global
         * used in the function isn't empty.
         *
         * @since 0.71
         *
         * @global string|array $smiliesSearch
         *
         * @param string $text Content to convert smilies from text.
         * @return string Converted content with text smilies replaced with images.
         */
        public function convertSmiles($text)
        {
            $smiliesSearch = $this->getSmiliesSearch();

            $output = '';

            // HTML loop taken from texturize function, could possible be consolidated
            $textarr = preg_split('/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE); // capture the tags as well as in between
            $stop = count($textarr);// loop stuff

            // Ignore proessing of specific tags
            $tags_to_ignore = 'code|pre|style|script|textarea';
            $ignore_block_element = '';

            for ($i = 0; $i < $stop; $i++) {
                $content = $textarr[$i];

                // If we're in an ignore block, wait until we find its closing tag
                if ('' == $ignore_block_element && preg_match('/^<(' . $tags_to_ignore . ')>/', $content, $matches))  {
                    $ignore_block_element = $matches[1];
                }

                // If it's not a tag and not in ignore block
                if ('' ==  $ignore_block_element && strlen($content) > 0 && '<' != $content[0]) {
                    $content = preg_replace_callback($smiliesSearch, [$this, 'translateSmiley'], $content);
                }

                // did we exit ignore block
                if ('' != $ignore_block_element && '</' . $ignore_block_element . '>' == $content)  {
                    $ignore_block_element = '';
                }

                $output .= $content;
            }

            return $output ?: $text;
        }

        /**
         * Convert one smiley code to the icon graphic file equivalent.
         *
         * Callback handler for {@link convert_smilies()}.
         * Looks up one smiley code in the $smiliesTrans global array and returns an
         * `<img>` string for that smiley.
         *
         * @since 2.8.0
         *
         * @global array $smiliesTrans
         *
         * @param array $matches Single match. Smiley code to convert to image.
         * @return string Image string for smiley.
         */
        public function translateSmiley($matches)
        {
            $smiliesTrans = $this->getSmiliesTrans();

            if (count($matches) == 0)
                return '';

            $smiley = trim(reset($matches));
            $img = $smiliesTrans[ $smiley ];

            $matches = array();
            $ext = preg_match('/\.([^.]+)$/', $img, $matches) ? strtolower($matches[1]) : false;
            $image_exts = array('jpg', 'jpeg', 'jpe', 'gif', 'png');

            // Don't convert smilies that aren't images - they're probably emoji.
            if (!in_array($ext, $image_exts)) {
                return $img;
            }

            /**
             * Filter the Smiley image URL before it's used in the image element.
             *
             * @since 2.9.0
             *
             * @param string $smiley_url URL for the smiley image.
             * @param string $img        Filename for the smiley image.
             * @param string $site_url   Site URL, as returned by site_url().
             */
            //$src_url = apply_filters('smilies_src', includes_url("images/smilies/$img"), $img, site_url());

            //return sprintf('<img src="%s" alt="%s" class="wp-smiley" style="height: 1em; max-height: 1em;" />', esc_url($src_url), esc_attr($smiley));
        }

        /**
         * Convert smiley code to the icon graphic file equivalent.
         *
         * You can turn off smilies, by going to the write setting screen and unchecking
         * the box, or by setting 'use_smilies' option to false or removing the option.
         *
         * Plugins may override the default smiley list by setting the $smiliesTrans
         * to an array, with the key the code the blogger types in and the value the
         * image file.
         *
         * The $smiliesSearch global is for the regular expression and is set each
         * time the function is called.
         *
         * The full list of smilies can be found in the function and won't be listed in
         * the description. Probably should create a Codex page for it, so that it is
         * available.
         *
         * @see functions/smilies_init
         *
         * @return array
         */
        protected function getSmiliesTrans()
        {
            static $smiliesTrans;
            if (empty($smiliesTrans)) {
                $smiliesTrans = array(
                    ':mrgreen:' => 'mrgreen.png',
                    ':neutral:' => "\xf0\x9f\x98\x90",
                    ':twisted:' => "\xf0\x9f\x98\x88",
                    ':arrow:' => "\xe2\x9e\xa1",
                    ':shock:' => "\xf0\x9f\x98\xaf",
                    ':smile:' => 'simple-smile.png',
                    ':???:' => "\xf0\x9f\x98\x95",
                    ':cool:' => "\xf0\x9f\x98\x8e",
                    ':evil:' => "\xf0\x9f\x91\xbf",
                    ':grin:' => "\xf0\x9f\x98\x80",
                    ':idea:' => "\xf0\x9f\x92\xa1",
                    ':oops:' => "\xf0\x9f\x98\xb3",
                    ':razz:' => "\xf0\x9f\x98\x9b",
                    ':roll:' => 'rolleyes.png',
                    ':wink:' => "\xf0\x9f\x98\x89",
                    ':cry:' => "\xf0\x9f\x98\xa5",
                    ':eek:' => "\xf0\x9f\x98\xae",
                    ':lol:' => "\xf0\x9f\x98\x86",
                    ':mad:' => "\xf0\x9f\x98\xa1",
                    ':sad:' => 'frownie.png',
                    '8-)' => "\xf0\x9f\x98\x8e",
                    '8-O' => "\xf0\x9f\x98\xaf",
                    ':-(' => 'frownie.png',
                    ':-)' => 'simple-smile.png',
                    ':-?' => "\xf0\x9f\x98\x95",
                    ':-D' => "\xf0\x9f\x98\x80",
                    ':-P' => "\xf0\x9f\x98\x9b",
                    ':-o' => "\xf0\x9f\x98\xae",
                    ':-x' => "\xf0\x9f\x98\xa1",
                    ':-|' => "\xf0\x9f\x98\x90",
                    ';-)' => "\xf0\x9f\x98\x89",
                    // This one transformation breaks regular text with frequency.
                    //     '8)' => "\xf0\x9f\x98\x8e",
                    '8O' => "\xf0\x9f\x98\xaf",
                    ':(' => 'frownie.png',
                    ':)' => 'simple-smile.png',
                    ':?' => "\xf0\x9f\x98\x95",
                    ':D' => "\xf0\x9f\x98\x80",
                    ':P' => "\xf0\x9f\x98\x9b",
                    ':o' => "\xf0\x9f\x98\xae",
                    ':x' => "\xf0\x9f\x98\xa1",
                    ':|' => "\xf0\x9f\x98\x90",
                    ';)' => "\xf0\x9f\x98\x89",
                    ':!:' => "\xe2\x9d\x97",
                    ':?:' => "\xe2\x9d\x93",
                );
            }
            return $smiliesTrans;
        }

        /**
         * @return string
         */
        protected function getSmiliesSearch()
        {
            static $smiliesSearch;
            if (!empty($smiliesSearch)) {
                return $smiliesSearch;
            }

            $smiliesTrans = $this->getSmiliesTrans();

            /*
             * NOTE: we sort the smilies in reverse key order. This is to make sure
             * we match the longest possible smilie (:???: vs :?) as the regular
             * expression used below is first-match
             */
            krsort($smiliesTrans);

            $spaces = $this->getSpacesRegex();

            // Begin first "subpattern"
            $smiliesSearch = '/(?<=' . $spaces . '|^)';

            $subchar = '';
            foreach ((array) $smiliesTrans as $smiley => $img) {
                $firstchar = substr($smiley, 0, 1);
                $rest = substr($smiley, 1);

                // new subpattern?
                if ($firstchar != $subchar) {
                    if ($subchar != '') {
                        $smiliesSearch .= ')(?=' . $spaces . '|$)';  // End previous "subpattern"
                        $smiliesSearch .= '|(?<=' . $spaces . '|^)'; // Begin another "subpattern"
                    }
                    $subchar = $firstchar;
                    $smiliesSearch .= preg_quote($firstchar, '/') . '(?:';
                } else {
                    $smiliesSearch .= '|';
                }
                $smiliesSearch .= preg_quote($rest, '/');
            }

            $smiliesSearch .= ')(?=' . $spaces . '|$)/m';

            return $smiliesSearch;
        }

        /**
         * Returns the regexp for common whitespace characters.
         *
         * By default, spaces include new lines, tabs, nbsp entities, and the UTF-8 nbsp.
         * This is designed to replace the PCRE \s sequence.  In ticket #22692, that
         * sequence was found to be unreliable due to random inclusion of the A0 byte.
         *
         * @see wp_spaces_regexp
         *
         * @staticvar string $spaces
         *
         * @return string The spaces regexp.
         */
        protected function getSpacesRegex()
        {
            return '[\r\n\t ]|\xC2\xA0|&nbsp;';
        }
    }
}
