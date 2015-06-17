<?php

namespace Asparagus\Tests\Integration;

use Asparagus\QueryBuilder;

/**
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase {

	private static $prefixes = array(
		'test' => 'http://www.example.com/test#'
	);

	public function testBasicFunctionality() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( 'name', 'email' )
			->where( '?person', 'test:name', '?name' )
			->plus( 'test:email', '?email' )
			->limit( 10 );

		$this->assertIsExpected( 'basic_functionality', $queryBuilder->format() );
	}

	private function assertIsExpected( $name, $sparql ) {
		$expected = file_get_contents( __DIR__ . '/../data/builder_' . $name . '.rq' );

		$this->assertEquals( $expected, $sparql, 'Query didn\'t match the expected content of integration_' . $name . '.rq' );
	}

}
