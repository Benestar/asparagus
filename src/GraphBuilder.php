<?php

namespace Asparagus;

use InvalidArgumentException;

/**
 * Abstraction layer to create graphs for SPARQL queries
 *
 * @todo support filter, optional, union, minus
 *
 * @since 0.3 (package-private since 0.1)
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class GraphBuilder {

	/**
	 * @var array nested list of conditions, grouped by subject and predicate
	 */
	private $conditions = array();

	/**
	 * @var string[] list of optional expressions
	 */
	private $optionals = array();

	/**
	 * @var string[] list of filter expressions
	 */
	private $filters = array();

	/**
	 * @var string[] list of unions
	 */
	private $unions = array();

	/**
	 * @var string[] list of subqueries
	 */
	private $subqueries = array();

	/**
	 * @var string
	 */
	private $currentSubject = null;

	/**
	 * @var string
	 */
	private $currentPredicate = null;

	/**
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	/**
	 * @var UsageValidator
	 */
	private $usageValidator;

	/**
	 * Package-private constructor, use QueryBuilder::newSubgraph instead
	 *
	 * @param UsageValidator $usageValidator
	 * @throws InvalidArgumentException
	 */
	public function __construct( UsageValidator $usageValidator ) {
		$this->expressionValidator = new ExpressionValidator();
		$this->usageValidator = $usageValidator;
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
		$this->expressionValidator->validate( $subject,
			ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_PREFIXED_IRI
		);
		$this->expressionValidator->validate( $predicate,
			ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_PATH
		);
		$this->expressionValidator->validate( $object,
			ExpressionValidator::VALIDATE_VARIABLE | ExpressionValidator::VALIDATE_PREFIXED_IRI | ExpressionValidator::VALIDATE_NATIVE
		);

		$this->usageValidator->trackUsedPrefixes(  $subject . ' ' . $predicate . ' ' . $object );
		$this->usageValidator->trackDefinedVariables( $subject  . ' ' . $predicate . ' ' . $object );

		$this->currentSubject = $subject;
		$this->currentPredicate = $predicate;
		$this->conditions[$subject][$predicate][] = $object;

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
		if ( $predicate === null ) {
			$this->where( $this->currentSubject, $this->currentPredicate, $subject );
		} else if ( $object === null ) {
			$this->where( $this->currentSubject, $subject, $predicate );
		} else {
			$this->where( $subject, $predicate, $object );
		}

		return $this;
	}

	/**
	 * Adds the given expression as a filter to this query.
	 *
	 * @param string $expression
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function filter( $expression ) {
		$this->expressionValidator->validate( $expression, ExpressionValidator::VALIDATE_FUNCTION );

		$this->usageValidator->trackUsedPrefixes( $expression );
		$this->usageValidator->trackUsedVariables( $expression );

		$this->filters[] = '(' . $expression . ')';

		return $this;
	}

	/**
	 * Adds a filter that the given graph or triple exists.
	 *
	 * @param string|GraphBuilder $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function filterExists( $subject, $predicate = null, $object = null ) {
		$graphBuilder = $this->getGraphBuilder( $subject, $predicate, $object );
		$this->filters[] = 'EXISTS {' . $graphBuilder->getSPARQL() . ' }';

		return $this;
	}

	/**
	 * Adds a filter that the given graph or triple does not exist.
	 *
	 * @param string|GraphBuilder $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function filterNotExists( $subject, $predicate = null, $object = null ) {
		$graphBuilder = $this->getGraphBuilder( $subject, $predicate, $object );
		$this->filters[] = 'NOT EXISTS {' . $graphBuilder->getSPARQL() . ' }';

		return $this;
	}

	/**
	 * Adds the given graph or triple as an optional condition.
	 *
	 * @param string|GraphBuilder $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function optional( $subject, $predicate = null, $object = null ) {
		$graphBuilder = $this->getGraphBuilder( $subject, $predicate, $object );
		$this->optionals[] = $graphBuilder->getSPARQL();
		return $this;
	}

	private function getGraphBuilder( $subject, $predicate, $object ) {
		if ( $subject instanceof GraphBuilder ) {
			return $subject;
		}

		$graphBuilder = new GraphBuilder( $this->usageValidator );
		return $graphBuilder->where( $subject, $predicate, $object );
	}

	/**
	 * Adds the given graphs as alternative conditions.
	 *
	 * @param GraphBuilder|GraphBuilder[] $graphs
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function union( $graphs /* graphs ... */ ) {
		$graphs = is_array( $graphs ) ? $graphs : func_get_args();

		$this->unions[] = implode( ' UNION', array_map( function( GraphBuilder $graph ) {
			return ' {' . $graph->getSPARQL() . ' }';
		}, $graphs ) );

		return $this;
	}

	/**
	 * Adds the given subquery.
	 *
	 * @param QueryBuilder $queryBuilder
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function subquery( QueryBuilder $queryBuilder ) {
		$this->subqueries[] = $queryBuilder->getSPARQL( false );
		$this->usageValidator->trackDefinedVariables( implode( ' ', $queryBuilder->getSelects() ) );

		// @todo temp hack to add AS definitions to defined variables
		$regexHelper = new RegexHelper();
		$matches = $regexHelper->getMatches( 'AS \{variable}', implode( ' ', $queryBuilder->getSelects() ) );
		$this->usageValidator->trackDefinedVariables( $matches );

		return $this;
	}

	/**
	 * Returns the plain SPARQL string of these conditions.
	 * Surrounding brackets are not included.
	 *
	 * @return string
	 */
	public function getSPARQL() {
		// add subqueries to the beginning because they are logically evaluated first
		$sparql = $this->formatSubqueries();

		foreach ( $this->conditions as $subject => $predicates ) {
			$sparql .= ' ' . $subject;
			$sparql .= $this->formatPredicates( $predicates ) . ' .';
		}

		$sparql .= $this->formatOptionals();
		$sparql .= $this->formatFilters();
		$sparql .= $this->formatUnions();

		return $sparql;
	}

	private function formatPredicates( array $predicates ) {
		return implode( ' ;', array_map( function( $predicate, $objects ) {
			return ' ' . $predicate . ' ' . implode( ' , ', $objects );
		}, array_keys( $predicates ), $predicates ) );
	}

	private function formatOptionals() {
		return implode( array_map( function( $optional ) {
			return ' OPTIONAL {' . $optional . ' }';
		}, $this->optionals ) );
	}

	private function formatFilters() {
		return implode( array_map( function( $filter ) {
			return ' FILTER ' . $filter;
		}, $this->filters ) );
	}

	private function formatUnions() {
		return implode( $this->unions );
	}

	private function formatSubqueries() {
		return implode( array_map( function( $subquery ) {
			return ' { ' . $subquery . ' }';
		}, $this->subqueries ) );
	}

}
