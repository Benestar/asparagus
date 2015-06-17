<?php

namespace Asparagus\Tests;

use Asparagus\QueryBuilder;

/**
 * @covers Asparagus\QueryBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testConstruct() {
		$queryBuilder = new QueryBuilder();

		$this->assertInstanceOf( 'Asparagus\QueryBuilder', $queryBuilder );
	}

	public function testEmptyQuery() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE {}', $queryBuilder->getSPARQL() );
	}

}
