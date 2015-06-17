<?php

namespace Asparagus\Tests;

use Asparagus\QueryFormatter;

/**
 * @covers Asparagus\QueryFormatter
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryFormatterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provideTestFormat
	 */
	public function testFormat( $input, $output ) {
		$formatter = new QueryFormatter();
		$sparql = file_get_contents( __DIR__ . '/../data/unit_' . $input . '.rq' );
		$expected = file_get_contents( __DIR__ . '/../data/unit_' . $output . '.rq' );

		$this->assertEquals( $expected, $formatter->format( $sparql ) );
	}

	public function provideTestFormat() {
		return array(
			array( 'in1', 'out1' )
		);
	}

}
