<?php
use Minesweeper\Minesweeper;
use Minesweeper\Grid;
use Minesweeper\Arr;

/**
 *  Bootstrap
 */
require '..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

// Start a new session
session_start();

// Get minesweeper factory
$minesweeper = new Minesweeper;

/**
 *  Get grid
 */
// Preset
if ($preset = Arr::get($_POST, 'preset'))
{
	switch ($preset)
	{
		case 2:
			$grid = $minesweeper->buildGrid(16, 16, 40);
		break;
		case 3:
			$grid = $minesweeper->buildGrid(30, 16, 99);
		break;
		default:
			$grid = $minesweeper->buildGrid(8, 8, 10);
	}
}
// Get existing grid from session
elseif (isset($_SESSION['grid']) AND $_SESSION['grid'] AND ! isset($_POST['reset']))
{
	$grid = $_SESSION['grid'];
}
// Custom grid or default grid
else
{
	if(($rows = Arr::get($_POST,    'rows',    8)) > 30)
		$rows = 8;

	if (($columns = Arr::get($_POST, 'columns', 8)) > 16)
		$columns = 8;

	if (($mines = Arr::get($_POST,   'mines',  10)) > 99)
		$mines = 10;

	$grid = $minesweeper->buildGrid($rows, $columns, $mines);
}


// Store grid to session
$_SESSION['grid'] = $grid;


/**
 *  Handle futher interaction
 */
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
	// Square selected and game is not over
	if ($square = Arr::get($_POST, 'square') AND ! $grid->isGameOver())
	{
		// Create position array
		$reveal_position = explode(',', $square);

		if (isset($reveal_position[0]) && isset($reveal_position[1])){

			$x = filter_var($reveal_position[0],FILTER_SANITIZE_NUMBER_INT);
			$y = filter_var($reveal_position[1],FILTER_SANITIZE_NUMBER_INT);

			// Reveal
			try
			{
				$grid->reveal(array($x,$y));
			}
			// Ignore gameover and alreadyrevealed exceptions.
			// Gameover is already checked above
			catch ( Exception $ex) {};

		}



	}

	if ($flag = Arr::get($_POST, 'flag') AND ! $grid->isGameOver()){

		$flag_position = explode(',', $flag);

		$grid->toggleFlag($flag_position);
	}


}


/**
 *  Make template friendly data
 */

$grid_data = array(
	'is_game_over'      => $grid->isGameOver(),
	'number_of_rows'    => $grid->getRows(),
	'number_of_columns' => $grid->getColumns(),
	'number_of_mines'   => $grid->numberOfSquares('Minesweeper\Square\MineSquare'),
	'is_game_over'      => $grid->isGameOver(),
	'is_won_by_player'  => $grid->isWonByPlayer()
);

// Loop grid
foreach ($grid->getGrid() as $row_key => $row)
foreach ($row as $column_key => $square)
{
	// Prepare square data
	$square_data = array(
		// Position
		'row'         => $row_key,
		'column'      => $column_key,

		'is_flagged'  => $square->isFlagged() OR
			($grid->isGameOver() AND $square->isGameOver() and $grid->isWonByPlayer()),

		'is_non_game_over_flagged'
		              => $grid->isGameOver() AND $square->isFlagged() AND !$square->isGameOver(),

		// Square revealed. Game over squares are always revealed at the end
		'is_revealed' => $square->isRevealed() OR
			($grid->isGameOver() AND $square->isGameOver()
				AND ! $grid->isWonByPlayer() AND ! $square->isFlagged()),

		'is_revealed_gameover'
		              => $square->isRevealed() AND $square->isGameOver(),
		'name'        => (string) $square,

		// Show number when there are surrounding squares, it is revealed and
		// the square is not a game over square
		'number'      => ($square->numberOfSurroundingGameOverSquares() > 0 AND
			              $square->isRevealed() AND ! $square->isGameOver()) ?
		                      $square->numberOfSurroundingGameOverSquares() :
			                  NULL,

		// Make square disabled when revealed or the game is over
		'is_disabled' => $square->isFlagged() OR $square->isRevealed()
							OR $grid->isGameOver()
	);

	// Store square data
	$grid_data['rows'][$row_key]['columns'][$column_key] = $square_data;
}


/**
 *  Render
 */
$mustache = new Mustache_Engine;
echo $mustache->render(
	file_get_contents('..'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'index.mustache'),
	array('grid' => $grid_data)
);