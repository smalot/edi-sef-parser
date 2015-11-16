<?php

/**
 *
 *
 *
 *
 *
 * Some comments on this page are under this copyright:
 *
 * Copyright 2000 Foresight Corp., Dublin, Ohio. All rights reserved. The SEF format can be used without permission
 * as described in this document. Any reproduction of this publication, in whole or in part, by any means, electronic
 * or mechanical, including photocopying, recording, or in use by software must preserve this copyright notice.
 */

namespace Smalot\Edi\Sef;

/**
 * Class Sef
 * @package Smalot\Edi\Sef
 */
class Sef
{
    const SECTION_VERSION = 'VER';

    const SECTION_INI = 'INI';

    const SECTION_PRIVATE = 'PRIVATE';

    const SECTION_PUBLIC = 'PUBLIC';

    const SECTION_STD = 'STD';

    const SECTION_SETS = 'SETS';

    const SECTION_SEGS = 'SEGS';

    const COUNT_UNLIMITED = '>1';

    /**
     * @var string
     */
    protected $version;

    /**
     * @var array
     */
    protected $ini_section;

    /**
     * @var array
     */
    protected $private_sections;

    /**
     * @var array
     */
    protected $std_section;

    /**
     * @var array
     */
    protected $sets_section;

    /**
     * @var array
     */
    protected $segs_section;

    /**
     * Sef constructor.
     */
    public function __construct($version = '1.0')
    {
        $this->version = $version;
    }

    /**
     * If the .VER section is included, it must be the first record
     * in the SEF file. If omitted, version 1.0 is used by default.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = (string) $version;
    }

    /**
     * The .INI section immediately follows the .VER section. If .VER is omitted, then the .INI section must be the
     * first two lines in the SEF file. The following is the .INI section for our example local standard INVPO.
     *
     * .INI
     * INVPO,,003 040,X,X12-3040,PO and INV for Slippers 'n Socks, Inc.
     *   ↑  ↑     ↑   ↑    ↑               ↑
     *   1  2     3   4    5               6
     * A comma separates each field. The numbers marking the fields in the example above are, in order:
     *
     * 1. The standard or implementation name (INVPO in the example above), generally the same as the filename of the
     *    SEF file. It should be 1 to 8 characters that would be valid as an MS-DOS file name.
     * 2. Reserved unused field that should be ignored.
     * 3. The Functional Group Version, Release and Industry code which will identify the standard in any Functional
     *    Group Envelope Header Segment. Each code is separated by a space. In the example, there is no industry code.
     *    With an industry code, this field might contain: 003 030 UCS .
     * 4. The responsible agency code, which identifies the standards organization in the Functional Group Header:
     *    GC   GENCOD
     *    T    for T.D.C.C. (EDIA)
     *    TD   TRADACOMS
     *    UN   for UN/EDIFACT
     *    X    for ASC X12 (DISA)
     * 5. The standard on which this implementation guidelines is based.
     * 6. The description (title) of the implementation guideline.
     */
    public function setIniSection()
    {
        $args = func_get_args();
        $section = is_array($args[0]) ? $args[0] : $args;
        $this->ini_section = array_slice($section, 0, 6);
        $this->ini_section += array('', '', '', '', '', '');
    }

    /**
     * @return array
     */
    public function getIniSection()
    {
        return $this->ini_section;
    }

    /**
     * PRIVATE is a place for companies or individuals to store information that they need to augment information in the
     * SEF file. It should be ignored by everyone else. The first entry in .PRIVATE is usually a company name.
     * Subsequent lines contain whatever information is needed.
     *
     * The .PUBLIC record ends the .PRIVATE section and restarts the public part of the SEF file.
     * Multiple .PRIVATE sections are allowed. Terminate each one with either another .PRIVATE or with a .PUBLIC.
     *
     * @return array
     */
    public function getPrivateSections()
    {
        return $this->private_sections;
    }

    /**
     * @param array $private_sections
     */
    public function setPrivateSections($private_sections)
    {
        $this->private_sections = $private_sections;
    }

    /**
     * @param array $private_section
     */
    public function addPrivateSection($private_section)
    {
        $this->private_sections[] = $private_section;
    }

    /**
     * @return array
     */
    public function getStdSection()
    {
        return $this->std_section;
    }

    /**
     * @param array $std_section
     */
    public function setStdSection($std_section)
    {
        $this->std_section = $std_section;
    }

    /**
     * @return array
     */
    public function getSetsSection()
    {
        return $this->sets_section;
    }

    /**
     * @param array $sets_section
     */
    public function setSetsSection($sets_section)
    {
        $this->sets_section = $sets_section;
    }

    /**
     * @return array
     */
    public function getSegsSection()
    {
        return $this->segs_section;
    }

    /**
     * @param array $segs_section
     */
    public function setSegsSection($segs_section)
    {
        $this->segs_section = $segs_section;
    }
}
