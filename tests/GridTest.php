<?php
use Minesweeper\Square\MineSquare;

class GridTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test grid size
	 */
	public function testGridSize()
	{
		$grid = new Minesweeper\Grid(8, 16);
		$this->assertSame(8, $grid->getRows());
		$this->assertSame(16, $grid->getColumns());
	}

	/**
	 * Test negative grid size
	 */
	public function testNegativeGridSize()
	{
		$grid = new Minesweeper\Grid(-5, -8);
		$this->assertSame(8, $grid->getRows());
		$this->assertSame(8, $grid->getColumns());
	}

	/**
	 * Test whether a position is valid
	 */
	public function testValidPosition()
	{
		$grid = new Minesweeper\Grid($rows = 8, $columns = 8);

		for($row=0; $row < $rows; $row++)
		for($column=0; $column < $columns; $column++)
		{
			$this->assertTrue($grid->isValidPosition(array($row,$column)));
		}
	}

	/**
	 * Test whether a position is invalid
	 */
	public function testInvalidPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$this->assertFalse($grid->isValidPosition(array(8, 8)));
	}

	/**
	 * Test adding a mine square to a fixed position
	 */
	public function testAddBombToFixedPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);

		$fixed_position = array(1, 2);
		$position = $grid->addSquare(
				$mine = new Minesweeper\Square\MineSquare,
				$fixed_position
		);

		// Same position returned by addSquare
		$this->assertSame($fixed_position, $position);

		// Position actually contains the given square
		$this->assertSame($mine, $grid->getSquare($position));
	}

	/**
	 * Test adding a mine square to a random position
	 */
	public function testAddBombToRandomPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$position = $grid->addSquare($mine = new Minesweeper\Square\MineSquare);
		$this->assertSame($mine, $grid->getSquare($position));
	}

	/**
	 * Test exception on adding a square to a invalid position
	 *
	 * @expectedException Minesweeper\Exception\InvalidPositionException
	 */
	public function testAddingSquareToIncorrectPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$position = $grid->addSquare(
			$mine = new Minesweeper\Square\MineSquare,
			array(7, 8)
		);
	}

	/**
	 * Test exception on getting a square from invalid position
	 *
	 * @expectedException Minesweeper\Exception\InvalidPositionException
	 */
	public function testGettingSquareFromIncorrectPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$square = $grid->getSquare(array(7, 8));
	}

	/**
	 * Test whether all squares are a instance of EmptySquare after a reset
	 */
	public function testResetGrid()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add mine
		$position = $grid->addSquare(new Minesweeper\Square\MineSquare);

		// Reset grid to empty squares
		$grid->reset();

		// Expect empty square
		$this->assertInstanceOf(
			'Minesweeper\Square\EmptySquare',
			$grid->getSquare($position)
		);
	}

	/**
	 * Test game over setting
	 */
	public function testManuallySettingGameOver()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Not game over
		$this->assertFalse($grid->isGameOver());

		// Game over
		$grid->setGameOver(TRUE);
		$this->assertTrue($grid->isGameOver());
	}

	/**
	 * Test game over with a mine
	 *
	 * @expectedException Minesweeper\Exception\GameOverException
	 */
	public function testGameOverWithMine()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add mine
		$position = $grid->addSquare(new Minesweeper\Square\MineSquare);

		// Reveal mine
		$this->assertTrue($grid->reveal($position));

		// Check again
		$this->assertTrue($grid->isGameOver());

		// Reveal again to test exception
		$grid->reveal($position);
	}

	/**
	 * Test exception on already revealed field
	 *
	 * @expectedException Minesweeper\Exception\SquareAlreadyRevealedException
	 */
	public function testAlreadyRevealed()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add some mines so the game is not directly over after reveal
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 0));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 2));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 4));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 6));

		// Reveal some position twice
		$grid->reveal(array(2,5));
		$grid->reveal(array(2,5));
	}

	/**
	 * Test getting position by square
	 */
	public function testPositionBySquare()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$mine = new Minesweeper\Square\MineSquare;

		$position = $grid->addSquare($mine);

		$this->assertSame($position, $grid->getPositionBySquare($mine));
	}

	/**
	 * Test getting the surrounding squares
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
	 */
	public function testSurroundingSquares()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Get surrounding squares by position
		$squares = $grid->getSurroundingSquaresByPosition(array(2, 3));

		// Expected position squares
		$expected_squares = array (
			array(1, 2),
			array(1, 3),
			array(1, 4),
			array(2, 4),
			array(3, 4),
			array(3, 3),
			array(3, 2),
			array(2, 2),
		);

		$this->assertSame($grid->getSquare(array(1, 2)), $squares[0]);

		// Test them
		foreach ($expected_squares as $key => $position)
		{
			$this->assertSame($grid->getSquare($position), $squares[$key]);
		}


		// Add a new square
		$new_square = new Minesweeper\Square\MineSquare;
		$position = $grid->addSquare($new_square, array(1, 2));

		// Reget surrounding squares by position
		$squares2 = $grid->getSurroundingSquaresByPosition(array(2, 3));

		// Test again
		foreach ($expected_squares as $key => $position)
		{
			$this->assertSame($grid->getSquare($position), $squares2[$key]);
		}
	}

	/**
	 * Test whether empty surrounding squares get revealed automaticly.
	 *
	 * Below is the test field. <*> are empty fields, <x> is field with mines.
	 * When 2,4 is clicked, everything from row 0 up to (including) 4 should
	 * be revealed.
	 *
	 *       0 1 2 3 4 5 6 7
	 *    0  * * * * * * * *
	 *    1  * * * * * * * *
	 *    2  * * * * * * * *
	 *    3  * * * * * * * *
	 *    4  * * * * * * * *
	 *    5  x * x * x * x *
	 *    6  * * * * * * * *
	 *    7  * * * * * * * *
	 */
	public function testRevealingEmptySurroundingSquares()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add a horizontal line of mines
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 0));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 2));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 4));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 6));

		// Reaveal a empty square
		$grid->reveal(array(2, 4));

		// Check all surrounding squares that should be revealed too
		for ($row=0; $row <= 4; $row++)
		for ($column=0; $column <= 7; $column++)
		{
			// Get square
			$square = $grid->getSquare(array($row, $column));

			// Should be revealed
			$this->assertTrue($square->isRevealed());
		}

		// Check all squares that should not be revealed
		for ($row=5; $row <= 7; $row++)
		for ($column=0; $column <= 7; $column++)
		{
			// Get square
			$square = $grid->getSquare(array($row, $column));

			// Should not be revealed
			$this->assertFalse($square->isRevealed());
		}
	}

	/**
	 * Test getting the number of squares of some type
	 */
	public function testNumberOfSquareTypes()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add 19 mines
		for($i=0; $i < 19; $i++)
		{
			$grid->addSquare(new Minesweeper\Square\MineSquare);
		}

		$this->assertSame(19, $grid->numberOfSquares('Minesweeper\Square\MineSquare'));
	}

	/**
	 * Test creating a random position
	 */
	public function testCreatingRandomPosition()
	{
		$grid = new Minesweeper\Grid(8, 8);
		$random = $grid->createRandomPosition();
		$this->assertTrue($grid->isValidPosition($random));
	}

	/**
	 * Test game over with empty squares
	 */
	public function testGameOverWithEmptySquare()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Reveal some position and game over return value
		$this->assertTrue($grid->reveal(array(2,5)));

		// Check game over again
		$this->assertTrue($grid->isGameOver());
	}

	/**
	 * Test whether the player succesfully wins the game
	 *
	 * Below is the test field. <*> are empty fields, <x> is field with mines.
	 * When 2,4 is clicked, everything from row 0 up to (including) 4 should
	 * be revealed.
	 *
	 *       0 1 2 3 4 5 6 7
	 *    0  * * * * * * * *
	 *    1  * * * * * * * *
	 *    2  * * * * * * * *
	 *    3  * * * * * * * *
	 *    4  * * * * * * * *
	 *    5  x * x * x * x *
	 *    6  * * * * * * * *
	 *    7  * * * * * * * *
	 */
	public function testWinningGameAndAllRevealed()
	{
		$grid = new Minesweeper\Grid(8, 8);

		// Add a horizontal line of mines
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 0));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 2));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 4));
		$grid->addSquare(new Minesweeper\Square\MineSquare, array(5, 6));

		// Reaveal a empty square
		$grid->reveal(array(2, 4));

		// Game not yet won
		$this->assertFalse($grid->isWonByPlayer());

		// Reveal some more
		$grid->reveal(array(5, 1));
		$grid->reveal(array(5, 3));
		$grid->reveal(array(5, 5));
		$grid->reveal(array(5, 7));

		// Game not yet won
		$this->assertFalse($grid->isWonByPlayer());

		// Reveal remaining squares row 7
		$grid->reveal(array(7, 0));

		// Game won
		$this->assertTrue($grid->isWonByPlayer());

		// All revealed
		$this->assertTrue($grid->allRevealed());
	}
}