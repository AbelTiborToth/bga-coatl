<?php

use Enums\PieceColor;
use Enums\PieceType;

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * coatl.action.php
 *
 * This is the file for main action entry point in BGA framework
 * https://en.doc.boardgamearena.com/Players_actions:_yourgamename.action.php
 */
class action_coatl extends APP_GameAction
{
	/**
	 * __default:
	 * Part of the BGA framework. "Please do not modify"
	 * @return void
	 */
	public function __default()
	{
		if (self::isArg('notifwindow')) {
			$this->view = "common_notifwindow";
			$this->viewArgs['table'] = self::getArg("table", AT_posint, true);
		}
		else {
			$this->view = "coatl_coatl";
			self::trace("Complete reinitialization of board game");
		}
	}

	/**
	 * actDiscardProphecy:
	 * Action to discard Prophecy cards from hand
	 *  (Used at the game start, to discard cards from players)
	 * @return void
	 */
	public function actDiscardProphecy(): void
	{
		self::setAjaxMode();
		$prophecies_raw = self::getArg("prophecy_cards", AT_alphanum, true); // getting cards from args
		$prophecy_ids = explode(' ', trim($prophecies_raw));
		$this->game->actDiscardProphecy($this->game->getProphecies($prophecy_ids));
		self::ajaxResponse();
	}

	/**
	 * actTakePiece:
	 * Action to take Cóatl pieces from the supply board
	 * @return void
	 */
	public function actTakePiece(): void
	{
		self::setAjaxMode();
		$piece_type = self::getArg("piece_type", AT_alphanum_dash, true); // getting Cóatl piece type from args
		$supply_location = self::getArg("board_space", AT_posint, true); // getting board space from args
		$this->game->actTakePiece(PieceType::stringToEnum($piece_type), $supply_location);
		self::ajaxResponse();
	}

	/**
	 * actConfirmTakePiece:
	 * Action to confirm Cóatl pieces taking
	 * @return void
	 */
	public function actConfirmTakePiece(): void
	{
		self::setAjaxMode();
		$this->game->actConfirmTakePiece();
		self::ajaxResponse();
	}


	/**
	 * actTakeProphecy:
	 * Action to take Prophecy card from supply
	 * @return void
	 */
	public function actTakeProphecy(): void
	{
		self::setAjaxMode();
		$card_id = self::getArg("card_id", AT_posint, true); // getting card id from args
		$this->game->actTakeProphecy($this->game->getProphecy($card_id));
		self::ajaxResponse();
	}

	/**
	 * actTakeProphecyFromDeck:
	 * Action to take Prophecy from deck
	 * @return void
	 */
	public function actTakeProphecyFromDeck(): void
	{
		self::setAjaxMode();
		$this->game->actTakeProphecyFromDeck();
		self::ajaxResponse();
	}

	/**
	 * actConfirmTakeProphecy:
	 * Action to confirm Prophecy choosing
	 * @return void
	 */
	public function actConfirmTakeProphecy(): void
	{
		self::setAjaxMode();
		$this->game->actConfirmTakeProphecy();
		self::ajaxResponse();
	}

	/**
	 * actAssemble:
	 * Action to start Assembling Cóatl
	 * @return void
	 */
	public function actAssemble(): void
	{
		self::setAjaxMode();
		$this->game->actAssemble();
		self::ajaxResponse();
	}

	public function actSteal(): void
	{
		self::setAjaxMode();
		$this->game->actSteal();
		self::ajaxResponse();
	}

	public function actConfirmSteal(): void
	{
		self::setAjaxMode();
		$opponent = self::getArg("opponent", AT_alphanum, true);
		$cell_ids_raw = self::getArg("cell_ids", AT_alphanum, true);
		$cell_ids = explode(' ', trim($cell_ids_raw));
		$this->game->dump("CELL-IDS", $cell_ids);
		if (count($cell_ids) === 1) {
			$piece_1 = $this->game->getPieceOnPlayerBoard($opponent, $cell_ids[0]);
			$this->game->actConfirmSteal($opponent, $piece_1);
		}
		else {
			$piece_1 = $this->game->getPieceOnPlayerBoard($opponent, $cell_ids[0]);
			$piece_2 = $this->game->getPieceOnPlayerBoard($opponent, $cell_ids[1]);
			$this->game->actConfirmSteal($opponent, $piece_1, $piece_2);
		}
		self::ajaxResponse();
	}

	/**
	 * newCoatl:
	 * Action to create new Cóatl by putting down a piece
	 * @return void
	 */
	public function actNewCoatl(): void
	{
		self::setAjaxMode();
		$piece_location_arg = self::getArg("piece", AT_posint, true); // getting piece location_arg from args
		$this->game->actNewCoatl($piece_location_arg);
		self::ajaxResponse();
	}

	/**
	 * actAddPiece:
	 * Action to add a Piece to a Cóatl
	 * @return void
	 */
	public function actAddPiece(): void
	{
		self::setAjaxMode();
		$coatl_id = self::getArg("coatl_id", AT_posint, true);
		$to_left = self::getArg("to_left", AT_bool, true);
		$piece = self::getArg("piece", AT_posint, true);
		$this->game->actAddPiece($coatl_id, $to_left, $piece);
		self::ajaxResponse();
	}

	/**
	 * actAddProphecy:
	 * Action to add a Prophecy to a Cóatl
	 * @return void
	 */
	public function actAddProphecy(): void
	{
		self::setAjaxMode();
		$coatl_id = self::getArg("coatl_id", AT_posint, true);
		$prophecy_id = self::getArg("prophecy", AT_posint, true);
		$this->game->actAddProphecy($this->game->getCoatl($coatl_id), $this->game->getProphecy($prophecy_id));
		self::ajaxResponse();
	}

	/**
	 * actAddProphecy:
	 * Action to add a Temple to a Cóatl
	 * @return void
	 */
	public function actAddTemple(): void
	{
		self::setAjaxMode();
		$coatl_id = self::getArg("coatl_id", AT_posint, true);
		$temple_id = self::getArg("temple", AT_posint, true);
		$this->game->actAddTemple($this->game->getCoatl($coatl_id), $this->game->getTemple($temple_id));
		self::ajaxResponse();
	}

	/**
	 * confirmAssemble:
	 * Action to confirm and end assemble
	 * @return void
	 */
	public function actConfirmAssemble(): void
	{
		self::setAjaxMode();
		$this->game->actConfirmAssemble();
		self::ajaxResponse();
	}

	/**
	 * actUsePieceToken:
	 * Action to use Piece Sacrifice Token
	 * @return void
	 */
	public function actUsePieceToken(): void
	{
		self::setAjaxMode();
		$piece_type = self::getArg("piece_type", AT_alphanum_dash, true); // getting Cóatl piece type from args
		$piece_colors_raw = self::getArg("piece_colors", AT_alphanum, true); // getting cards from args
		$piece_colors = array_map(function ($piece_color) {
			return PieceColor::stringToEnum($piece_color);
		}, explode(' ', trim($piece_colors_raw)));
		$this->game->actUsePieceToken(PieceType::stringToEnum($piece_type), $piece_colors);
		self::ajaxResponse();
	}

	/**
	 * actUseProphecyToken:
	 * Action to use Prophecy Sacrifice Token
	 * @return void
	 */
	public function actUseProphecyToken(): void
	{
		self::setAjaxMode();
		$this->game->actUseProphecyToken();
		self::ajaxResponse();
	}

	/**
	 * actDiscardProphecySacrificeToken
	 * Action to use Prophecy Sacrifice Token
	 * @return void
	 */
	public function actDiscardProphecySacrificeToken(): void
	{
		self::setAjaxMode();
		$prophecies_raw = self::getArg("prophecy_cards", AT_alphanum, true);
		$prophecies_raw = trim($prophecies_raw);
		if ($prophecies_raw == "") $this->game->actDiscardProphecySacrificeToken(array());
		else {
			$prophecy_ids = explode(' ', $prophecies_raw);
			$this->game->actDiscardProphecySacrificeToken($this->game->getProphecies($prophecy_ids));
		}
		self::ajaxResponse();
	}

	/**
	 * actUseTempleToken
	 * Action to use Temple Sacrifice Token
	 * @return void
	 */
	public function actUseTempleToken(): void
	{
		self::setAjaxMode();
		$this->game->actUseTempleToken();
		self::ajaxResponse();
	}

	/**
	 * actUseTempleToken
	 * Action to use Temple Sacrifice Token
	 * @return void
	 */
	public function actTakeTempleSacrificeToken(): void
	{
		self::setAjaxMode();
		$temple_id = self::getArg("card_id", AT_posint, true);
		$this->game->actTakeTempleSacrificeToken($this->game->getTemple($temple_id));
		self::ajaxResponse();
	}

	/**
	 * undo:
	 * Action to undo last action
	 * @return void
	 */
	public function actUndo(): void
	{
		self::setAjaxMode();
		$this->game->actUndo();
		self::ajaxResponse();
	}

	/**
	 * restart:
	 * Action to undo all action
	 * @return void
	 */
	public function actRestart(): void
	{
		self::setAjaxMode();
		$this->game->actRestart();
		self::ajaxResponse();
	}

	/**
	 * cancel:
	 * Action to cancel started action
	 * @return void
	 */
	public function actCancel(): void
	{
		self::setAjaxMode();
		$this->game->actCancel();
		self::ajaxResponse();
	}

	public function actGiveUp(): void
	{
		self::setAjaxMode();
		$this->game->actGiveUp();
		self::ajaxResponse();
	}
}

