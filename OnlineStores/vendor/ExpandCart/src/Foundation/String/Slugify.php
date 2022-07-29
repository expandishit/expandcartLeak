<?php
namespace ExpandCart\Foundation\String;

class Slugify
{

    /**
     * array of characters to be replaced.
     *
     * @var array
     */
    public $search = [
        ' ',
        '!',
        '#',
        '$',
        '%',
        '^',
        '*',
        '{',
        '}',
        '\'',
        '"',
        '/',
        '?',
        '\\'
    ];

    /**
     * pattern used in replace function.
     *
     * @var string
     */
    public $pattern = '[^\p{Arabic}\w\-]+';

    /**
     * convert string into a slug.
     *
     * @param string $string
     * @param string $separator
     *
     * @return string
     */
    public function slug($string, $separator = '-')
    {
        return str_replace(['؛', '٪', '؟', '٫'], '', mb_strtolower(
            preg_replace('#' . $this->pattern . '#iu', $separator, $this->trim(
                html_entity_decode($string))
            ),'UTF-8'
        ));
    }

    /**
     * trim white spaces from string.
     *
     * @param string $string
     *
     * @return string
     */
    public function trim($string)
    {
        return trim($string);
    }

    /**
     * Convert a given word to a camel case
     *
     * @param string $wordString
     * @param array $delimiters
     *
     * @return string
     */
    public function camelize(string $wordString, $delimiters = []) : string
    {
        $delimiters = $delimiters ?: ['-', ' ', '_'];
        return lcfirst(str_replace(' ', '', ucwords(str_replace($delimiters, ' ', $wordString))));
    }
}
