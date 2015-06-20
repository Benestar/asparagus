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
		'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
		'xsd' => 'http://www.w3.org/2001/XMLSchema#'
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

	public function testPresidentsAndCausesOfDeath() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?h', '?cause', '?hl', '?causel' )
			->where( '?h', 'wdt:P39', 'wd:Q11696' )
			->also( 'wdt:P509', '?cause' )
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?h', 'rdfs:label', '?hl' )
					->filter( 'LANG(?hl) = "en"' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?cause', 'rdfs:label', '?causel' )
					->filter( 'LANG(?causel) = "en"' )
			);

		$this->assertIsExpected( 'Presidents_and_causes_of_death', $queryBuilder->format() );
	}

	public function testPeopleBornBeforeYear1880WithNoDeathDate() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?h', '?date' )
			->where( '?h', 'wdt:P31', 'wd:Q5' )
			->also( 'wdt:P569', '?date' )
			->optional( '?h', 'wdt:P570', '?d' )
			->filter( '?date < "1880-01-01T00:00:00Z"^^xsd:dateTime' )
			->filter( '!BOUND(?d)' )
			->limit( 100 );

		$this->assertIsExpected( 'People_born_before_year_1880_with_no_death_date', $queryBuilder->format() );
	}

	public function testLargestCitiesWithFemaleMayor() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->selectDistinct( '?city', '?citylabel', '?mayorlabel' )
			->where( '?city', 'wdt:P31/wdt:P279*', 'wd:Q515' )
			->also( 'p:P6', '?statement' )
			->also( 'wdt:P1082', '?population' )
			->where( '?statement', 'v:P6', '?mayor' )
			->where( '?mayor', 'wdt:P21', 'wd:Q6581072' )
			->filterNotExists(
				$queryBuilder->newSubgraph()
					->where( '?statement', 'q:P582', '?x' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?city', 'rdfs:label', '?citylabel' )
					->filter( 'LANG(?citylabel) = "en"' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?mayor', 'rdfs:label', '?mayorlabel' )
					->filter( 'LANG(?mayorlabel) = "en"' )
			)
			->orderBy( '?population', 'DESC' )
			->limit( 10 );

		$this->assertIsExpected( 'Largest_cities_with_female_mayor', $queryBuilder->format() );
	}

	public function testListOfCountriesOrderedByTheNumberOfTheirCitiesWithFemaleMayor() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?country', '?label', '(COUNT(*) AS ?COUNT)' )
			->where( '?city', 'wdt:P31/wdt:P279*', 'wd:Q515' )
			->also( 'p:P6', '?statement' )
			->also( 'wdt:P17', '?country' )
			->where( '?statement', 'v:P6', '?mayor' )
			->where( '?mayor', 'wdt:P21', 'wd:Q6581072' )
			->filterNotExists(
				$queryBuilder->newSubgraph()
					->where( '?statement', 'q:P582', '?x' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?country', 'rdfs:label', '?label' )
					->filter( 'LANG(?label) = "en"' )
			)
			->groupBy( '?country', '?label' )
			->orderBy( '?COUNT', 'DESC' )
			->limit( 100 );

		$this->assertIsExpected( 'List_of_countries_ordered_by_the_number_of_their_cities_with_female_mayor', $queryBuilder->format() );
	}

	private function assertIsExpected( $name, $sparql ) {
		$expected = file_get_contents( __DIR__ . '/../data/builder_' . $name . '.rq' );

		$this->assertEquals( $expected, $sparql, 'Query didn\'t match the expected content of integration_' . $name . '.rq' );
	}

}
