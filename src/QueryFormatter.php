<?php

namespace Asparagus;

/**
 * Formatter for SPARQL queries
 *
 * @since 0.1
 *
 * @license GNU GPL v2+
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class QueryFormatter {

	/**
	 * @var string[]
	 */
	private $formattedParts;

	/**
	 * @var int
	 */
	private $indentationLevel;

	/**
	 * @var string[]
	 */
	private $replacements;

	/**
	 * Formats the given SPARQL string.
	 * Note that there have to be spaces before and after brackets and dots.
	 *
	 * @param string $sparql
	 * @return string
	 */
	public function format( $sparql ) {
		$this->formattedParts = array();
		$this->indentationLevel = 0;
		$this->replacements = array();

		$sparql = $this->escape( $sparql );

		foreach ( $this->split( $sparql ) as $part ) {
			if ( !empty( $this->formattedParts ) ) {
				$this->before( $part );
			}

			$this->indentation( $part );
			$this->append( $part );
			$this->after( $part );
		}

		$this->trimEnd();
		$this->formattedParts[] = "\n";

		return strtr( implode( $this->formattedParts ), $this->replacements );
	}

	private function escape( $string ) {
		$replacements = &$this->replacements;
		// @todo this is not completely safe but works in most cases
		return preg_replace_callback( '/("((\\.|[^\\"])*)"|\<((\\.|[^\\<])*)\>)/', function( $match ) use ( &$replacements ) {
			$key = '<' . md5( $match[0] ) . '>';
			$replacements[$key] = $match[0];
			return $key;
		}, $string );
	}

	private function split( $string ) {
		return preg_split(
			'/(\W)/',
			$string,
			-1,
			PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
		);
	}

	private function before( $part ) {
		if ( $part === 'PREFIX' || $part === '}' ) {
			$this->trimEnd();
			$this->formattedParts[] = "\n";
		}

		if ( $part === 'SELECT' ) {
			$this->trimEnd();
			$this->formattedParts[] = "\n\n";
		}

		if ( end( $this->formattedParts ) !== "\n" &&
			in_array( $part, array( '.', '=', '(', '<', '{', '?', '$' ) )
		) {
			$this->trimEnd();
			$this->append( ' ' );
		}
	}

	private function indentation( $part ) {
		if ( $part === '}' ) {
			$this->indentationLevel--;
		}

		if ( !ctype_space( $part ) && substr( end( $this->formattedParts ), 0, 1 ) === "\n" ) {
			$this->formattedParts[] = str_repeat( "\t", $this->indentationLevel );
		}

		if ( $part === '{' ) {
			$this->indentationLevel++;
		}
	}

	private function append( $part ) {
		if ( !ctype_space( $part ) ) {
			$this->formattedParts[] = $part;
		} else if ( !ctype_space( end( $this->formattedParts ) ) &&
			end( $this->formattedParts ) !== '('
		) {
			$this->formattedParts[] = ' ';
		}
	}

	private function after( $part ) {
		if ( in_array( $part, array( '{', '}', '.' ) ) ) {
			$this->formattedParts[] = "\n";
		}

		if ( $part === ';' ) {
			$this->formattedParts[] = "\n\t";
		}
	}

	private function trimEnd() {
		while ( ctype_space( end( $this->formattedParts ) ) ) {
			array_pop( $this->formattedParts );
		}
	}

}
