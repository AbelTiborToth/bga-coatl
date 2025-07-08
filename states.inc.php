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
 * states.inc.php
 *
 * coatl game states description
 */

if (!defined('ST_END_GAME')) { // guard since this included multiple times
	define('ST_GAME_SETUP', 1);

	define("ST_EVERYONE_BUT_FIRST_PLAYER", 2);
	define("ST_DISCARD_PROPHECY", 3);
	define("ST_FIRST_PLAYER", 4);
	define("ST_PLAYER_TURN", 5);
	define("ST_TAKE_PIECE", 6);
	define("ST_TAKE_PROPHECY", 7);
	define("ST_ASSEMBLE", 8);
	define("ST_STEAL", 11);
	define("ST_DISCARD_PROPHECY_SACRIFICE_TOKEN", 9);
	define("ST_TAKE_TEMPLE_SACRIFICE_TOKEN", 10);

	define("ST_NEXT_PLAYER", 98);
	define("ST_END_GAME", 99);
}

$machinestates = array(

	/**
	 * The initial state.
	 *
	 * Part of the BGA framework.
	 * "Please do not modify."
	 */
	ST_GAME_SETUP => array(
		"name" => "gameSetup",
		"description" => "",
		"type" => "manager",
		"action" => "stGameSetup",
		"transitions" => array(
			"" => ST_DISCARD_PROPHECY
		)
	),

	/**
	 * Discard Prophecy Cards state.
	 *
	 * Discard Prophecy cards at the beginning of the game.
	 * The first player doesn't discard any cards.
	 */
	ST_DISCARD_PROPHECY => array(
		"name" => "discardProphecy",
		"description" => clienttranslate('Waiting for other players to discard Prophecy cards from their hands'),
		"descriptionmyturn" => '',
		"type" => "multipleactiveplayer",
		"action" => "stEveryoneButFirstPlayerActive",
		"args" => "argDiscardProphecy",
		"possibleactions" => array(
			"discardProphecy"
		),
		"transitions" => array(
			"firstPlayer" => ST_FIRST_PLAYER,
			'zombiePass' => ST_FIRST_PLAYER
		)
	),

	/**
	 * First player state.
	 *
	 * Set the first player active after card discard preparation.
	 */
	ST_FIRST_PLAYER => array(
		"name" => "firstPlayer",
		"description" => '',
		"type" => "game",
		"action" => "stFirstPlayer",
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN
		)
	),

	ST_PLAYER_TURN => array(
		"name" => "playerTurn",
		"description" => '',
		"description-PieceProphecyAssemble" => clienttranslate('${actplayer} must take Cóatl pieces, choose Prophecy cards or assemble their Cóatl'),
		"description-PieceProphecy" => clienttranslate('${actplayer} must take Cóatl pieces or choose Prophecy cards'),
		"description-PieceAssemble" => clienttranslate('${actplayer} must take Cóatl pieces or assemble their Cóatl'),
		"description-Piece" => clienttranslate('${actplayer} must take Cóatl pieces'),
		"description-ProphecyAssemble" => clienttranslate('${actplayer} must choose Prophecy cards or assemble their Cóatl'),
		"description-Prophecy" => clienttranslate('${actplayer} must choose Prophecy cards'),
		"description-Assemble" => clienttranslate('${actplayer} must assemble their Cóatl'),
		"description-SacrificeToken" => clienttranslate('${actplayer} must use a Sacrifice token'),
		"descriptionmyturn" => '',
		"descriptionmyturn-PieceProphecyAssemble" => clienttranslate('${you} must take Cóatl pieces, choose Prophecy cards or assemble your Cóatl'),
		"descriptionmyturn-PieceProphecy" => clienttranslate('${you} must take Cóatl pieces or choose Prophecy cards'),
		"descriptionmyturn-PieceAssemble" => clienttranslate('${you} must take Cóatl pieces or assemble your Cóatl'),
		"descriptionmyturn-Piece" => clienttranslate('${you} must take Cóatl pieces'),
		"descriptionmyturn-ProphecyAssemble" => clienttranslate('${you} must choose Prophecy cards or assemble your Cóatl'),
		"descriptionmyturn-Prophecy" => clienttranslate('${you} must choose Prophecy cards'),
		"descriptionmyturn-Assemble" => clienttranslate('${you} must assemble your Cóatl'),
		"descriptionmyturn-SacrificeToken" => clienttranslate('${you} must use a Sacrifice token'),
		"type" => "activeplayer",
		"args" => "argPlayerTurn",
		"possibleactions" => array(
			"undo",
			"takePiece",
			"takeProphecy",
			"assemble",
			"steal",
			"usePieceToken",
			"useProphecyToken",
			"useTempleToken",
			"useSacrifice",
			"giveUp"
		),
		"transitions" => array(
			"takePiece" => ST_TAKE_PIECE,
			"takeProphecy" => ST_TAKE_PROPHECY,
			"assemble" => ST_ASSEMBLE,
			"steal" => ST_STEAL,
			"useProphecyToken" => ST_DISCARD_PROPHECY_SACRIFICE_TOKEN,
			"useTempleToken" => ST_TAKE_TEMPLE_SACRIFICE_TOKEN,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER,
			"endGame" => ST_END_GAME
		)
	),

	ST_TAKE_PIECE => array(
		"name" => "takePiece",
		"description" => clienttranslate('${actplayer} must confirm the piece take'),
		"description-Replenish" => clienttranslate('${actplayer} must confirm the piece take and replenish the board'),
		"descriptionmyturn" => clienttranslate('${you} must confirm the piece take'),
		"descriptionmyturn-Replenish" => clienttranslate('${you} must confirm the piece take and replenish the board'),
		"type" => "activeplayer",
		"args" => "argTakePiece",
		"possibleactions" => array(
			"undo",
			"confirmTakePiece"
		),
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	// Card choose
	ST_TAKE_PROPHECY => array(
		"name" => "takeProphecy",
		"description" => clienttranslate('${actplayer} can choose more Prophecy cards'),
		"descriptionmyturn" => clienttranslate('${you} can choose more Prophecy cards'),
		"description-Confirm" => clienttranslate('${actplayer} must confirm the Prophecy card choice'),
		"descriptionmyturn-Confirm" => clienttranslate('${you} must confirm the Prophecy card choice'),
		"description-SacrificeToken" => clienttranslate('${actplayer} can choose Prophecy cards'),
		"descriptionmyturn-SacrificeToken" => clienttranslate('${you} can choose Prophecy cards'),
		"type" => "activeplayer",
		"args" => "argTakeProphecy",
		"possibleactions" => array(
			"undo",
			"restart",
			"takeProphecy",
			"confirmTakeProphecy"
		),
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"takeProphecy" => ST_TAKE_PROPHECY,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	ST_ASSEMBLE => array(
		"name" => "assemble",
		"description" => clienttranslate('${actplayer} can assemble Cóatls'),
		"description-Confirm" => clienttranslate('${actplayer} must confirm the Cóatl assemble'),
		"descriptionmyturn" => clienttranslate('${you} can assemble Cóatls'),
		"descriptionmyturn-Confirm" => clienttranslate('${you} must confirm the Cóatl assemble'),
		"type" => "activeplayer",
		"args" => "argAssemble",
		"possibleactions" => array(
			"cancel",
			"undo",
			"restart",
			"newCoatl",
			"addPiece",
			"addCard",
			"confirmAssemble"
		),
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"assemble" => ST_ASSEMBLE,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	ST_STEAL => array(
		"name" => "steal",
		"description" => clienttranslate('${actplayer} can steal up to 2 body segments from an opponent'),
		"descriptionmyturn" => clienttranslate('${you} can steal up to 2 body segments from an opponent'),
		"type" => "activeplayer",
		"args" => "argSteal",
		"possibleactions" => array(
			"cancel",
			"confirmSteal"
		),
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	ST_DISCARD_PROPHECY_SACRIFICE_TOKEN => array(
		"name" => "discardProphecySacrificeToken",
		"description" => clienttranslate('${actplayer} can discard any Prophecy cards from their hand'),
		"descriptionmyturn" => clienttranslate('${you} can discard any Prophecy cards from your hand'),
		"type" => "activeplayer",
		"args" => "argFinalTurn",
		"possibleactions" => array(
			"discardProphecySacrificeToken"
		),
		"transitions" => array(
			"discardProphecySacrificeToken" => ST_TAKE_PROPHECY,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	ST_TAKE_TEMPLE_SACRIFICE_TOKEN => array(
		"name" => "takeTempleSacrificeToken",
		"description" => clienttranslate('${actplayer} can take a Temple card from the supply to their hand'),
		"descriptionmyturn" => clienttranslate('${you} can take a Temple card from the supply to your hand'),
		"type" => "activeplayer",
		"args" => "argFinalTurn",
		"possibleactions" => array(
			"cancel",
			"takeTempleSacrificeToken"
		),
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"nextPlayer" => ST_NEXT_PLAYER,
			'zombiePass' => ST_NEXT_PLAYER
		)
	),

	// Next player.
	ST_NEXT_PLAYER => array(
		"name" => "nextPlayer",
		"description" => '',
		"type" => "game",
		"action" => "stNextPlayer",
		"updateGameProgression" => true,
		"transitions" => array(
			"playerTurn" => ST_PLAYER_TURN,
			"endGame" => ST_END_GAME
		)
	),

	// Final state.
	// Please do not modify (and do not overload action/args methods).
	ST_END_GAME => array(
		"name" => "gameEnd",
		"description" => clienttranslate("End of game"),
		"type" => "manager",
		"action" => "stGameEnd",
		"args" => "argGameEnd"
	)

);