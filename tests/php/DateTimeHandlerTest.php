<?php

namespace Webforge\Symfony;

use Webforge\Symfony\DateTimeHandler;

class DateTimeHandlerTest extends \PHPUnit_Framework_TestCase {

  public function testDateParsing() {
    $value = '2016-03-10T23:00:00.000000+0100';

    $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $dateTime = DateTimeHandler::parse($value));
    $this->assertEquals($value, DateTimeHandler::export($dateTime));
  }
}
