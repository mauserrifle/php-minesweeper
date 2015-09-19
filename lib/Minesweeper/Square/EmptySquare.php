<?php
namespace Minesweeper\Square;

use Minesweeper\Grid;

class EmptySquare extends Square {

	/**
	 * @see Minesweeper\Square.Square::isGameOver()
	 */
	public function isGameOver()
	{
		return FALSE;
	}

	/**
	 * @see Minesweeper\Square.Square::isAutoRevealable()
	 */
	public function isAutoRevealable()
	{
		return TRUE;
	}

	/**
	 * @see Minesweeper\Square.Square::__toString()
	 */
	public function __toString()
	{
		return 'empty';
	}
}