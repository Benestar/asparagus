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
	public function testFormat( $expected, $input ) {
		$formatter = new QueryFormatter();

		$this->assertEquals( $expected, $formatter->format( $input ) );
	}

	public function provideTestFormat() {
		return array(
			'new line before prefix' => array( "PREFIX abc\nPREFIX\n", 'PREFIX abc PREFIX' ),
			'new lines before select' => array( "foobar\n\nSELECT xyz\n", 'foobar SELECT xyz' ),
			'new line after brackets' => array( "abc {\n}\ndef\n", 'abc { } def' ),
			'indentation by brackets' => array( "{\n\t{\n\t\tfoobar .\n\t}\n\tnyan .\n}\n", '{ { foobar . } nyan . }' ),
			'strings get escaped' => array( '"abc { def } hij" <abc { xyy > <"a{2>' . "\n", '"abc { def } hij" <abc { xyy > <"a{2>' ),
			'spaces before characters' => array( "a .\nb =c (d <e {\n\tf ?g \$h\n", 'a.b=c(d<e{f?g$h' )
		);
	}

}
