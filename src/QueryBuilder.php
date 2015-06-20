<?php

namespace Asparagus;

use InvalidArgumentException;
use RangeException;

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
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	/**
	 * @var QueryPrefixBuilder
	 */
	private $prefixBuilder;

	/**
	 * @var string[] list of variables to select
	 */
	private $variables = array();

	/**
	 * @var GraphBuilder
	 */
	private $graphBuilder;

	/**
	 * @var QueryModifierBuilder
	 */
	private $modifierBuilder;

	/**
	 * @var string[] $prefixes
	 * @throws InvalidArgumentException
	 */
	public function __construct( array $prefixes = array() ) {
		$this->expressionValidator = new ExpressionValidator();
		$this->prefixBuilder = new QueryPrefixBuilder( $prefixes );
		$this->graphBuilder = new GraphBuilder();
		$this->modifierBuilder = new QueryModifierBuilder();
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
			$this->expressionValidator->validate( $variable,
				ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_FUNCTION_AS
			);

			$this->variables[] = substr( $variable, 1 );
		}

		return $this;
	}

	/**
	 * Adds the given triple as a condition.
	 *
	 * @param string $subject
	 * @param string $predicate
	 * @param string $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function where( $subject, $predicate, $object ) {
		$this->graphBuilder->where( $subject, $predicate, $object );
		return $this;
	}

	/**
	 * Adds the given triple/double/single value as an additional condition
	 * to the previously added condition.
	 *
	 * @param string $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function also( $subject, $predicate = null, $object = null ) {
		$this->graphBuilder->also( $subject, $predicate, $object );
		return $this;
	}

	/**
	 * Adds the given expression as a filter to this query.
	 *
	 * @since 0.3
	 *
	 * @param string $expression
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function filter( $expression ) {
		$this->graphBuilder->filter( $expression );
		return $this;
	}

	/**
	 * Adds a filter that the given condition builder exists.
	 *
	 * @since 0.3
	 *
	 * @param GraphBuilder $graphBuilder
	 * @return self
	 */
	public function filterExists( GraphBuilder $graphBuilder ) {
		$this->graphBuilder->filterExists( $graphBuilder );
		return $this;
	}

	/**
	 * Adds a filter that the given condition builder does not exist.
	 *
	 * @since 0.3
	 *
	 * @param GraphBuilder $graphBuilder
	 * @return self
	 */
	public function filterNotExists( GraphBuilder $graphBuilder ) {
		$this->graphBuilder->filterNotExists( $graphBuilder );
		return $this;
	}

	/**
	 * Adds the given graph or triple as an optional condition.
	 *
	 * @since 0.3
	 *
	 * @param string|GraphBuilder $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function optional( $subject, $predicate = null, $object = null ) {
		$this->graphBuilder->optional( $subject, $predicate, $object );
		return $this;
	}

	/**
	 * Adds the given subquery.
	 *
	 * @param QueryBuilder $query
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function subquery( QueryBuilder $query ) {
		$this->graphBuilder->subquery( $query );
		return $this;
	}

	/**
	 * Creates a new subquery builder.
	 *
	 * @return QueryBuilder
	 */
	public function newSubquery() {
		return new QueryBuilder( $this->prefixBuilder->getPrefixes() );
	}

	/**
	 * Creates a new subgraph builder.
	 *
	 * @since 0.3
	 *
	 * @return GraphBuilder
	 */
	public function newSubgraph() {
		return new GraphBuilder();
	}

	/**
	 * Sets the GROUP BY modifier.
	 *
	 * @param string $expression
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function groupBy( $expression )  {
		$this->modifierBuilder->groupBy( $expression );
		return $this;
	}

	/**
	 * Sets the HAVING modifier.
	 *
	 * @param string $expression
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function having( $expression ) {
		$this->modifierBuilder->having( $expression );
		return $this;
	}

	/**
	 * Sets the ORDER BY modifier.
	 *
	 * @param string $expression
	 * @param string $direction one of ASC or DESC
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function orderBy( $expression, $direction = 'ASC' ) {
		$this->modifierBuilder->orderBy( $expression, $direction );
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
	 * @throws InvalidArgumentException
	 * @throws RangeException
	 */
	public function getSPARQL( $includePrefixes = true ) {
		if ( !is_bool( $includePrefixes ) ) {
			throw new InvalidArgumentException( '$includePrefixes has to be a bool' );
		}

		$this->validatePrefixes();
		$this->validateVariables();

		$sparql = $includePrefixes ? $this->prefixBuilder->getSPARQL() : '';
		$sparql .= 'SELECT ' . $this->getVariables() . ' WHERE';
		$sparql .= ' {' . $this->graphBuilder->getSPARQL() . ' }';
		$sparql .= $this->modifierBuilder->getSPARQL();

		return $sparql;
	}

	private function validatePrefixes() {
		$definedPrefixes = array_keys( $this->prefixBuilder->getPrefixes() );
		$usedPrefixes = array_merge( $this->graphBuilder->getPrefixes(), $this->modifierBuilder->getPrefixes() );

		$diff = array_diff( $usedPrefixes, $definedPrefixes );
		if ( !empty( $diff ) ) {
			throw new RangeException( 'The prefixes ' . implode( ', ', $diff ) . ' aren\'t defined for this query.' );
		}
	}

	private function validateVariables() {
		$definedVariables = $this->graphBuilder->getVariables();
		$usedVariables = array_merge( $this->variables, $this->modifierBuilder->getVariables() );

		$diff = array_diff( $usedVariables, $definedVariables );
		if ( !empty( $diff ) ) {
			throw new RangeException( 'The variables ?' . implode( ', ?', $diff ) . ' don\'t occur in this query.' );
		}
	}

	private function getVariables() {
		return empty( $this->variables ) ? '*' : '?' . implode( ' ?', $this->variables );
	}

	/**
	 * @see self::getSPARQL
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->getSPARQL();
	}

	/**
	 * Returns the formatted SPARQL string of this query.
	 *
	 * @see QueryFormatter::format
	 *
	 * @return string
	 */
	public function format() {
		$formatter = new QueryFormatter();
		return $formatter->format( $this->getSPARQL() );
	}

}
