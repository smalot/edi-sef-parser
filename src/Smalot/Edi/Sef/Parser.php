<?php

namespace Smalot\Edi\Sef;

use Smalot\Edi\Sef\Exception\ParseException;

class Parser
{
    /**
     * @var array
     */
    protected $sections;

    /**
     * Parser constructor.
     * @param array $sections
     */
    public function __construct($sections)
    {
        $this->sections = $sections;
    }

    /**
     * @param string $content
     * @param bool $checkIntegrity
     * @return \Smalot\Edi\Sef\Sef
     */
    public static function parse($content, $checkIntegrity = true)
    {
        $sections = self::parseSections($content);
        $parser = new self($sections);

        if ($checkIntegrity) {
            $parser->checkIntegrity();
        }

        $schema = new Sef($parser->extractVersion());
        $schema->setIniSection($parser->extractIniSection());
        $schema->setStdSection($parser->extractStdSection());
        $schema->setSetsSection($parser->extractSetsSection());
        $schema->setSegsSection($parser->extractSegsSection());

        return $schema;
    }

    /**
     * @param string $content
     * @return array
     */
    public static function parseSections($content)
    {
        $lines = preg_split('/[\n|\r]+/', trim($content));
        $sections = array();
        $code = $content = null;

        foreach ($lines as $line) {
            if (preg_match('/^\.[A-Z0-9]+\s*/i', $line)) {
                $parts = preg_split('/[\s]+/', $line, 2);

                if ($code && !is_null($content)) {
                    $sections[] = array(
                      'code'    => $code,
                      'content' => $content,
                    );
                }

                $code = ltrim($parts[0], '.');
                $content = (!empty($parts[1]) ? trim($parts[1]) : '');
            } else {
                $content = ($content ? $content . PHP_EOL . $line : $line);
            }
        }

        if ($code && !is_null($content)) {
            $sections[] = array(
              'code'    => $code,
              'content' => $content,
            );
        }

        return $sections;
    }

    /**
     * @return bool
     */
    public function checkIntegrity()
    {
        $has_version_section = false;

        // Version section (if exists) must be the first one.
        if ($sections = $this->getSections(Sef::SECTION_VERSION)) {
            if (count($sections) != 1) {
                throw new ParseException('Too many "VERSION" sections. Only one allowed.');
            }
            if (key($sections) != 0) {
                throw new ParseException('The "VERSION" section is not at the first position.');
            }

            $has_version_section = true;
        }

        // Ini section (if exists) must be the first one or follow the version section.
        if ($sections = $this->getSections(Sef::SECTION_INI)) {
            if (count($sections) != 1) {
                throw new ParseException('Too many "INI" sections. Only one allowed.');
            }
            if ($has_version_section && key($sections) != 1) {
                throw new ParseException('The "INI" section must follow directly the "VERSION" section.');
            } elseif (!$has_version_section && key($sections) != 0) {
                throw new ParseException('The "INI" section is not at the first position.');
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function extractVersion()
    {
        if ($sections = $this->getSections(Sef::SECTION_VERSION)) {
            $section = current($sections);

            return $section['content'];
        }

        return '1.0';
    }

    /**
     * @return array
     */
    public function extractIniSection()
    {
        if ($sections = $this->getSections(Sef::SECTION_INI)) {
            $section = current($sections);

            return explode(',', $section['content']);
        }

        return array();
    }

    /**
     * @return array
     */
    public function extractStdSection()
    {
        $sections = $this->getSections(Sef::SECTION_STD);
        $section = current($sections);

        return explode(',', trim($section['content'], ','));
    }

    /**
     * @return array
     */
    public function extractSetsSection()
    {
        return $this->extractSimpleSection(Sef::SECTION_SETS, array($this, 'parseTransactionSet'));
    }

    /**
     * @return array
     */
    public function extractSegsSection()
    {
        return $this->extractSimpleSection(Sef::SECTION_SEGS, array($this, 'parseTransactionSegment'));
    }

    /**
     * @param string   $section_name
     * @param callable $callback
     *
     * @return array
     */
    protected function extractSimpleSection($section_name, $callback = null)
    {
        $parts = array();

        foreach ($this->getSections($section_name) as $section) {
            foreach (preg_split('/[\n|\r]+/', $section['content']) as $line) {
                list($code, $elements) = explode('=', $line, 2);

                if (is_callable($callback)) {
                    $parts[trim($code)] = call_user_func_array($callback, array($elements));
                } else {
                    $parts[trim($code)] = $elements;
                }
            }
        }

        return $parts;
    }

    /**
     * @param string|null $code
     * @return array
     */
    public function getSections($code = null)
    {
        if ($code) {
            $sections = array();

            foreach ($this->sections as $position => $section) {
                if ($section['code'] == $code) {
                    $sections[$position] = $section;
                }
            }

            return $sections;
        }

        return $this->sections;
    }

    /**
     * @param array $sections
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
    }

    /**
     * @param string $set
     * @return array
     */
    public function parseTransactionSet($set)
    {
        $tables = array();

        foreach (preg_split('/\^+/', ltrim($set, '^')) as $table) {
            $tables[] = $this->parseTransactionSetSequence($table);
        }

        return $tables;
    }

    /**
     * @param string $text
     * @return array
     */
    public function parseTransactionSegment($text)
    {
        // Todo
        return array('foo');
    }

    /**
     * @param string $text
     * @param int $offset
     * @return array
     */
    public function parseTransactionSetSequence($text, &$offset = 0)
    {
        $sequence = array();

        do {
            $sub_text = substr($text, $offset);
            if (preg_match('/^((\+\d+)?\[[^\]]+\])+/i', $sub_text)) {
                $sequence[] = $this->parseTransactionSetSegment($text, $offset);
            } elseif (preg_match('/^{/', $sub_text)) {
                $sequence[] = $this->parseTransactionSetLoop($text, $offset);
            } else {
                break;
            }
        } while (true);

        if (empty($sequence)) {
            throw new ParseException('No sequence found.');
        }

        return $sequence;
    }

    /**
     * @param string $text
     * @param integer $offset
     * @return array|false
     */
    public function parseTransactionSetSegment($text, &$offset = 0)
    {
        $sub_text = substr($text, $offset);
        $match = array();

        $parts = array(
          'ordinal'     => '(\+(?<ordinal>(\d+)))',
          'condition'   => '(?<condition>([\.!$\-&]))',
          'segment'     => '(?<segment>([A-Z0-9]+))',
          'mask'        => '(\*(?<mask>(\d+)))',
          'ordinal_out' => '(\@(?<ordinal_out>(\d+)))',
          'required'    => '(?<required>([A-Z]))',
          'maximum'     => '(?<maximum>(\>?\d+))',
        );

        $composition = array(
          $parts['ordinal'] . '?',
          '\[',
          $parts['condition'] . '?',
          $parts['segment'],
          $parts['mask'] . '?',
          $parts['ordinal_out'] . '?',
          '(\,' . $parts['required'] . '?' . '(\,' . $parts['maximum'] . '?)?)?',
          '\]',
        );

        $composition = implode('', $composition);

        if (preg_match(
          '/^' . $composition . '/',
          $sub_text,
          $match
        )) {
            $offset += strlen($match[0]);

            return array(
              'ordinal'     => isset($match['ordinal']) ? $match['ordinal'] : '',
              'condition'   => isset($match['condition']) ? $match['condition'] : '',
              'segment'     => $match['segment'],
              'mask'        => isset($match['mask']) ? $match['mask'] : '',
              'ordinal_out' => isset($match['ordinal_out']) ? $match['ordinal_out'] : '',
              'required'    => isset($match['required']) ? $match['required'] : '',
              'maximum'     => isset($match['maximum']) ? $match['maximum'] : '',
            );
        }

        throw new ParseException('Invalid segment syntax.');
    }

    /**
     * @param string $text
     * @param int $offset
     * @return array
     */
    public function parseTransactionSetLoop($text, &$offset = 0)
    {
        $sub_text = substr($text, $offset);
        $match = array();

        if (preg_match('/^{(\d+)?:(\d+|\>1)(\+\d+)?\[/', $sub_text, $match)) {
            $loop_id = $match[1];
            $repeat_count = $match[2];
            $offset += strlen('{' . $match[1] . ':' . $match[2]);
        } elseif (preg_match('/^{/', $sub_text)) {
            $loop_id = '';
            $repeat_count = '';
            $offset += 1;
        } else {
            throw new ParseException('Malformed loop syntax.');
        }

        $loop = array(
          'type'     => 'loop',
          'loop_id'  => $loop_id,
          'maximum'  => $repeat_count,
          'children' => $this->parseTransactionSetSequence($text, $offset),
        );

        if (substr($text, $offset, 1) == '}') {
            $offset++;
        } else {
            throw new ParseException('Malformed loop syntax, missing last bracket.');
        }

        return $loop;
    }
}
