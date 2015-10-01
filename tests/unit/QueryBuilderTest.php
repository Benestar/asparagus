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

	public function testSelect() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->select( '?a', '?b' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals(
			'SELECT ?a ?b WHERE { ?a ?b ?c . }',
			$queryBuilder->getSPARQL()
		);
	}

	public function testSelect_invalidArgument() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'InvalidArgumentException' );

		$queryBuilder->select( '?a', false );
	}

	public function testSelectDistinct() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->selectDistinct( '?a', '?b' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals(
			'SELECT DISTINCT ?a ?b WHERE { ?a ?b ?c . }',
			$queryBuilder->getSPARQL()
		);
	}

	public function testSelectReduced() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->selectReduced( '?a', '?b' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals(
			'SELECT REDUCED ?a ?b WHERE { ?a ?b ?c . }',
			$queryBuilder->getSPARQL()
		);
	}

	public function testDescribe() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->describe( '?a', '?b', '<foo:bar>' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->assertEquals(
			'DESCRIBE ?a ?b <foo:bar> WHERE { ?a ?b ?c . }',
			$queryBuilder->getSPARQL()
		);
	}

	public function testDescribe_undefinedPrefix() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->describe( 'foo:bar' )
		);

		// use variables ?a and ?b
		$queryBuilder->where( '?a', '?b', '?c' );

		$this->setExpectedException( 'RangeException' );
		$queryBuilder->getSPARQL();
	}

	public function testDescribe_queryFormAlreadySet() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->select( '?a', '?b' )
		);

		$this->setExpectedException( 'RuntimeException' );
		$queryBuilder->describe( 'foo:bar' );
	}

	public function testWhere() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->where( '?a', '?b', '?c' )
		);
	}

	public function testAlso() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->also( '?a', '?b', '?c' )
		);
	}

	public function testAlso_knownSubject() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->also( '?x', '?y' )
		);
	}

	public function testAlso_knownPredicate() {
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->also( '?z' )
		);
	}

	public function testFilter() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filter( 'AVG (?x) > 9' )
		);
	}

	public function testFilterExists() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filterExists( $queryBuilder->newSubgraph() )
		);
	}

	public function testFilterNotExists() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->filterNotExists( $queryBuilder->newSubgraph() )
		);
	}

	public function testOptional() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->optional( '?a', '?b', '?c' )
		);
	}

	public function testUnion() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->union( $queryBuilder->newSubgraph() )
		);
	}

	public function testSubquery() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->subquery( $queryBuilder->newSubquery() )
		);
	}

	public function testNewSubquery() {
		$queryBuilder = new QueryBuilder( array( 'a' => 'b' ) );
		$this->assertEquals(
			new QueryBuilder( array( 'a' => 'b' ) ),
			$queryBuilder->newSubquery()
		);
	}

	public function testNewSubgraph() {
		$queryBuilder = new QueryBuilder();
		$this->assertInstanceOf( 'Asparagus\GraphBuilder', $queryBuilder->newSubgraph() );
		$this->assertNotSame( $queryBuilder->newSubgraph(), $queryBuilder->newSubgraph() );
	}

	public function testGroupBy() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->groupBy( '?test' )
		);
	}

	public function testHaving() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->having( 'AVG(?size) > 10' )
		);
	}

	public function testOrderBy() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->orderBy( '?test' )
		);
	}

	public function testLimit() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->limit( 5 )
		);
	}

	public function testOffset() {
		$queryBuilder = new QueryBuilder();
		$this->assertSame(
			$queryBuilder,
			$queryBuilder->offset( 5 )
		);
	}

	public function testGetSPARQL() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE { }', $queryBuilder->getSPARQL() );
	}

	public function testGetSPARQL_withPrefixes() {
		$queryBuilder = new QueryBuilder( array( 'foo' => 'bar' ) );

		$this->assertEquals( 'PREFIX foo: <bar> SELECT * WHERE { }', $queryBuilder->getSPARQL() );
	}

	public function testGetSPARQL_noPrefixes() {
		$queryBuilder = new QueryBuilder( array( 'foo' => 'bar' ) );

		$this->assertEquals( 'SELECT * WHERE { }', $queryBuilder->getSPARQL( false ) );
	}

	public function testGetSPARQL_undefinedVariable() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'RangeException', '?x' );

		$queryBuilder->select( '?x' )->getSPARQL();
	}

	public function testGetSPARQL_undefinedPrefix() {
		$queryBuilder = new QueryBuilder();
		$this->setExpectedException( 'RangeException', 'foo, nyan' );

		$queryBuilder->where( '?x', 'foo:bar', 'nyan:data' )->getSPARQL();
	}

	public function testToString() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( 'SELECT * WHERE { }', strval( $queryBuilder ) );
	}

	public function testFormat() {
		$queryBuilder = new QueryBuilder();

		$this->assertEquals( "SELECT * WHERE {\n}\n", $queryBuilder->format() );
	}

}
