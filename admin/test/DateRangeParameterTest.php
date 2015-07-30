<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DateRangeParameterTest
 *
 * @author bgarcia
 */
class DateRangeParameterTest extends PHPUnit_Framework_TestCase {


    public function testEncode() {
        $param = new DateRangeParameter(null,null,null);
        $data = array('m0'=>2,'y0'=>1993,'m1'=>7,'y1'=>2015);

        try {
            $result = $param->encode($data);
        } catch (InvalidArgumentException $exception) {
            $this->fail($exception->getMessage());
        } catch (UnexpectedValueException $exception) {
            $this->fail($exception->getMessage());
        }

        $this->assertSame("021993072015",$result);

        return $result;
    }

    /**
     * @depends testEncode
     */
    public function testDecode($data) {
        $param = new DateRangeParameter(null,null,null);

        try {
            $result = $param->decode($data);
        } catch (InvalidArgumentException $exception) {
            $this->fail($exception->getMessage());
        } catch (UnexpectedValueException $exception) {
            $this->fail($exception->getMessage());
        }

        $testData = array('m0'=>2,'y0'=>1993,'m1'=>7,'y1'=>2015);
        $this->assertSame($testData,$result);

        return $result;
    }

    /**
     * @depends testEncode
     * @depends testDecode
     * @expectedException InvalidArgumentException
     * @dataProvider encodeBadParamProvider
     */
    public function testEncodeFailOnBadParam($data) {
        $param = new DateRangeParameter(null,null,null);
        $param->encode($data);
    }

    public function encodeBadParamProvider() {
        return array(
            array(array('m0'=>1,'m1'=>13,'y0'=>2019,'y1'=>'2020')), // invalid month
            array(array('m0'=>1,'m1'=>11,'y0'=>2015,'y1'=>'2000')), // y0 > y1
            array(array('m0'=>1,'m2'=>3)),                          // missing years
            array(array('m0'=>11,'m1'=>11, 'y0'=>20,'y1'=>24))      // years wrong length
            );
    }

    /**
     * @depends testEncode
     * @depends testDecode
     * @expectedException InvalidArgumentException
     * @dataProvider decodeBadParamProvider
     */
    public function testDecodeFailOnBadParam($data) {
        $param = new DateRangeParameter(null,null,null);
        $param->decode($data);
    }

    public function decodeBadParamProvider() {
        return array(
            array('12013122013'),    // wrong length
            array('0120131220130'),  // wrong length
            array(122001112015),     // wrong type
            array('13201312015'),    // invalid month
            array('082012082011'),   // end year comes before start year
            array('092012082012'),   // end month comes before start month in same year
            );
    }
}
