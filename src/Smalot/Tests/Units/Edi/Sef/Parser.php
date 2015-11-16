<?php

namespace Smalot\Tests\Units\Edi\Sef;

use mageekguy\atoum;
use Smalot\Edi\Sef\Sef;

/**
 * Class Parser
 * @package Smalot\Tests\Units\Edi\Sef
 */
class Parser extends atoum\test
{
    public function testExtractSegsSection()
    {
        $data = array(
          array(
            'code'    => Sef::SECTION_SEGS,
            'content' => 'AAA=[1073,M][559][901][889]
ACD=[1636][650][1262]
ACK=[668,M][380][355][374][373][326]{10[235][234]}[559][822][1271]+P0203C0405P0708P0910P1112P1314P1516P1718P1920P2122P2324P2526P2728C282729
ACS=[610,M][150,M][352][146]
ADJ=[952,M][782,M][782]{2[373,M]}[1470][352][235][234]{3[610]}{3[380]}[128][127]+P0809L101112C1009L111012C1109L121011C1209C1309L131415C1409L141315C1509L151314P1617C1709',
          ),
        );
        $parser = new \Smalot\Edi\Sef\Parser($data);
        $segs = $parser->extractSegsSection();
        $this->array($segs)->hasSize(5);
    }

    public function testParseTransactionSet()
    {
        $parser = new \Smalot\Edi\Sef\Parser(array());

        $text = '^[BIG,N,]{:5[BTN]}^{:5[BTN]}[SMALL,,>1]^+5[BTN]';
        $offset = 0;
        $set = $parser->parseTransactionSet($text);
        $expected = array(
          array(
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'BIG',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => 'N',
              'maximum'     => '',
            ),
            array(
              'type'     => 'loop',
              'loop_id'  => '',
              'maximum'  => '5',
              'children' => array(
                array(
                  'ordinal'     => '',
                  'condition'   => '',
                  'segment'     => 'BTN',
                  'mask'        => '',
                  'ordinal_out' => '',
                  'required'    => '',
                  'maximum'     => '',
                ),
              ),
            ),
          ),
          array(
            array(
              'type'     => 'loop',
              'loop_id'  => '',
              'maximum'  => '5',
              'children' => array(
                array(
                  'ordinal'     => '',
                  'condition'   => '',
                  'segment'     => 'BTN',
                  'mask'        => '',
                  'ordinal_out' => '',
                  'required'    => '',
                  'maximum'     => '',
                ),
              ),
            ),
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'SMALL',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '>1',
            ),
          ),
          array(
            array(
              'ordinal'     => '5',
              'condition'   => '',
              'segment'     => 'BTN',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
          ),
        );
        $this->array($set);
        $this->string(serialize($set))->isEqualTo(serialize($expected));
    }

    public function testParseTransactionSetLoop()
    {
        $parser = new \Smalot\Edi\Sef\Parser(array());

        $text = '{[BIG][TIG]}';
        $offset = 0;
        $loop = $parser->parseTransactionSetLoop($text, $offset);
        $expected = array(
          'type'     => 'loop',
          'loop_id'  => '',
          'maximum'  => '',
          'children' => array(
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'BIG',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'TIG',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(12);

        $text = '{:100[BIG]}';
        $offset = 0;
        $loop = $parser->parseTransactionSetLoop($text, $offset);
        $expected = array(
          'type'     => 'loop',
          'loop_id'  => '',
          'maximum'  => '100',
          'children' => array(
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'BIG',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(11);

        $text = '{:100[BIG]+5[SMA]{:5[STN][TST]}}';
        $offset = 0;
        $loop = $parser->parseTransactionSetLoop($text, $offset);
        $expected = array(
          'type'     => 'loop',
          'loop_id'  => '',
          'maximum'  => '100',
          'children' => array(
            array(
              'ordinal'     => '',
              'condition'   => '',
              'segment'     => 'BIG',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
            array(
              'ordinal'     => '5',
              'condition'   => '',
              'segment'     => 'SMA',
              'mask'        => '',
              'ordinal_out' => '',
              'required'    => '',
              'maximum'     => '',
            ),
            array(
              'type'     => 'loop',
              'loop_id'  => '',
              'maximum'  => '5',
              'children' => array(
                array(
                  'ordinal'     => '',
                  'condition'   => '',
                  'segment'     => 'STN',
                  'mask'        => '',
                  'ordinal_out' => '',
                  'required'    => '',
                  'maximum'     => '',
                ),
                array(
                  'ordinal'     => '',
                  'condition'   => '',
                  'segment'     => 'TST',
                  'mask'        => '',
                  'ordinal_out' => '',
                  'required'    => '',
                  'maximum'     => '',
                ),
              ),
            ),
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(32);

        $this->exception(
          function () use ($parser) {
              $parser->parseTransactionSetLoop('FOO');
          }
        )->message->isEqualTo('Malformed loop syntax.');

        $this->exception(
          function () use ($parser) {
              $parser->parseTransactionSetLoop('[BIG]');
          }
        )->message->isEqualTo('Malformed loop syntax.');

        $this->exception(
          function () use ($parser) {
              $parser->parseTransactionSetLoop('{}');
          }
        )->message->isEqualTo('No sequence found.');

        $this->exception(
          function () use ($parser) {
              $parser->parseTransactionSetLoop('{FOO}');
          }
        )->message->isEqualTo('No sequence found.');

        $this->exception(
          function () use ($parser) {
              $parser->parseTransactionSetLoop('{[BIG]');
          }
        )->message->isEqualTo('Malformed loop syntax, missing last bracket.');
    }

    public function testParseTransactionSetSegment()
    {
        $parser = new \Smalot\Edi\Sef\Parser(array());
        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[BIG]additionnal text', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(5);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[810]', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => '810',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(5);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[BIG,N]', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => 'N',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(7);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[BIG,N,]', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => 'N',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(8);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[BIG,,]', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(7);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('[810,,5]', $offset);
        $expected = array(
          'ordinal'     => '',
          'condition'   => '',
          'segment'     => '810',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '5',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(8);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[BIG]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(9);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+2[BIG,O,5]', $offset);
        $expected = array(
          'ordinal'     => '2',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => 'O',
          'maximum'     => '5',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(11);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[BIG,O,>1]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => 'O',
          'maximum'     => '>1',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(14);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[BIG@2,O,>1]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '2',
          'required'    => 'O',
          'maximum'     => '>1',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(16);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[BIG*1,O,>1]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '1',
          'ordinal_out' => '',
          'required'    => 'O',
          'maximum'     => '>1',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(16);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[BIG*1@2,O,>1]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '',
          'segment'     => 'BIG',
          'mask'        => '1',
          'ordinal_out' => '2',
          'required'    => 'O',
          'maximum'     => '>1',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(18);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[.BIG]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '.',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(10);

        $offset = 0;
        $segment = $parser->parseTransactionSetSegment('+200[!BIG]', $offset);
        $expected = array(
          'ordinal'     => '200',
          'condition'   => '!',
          'segment'     => 'BIG',
          'mask'        => '',
          'ordinal_out' => '',
          'required'    => '',
          'maximum'     => '',
        );
        $this->array($segment)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(10);
    }

    public function testParseTransactionSetSegmentError()
    {
        $parser = new \Smalot\Edi\Sef\Parser(array());

        $invalidSegments = array(
          '[]',
          '+[BIG]',
          '[%BIG]',
          '+A[BIG]',
          '[BIG',
          '[.]',
          '[.@]',
          '[BIG@2*,O,>1]',
          '+200[BIG@2*1,O,>]',
          '+200[BIG@2*1,O,>1]',
        );

        foreach ($invalidSegments as $invalidSegment) {
            $this->exception(
              function () use ($parser, $invalidSegment) {
                  $parser->parseTransactionSetSegment($invalidSegment);
              }
            )->message->contains('Invalid');
        }
    }

    public function testParseTransactionSetSequence()
    {
        $parser = new \Smalot\Edi\Sef\Parser(array());

        $text = '[BIG,N,][SMALL,,>1]';
        $offset = 0;
        $loop = $parser->parseTransactionSetSequence($text, $offset);
        $expected = array(
          array(
            'ordinal'     => '',
            'condition'   => '',
            'segment'     => 'BIG',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => 'N',
            'maximum'     => '',
          ),
          array(
            'ordinal'     => '',
            'condition'   => '',
            'segment'     => 'SMALL',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => '',
            'maximum'     => '>1',
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(19);

        $text = '+10[BIG,N,]+50[SMALL,,>1]';
        $offset = 0;
        $loop = $parser->parseTransactionSetSequence($text, $offset);
        $expected = array(
          array(
            'ordinal'     => '10',
            'condition'   => '',
            'segment'     => 'BIG',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => 'N',
            'maximum'     => '',
          ),
          array(
            'ordinal'     => '50',
            'condition'   => '',
            'segment'     => 'SMALL',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => '',
            'maximum'     => '>1',
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(25);

        $text = '+10[BIG,N,]{:5[BTN]}[FOO]';
        $offset = 0;
        $loop = $parser->parseTransactionSetSequence($text, $offset);
        $expected = array(
          array(
            'ordinal'     => '10',
            'condition'   => '',
            'segment'     => 'BIG',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => 'N',
            'maximum'     => '',
          ),
          array(
            'type'     => 'loop',
            'loop_id'  => '',
            'maximum'  => '5',
            'children' => array(
              array(
                'ordinal'     => '',
                'condition'   => '',
                'segment'     => 'BTN',
                'mask'        => '',
                'ordinal_out' => '',
                'required'    => '',
                'maximum'     => '',
              ),
            ),
          ),
          array(
            'ordinal'     => '',
            'condition'   => '',
            'segment'     => 'FOO',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => '',
            'maximum'     => '',
          ),
        );
        $this->array($loop)->isEqualTo($expected);
        $this->integer($offset)->isEqualTo(25);
    }

    public function testRealParsingSet()
    {
        $set = '^+100[ST,M][BEG,M]+200[CUR]+100[REF,,>1][PER,,3][TAX,,>1][FOB,,>1][CTP,,>1]+50[PAM,,10]+150[CSH,,5]+50[TC2,,>1]{:25[SAC][CUR]}[ITD,,>1]+100[DIS,,20]+50[INC][DTM,,10]+300[LIN,,5]+50[SI,,>1][PID,,200]+100[MEA,,40][PWK,,25][PKG,,200][TD1,,2][TD5,,>1][TD3,,12][TD4,,5][MAN,,10]+60[PCT,,>1]+40[CTB,,5]+50[TXI,,>1]{:>1+5[LDT]+3[QTY,,>1]+2[MTX,,>1]+5[REF,,>1]}{:>1[AMT]+20[REF,,>1]+10[DTM]+20[PCT,,>1]{:>1+10[FA1][FA2,M,>1]}}{:1000[N9]+20[DTM,,>1]+30[MTX,,>1]+50[PWK,,>1]+30[EFI,,>1]}{:200+20[N1]+100[N2,,2]+50[IN2,,>1][N3,,2]+100[N4,,>1]+50[NX2,,>1][REF,,12]+100[PER,,>1]+50[SI,,>1][FOB]+100[TD1,,2][TD5,,12][TD3,,12][TD4,,5][PKG,,200]}{:>1[LM][LQ,M,>1]}{:>1[SPI][REF,,5][DTM,,5][MTX,,>1]{:20[N1][N2,,2][N3,,2][N4][REF,,20][G61][MTX,,>1]}{:>1[CB1][REF,,20][DTM,,5][LDT][MTX,,>1]}}{:>1[ADV][DTM,,>1][MTX,,>1]}^{:100000+100[PO1,M]+50[LIN,,>1]+30[SI,,>1]+20[CUR]+50[CN1][PO3,,25]{:>1+100[CTP]+30[CUR]}+20[PAM,,10]+40[MEA,,40]{:1000+10[PID]+100[MEA,,10]}[PWK,,25]+200[PO4,,>1]+100[REF,,>1][PER,,3]{:25+200[SAC]+50[CUR]+20[CTP]}+30[IT8]+20[CSH,,>1]+80[ITD,,2]+100[DIS,,20]+50[INC][TAX,,>1]+100[FOB,,>1][SDQ,,500][IT3,,5][DTM,,10]+250[TC2,,>1]+50[TD1]+100[TD5,,12][TD3,,12][TD4,,5]+60[PCT,,>1]+40[MAN,,10]+90[MTX,,>1]+10[SPI,,>1][TXI,,>1][CTB,,>1]{:>1[QTY][SI,,>1]}{:200[SCH][TD1,,2][TD5,,12][TD3,,12][TD4,,5][REF,,>1]}{:200+50[PKG][MEA,,>1]}+100[LS]{:>1+10[LDT][QTY,,>1][MTX,,>1][REF,,3]{:>1[LM][LQ,M,>1]}}[LE]{:1000+30[N9]+20[DTM,,>1]+30[MEA,,40]+50[MTX,,>1][PWK,,>1]+30[EFI,,>1]}{:200+20[N1]+100[N2,,2]+50[IN2,,>1][N3,,2]+100[N4]+30[QTY,,>1]+20[NX2,,>1]+50[REF,,12]+100[PER,,3]+50[SI,,>1]+10[DTM]+40[FOB]+50[SCH,,200][TD1,,2]+100[TD5,,12][TD3,,12][TD4,,5][PKG,,200]{:>1+20[LDT][MAN,,10][QTY,,5][MTX,,>1]+10[REF,,3]}}{:1000[SLN]+50[MTX,,>1][SI,,>1]+100[PID,,1000][PO3,,104]+50[TC2,,>1]+80[ADV,,>1]+20[DTM,,10]+10[CTP,,25][PAM,,10][PO4][TAX,,3]{:>1+40[N9]+10[DTM,,>1][MTX,,>1]}{:25[SAC][CUR][CTP]}{:>1[QTY][SI,,>1]}{:10+50[N1][N2,,2][IN2,,>1][N3,,2]+100[N4][NX2,,>1][REF,,12][PER,,3]+50[SI,,>1]}}{:>1[AMT]+100[REF]+20[PCT,,>1]}{:>1+80[LM]+100[LQ,M,>1]}}^{:1+100[CTT][AMT]}[SE,M]';

        $parser = new \Smalot\Edi\Sef\Parser(array());
        $result = $parser->parseTransactionSet($set);

        $this->array($result)->hasSize(3);
        $this->array($result[0][0])->isEqualTo(
          array(
            'ordinal'     => '100',
            'condition'   => '',
            'segment'     => 'ST',
            'mask'        => '',
            'ordinal_out' => '',
            'required'    => 'M',
            'maximum'     => '',
          )
        );
        $this->array($result[2])->hasSize(2);
    }
}
