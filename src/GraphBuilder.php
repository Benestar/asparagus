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
	 * @var string[] list of filter expressions
	 */
	private $filters = array();

	/**
	 * @var string[] list of optional expressions
	 */
	private $optionals = array();

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
	 * @param string|null $subject
	 * @param string|null $predicate
	 * @param string|null $object
	 * @throws InvalidArgumentException
	 */
	public function __construct( $subject = null, $predicate = null, $object = null ) {
		$this->expressionValidator = new ExpressionValidator();

		if ( $subject !== null && $predicate !== null && $object !== null ) {
			$this->where( $subject, $predicate, $object );
		}
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
	 * Adds the given expression as a filter to this query.
	 *
	 * @param string $expression
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function filter( $expression ) {
		$this->expressionValidator->validate( $expression, ExpressionValidator::VALIDATE_FUNCTION );
		$this->filters[] = $expression;

		return $this;
	}

	/**
	 * Adds a filter that the given condition builder exists.
	 *
	 * @param GraphBuilder $graphBuilder
	 * @return self
	 */
	public function filterExists( GraphBuilder $graphBuilder ) {
		// @todo track variables and prefixes
		$this->filters[] = 'EXISTS {' . $graphBuilder->getSPARQL() . ' }';

		return $this;
	}

	/**
	 * Adds a filter that the given condition builder does not exist.
	 *
	 * @param GraphBuilder $graphBuilder
	 * @return self
	 */
	public function filterNotExists( GraphBuilder $graphBuilder ) {
		// @todo track variables and prefixes
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
		if ( !( $subject instanceof GraphBuilder ) ) {
			$subject = new GraphBuilder( $subject, $predicate, $object );
		}

		$this->optionals[] = $subject->getSPARQL();

		return $this;
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
		return implode( array_map( function( $optional ) {
			return ' OPTIONAL {' . $optional . ' }';
		}, $this->optionals ) );
	}

	/**
	 * @return string[]
	 */
	public function getPrefixes() {
		return $this->expressionValidator->getPrefixes();
	}

	/**
	 * @return string[]
	 */
	public function getVariables() {
		return $this->expressionValidator->getVariables();
	}

}
