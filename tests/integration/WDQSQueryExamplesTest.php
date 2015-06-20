<?php

namespace Asparagus\Tests\Integration;

use Asparagus\QueryBuilder;

/**
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class WDQSQueryExamplesTest extends \PHPUnit_Framework_TestCase {

	private static $prefixes = array(
		'wd' => 'http://www.wikidata.org/entity/',
		'wdt' => 'http://www.wikidata.org/prop/direct/',
		'wikibase' => 'http://wikiba.se/ontology#',
		'p' => 'http://www.wikidata.org/prop/',
		'v' => 'http://www.wikidata.org/prop/statement/',
		'q' => 'http://www.wikidata.org/prop/qualifier/',
		'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#'
	);

	public function testUSPresidentsAndSpouses() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?p', '?w', '?l', '?wl' )
			->where( 'wd:Q30', 'p:P6/v:P6', '?p' )
			->where( '?p', 'wdt:P26', '?w' )
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?p', 'rdfs:label', '?l' )
					->filter( 'LANG(?l) = "en"' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?w', 'rdfs:label', '?wl' )
					->filter( 'LANG(?wl) = "en"' )
			);

		$this->assertIsExpected( 'US_presidents_and_spouses', $queryBuilder->format() );
	}

	private function assertIsExpected( $name, $sparql ) {
		$expected = file_get_contents( __DIR__ . '/../data/builder_' . $name . '.rq' );

		$this->assertEquals( $expected, $sparql, 'Query didn\'t match the expected content of integration_' . $name . '.rq' );
	}

}
