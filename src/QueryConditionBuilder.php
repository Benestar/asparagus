<?php

namespace Asparagus;

/**
 * Package-private class to build the conditions of a SPARQL query.
 *
 * @todo support filter, optional, union, minus
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryConditionBuilder {

	/**
	 * @var array nested list of conditions, grouped by subject and predicate
	 */
	private $conditions = array();

	/**
	 * @var string[] list of filters
	 */
	private $filters = array();

	/**
	 * @var QueryConditionBuilder[] list of optionals
	 */
	private $optionals = array();

	/**
	 * @var QueryConditionBuilder[] list of unions
	 */
	private $unions = array();

	/**
	 * @var QueryConditionBuilder[] other subgraphs
	 */
	private $subgraphs = array();

	/**
	 * @var string
	 */
	private $currentSubject = null;

	/**
	 * @var string
	 */
	private $currentPredicate = null;

	/**
	 * @var QueryConditionBuilder
	 */
	private $currentCondition = null;

	/**
	 * @var ExpressionValidator
	 */
	private $expressionValidator;

	public function __construct() {
		$this->expressionValidator = new ExpressionValidator();
	}

	/**
	 * Adds the given triple as a condition.
	 *
	 * @param string|self $subject
	 * @param string $predicate
	 * @param string $object
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function where( $subject, $predicate, $object ) {
		if ( $this->currentCondition !== null ) {
			$this->subgraphs[] = $this->currentCondition;
			$this->currentCondition = null;
		}

		$this->expressionValidator->validate( $subject,
			ExpressionValidator::VALIDATE_PREFIXED_IRI | ExpressionValidator::VALIDATE_VARIABLE
		);
		$this->expressionValidator->validate( $predicate,
			ExpressionValidator::VALIDATE_PATH | ExpressionValidator::VALIDATE_VARIABLE
		);
		$this->expressionValidator->validate( $object,
			ExpressionValidator::VALIDATE_PREFIXED_IRI | ExpressionValidator::VALIDATE_VARIABLE
		);

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
	 * Adds the given expression as filter.
	 *
	 * @param string $expression
	 */
	public function filter( $expression ) {
		$this->expressionValidator->validate( $expression, ExpressionValidator::VALIDATE_FUNCTION );
		$this->filters[] = $expression;
	}

	/**
	 * Adds the given group graph pattern as optional.
	 *
	 * @param QueryConditionBuilder $conditionBuilder
	 */
	public function optional( QueryConditionBuilder $conditionBuilder ) {
		$this->optionals[] = $conditionBuilder;
	}

	/**
	 * Adds the given group graph pattern as union.
	 *
	 * @param QueryConditionBuilder $conditionBuilder
	 */
	public function union( QueryConditionBuilder $conditionBuilder ) {
		if ( $this->currentCondition !== null ) {
			$this->unions[] = $this->currentCondition;
			$this->currentCondition = null;
		}

		$this->unions[] = $conditionBuilder;
	}

	/**
	 * Returns the plain SPARQL string of these conditions.
	 * Surrounding brackets are not included.
	 *
	 * @return string
	 */
	public function getSPARQL() {
		$sparql = '';

		foreach ( $this->conditions as $subject => $predicates ) {
			$sparql .= ' ' . $subject;
			$sparql .= $this->formatPredicates( $predicates ) . ' .';
		}

		$sparql .= $this->formatFilters();
		$sparql .= $this->formatOptionals();
		$sparql .= $this->formatUnions();
		$sparql .= $this->formatSubgraphs();

		return $sparql;
	}

	private function formatPredicates( array $predicates ) {
		return implode( ' ;', array_map( function( $predicate, $objects ) {
			return ' ' . $predicate . ' ' . implode( ' , ', $objects );
		}, array_keys( $predicates ), $predicates ) );
	}

	private function formatFilters() {
		return implode( array_map( function( $filter ) {
			return ' FILTER ' . $filter;
		}, $this->filters ) );
	}

	private function formatOptionals() {
		return implode( array_map( function( QueryConditionBuilder $conditionBuilder ) {
			return ' OPTIONAL {' . $conditionBuilder->getSPARQL() . ' }';
		}, $this->optionals ) );
	}

	private function formatUnions() {
		return implode( ' UNION', function( QueryConditionBuilder $conditionBuilder ) {
			return ' {' . $conditionBuilder->getSPARQL() . ' }';
		}, $this->unions );
	}

	private function formatSubgraphs() {
		return implode( array_map( function( QueryConditionBuilder $conditionBuilder ) {
			return ' {' . $conditionBuilder->getSPARQL() . ' }';
		}, $this->subgraphs ) );
	}

	/**
	 * @return string[]
	 */
	public function getPrefixes() {
		// @todo include subgraphs
		return $this->expressionValidator->getPrefixes();
	}

	/**
	 * @return string[]
	 */
	public function getVariables() {
		// @todo include subgraphs
		return $this->expressionValidator->getVariables();
	}

}
