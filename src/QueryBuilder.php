<?php

namespace Asparagus;

use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Abstraction layer to build SPARQL queries
 *
 * @since 0.1
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryBuilder {

	/**
	 * @var string[] map of prefixes to IRIs
	 */
	private $prefixes = array();

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
	 * @var string[] list of modifiers including limit, offset and order by
	 */
	private $modifiers = array();

	/**
	 * @var string[] $prefxies
	 */
	public function __construct( array $prefixes = array() ) {
		$this->prefix( $prefixes );
	}

	/**
	 * Adds a prefix for the given IRI.
	 *
	 * @param string|array $prefix
	 * @param string|null $iri
	 * @return self
	 * @throws InvalidArgumentException
	 * @throws OutOfBoundsException
	 */
	public function prefix( $prefix, $iri = null ) {
		$prefixes = is_array( $prefix ) ? $prefix : array( $prefix => $iri );

		foreach ( $prefixes as $prefix => $iri ) {
			// @todo better string validation
			if ( !is_string( $prefix ) || !is_string( $iri ) ) {
				throw new InvalidArgumentException( '$prefix and $iri have to be strings' );
			}

			if ( isset( $this->prefixes[$prefix] ) ) {
				throw new OutOfBoundsException( 'Prefix ' . $prefix . ' is already set.' );
			}

			$this->prefixes[$prefix] = $iri;
		}

		return $this;
	}

	/**
	 * Specifies the variables to select.
	 *
	 * @param array|string $variables
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
		return new QueryBuilder( $this->prefixes );
	}

	/**
	 * @return self
	 */
	public function where( $condition ) {
		// @todo
		$this->conditions[] = $condition;

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
		// @todo better string validation
		if ( !is_string( $variable ) ) {
			throw new InvalidArgumentException( '$variable has to be a string' );
		}

		$this->modifiers['GROUP BY'] = '?' . $variable;

		return $this;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 * @return self
	 */
	public function having( $expression ) {
		// @todo better string validation
		if ( !is_string( $expression ) ) {
			throw new InvalidArgumentException( '$expression has to be a string' );
		}

		$this->modifiers['HAVING'] = $expression;

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
		// @todo better string validation
		if ( !is_string( $variable ) ) {
			throw new InvalidArgumentException( '$variable has to be a string' );
		}

		// @todo also allow lower case
		if ( !in_array( $direction, array( 'ASC', 'DESC' ) ) ) {
			throw new InvalidArgumentException( '$direction has to be either ASC or DESC' );
		}

		$this->modifiers['ORDER BY'] = '?' . $variable . ' ' . $direction;

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
		if ( !is_int( $limit ) ) {
			throw new InvalidArgumentException( '$limit has to be an integer' );
		}

		$this->modifiers['LIMIT'] = $limit;

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
		if ( !is_int( $offset ) ) {
			throw new InvalidArgumentException( '$offset has to be an integer' );
		}

		$this->modifiers['OFFSET'] = $offset;

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

		$sparql = $includePrefixes ? $this->getPrefixes() : '';
		$sparql .= 'SELECT ' . $this->getVariables() . ' WHERE {';
		$sparql .= $this->getSubqueries();
		$sparql .= $this->getConditions();
		$sparql .= '}';
		$sparql .= $this->getModifiers();

		return $sparql;
	}

	private function getPrefixes() {
		return implode( array_map( function( $prefix, $iri ) {
			return 'PREFIX ' . $prefix . ': <' . $iri . '> '; 
		}, $this->prefixes ) );
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

	private function getModifiers() {
		$modifiers = $this->modifiers;
		return implode( array_map( function( $key ) use ( $modifiers ) {
			if ( isset( $modifiers[$key] ) ) {
				return ' ' . $key . ' ' . $modifiers[$key];
			}
		}, array( 'GROUP BY', 'HAVING', 'ORDER BY', 'LIMIT', 'OFFSET' ) ) );
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
