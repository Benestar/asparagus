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
			->filterNotExists( '?statement', 'q:P582', '?x' )
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
			->filterNotExists( '?statement', 'q:P582', '?x' )
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

	public function testsHowManyStatesThisUSStateBorders() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?state', '?stateL', '?borders' )
			->subquery(
				$queryBuilder->newSubquery()
					->select( '?state', '(COUNT (?otherState) AS ?borders)' )
					->where( '?state', 'wdt:P31', 'wd:Q35657' )
					->where( '?otherState', 'wdt:P47', '?state' )
					->also( 'wdt:P31', 'wd:Q35657' )
					->groupBy( '?state' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?state', 'rdfs:label', '?stateL' )
					->filter( 'LANG(?stateL) = "en"' )
			)
			->orderBy( '?borders', 'DESC' );

		$this->assertIsExpected( 'How_many_states_this_US_state_borders', $queryBuilder->format() );
	}

	public function testWhoseBirthdayIsToday() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?entity', '(YEAR(?date) AS ?YEAR)' )
			->where( '?entityS', 'wdt:P569', '?date' )
			->also( 'rdfs:label', '?entity' )
			->filter( 'DATATYPE (?date) = xsd:dateTime' )
			->filter( 'MONTH (?date) = MONTH (NOW ())' )
			->filter( 'DAY (?date) = DAY (NOW ())' )
			->limit( 10 );

		$this->assertIsExpected( 'Whose_birthday_is_today', $queryBuilder->format() );
	}

	public function testWhoDiscoveredTheMostAsteroids() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?discoverer', '?name', '(COUNT (?asteroid) AS ?count)' )
			->where( '?asteroid', 'wdt:P31', 'wd:Q3863' )
			->also( 'wdt:P61', '?discoverer' )
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?discoverer', 'rdfs:label', '?name' )
					->filter( 'LANG(?name) = "en"' )
			)
			->groupBy( '?discoverer', '?name' )
			->orderBy( '?count', 'DESC' )
			->limit( 10 );

		$this->assertIsExpected( 'Who_discovered_the_most_asteroids', $queryBuilder->format() );
	}

	public function testWhoDiscoveredTheMostPlanets() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?discoverer', '?name', '(COUNT (DISTINCT ?planet) AS ?count)' )
			->where( '?ppart', 'wdt:P279*', 'wd:Q634' )
			->where( '?planet', 'wdt:P31', '?ppart' )
			->also( 'wdt:P61', '?discoverer' )
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?discoverer', 'rdfs:label', '?name' )
					->filter( 'LANG(?name) = "en"' )
			)
			->groupBy( '?discoverer', '?name' )
			->orderBy( '?count', 'DESC' )
			->limit( 10 );

		$this->assertIsExpected( 'Who_discovered_the_most_planets', $queryBuilder->format() );
	}

	public function testAmericanUniversitiesFoundedBeforeTheStatesTheyResideInWereCreated() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?uniName', '?founded', '?stateName', '?stateStart' )
			->where( '?uni', 'wdt:P31|wdt:P279/wdt:P31', 'wd:Q3918' )
			->also( 'wdt:P131+', '?state' )
			->also( 'wdt:P571', '?founded' )
			->where( '?state', 'wdt:P31', 'wd:Q35657' )
			->also( 'wdt:P571', '?stateStart' )
			->filter( '?founded < ?stateStart' )
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?state', 'rdfs:label', '?stateName' )
					->filter( 'LANG(?stateName) = "en"' )
			)
			->optional(
				$queryBuilder->newSubgraph()
					->where( '?uni', 'rdfs:label', '?uniName' )
					->filter( 'LANG(?uniName) = "en"' )
			)
			->limit( 10 );

		$this->assertIsExpected( 'American_universities_founded_before_the_states_they_reside_in_were_created', $queryBuilder->format() );
	}

	public function testWhatIsTheRelationBetweenTerrellBuckleyAndMiamiDolphins() {
		$queryBuilder = new QueryBuilder( self::$prefixes );

		$queryBuilder->select( '?l' )
			->where( 'wd:Q5571382', '?p', 'wd:Q223243' )
			->where( '?property', '?ref', '?p' )
			->also( 'a', 'wikibase:Property' )
			->also( 'rdfs:label', '?l' )
			->filter( 'LANG(?l) = "en"' )
			->limit( 10 );

		$this->assertIsExpected( 'What_is_the_relation_between_Terrell_Buckley_and_Miami_Dolphins', $queryBuilder->format() );
	}

	public function testAliasesOfPropertiesWhichAreUsedMoreThanOnce() {
		$queryBuilder = new QueryBuilder( self::$prefixes + array(
			'skos' => 'http://www.w3.org/2004/02/skos/core#'
		) );

		$queryBuilder->select( '?property', '?alias', '?occurences' )
			->subquery(
				$queryBuilder->newSubquery()
					->select( '?alias', '(COUNT (?alias) AS ?occurences)' )
					->where( '?tmp', 'a', 'wikibase:Property' )
					->also( 'skos:altLabel', '?alias' )
					->filter( 'LANG (?alias) = "en"' )
					->groupBy( '?alias' )
			)
			->where( '?property', 'a', 'wikibase:Property' )
			->also( 'skos:altLabel', '?alias' )
			->filter( '?occurences > 1' )
			->orderBy( '?alias' );

		$this->assertIsExpected( 'Aliases_of_properties_which_are_used_more_than_once', $queryBuilder->format() );
	}

	private function assertIsExpected( $name, $sparql ) {
		$expected = file_get_contents( __DIR__ . '/../data/wdqs_' . $name . '.rq' );

		$this->assertEquals( $expected, $sparql, 'Query didn\'t match the expected content of integration_' . $name . '.rq' );
	}

}
