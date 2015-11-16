<?php

namespace Smalot\Edi\Sef\Format;

use Smalot\Edi\Sef\Sef;

class Table
{
    /**
     * @var Sef
     */
    protected $sef;

    /**
     * @return Sef
     */
    public function getSef()
    {
        return $this->sef;
    }

    /**
     * @param Sef $sef
     */
    public function setSef($sef)
    {
        $this->sef = $sef;
    }

    /**
     *
     */
    public function render()
    {
        $content = '<style type="text/css">
td {text-align: center}
</style>';

        $content .= $this->sef->getResponsibleAgencyLabel() . ' &deg; ' . $this->sef->getFunctionalGroupVersion(
          ) . '<hr/>';
        $content .= '<div style="background-color: #ddd"><small>Implementation Guideline Description</small><br/>';
        $content .= $this->sef->getImplementationDescription() . '</div>';

        foreach ($this->sef->getSetsSection() as $set => $tables) {
            $content .= '<h1 style="border-bottom: 1px solid #ddd">' . $set . '</h1>';

            foreach ($tables as $tableIndex => $table) {
                $content .= '<h3>Table ' . ($tableIndex + 1) . '</h3>';

                $content .= '<table border="1" style="width: 780px">
<tr>
<th>USER<br/>REQ</th>
<th>REQ.<br/>DES.</th>
<th>MAX<br/>USE</th>
<th>POS<br/>NO.</th>
<th>SEGMENT<br/>ID</th>
<th>SEGMENT<br/>NAME</th>
</tr>';

                $increment = 0;
                $position = 0;

                foreach ($table as $segment) {
                    if (isset($segment['type']) && $segment['type'] == 'loop') {
                        $content.= '<tr><td colspan="6">Loop ...</td></tr>';
                    } else {
                        if (!$increment) {
                            $increment = $segment['ordinal'];
                        }

                        $position += $segment['ordinal'];

                        $content .= '<tr>
<td></td>
<td>' . ($segment['required'] ? $segment['required'] : 'O') . '</td>
<td>' . ($segment['maximum'] ? $segment['maximum'] : 1) . '</td>
<td>' . $position . '</td>
<td>' . $segment['segment'] . '</td>
<td></td>
</tr>';
                        if (!$segment['ordinal']) {
                            $position += $increment;
                        }
                    }
                }

                $content .= '</table>';
            }
        }

        return $content;
    }


}
