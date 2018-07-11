<?php

namespace Illuminate\Support;

class Str
{
    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = array();

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string   $value
     * @return string
     */
    public static function ascii($value)
    {
        foreach (static::charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string   $value
     * @return string
     */
    public static function camel($value)
    {
        return lcfirst(static::studly($value));
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }

        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle == substr($haystack, -strlen($needle))) {
                return true;
            }

        }

        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string   $value
     * @param  string   $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/', '', $value) . $cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string $pattern
     * @param  string $value
     * @return bool
     */
    public static function is($pattern, $value)
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern) . '\z';

        return (bool) preg_match('#^' . $pattern . '#', $value);
    }

    /**
     * Return the length of the given string.
     *
     * @param  string $value
     * @return int
     */
    public static function length($value)
    {
        return mb_strlen($value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string   $value
     * @param  int      $limit
     * @param  string   $end
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...')
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string   $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value);
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string   $value
     * @param  int      $words
     * @param  string   $end
     * @return string
     */
    public static function words($value, $words = 100, $end = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0])) {
            return $value;
        }

        if (strlen($value) == strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param  string  $callback
     * @param  string  $default
     * @return array
     */
    public static function parseCallback($callback, $default)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : array($callback, $default);
    }

    /**
     * Get the plural form of an English word.
     *
     * @param  string   $value
     * @param  int      $count
     * @return string
     */
    public static function plural($value, $count = 2)
    {
        return Pluralizer::plural($value, $count);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int                 $length
     * @throws \RuntimeException
     * @return string
     */
    public static function random($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \RuntimeException('Unable to generate random string.');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        return static::quickRandom($length);
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param  int      $length
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Convert the given string to upper-case.
     *
     * @param  string   $value
     * @return string
     */
    public static function upper($value)
    {
        return mb_strtoupper($value);
    }

    /**
     * Convert the given string to title case.
     *
     * @param  string   $value
     * @return string
     */
    public static function title($value)
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Get the singular form of an English word.
     *
     * @param  string   $value
     * @return string
     */
    public static function singular($value)
    {
        return Pluralizer::singular($value);
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param  string   $title
     * @param  string   $separator
     * @return string
     */
    public static function slug($title, $separator = '-')
    {
        $title = static::ascii($title);

        // Convert all dashes/underscores into separator
        $flip = $separator == '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string   $value
     * @param  string   $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $replace = '$1' . $delimiter . '$2';

        return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', $replace, $value));
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string       $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }

        }

        return false;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string   $value
     * @return string
     */
    public static function studly($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Register a custom string macro.
     *
     * @param  string   $name
     * @param  callable $macro
     * @return void
     */
    public static function macro($name, $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Dynamically handle calls to the string class.
     *
     * @param  string                    $method
     * @param  array                     $parameters
     * @throws \BadMethodCallException
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (isset(static::$macros[$method])) {
            return call_user_func_array(static::$macros[$method], $parameters);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
     *
     * @return array
     */
    protected static function charsArray()
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = array(
            '0'    => array('°', '₀', '۰'),
            '1'    => array('¹', '₁', '۱'),
            '2'    => array('²', '₂', '۲'),
            '3'    => array('³', '₃', '۳'),
            '4'    => array('⁴', '₄', '۴', '٤'),
            '5'    => array('⁵', '₅', '۵', '٥'),
            '6'    => array('⁶', '₆', '۶', '٦'),
            '7'    => array('⁷', '₇', '۷'),
            '8'    => array('⁸', '₈', '۸'),
            '9'    => array('⁹', '₉', '۹'),
            'a'    => array('à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا'),
            'b'    => array('б', 'β', 'Ъ', 'Ь', 'ب', 'ဗ', 'ბ'),
            'c'    => array('ç', 'ć', 'č', 'ĉ', 'ċ'),
            'd'    => array('ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ'),
            'e'    => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ'),
            'f'    => array('ф', 'φ', 'ف', 'ƒ', 'ფ'),
            'g'    => array('ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ'),
            'h'    => array('ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ'),
            'i'    => array('í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ'),
            'j'    => array('ĵ', 'ј', 'Ј', 'ჯ', 'ج'),
            'k'    => array('ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک'),
            'l'    => array('ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ'),
            'm'    => array('м', 'μ', 'م', 'မ', 'მ'),
            'n'    => array('ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ'),
            'o'    => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ'),
            'p'    => array('п', 'π', 'ပ', 'პ', 'پ'),
            'q'    => array('ყ'),
            'r'    => array('ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ'),
            's'    => array('ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს'),
            't'    => array('ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ'),
            'u'    => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ'),
            'v'    => array('в', 'ვ', 'ϐ'),
            'w'    => array('ŵ', 'ω', 'ώ', 'ဝ', 'ွ'),
            'x'    => array('χ', 'ξ'),
            'y'    => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ'),
            'z'    => array('ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ'),
            'aa'   => array('ع', 'आ', 'آ'),
            'ae'   => array('ä', 'æ', 'ǽ'),
            'ai'   => array('ऐ'),
            'at'   => array('@'),
            'ch'   => array('ч', 'ჩ', 'ჭ', 'چ'),
            'dj'   => array('ђ', 'đ'),
            'dz'   => array('џ', 'ძ'),
            'ei'   => array('ऍ'),
            'gh'   => array('غ', 'ღ'),
            'ii'   => array('ई'),
            'ij'   => array('ĳ'),
            'kh'   => array('х', 'خ', 'ხ'),
            'lj'   => array('љ'),
            'nj'   => array('њ'),
            'oe'   => array('ö', 'œ', 'ؤ'),
            'oi'   => array('ऑ'),
            'oii'  => array('ऒ'),
            'ps'   => array('ψ'),
            'sh'   => array('ш', 'შ', 'ش'),
            'shch' => array('щ'),
            'ss'   => array('ß'),
            'sx'   => array('ŝ'),
            'th'   => array('þ', 'ϑ', 'ث', 'ذ', 'ظ'),
            'ts'   => array('ц', 'ც', 'წ'),
            'ue'   => array('ü'),
            'uu'   => array('ऊ'),
            'ya'   => array('я'),
            'yu'   => array('ю'),
            'zh'   => array('ж', 'ჟ', 'ژ'),
            '(c)'  => array('©'),
            'A'    => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ'),
            'B'    => array('Б', 'Β', 'ब'),
            'C'    => array('Ç', 'Ć', 'Č', 'Ĉ', 'Ċ'),
            'D'    => array('Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ'),
            'E'    => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə'),
            'F'    => array('Ф', 'Φ'),
            'G'    => array('Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ'),
            'H'    => array('Η', 'Ή', 'Ħ'),
            'I'    => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ'),
            'K'    => array('К', 'Κ'),
            'L'    => array('Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल'),
            'M'    => array('М', 'Μ'),
            'N'    => array('Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν'),
            'O'    => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ'),
            'P'    => array('П', 'Π'),
            'R'    => array('Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ'),
            'S'    => array('Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ'),
            'T'    => array('Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ'),
            'U'    => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'),
            'V'    => array('В'),
            'W'    => array('Ω', 'Ώ', 'Ŵ'),
            'X'    => array('Χ', 'Ξ'),
            'Y'    => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ'),
            'Z'    => array('Ź', 'Ž', 'Ż', 'З', 'Ζ'),
            'AE'   => array('Ä', 'Æ', 'Ǽ'),
            'CH'   => array('Ч'),
            'DJ'   => array('Ђ'),
            'DZ'   => array('Џ'),
            'GX'   => array('Ĝ'),
            'HX'   => array('Ĥ'),
            'IJ'   => array('Ĳ'),
            'JX'   => array('Ĵ'),
            'KH'   => array('Х'),
            'LJ'   => array('Љ'),
            'NJ'   => array('Њ'),
            'OE'   => array('Ö', 'Œ'),
            'PS'   => array('Ψ'),
            'SH'   => array('Ш'),
            'SHCH' => array('Щ'),
            'SS'   => array('ẞ'),
            'TH'   => array('Þ'),
            'TS'   => array('Ц'),
            'UE'   => array('Ü'),
            'YA'   => array('Я'),
            'YU'   => array('Ю'),
            'ZH'   => array('Ж'),
            ' '    => array("\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"),
        );
    }

}
