<?php
namespace Minesweeper\Square;

use Minesweeper\Grid;

/**
 * Base class for squares. Defines whether the square makes you lose the game
 * and holds it's direct surrounding squares for easier recursive
 * manipulations.
 *
 */
abstract class Square {

	/**
	 * @var  boolean  Whether the square is already revealed
	 */
	private $revealed = FALSE;

	/**
	 * @var  array  Simple array with surrounding squares
	 */
	private $surrounding_squares = array();

	/**
	 * @var bool	Whether the square has been flagged as bomb or not
	 */
	private $flagged = FALSE;

	/**
	 * Let the square reveal itself and its surroundings
	 *
	 * @return  boolean  Whether the game is over
	 */
	public function reveal()
	{
		// Set revealed
		$this->revealed = TRUE;
		$this->flagged = FALSE;

		// Return game over
		if ($game_over = $this->isGameOver())
		{
			return $game_over;
		}

		// Reveal surrounding squares if there are no game overs nearby
		if ($this->numberOfSurroundingGameOverSquares() === 0)
		{
			foreach ($this->getSurroundingSquares() as $square)
			{
				// Is auto revealable and not already revealed
				if ($square->isAutoRevealable() AND ! $square->isRevealed())
				{
					// Reveal
					$square->reveal();
				}
			}
		}
	}

	/**
	 * Toggle flag
	 */
	public function toggleFlag()
	{
		$this->flagged = ! $this->flagged;
	}

	/**
	 * Wheter the squalre has been flagged as bomb or not
	 *
	 * @return boolean
	 */
	public function isFlagged()
	{
		return $this->flagged;
	}

	/**
	 * Whether the square is already revealed
	 *
	 * @return  boolean
	 */
	public function isRevealed()
	{
		return $this->revealed;
	}

	/**
	 * Returns whether this square makes the game over
	 *
	 * #return  boolean
	 */
	abstract public function isGameOver();

	/**
	 * Whether this square may be auto revealed by surrounding squares
	 *
	 * @return  boolean
	 */
	abstract public function isAutoRevealable();


	public function addSurroundingSquare(Square $square)
	{
		array_push($this->surrounding_squares, $square);
	}

	/**
	 * Get the surrounding squares
	 *
	 * @return  array
	 */
	public function getSurroundingSquares()
	{
		return $this->surrounding_squares;
	}

	/**
	 * Set surrounding squares
	 *
	 * @param array $surrounding_squares
	 */
	public function setSurroundingSquares(array $surrounding_squares)
	{
		$this->surrounding_squares = $surrounding_squares;
	}

	/**
	 * Return how much surrounding squares will be a game over
	 *
	 * @return  int
	 */
	public function numberOfSurroundingGameOverSquares()
	{
		$game_overs = 0;
		foreach ($this->getSurroundingSquares() as $square)
		{
			if ($square->isGameOver())
			{
				$game_overs++;
			}
		}

		return $game_overs;
	}

	/**
	 * Description of square
	 */
	abstract public function __toString();
}