<?php
namespace Minesweeper;

class Grid {

	/**
	 * @var  array   array containing the rows and columns (e.g. [8][8])
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
	 * Construct Grid with a grid size.
	 *
	 * @param  int  $rows
	 * @param  int  $columns
	 */
	public function __construct($rows=8, $columns=8)
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

		// Prepare grid array
		for ($row=0; $row < $rows; $row++)
		for ($column=0; $column < $columns; $column++)
		{
			$this->grid[$row][$column] = NULL;
		}

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
			$this->addSquare(new Square\EmptySquare, array($row, $column), FALSE);
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
	 *
	 * @throws  Exception\InvalidPositionException
	 *
	 * @return  int  position
	 */
	public function addSquare(Square\Square $square,
	                          array $position = NULL,
	                          $fix_square_surroundings=TRUE)
	{
		// Use given position
		if ($position)
		{
			if ( ! $this->isValidPosition($position))
			{
				throw new Exception\InvalidPositionException;
			}

		}
		// Create random position
		else
		{
			$position = $this->createRandomPosition();

			// All places already filled randomly?
			$random_full = sizeof($this->occupied_random_positions) ===
			                   $this->numberOfSquares();

			// Not everything filled randomly. Make sure the random position does
			// not fill a previous random position
			while (in_array($position, $this->occupied_random_positions) AND ! $random_full)
			{
				$position = $this->createRandomPosition();
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
	 * @return Square
	 */
	public function getSquare(array $position)
	{
		if( ! $this->isValidPosition($position))
		{
			throw new Exception\InvalidPositionException;
		}

		return $this->grid[$position[0]][$position[1]];
	}


	/**
	 * Reveal position
	 *
	 * @throws  Exception\GameOverException
	 * @throws  Exception\InvalidPositionException
	 * @throws  Exception\SquareAlreadyRevealedException
	 *
	 * @return  boolean  game over
	 */
	public function reveal(array $position)
	{
		// Game over
		if ($this->isGameOver())
		{
			throw new Exception\GameOverException;
		}

		// Not a valid position
		if ( ! $this->isValidPosition($position))
		{
			throw new Exception\InvalidPositionException;
		}

		// Get square
		$square = $this->grid[$position[0]][$position[1]];

		// Already revealed
		if ($square->isRevealed())
		{
			throw new Exception\SquareAlreadyRevealedException;
		}

		// Let the square reveal
		$this->setGameOver($square->reveal());

		// Not gameover and all revealed
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
	 * @param Square\Square $square
	 */
	public function getPositionBySquare(Square\Square $square)
	{
		for ($row=0; $row < $this->getRows(); $row++)
		for ($column=0; $column < $this->getColumns(); $column++)
		{
			if ($this->getSquare(array($row, $column)) === $square)
			{
				return array($row, $column);
			}
		}
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
	 * @throws  Exception\InvalidPositionException
	 *
	 * @return  array  position
	 */
	public function getSurroundingSquaresByPosition(array $position)
	{
		// Not a valid position
		if ( ! $this->isValidPosition($position))
		{
			throw new Exception\InvalidPositionException;
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
	 *
	 * @return  array  position
	 */
	public function createRandomPosition()
	{
		return array(
				rand(0, $this->getRows() - 1),
				rand(0, $this->getColumns() - 1)
		);
	}
}