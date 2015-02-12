<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 12/02/15 - 23:19
 * 
 */

namespace BitPrepared\Tests;

use BitPrepared\Commons\TestEnum;

class CommonsEnumTest extends \PHPUnit_Framework_TestCase {

    public function testBasicEnumCheckValue()
    {
        $this->assertTrue(TestEnum::isValidValue(TestEnum::TEST1),'check valore valido');
        $this->assertTrue(TestEnum::isValidValue(1),'check valore valido');
    }

    public function testBasicEnumCheckValueInvalid()
    {
        $this->assertFalse(TestEnum::isValidValue('3'),'check valore non valido');
    }

    public function testBasicEnumFromValue()
    {
        $r = TestEnum::fromValue(1);
        $this->assertEquals('TEST1',$r,'uguaglianza tra valore ed enum');
    }

    public function testBasicEnumValidName()
    {
        $this->assertTrue(TestEnum::isValidName('TEST1'),'check chiave valida');
    }

    public function testBasicEnumInValidName()
    {
        $this->assertFalse(TestEnum::isValidName('TEST3'),'check chiave non valida');
    }
}