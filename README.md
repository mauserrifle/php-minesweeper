# PHP-Minesweeper

This is an object-oriented PHP implementation of the game Minesweeper. I made 
this to practice test-driven development. So this minesweeper implementation is 
fully unit tested.

Because of its OOP nature, it's easy to add custom squares instead of mines.

I hope anyone finds it useful.

![PHP-Minesweeper screenshot](https://raw.github.com/mauserrifle/php-minesweeper/master/screenshot.png "PHP-Minesweeper screenshot")

## Requirements

* PHP 5.3

## Installation details

Download the files or clone this project.

    git clone https://github.com/mauserrifle/php-minesweeper.git

Get all dependencies through composer:

    ./composer.phar update

## Running unittests

    ./vendor/bin/phpunit --colors tests


This should output:

    ......................

    Time: 0 seconds, Memory: 4.00Mb

    OK (22 tests, 175 assertions)

## Playing

PHP 5.4 is easy and fast:

    cd public
    php -S localhost:8000

Open <http://localhost:8000> in your browser.

## Demo

I hope to put it online as a demo soon.
