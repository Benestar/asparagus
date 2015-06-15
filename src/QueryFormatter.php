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

		$sparql = $this->escapeStrings( $sparql );
		$sparql = $this->escapeIRIs( $sparql );

		foreach ( $this->split( $sparql ) as $part ) {
			if ( ctype_space( end( $this->formattedParts ) ) && ctype_space( $part) ) {
				continue;
			}

			if ( !empty( $this->formattedParts ) ) {
				$this->before( $part );
			}

			$this->indentation( $part );
			$this->append( $part );
			$this->after( $part );
		}

		return strtr( implode( $this->formattedParts ), $this->replacements );
	}

	private function escapeStrings( $string ) {
		$replacements = &$this->replacements;
		return preg_replace_callback( '/"((\\.|[^\\"])*)"/', function( $match ) use ( &$replacements ) {
			$replacements[md5( $match[0] )] = $match[0];
			return md5( $match[0] );
		}, $string );
	}

	private function escapeIRIs( $string ) {
		$replacements = &$this->replacements;
		return preg_replace_callback( '/\<((\\.|[^\\<])*)\>/', function( $match ) use ( &$replacements ) {
			$replacements[md5( $match[0] )] = $match[0];
			return md5( $match[0] );
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
		if ( $part === 'PREFIX' ) {
			$this->formattedParts[] = "\n";
		}

		if ( $part === 'SELECT' ) {
			$this->formattedParts[] = "\n\n";
		}

		if ( !ctype_space( end( $this->formattedParts ) )
			&& in_array( strtoupper( $part ), array( '.', '=' ) )
		) {
			$this->formattedParts[] = ' ';
		}
	}

	private function indentation( $part ) {
		if ( $part === '{' ) {
			$this->indentationLevel++;
		}

		if ( $part === '}' ) {
			$this->indentationLevel--;
		}

		if ( end( $this->formattedParts ) === "\n" ) {
			$this->formattedParts[] = str_repeat( "\t", $this->indentationLevel );
		}
	}

	private function append( $part ) {
		$this->formattedParts[] = $part;
	}

	private function after( $part ) {
		if ( $part === '{' || $part === '}' || $part === '.' ) {
			$this->formattedParts[] = "\n";
		}
	}

}
