<?php

/**
 * @author Neil Bertram <neil@fishy.net.nz>
 * @copyright Copyright(c) 2015 Neil Bertram
 * @link https://github.com/rchouinard/rych-otp
 * @license GPL v3 or later, see LICENSE
 */

namespace NeilNZ\NZBankAccountValidation\Test;

use NeilNZ\NZBankAccountValidation\Validator as Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testShortFormatting()
    {
        $this->assertEquals(Validator::format('12', '3140', '171323', '50', false), '12-3140-00171323-0050');
    }

    public function testLongFormatting()
    {
        $this->assertEquals(Validator::format('12', '3140', '171323', '50', true), '12-3140-00171323-50');
    }

    public function testAlogrithmMappings()
    {
        $this->assertEquals(Validator::getAlgorithm('01', '0902', '00068389', '00'), 'A');
        $this->assertEquals(Validator::getAlgorithm('08', '6523', '01954512', '01'), 'D');
        $this->assertEquals(Validator::getAlgorithm('26', '2600', '00320871', '32'), 'G');
    }

    public function testNumberValidation()
    {
        $this->assertTrue(Validator::validate('12', '3140', '0171323', '50'));
        $this->assertTrue(Validator::validate('06', '0501', '919189', '00'));
        $this->assertTrue(Validator::validate('26', '2600', '0320871', '032'));
        $this->assertTrue(Validator::validate('12', '3141', '325080', '00'));
        $this->assertTrue(Validator::validate('01', '902', '68389', '00'));
        $this->assertTrue(Validator::validate('08', '6523', '1954512', '001'));

        $this->assertFalse(Validator::validate('12', '3140', '0171123', '50'));
        $this->assertFalse(Validator::validate('06', '0502', '0919189', '00'));
        $this->assertFalse(Validator::validate('26', '2600', '0320871', '033'));
    }
}
