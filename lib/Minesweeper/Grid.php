<?php
namespace Minesweeper;

use SebastianBergmann\RecursionContext\Exception;

use Minesweeper\Exception\GameOverException;
use Minesweeper\Exception\InvalidPositionException;
use Minesweeper\Exception\SquareAlreadyRevealedException;

use Minesweeper\Square\EmptySquare;
use Minesweeper\Square\MineSquare;
use Minesweeper\Square\Square;

class Grid {

	/**
	 * @var  Square[][]   array containing the rows and columns (e.g. [8][8])
	 */
	private $grid = array();

	/**
	 * @var  boolean  Whether the game is over. Defaults FALSE
	 */
	private $game_over = FALSE;

	/**
	 * @var  boolean  Whether the game has been won by the player
	 */
	private $won_by_player = FALSE;

	/**
	 * @var  array   Positions that are already filled randomly.
	 */
	private $occupied_random_positions = array();

	/**
	 * @var int	Number of mines in game.
	 */
	private $number_of_mines = null;

	/**
	 * @var bool Player has already made a first move?
	 */
	private $game_has_initiated = false;



	/**
	 * Construct Grid with a grid size.
	 *
	 * @param  int  $rows
	 * @param  int  $columns
	 * @param  int  $mines
	 */
	public function __construct($rows=8, $columns=8, $mines=10)
	{
		// Negative number
		if ( ! is_numeric($rows) OR $rows < 0)
		{
			$rows = 8;
		}

		// Negative number
		if (! is_numeric($columns) OR $columns < 0)
		{
			$columns = 8;
		}

		// Negative number
		if (! is_numeric($mines) OR $mines < 0)
		{
			$mines = 10;
		}

		// Prepare grid array
		for ($row=0; $row < $rows; $row++)
		for ($column=0; $column < $columns; $column++)
		{
			$this->grid[$row][$column] = NULL;
		}

		$this->number_of_mines = $mines;

		// Reset grid
		$this->reset();
	}

	/**
	 * Get raw grid
	 *
	 * @return  array  grid
	 */
	public function getGrid()
	{
		return $this->grid;
	}


	/**
	 * Reset the grid so it only consists of empty squares
	 */
	public function reset()
	{
		// Fill whole grid with empty squares
		for ($row=0; $row < $this->getRows(); $row++)
		for ($column=0; $column < $this->getColumns(); $column++)
		{
			$this->addSquare(new EmptySquare, array($row, $column), FALSE);
		}

		// Fill surrounding squares of all squares
		$this->fillSurroundingSquares();
	}

	/**
	 * Get amount of columns in the grid.
	 *
	 * @return  int
	 */
	public function getColumns()
	{
		return count($this->grid[0]);
	}


	/**
	 * Get amount of rows in the grid.
	 *
	 * @return  int
	 */
	public function getRows()
	{
		return count($this->grid);
	}

	/**
	 * Get the number of mines in the grid.
	 *
	 * @return  int
	 */
	public function getNumberOfMines()
	{
		return $this->number_of_mines;
	}

	/**
	 * Add square to grid. Returns the position on success.
	 *
	 * @param   Square    $square
	 *
	 * @param   array     $position  array with key 0 for row and key 1 for
	 *                               column zero based.
	 *
	 * @param   boolean   $fix_square_surroundings
	 *        Whether to fill square surroundings afterwards. When disabled
	 *        (for improved performance), make sure running
	 *        fillSurroundingSquares()
	 *
	 * @param	array	  $avoid_position
	 *        Position to avoid placing mines. Only works if $position
	 *   	  is set to null. It will also avoid place mines on surroundings
	 * 		  of this position
	 *
	 *
	 * @throws  InvalidPositionException
	 *
	 * @return  int  position
	 */
	public function addSquare(Square $square,
	                          array $position = NULL,
	                          $fix_square_surroundings = TRUE,
							  $avoid_position = NULL)
	{
		// Use given position
		if ($position)
		{
			if ( ! $this->isValidPosition($position))
			{
				throw new InvalidPositionException;
			}

		}
		// Create random position
		else
		{

			$position = $this->createRandomPosition($avoid_position);


			// All places already filled randomly?
			$random_full = sizeof($this->occupied_random_positions) ===
			                   $this->numberOfSquares();

			// Not everything filled randomly. Make sure the random position does
			// not fill a previous random position
			while (in_array($position, $this->occupied_random_positions) AND ! $random_full)
			{
				$position = $this->createRandomPosition($avoid_position);
			}

			// Add position to occupied random positions
			$this->occupied_random_positions[] = $position;
		}

		// Add square to grid
		$this->grid[$position[0]][$position[1]] = $square;

		// Fix positions
		if ($fix_square_surroundings)
		{
			$this->fillSurroundingSquares();
		}

		// Return the position
		return $position;
	}

	/**
	 * Get square from position
	 *
	 * @param  array  $position  array with key 0 for row and key 1 for column
	 *                           zero based.
	 *
	 * @throws InvalidPositionException
	 *
	 * @return Square
	 */
	public function getSquare(array $position)
	{
		if( ! $this->isValidPosition($position))
		{
			throw new InvalidPositionException;
		}

		return $this->grid[$position[0]][$position[1]];
	}

	/**
	* Toggle flag by position
	*
	* @throws InvalidPositionException
	* @throws  SquareAlreadyRevealedException
	*
	* @param       array $position the array containing the position to reveal
	*/
	public function toggleFlag(array $position)
	{
		$square = $this->getSquare($position);

		if ($square->isRevealed())
		{
			throw new SquareAlreadyRevealedException;
		}

		$square->toggleFlag();
	}


	/**
	 * Reveal position
	 *
	 * @param	array $position the array containing the position to reveal
	 *
	 * @throws  GameOverException
	 * @throws  InvalidPositionException
	 * @throws  SquareAlreadyRevealedException
	 *
	 * @return  boolean  game over
	 */
	public function reveal(array $position)
	{
		// Game over
		if ($this->isGameOver())
		{
			throw new GameOverException;
		}

		// Not a valid position
		if ( ! $this->isValidPosition($position))
		{
			throw new InvalidPositionException;
		}

		// First reveal, add mines
		if ( ! $this->game_has_initiated)
		{
			for($i=0; $i < $this->number_of_mines; $i++)
			{
				$this->addSquare(new MineSquare, NULL, TRUE, $position);
			}

			$this->game_has_initiated = true;
		}

		// Get square
		$square = $this->getSquare($position);

		// Already revealed
		if ($square->isRevealed())
		{
			throw new SquareAlreadyRevealedException;
		}

		// Let the square reveal
		$this->setGameOver($square->reveal());

		// Not game over and all revealed
		if ( ! $this->isGameOver() AND $this->allRevealed())
		{
			// Player won
			$this->won_by_player = TRUE;

			// Game over
			$this->setGameOver(TRUE);
		}

		// Return whether the game is over
		return $this->isGameOver();
	}

	/**
	 * Get position by square
	 *
	 * @param Square $square
	 *
	 * @return array|null Position of the square
	 */
	public function getPositionBySquare(Square $square)
	{
		for ($row=0; $row < $this->getRows(); $row++)
		for ($column=0; $column < $this->getColumns(); $column++)
		{
			if ($this->getSquare(array($row, $column)) === $square)
			{
				return array($row, $column);
			}
		}

		return null;
	}

	/**
	 * Get the surrounding squares by position.
	 *
	 * Example grid:
	 *
	 *       0 1 2 3 4 5 6 7
	 *    0  * * * * * * * *
	 *    1  * X X X * * * *
	 *    2  * X x X * * * *
	 *    3  * X X X * * * *
	 *    4  * * * * * * * *
	 *    5  * * * * * * * *
	 *    6  * * * * * * * *
	 *    7  * * * * * * * *
	 *
	 * @param   array  $position
	 *
	 * @throws  InvalidPositionException
	 *
	 * @return  array  position
	 */
	public function getSurroundingSquaresByPosition(array $position)
	{
		// Not a valid position
		if ( ! $this->isValidPosition($position))
		{
			throw new InvalidPositionException;
		}

		// Get all surrounding squares (from top left to left)
		$squares = array(
				// Top left
				Arr::get(
					Arr::get($this->grid, ($position[0] - 1)),
					($position[1] - 1))
				,
				// Top
				Arr::get(
					Arr::get($this->grid, ($position[0] - 1)),
					$position[1])
				,
				// Top right
				Arr::get(
					Arr::get($this->grid, ($position[0] - 1)),
					($position[1] + 1))
				,
				// Right
				Arr::get($this->grid[$position[0]], ($position[1] + 1)),

				// Bottom right
				Arr::get(
					Arr::get($this->grid, ($position[0] + 1)),
					($position[1] + 1))
				,
				// Bottom
				Arr::get(
					Arr::get($this->grid, ($position[0] + 1)),
					$position[1])
				,
				// Bottom left
				Arr::get(
					Arr::get($this->grid, ($position[0] + 1)),
					($position[1] - 1))
				,
				// Left
				Arr::get($this->grid[$position[0]], ($position[1] - 1)),
		);

		// Remove NULL values
		$squares = array_values(array_filter($squares, 'is_object'));

		return $squares;
	}

	/**
	 * Returns TRUE when game over
	 *
	 * @return  boolean
	 */
	public function isGameOver()
	{
		return $this->game_over;
	}

	/**
	 * Returns TRUE when the player has won
	 *
	 * @return  boolean
	 */
	public function isWonByPlayer()
	{
		return $this->won_by_player;
	}

	/**
	 * Set whether the game is over
	 *
	 * @param  boolean  $game_over
	 */
	public function setGameOver($game_over)
	{
		$this->game_over = (bool) $game_over;
	}

	/**
	 * Check whether a position array is valid
	 *
	 * @param  array  $position
	 *
	 * @return bool Either the position is valid or not
	 */
	public function isValidPosition(array $position)
	{
		// Valid row, column?
		if ( ! $x = is_numeric(Arr::get($position, 0)) OR
				! $y = is_numeric(Arr::get($position, 1)))
		{
			return FALSE;
		}

		// Position in grid?
		if ( ! array_key_exists($position[0], $this->grid) OR
				! array_key_exists($position[1], $this->grid[$position[0]]))
		{
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Returns the number of squares
	 *
	 * @param  string  $type
	 *
	 * @return  int  number of squares
	 */
	public function numberOfSquares($type=NULL)
	{
		// No type
		if ( ! $type)
		{
			return $this->getRows() * $this->getColumns();
		}

		// By type
		$number = 0;
		for ($row=0; $row < $this->getRows(); $row++)
		for ($column=0; $column < $this->getColumns(); $column++)
		{
			if ($square = $this->getSquare(array($row, $column)) instanceof $type)
			{
				$number++;
			}
		}

		return $number;
	}

	/**
	 * Test whether all non-gameover squares are revealed
	 */
	public function allRevealed()
	{
		for ($row=0; $row < $this->getRows(); $row++)
		for ($column=0; $column < $this->getColumns(); $column++)
		{
			$square = $this->getSquare(array($row, $column));
			if ( ! $square->isGameOver() AND ! $square->isRevealed())
			{
				RETURN FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Fills the surrounding squares on all squares within this grid. Use this
	 * function when using addSquare without `$fix_square_surroundings = TRUE`
	 */
	public function fillSurroundingSquares()
	{
		for ($row=0; $row < $this->getRows(); $row++)
			for ($column=0; $column < $this->getColumns(); $column++)
			{
				$position = array($row, $column);

				// Get square first
				$square = $this->getSquare($position);

				// Set surrounding squares to the square
				$square->setSurroundingSquares(
						$this->getSurroundingSquaresByPosition($position)
				);
			}
	}

	/**
	 * Create a new random position
	 *	 *
	 * @param	array  $avoid_position    Position to avoid being generated
	 *
	 * @return  array  position
	 */
	public function createRandomPosition($avoid_position = null)
	{
		if ($avoid_position !== NULL){
			$ax = intval($avoid_position[0]);
			$ay = intval($avoid_position[1]);

			$i = 0;

			while (true){
				$position = $this->createRandomPosition();
				$x = $position[0];
				$y = $position[1];

				$x_d = abs($x - $ax);
				$y_d = abs($y - $ay);

				$mod = ($i >= 50)?0:1;

				$invalid = ($x_d <= $mod) && ($y_d <= $mod);

				if ( ! $invalid){
					return $position;
				}
			}
		} else {
			return array(
				rand(0, $this->getRows() - 1),
				rand(0, $this->getColumns() - 1)
			);
		}
	}
}