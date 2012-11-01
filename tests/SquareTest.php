<?php
use Minesweeper\Square\MineSquare;

class SquareTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test surrounding squares
	 */
	public function testSurroundingSquares()
	{
		$mine = new Minesweeper\Square\MineSquare;

		// Test by adding
		$mine->addSurroundingSquare($mine2 = new Minesweeper\Square\MineSquare);
		$surroundings = $mine->getSurroundingSquares();
		$this->assertEquals($mine2, $surroundings[0]);

		// Test by setting full array
		$mine->setSurroundingSquares(array(
				$mine2
		));
		$this->assertEquals($mine2, $surroundings[0]);
	}

	/**
	 * Test the number of surrounding game over squares
	 */
	public function testNumberOfSurroundingGameOverSquares()
	{
		$square = new Minesweeper\Square\EmptySquare;
		$square->setSurroundingSquares(array(
				new Minesweeper\Square\EmptySquare,
				new Minesweeper\Square\MineSquare,
				new Minesweeper\Square\EmptySquare,
				new Minesweeper\Square\MineSquare,
				new Minesweeper\Square\MineSquare,
		));

		$this->assertEquals(3, $square->numberOfSurroundingGameOverSquares());
	}

	/**
	 * Tests toString version of default squares
	 */
	public function testSquaresConvertedToString()
	{
		$mine = new Minesweeper\Square\MineSquare;
		$this->assertEquals('mine', (string) $mine);

		$empty = new Minesweeper\Square\EmptySquare;
		$this->assertEquals('empty', (string) $empty);
	}

	/**
	 * Test whether the default mine square stops a game
	 */
	public function testMineSquareGameover()
	{
		$mine = new Minesweeper\Square\MineSquare;
		$this->assertTrue($mine->reveal());
	}
}