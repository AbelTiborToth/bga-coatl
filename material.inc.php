<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * coatl game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->pieceTypeAndColor = [
	'head' => [
		'yellow' => clienttranslate("[yellow head]"),
		"red" => clienttranslate("[red head]"),
		"black" => clienttranslate("[black head]"),
		"green" => clienttranslate("[green head]"),
		"blue" => clienttranslate("[blue head]")
	],
	'body' => [
		'yellow' => clienttranslate("[yellow body]"),
		"red" => clienttranslate("[red body]"),
		"black" => clienttranslate("[black body]"),
		"green" => clienttranslate("[green body]"),
		"blue" => clienttranslate("[blue body]"),
		"gold" => clienttranslate("[golden body]")
	],
	'tail' => [
		'yellow' => clienttranslate("[yellow tail]"),
		"red" => clienttranslate("[red tail]"),
		"black" => clienttranslate("[black tail]"),
		"green" => clienttranslate("[green tail]"),
		"blue" => clienttranslate("[blue tail]")
	],
];