<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Abstraction layer to build SPARQL queries
 *
 * Nested filters not supported
 * Supports SPARQL v1.0 (v1.1 to come)
 *
 * @since 0.1
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilder {

	/**
	 * @var QueryPrefixBuilder
	 */
	private $prefixBuilder;

	/**
	 * @var string[] list of variables to select
	 */
	private $variables = array();

	/**
	 * @var QueryBuider[]
	 */
	private $subqueries = array();

	/**
	 * @var string[] list of conditions
	 */
	private $conditions = array();

	/**
	 * @var QueryModifierBuilder
	 */
	private $modifierBuilder;

	/**
	 * @var string[] $prefixes
	 */
	public function __construct( array $prefixes = array() ) {
		$this->prefixBuilder = new QueryPrefixBuilder( $prefixes );
		$this->modifierBuilder = new QueryModifierBuilder();
	}

	/**
	 * Adds a prefix for the given IRI.
	 *
	 * @param string|string[] $prefix
	 * @param string|null $iri
	 * @return self
	 */
	public function prefix( $prefix, $iri = null ) {
		$prefixes = is_array( $prefix ) ? $prefix : array( $prefix => $iri );
		$this->prefixBuilder->setPrefixes( $prefixes );
		return $this;
	}

	/**
	 * Specifies the variables to select.
	 *
	 * @param string|string[] $variables
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function select( $variables /* variables ... */ ) {
		$variables = is_array( $variables ) ? $variables : func_get_args();

		foreach ( $variables as $variable ) {
			// @todo better string validation
			if ( !is_string( $variable ) ) {
				throw new InvalidArgumentException( '$variables has to be an array of strings' );
			}

			$this->variables[] = '?' . $variable;
		}

		return $this;
	}

	/**
	 * Adds a subquery to this query. Recursive dependencies are prohibited.
	 *
	 * @param self $query
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function subquery( QueryBuilder $query ) {
		if ( $query === $this || $query->hasSubquery( $this ) ) {
			throw new InvalidArgumentException( 'Cannot add the same query as subquery' );
		}

		$this->subqueries[] = $query;

		return $this;
	}

	/**
	 * Checks recursively if the given query is included as a subquery.
	 *
	 * @param self $query
	 * @return bool
	 */
	public function hasSubquery( QueryBuilder $query ) {
		foreach ( $this->subqueries as $subquery ) {
			if ( $query === $subquery || $subquery->hasSubquery( $query) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates a new subquery builder.
	 *
	 * @return self
	 */
	public function newSubquery() {
		return new QueryBuilder( $this->prefixBuilder->getPrefixes() );
	}

	/**
	 * @return self
	 */
	public function where( $condition ) {
		// @todo
		$this->conditions[] = $condition;

		return $this;
	}

	public function filter( $filter ) {
		return $this;
	}

	public function filterExists( $condition ) {
		return $this;
	}

	public function filterNotExists( $condition ) {
		return $this;
	}

	public function optional( $optional ) {
		return $this;
	}

	public function union( $condition ) {
		return $this;
	}

	public function minus( $condition ) {
		return $this;
	}

	/**
	 * Sets the GROUP BY modifier.
	 *
	 * @param type $variable
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function groupBy( $variable )  {
		$this->modifierBuilder->groupBy( $variable );
		return $this;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 * @return self
	 */
	public function having( $expression ) {
		$this->modifierBuilder->having( $expression );
		return $this;
	}

	/**
	 * Sets the ORDER BY modifier.
	 *
	 * @param string $variable
	 * @param string $direction one of ASC or DESC
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function orderBy( $variable, $direction = 'ASC' ) {
		$this->modifierBuilder->orderBy( $variable, $direction );
		return $this;
	}

	/**
	 * Sets the LIMIT modifier.
	 *
	 * @param int $limit
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function limit( $limit ) {
		$this->modifierBuilder->limit( $limit );
		return $this;
	}

	/**
	 * Sets the OFFSET modifier.
	 *
	 * @param int $offset
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function offset( $offset ) {
		$this->modifierBuilder->offset( $offset );
		return $this;
	}

	/**
	 * Returns the plain SPARQL string of this query.
	 *
	 * @param bool $includePrefixes
	 * @return string
	 */
	public function getSPARQL( $includePrefixes = true ) {
		if ( !is_bool( $includePrefixes ) ) {
			throw new InvalidArgumentException( '$includePrefixes has to be a bool' );
		}

		$sparql = $includePrefixes ? $this->prefixBuilder->getSPARQL() : '';
		$sparql .= 'SELECT ' . $this->getVariables() . ' WHERE {';
		$sparql .= $this->getSubqueries();
		$sparql .= $this->getConditions();
		$sparql .= '}';
		$sparql .= $this->modifierBuilder->getSPARQL();

		return $sparql;
	}

	private function getVariables() {
		return empty( $this->variables ) ? '*' : implode( ' ', $this->variables );
	}

	private function getSubqueries() {
		return implode( array_map( function( QueryBuilder $query ) {
			return ' {' . $query->getSPARQL( false ) . '}';
		}, $this->subqueries ) );
	}

	private function getConditions() {
		return implode( array_map( function( $condition ) {
			return ' ' . $condition;
		}, $this->conditions ) );
	}

	/**
	 * @see self::getSPARQL
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getSPARQL();
	}

}
