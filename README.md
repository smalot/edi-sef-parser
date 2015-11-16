# EDI Sef Parser

[![Latest Stable Version](https://poser.pugx.org/smalot/edi-sef-parser/v/stable)](https://packagist.org/packages/smalot/edi-sef-parser)
[![Total Downloads](https://poser.pugx.org/smalot/edi-sef-parser/downloads)](https://packagist.org/packages/smalot/edi-sef-parser)
[![Latest Unstable Version](https://poser.pugx.org/smalot/edi-sef-parser/v/unstable)](https://packagist.org/packages/smalot/edi-sef-parser)
[![License](https://poser.pugx.org/smalot/edi-sef-parser/license)](https://packagist.org/packages/smalot/edi-sef-parser)
[![Build Status](https://travis-ci.org/smalot/edi-sef-parser.svg)](https://travis-ci.org/smalot/edi-sef-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smalot/edi-sef-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smalot/edi-sef-parser/?branch=master)

# Implementation

````php
<?php

include 'vendor/autoload.php';

$content = file_get_contents('810_4050.sef');
$sef = \Smalot\Edi\Sef\Parser::parse($content);

$version = $sef->getVersion();
$ini = $sef->getIniSection();
$description = $ini[5];

````
