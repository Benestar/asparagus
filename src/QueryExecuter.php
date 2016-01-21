<?php

namespace Asparagus;

use InvalidArgumentException;
use RangeException;
use RuntimeException;

/**
 * Allows the execution of a query to a remote SPARQL endpoint.
 *
 * @since 0.1
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryExecuter {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string[]
	 */
	private $options;

	/**
	 * @var Http
	 */
	private $http;

	/**
	 * @param string $url
	 * @param string[] $options one of queryParam, formatParam or userAgent
	 * @param Http|null $http
	 */
	public function __construct( $url, array $options = array(), Http $http = null ) {
		$this->url = $url;
		$this->options = array_merge( array(
			'queryParam' => 'query',
			'formatParam' => 'format',
			'userAgent' => 'Asparagus/Asparagus 0.1'
		), $options );
		$this->http = $http ?: new Http( $this->options['userAgent'] );
	}

	/**
	 * Executes the given SPARQL query.
	 *
	 * @param string|QueryBuilder $query
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws RangeException
	 * @throws RuntimeException
	 */
	public function execute( $query ) {
		if ( $query instanceof QueryBuilder ) {
			$query = $query->getSPARQL();
		}

		if ( !is_string( $query ) ) {
			throw new InvalidArgumentException( '$query has to be a string or an instance of QueryBuilder' );
		}

		$result = $this->getResult( $query );
		
		// TODO also support ask queries (with key 'boolean')
		//      https://www.w3.org/TR/rdf-sparql-json-res/
		return $result['results'];
	}

	private function getResult( $query ) {
		$result = $this->http->request( $this->url, array(
			$this->options['queryParam'] => $query,
			$this->options['formatParam'] => 'json'
		) );

		return json_decode( $result, true );
	}

}
