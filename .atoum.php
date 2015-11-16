<?php

use \mageekguy\atoum;

$report = $script->addDefaultReport();

// Please replace in next line "Project Name" by your project name and "/path/to/destination/directory" by your destination directory path for html files.
$coverageField = new atoum\report\fields\runner\coverage\html('Cerbere', 'coverage');

// Please replace in next line http://url/of/web/site by the root url of your code coverage web site.
$coverageField->setRootUrl('http://test.local');

$report->addField($coverageField);
