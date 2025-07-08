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
 * coatl.game.php
 *
 * This is the main file for the game logic in BGA framework.
 * https://en.doc.boardgamearena.com/Main_game_logic:_Game.php
 */

require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');

require_once('modules/php/Utils.trait.php');
require_once('modules/php/Actions.trait.php');
require_once('modules/php/States.trait.php');
require_once('modules/php/Args.trait.php');

require_once("modules/php/Enums/PieceColor.enum.php");
require_once("modules/php/Enums/PieceType.enum.php");

require_once("modules/php/ActionLog.trait.php");

require_once('modules/php/Pieces/Pieces.trait.php');
require_once('modules/php/Pieces/Piece.class.php');

require_once('modules/php/Prophecies/Prophecies.trait.php');
require_once('modules/php/Prophecies/Prophecy.class.php');

require_once('modules/php/Temples/Temples.trait.php');
require_once('modules/php/Temples/Temple.class.php');

require_once('modules/php/Coatls/Cooatls.trait.php');
require_once('modules/php/Coatls/Cooatl.class.php');

/**
 * Coatl:
 * Class for main game logic, defined in BGA framework
 * https://en.doc.boardgamearena.com/Main_game_logic:_Game.php
 */
class coatl extends Table
{
	// Used traits
	use Utils;
	use Actions;
	use States;
	use Args;
	use ActionLog;

	use Pieces;
	use Prophecies;
	use Temples;
	use Cooatls;

	/**
	 * __construct:
	 * Constructor of the game logic
	 */
	function __construct()
	{
		// Part of the BGA framework. Do not modify.
		parent::__construct();

		// Part of the BGA framework, unused for this game.
		$this->initGameStateLabels([]);

		// Initializing piece, prophecy and temple decks, using the deck component of the BGA framework
		// https://en.doc.boardgamearena.com/Deck
		$this->pieces = $this->getNew("module.common.deck");
		$this->pieces->init("pieces");

		$this->prophecies = $this->getNew("module.common.deck");
		$this->prophecies->init("prophecies");
		$this->prophecies->autoreshuffle = true;

		$this->temples = $this->getNew("module.common.deck");
		$this->temples->init("temples");
	}

	/**
	 * getGameName:
	 * Used for translations and stuff.
	 * Part of the BGA framework. Do not modify.
	 * @return string
	 */
	protected function getGameName(): string
	{
		return "coatl";
	}

	/**
	 * setupNewGame:
	 * This method is called only once, when a new game is launched.
	 * In this method, we setup the game according to the game rules, so that the game is ready to be played.
	 * @param $players : the players around the table
	 * @param array $options : the options for the game (Sacrifice tokens ON/OFF)
	 * @return void
	 */
	protected function setupNewGame($players, $options = array()): void
	{
		// Setup player colors
		$gameinfos = self::getGameinfos();
		$default_colors = $gameinfos['player_colors'];

		// Setup globals for final round information
		$this->globals->set("final_round", 0);
		$this->globals->set("final_turn_no", 0);

		// Setup players
		$sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
		$values = array();
		foreach ($players as $player_id => $player) {
			// Setup player records
			$color = array_shift($default_colors);
			$values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
		}
		// Save player records to database
		$sql .= implode(",", $values);
		self::DbQuery($sql);

		// Set player colors
		self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
		self::reloadPlayersBasicInfos();

		// Setup Cóatl pieces
		if (count($players) == 2) {
			if ($options[107] === "2") $this->setupPieces(14, 4);
			else if ($options[102] === "2") $this->setupPieces(14);
			else $this->setupPieces(24);
		}
		else if (count($players) == 3) {
			if ($options[107] === "2") $this->setupPieces(18, 5);
			else if ($options[102] === "2") $this->setupPieces(18);
			else $this->setupPieces(24);
		}
		else {
			if ($options[107] === "2") $this->setupPieces(24, 6);
			else $this->setupPieces(24);
		}
		// Setup center board with Cóatl pieces
		$this->replenishPieceSupply();

		// Setup Prophecy Cards
		$this->setupProphecies($options[101] === "2");
		// Setup Prophecy Card supply
		$this->refillProphecySupply();

		// Setup Temple Cards
		$this->setupTemples($options[101] === "2");
		// Setup Temple Card supply
		$this->refillTempleSupply();

		// Setup player card hand
		foreach ($players as $player_id => $player) {
			$this->setupPlayerProphecyHand($player_id);
			$this->setupPlayerTempleHand($player_id);
		}

		// If Sacrifice Tokens option is OFF, set all tokens to false, like the players already used them all
		if ($options[100] === "1") {
			self::DbQuery("UPDATE player SET piece_token = FALSE, prophecy_token = FALSE, temple_token = FALSE");
		}
		// If Sacrifice Tokens option is ON, initialize statistics for them
		else {
			self::initStat("player", "piece_sacrifice_used", false);
			self::initStat("player", "prophecy_sacrifice_used", false);
			self::initStat("player", "temple_sacrifice_used", false);
		}

		// Initialize statistics
		self::initStat("table", "turns_number", 0);
		self::initStat("player", "turns_number", 0);
		self::initStat("player", "taken_head_pieces", 0);
		self::initStat("player", "taken_body_pieces", 0);
		self::initStat("player", "taken_tail_pieces", 0);
		self::initStat("player", "taken_prophecy_cards", 0);
		self::initStat("player", "completed_coatls", 0);
		self::initStat("player", "coatl_score_1", 0);
		self::initStat("player", "coatl_score_2", 0);
		self::initStat("player", "coatl_score_3", 0);
		self::initStat("player", "coatl_score_average", 0);
		self::initStat("player", "coatl_score_max", 0);
		self::initStat("player", "coatl_length_average", 0);
		self::initStat("player", "fulfilled_prophecy_cards", 0);
		self::initStat("player", "fulfilled_temple_cards", 0);

		if ($options[107] === "2") {
			self::initStat("player", "gold_used", 0);
			self::initStat("player", "gold_received", 0);
		}

		$this->activeNextPlayer();
	}

	/**
	 * getAllDatas:
	 * Gather all information about current game situation (visible by the current player).
	 * The method is called each time the game interface is displayed to a player, ie:
	 * _ when the game starts
	 * _ when a player refreshes the game page (F5)
	 * self::getCurrentPlayerId() !! We must only return information visible by this player !!
	 * @return array of all gamedata
	 */
	protected function getAllDatas(): array
	{
		$result = array();
		// Get all player information
		$players = self::getCollectionFromDb("SELECT player_id id, player_score score, piece_token, prophecy_token, temple_token FROM player");
		$result['players'] = $players;
		foreach ($players as $player_id => $player) {
			$result['players'][$player_id]["player_no"] = $this->getPlayerNoById($player_id);
			$result['players'][$player_id]["pieces_on_player_board"] = $this->getPiecesOnPlayerBoard($player_id, true);
			$result['players'][$player_id]["prophecy_card_count"] = $this->countPropheciesInPlayerHand($player_id);
			$result['players'][$player_id]["temple_card_count"] = $this->countTemplesInPlayerHand($player_id);
			$result['players'][$player_id]["gold"] = $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location = 'panel' AND card_location_arg = {$player_id}");
		}
		// Get current player secret information
		// (Or nothing for spectators)
		if (!self::isSpectator()) {
			$current_player_id = self::getCurrentPlayerId();
			$current_player_color = self::getPlayerColorById($current_player_id);
			$result['current_player_color'] = $current_player_color;
			$result['prophecy_cards_hand'] = $this->getPropheciesInPlayerHand($current_player_id);
			$result['temple_hand'] = $this->getTemplesInPlayerHand($current_player_id);
		}
		// Get if these are the final rounds of the game
		$result['final_round'] = $this->globals->get("final_round") !== 0;
		// Get remaining pieces in bag count
		$result['piece_counters'] = $this->countPiecesInBags();
		// Get Pieces from supply board
		$result['pieces_on_supply'] = array();
		$pieces_on_supply = $this->getPiecesOnSupply();
		foreach ($pieces_on_supply as $type => $pieces) {
			$result['pieces_on_supply'][$type] = array();
			foreach ($pieces as $location_arg => $piece) {
				$result['pieces_on_supply'][$type][$location_arg] = $piece;
			}
		}
		// Get Prophecy cards from Prophecy card supply
		$result['card_supply'] = $this->getPropheciesInSupply();
		$result['temple_supply'] = $this->getTemplesInSupply();
		// Get Cóatls
		$coatls = $this->getCoatls();
		foreach ($coatls as $coatl) {
			$result['coatls'][$coatl->id] = $coatl->getAllData();
		}
		// Get cancelled move ids for game log
		$result['cancelMoveIds'] = array_map('intval', self::getObjectListFromDb("SELECT `gamelog_move_id` FROM gamelog WHERE `cancel` = 1 ORDER BY 1", true));
		// Return the results
		return $result;
	}

	/**
	 * getGameProgression:
	 * Function to compute and return the current game progression
	 * Part of the BGA framework. This method is called each time we are in a game state with the "updateGameProgression" property set to true
	 * Calculated with the pieces on the player boards and pieces built into Cóatls
	 * @return int between 0 (=the game just started) and 100 (= the game is finished or almost finished)
	 */
	function getGameProgression(): int
	{
		$players = self::getCollectionFromDb("SELECT player_id id FROM player");
		$best_progression = 0;
		// Count a progression for each player
		foreach ($players as $player_id => $player) {
			$player_progression = 0;
			$coatls = $this->getCoatlsOfPlayer($player_id);
			$finished_coatls = $this->countFinishedCoatls($coatls);
			// For each completed Cóatl count 33%
			if ($finished_coatls === 1) $player_progression = 33;
			else if ($finished_coatls === 2) $player_progression = 66;
			// When 3 Cóatl is completed, we are in the final turns
			else if ($finished_coatls === 3) return 100;
			foreach ($coatls as $coatl) {
				if (!$coatl->is_locked) {
					$count = count($coatl->pieces);
					// For each incomplete Cóatl we count each placed body piece as 2%
					// Or 26% if the Cóatl have 13 or more body pieces (suggesting this as the highes body piece count for an incomplete Cóatl)
					$player_progression += min($count * 2, 26);
					// For each incomplete Cóatl we count placed head/tail pieces as 3%
					if ($coatl->has_head || $coatl->has_tail) $player_progression += 3;
				}
			}
			// For each pieces on the player's board count 0.5%
			$player_progression += $this->countPiecesOnPlayerBoard($player_id, false) / 2;
			// If the progression is better than the current best, we override the best
			if ($player_progression >= $best_progression) $best_progression = $player_progression;
		}
		// Return the best progression
		return $best_progression;
	}

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

	/**
	 * zombieTurn:
	 * Part of the BGA framework. Skipping turns for players who left the game
	 * https://en.doc.boardgamearena.com/Main_game_logic:_Game.php#Zombie_mode
	 * @param array $state
	 * @param int $active_player
	 * @return void
	 */
	function zombieTurn(array $state, int $active_player): void
	{
		$statename = $state['name'];
		if ($state['type'] === "activeplayer") {
			$this->gamestate->nextState("zombiePass");
			return;
		}
		if ($state['type'] === "multipleactiveplayer") {
			$this->gamestate->setPlayerNonMultiactive($active_player, "zombiePass");
			return;
		}
		throw new feException("Zombie mode not supported at this game state: " . $statename);
	}

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

	/**
	 * upgradeTableDb:
	 * Part of the BGA framework. Used for debugging between versions
	 * https://en.doc.boardgamearena.com/Post-release_phase#Updating_the_database_schema
	 * @param $from_version
	 * @return void
	 */
	function upgradeTableDb($from_version)
	{

	}
}
