<?php

namespace Asparagus\Tests;

use Asparagus\GraphBuilder;
use Asparagus\QueryBuilder;
use Asparagus\UsageValidator;

/**
 * @covers Asparagus\GraphBuilder
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class GraphBuilderTest extends \PHPUnit_Framework_TestCase {

	public function testWhere() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->where( '?a', '?b', '?c' )
		);

		$this->assertEquals( ' ?a ?b ?c .', $graphBuilder->getSPARQL() );
	}

	public function testWhere_invalidSubject() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( null, '?b', '?c' );
	}

	public function testWhere_invalidPredicate() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( '?a', null, '?c' );
	}

	public function testWhere_invalidObject() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->where( '?a', '?b', null );
	}

	public function testAlso_knownSubject() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->also( '?x', '?y' )
		);

		$this->assertEquals( ' ?a ?b ?c ; ?x ?y .', $graphBuilder->getSPARQL() );
	}

	public function testAlso_knownPredicate() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->also( '?x' )
		);

		$this->assertEquals( ' ?a ?b ?c , ?x .', $graphBuilder->getSPARQL() );
	}

	public function testAlso_unknownSubject() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->also( '?x', '?y' );
	}

	public function testAlso_unknownPredicate() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->also( '?y' );
	}

	public function testFilter() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->filter( 'AVG (?x) > 5' )
		);

		$this->assertEquals( ' FILTER (AVG (?x) > 5)', $graphBuilder->getSPARQL() );
	}

	public function testFilter_invalidExpression() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->setExpectedException( 'InvalidArgumentException' );

		$graphBuilder->filter( 'FooBar' );
	}

	public function testFilterExists() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->filterExists( $graphBuilder )
		);

		$this->assertEquals( ' ?a ?b ?c . FILTER EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testFilterExists_triple() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->filterExists( '?a', '?b', '?c' )
		);

		$this->assertEquals( ' FILTER EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testFilterNotExists() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->filterNotExists( $graphBuilder )
		);

		$this->assertEquals( ' ?a ?b ?c . FILTER NOT EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testFilterNotExists_triple() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->filterNotExists( '?a', '?b', '?c' )
		);

		$this->assertEquals( ' FILTER NOT EXISTS { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testOptional() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->optional( $graphBuilder )
		);

		$this->assertEquals( ' ?a ?b ?c . OPTIONAL { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testOptional_triple() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->optional( '?a', '?b', '?c' )
		);

		$this->assertEquals( ' OPTIONAL { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testUnion() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->union( $graphBuilder, $graphBuilder )
		);

		$this->assertEquals( ' ?a ?b ?c . { ?a ?b ?c . } UNION { ?a ?b ?c . }', $graphBuilder->getSPARQL() );
	}

	public function testSubquery() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$queryBuilder = new QueryBuilder();
		$queryBuilder->where( '?a', '?b', '?c' );
		$this->assertSame(
			$graphBuilder,
			$graphBuilder->subquery( $queryBuilder )
		);

		$this->assertEquals( ' { SELECT * WHERE { ?a ?b ?c . } }', $graphBuilder->getSPARQL() );
	}

	public function testGetSPARQL() {
		$graphBuilder = new GraphBuilder( new UsageValidator() );
		$graphBuilder->where( '?a', '?b', '?c' );
		$graphBuilder->also( '?x', '?y' );
		$graphBuilder->also( '?z' );
		$graphBuilder->where( '?a', '?b', '?z' );

		$this->assertEquals( ' ?a ?b ?c , ?z ; ?x ?y , ?z .', $graphBuilder->getSPARQL() );
	}

}
