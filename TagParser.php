<?php

namespace TagParser;

class TagNotFound extends \Exception {}

abstract class TagParser {
    /* @var $tags array[array] */
    private static $tags = [];

    /**
     * Returns all currently registered tag handler
     *
     * @access public
     * @return array[array]
     */
    public static function getTags()
    {
        return self::$tags;
    }

    /**
     * Overwrites the array with given handlers (maybe from cache?)
     *
     * @access public
     *
     * @param array $array Array containing all handlers
     * @return array[array]
     */
    public static function setTags($array)
    {
        return self::$tags = $array;
    }

    /**
     * Adds a new handler to the list
     *
     * @access public
     *
     * @param array $definition Structure defining the tag (array)
     * @return array
     */
    public static function addTag($definition)
    {
        self::$tags[$definition['Name']] = $definition;

        return $definition;
    }

    /**
     * Processes the given text with its tags
     *
     * @access public
     *
     * @param string $text Text to process
     * @param bool $live Always ask the callback for fresh content
     *
     * @throws TagNotFound
     *
     * @return string
     */
    public static function process($text, $live = true)
    {
        $return    = $text;
        $processed = self::parse($text);

        // From end to start, so our positions don't get invalid
        $processed = array_reverse($processed);

        foreach($processed as $tag) {
            $alltags = self::$tags;

            $name = $tag['Name'];

            if(isset($alltags[$name])) {
                $replacement = $alltags[$name]['Handler'](
                    $tag['Flags'],
                    $tag['Attributes']
                );

                if(!$live) {
                    // Calls the callback one time, and replaces every occurrence
                    $return = str_replace(
                        $tag['Raw'],
                        $replacement,
                        $return
                    );

                    continue;
                }

                // Calls the callback every time, allowing the callback to change the replacement
                // even if the Tag is exactly the same
                $return = substr_replace(
                    $return,
                    $replacement,
                    $tag['Start'],
                    $tag['Length']
                );
            } else {
                throw new TagNotFound('Invalid tag ' . $name . ' found (not a valid tag)', 1);
            }
        }

        return $return;
    }

    /**
     * Parses the text and searches for tags
     *
     * @access public
     *
     * @param string $text Text to parse
     *
     * @return array
     */
    public static function parse($text)
    {
        // Result and regex matches
        $results = [];
        $matches = ['Tags' => [], 'Content'  => [], 'Flags' => [], 'Attributes' => []];

        // First of all, detect any tag
        preg_match_all(
            '/\[(?<Name>[[:alnum:]]+)(|\s(?<Value>.*))\]/m',
            $text,
            $matches['Tags'],
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
            0
        );

        foreach($matches['Tags'] as $tag) {
            $entry = [];

            $entry['Name']       = $tag['Name'][0];
            $entry['Flags']      = [];
            $entry['Attributes'] = [];

            $entry['Raw']        = $tag[0][0];
            $entry['Start']      = $tag[0][1];
            $entry['Length']     = strlen($tag[0][0]);

            // Has the tag parameters? (Flags or Attributes)
            if(isset($tag['Value'][0])) {
                // Get all flags (if there any)
                preg_match_all(
                    '/(|\s)(?<Flag>([[:alnum:]]+((?= )|(?=]))))(|\s)/m',
                    $tag['Value'][0],
                    $matches['Flags'],
                    PREG_SET_ORDER,
                    0
                );

                foreach($matches['Flags'] as $flag)
                    $entry['Flags'][$flag['Flag']] = '';

                // Now get the attributes (if there any)
                preg_match_all(
                    '/(|\s+)(?<Key>\S+)=["\']?(?<Value>(?:.(?!["\']?\s+(?:\S+)=|["\']))+.)["\']?/',
                    $tag['Value'][0],
                    $matches['Attributes'],
                    PREG_SET_ORDER,
                    0
                );

                foreach($matches['Attributes'] as $attribute)
                    $entry['Attributes'][$attribute['Key']] = $attribute['Value'];
            }

            $results[] = $entry;
        }

        return $results;
    }
}