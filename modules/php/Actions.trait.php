<?php /** @noinspection ALL */

use Enums\PieceColor;
use Enums\PieceType;

/**
 * Actions:
 * Trait for isolating Game Actions related methods in the main game logic
 *
 * Each time a player is doing some game action, one of the methods below is called.
 * (note: each method below must match an input method in coatl.action.php)
 */
trait Actions
{
	/**
	 * actDiscardProphecy:
	 * Action method to discard Prophecy cards from hand
	 * @param Prophecy[] $prophecies to discard
	 * @return void
	 */
	function actDiscardProphecy(array $prophecies): void
	{
		self::checkAction('discardProphecy');
		$player_id = self::getCurrentPlayerId();
		$discard_count = intval($this->getPlayerNoById($player_id)) - 1;
		if (count($prophecies) !== $discard_count) throw new BgaVisibleSystemException(totranslate("actions.actDiscardProphecy: Not enough cards selected"));
		foreach ($prophecies as $prophecy) if ($prophecy->location_arg !== intval($player_id)) throw new BgaVisibleSystemException(totranslate("actions.actDiscardProphecy: Impossible card discard"));
		$this->discardProphecies($prophecies);
		if ($discard_count === 1) {
			self::notifyAllPlayers("discardCards", clienttranslate('${player_name} discards 1 Prophecy card'), array(
				'player_name' => self::getPlayerNameById($player_id),
				'player_id' => $player_id,
				'n' => 1
			));
		}
		else {
			self::notifyAllPlayers("discardCards", clienttranslate('${player_name} discards ${n} Prophecy cards'), array(
				'player_name' => self::getPlayerNameById($player_id),
				'player_id' => $player_id,
				'n' => $discard_count
			));
		}
		self::notifyPlayer($player_id, "discardCards_private", '', array(
			'prophecy_ids' => array_map(function ($prophecy) {
				return $prophecy->id;
			}, $prophecies)
		));
		$this->gamestate->setPlayerNonMultiactive($player_id, 'firstPlayer');
	}

	/**
	 * actTakePiece:
	 * Action method to take Cóatl pieces from the supply board
	 * @param PieceType $piece_type type of the cóatl pieces
	 * @param int $supply_location : the supply location of the pieces
	 * @return void
	 */
	function actTakePiece(PieceType $piece_type, int $supply_location): void
	{
		self::checkAction('takePiece');
		$player_id = self::getActivePlayerId();
		$pieces_count = $this->countPiecesOnPlayerBoard($player_id, true);
		if ($pieces_count === 8 || ($pieces_count === 7 && $piece_type === PieceType::Body)) throw new BgaVisibleSystemException("Undo.takePiece: This action isn't possible now");
		$player_board_locations = array_slice($this->getFreeLocationsOnPlayerBoard($player_id), 0, ($piece_type == PieceType::Body ? 2 : 1));
		$piece_ids = $this->takePieceToPlayerBoard($piece_type, $supply_location, $player_id, $player_board_locations);
		$action_args = array(
			"piece_type" => $piece_type->value,
			"piece_ids" => $piece_ids,
			"player_board_locations" => $player_board_locations,
			"supply_location" => $supply_location,
		);
		$this->saveAction("takePiece", $player_id, $action_args);
		if ($piece_type === PieceType::Body && count($piece_ids) === 2) {
			$piece_1 = $this->getPiece($piece_ids[0]);
			$piece_2 = $this->getPiece($piece_ids[1]);
			self::notifyAllPlayers("takePiece", clienttranslate('${player_name} takes Cóatl pieces ${piece_1}${piece_2}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'piece_type' => $piece_type,
				'piece_ids' => $piece_ids,
				'i18n' => [
					'piece_1',
					'piece_2'
				],
				'colors' => [
					$piece_1->color->value,
					$piece_2->color->value
				],
				'piece_1' => $this->pieceTypeAndColor[$piece_1->type->value][$piece_1->color->value],
				'piece_2' => $this->pieceTypeAndColor[$piece_2->type->value][$piece_2->color->value],
				'player_board_locations' => $player_board_locations,
				'supply_location' => $supply_location
			));
		}
		else {
			$piece = $this->getPiece($piece_ids[0]);
			self::notifyAllPlayers("takePiece", clienttranslate('${player_name} takes a Cóatl piece ${piece}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'piece_type' => $piece_type,
				'piece_ids' => $piece_ids,
				'i18n' => [
					'piece'
				],
				'colors' => [
					$piece->color->value,
				],
				'piece' => $this->pieceTypeAndColor[$piece->type->value][$piece->color->value],
				'player_board_locations' => $player_board_locations,
				'supply_location' => $supply_location
			));
		}
		self::incStat($piece_type == PieceType::Body ? 2 : 1, "taken_" . $piece_type->value . "_pieces", $player_id);
		$this->gamestate->nextState('takePiece');
	}

	/**
	 * actConfirmTakePiece:
	 * Action method to confirm Cóatl pieces taking
	 * @return void
	 */
	function actConfirmTakePiece(): void
	{
		self::checkAction('confirmTakePiece');
		if ($this->isBoardReplenishNeeded()) {
			$replenish_happened = $this->replenishPieceSupply();
			if ($replenish_happened) {
				self::notifyAllPlayers("replenishPieceSupply", clienttranslate('${player_name} replenishes the Supply board'), array(
					'player_name' => self::getActivePlayerName(),
					'pieces_on_supply' => $this->getPiecesOnSupply(),
					'piece_counts' => $this->countPiecesInBags()
				));
			}
		}
		$this->gamestate->nextState('nextPlayer');
	}

	/**
	 * actTakeProphecy:
	 * Action method to take Prophecy from supply
	 * @param Prophecy $prophecy : the Prophecy to take
	 * @return void
	 */
	function actTakeProphecy(Prophecy $prophecy): void
	{
		self::checkAction('takeProphecy');
		$player_id = self::getActivePlayerId();
		$prophecy_count = $this->countPropheciesInPlayerHand($player_id);
		if (!$this->isTakeProphecyPossible($prophecy_count)) throw new BgaVisibleSystemException("Actions.actTakeProphecy: This action isn't possible now");
		if ($prophecy->location !== "supply") throw new BgaVisibleSystemException("Actions.actTakeProphecy: This Prophecy is not in the supply");
		$this->takeProphecy($player_id, $prophecy->id);
		$action_args = array(
			'card_id' => $prophecy->id,
			'card_type' => $prophecy->type
		);
		$this->saveAction("takeProphecy", $player_id, $action_args);
		self::notifyAllPlayers("takeProphecy", clienttranslate('${player_name} chooses a Prophecy card from the supply ${prophecy}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'fromDeck' => false,
			'card_type' => $prophecy->type,
			'card_id' => $prophecy->id,
			'i18n' => [
				'prophecy'
			],
			'prophecy' => "[" . $prophecy->type . "]"
		));
		self::incStat(1, "taken_prophecy_cards", $player_id);
		$this->gamestate->nextState('takeProphecy');
	}

	/**
	 * actTakeProphecyFromDeck:
	 * Action method to take Prophecy from deck
	 * @return void
	 */
	function actTakeProphecyFromDeck(): void
	{
		self::checkAction('takeProphecy');
		$player_id = self::getActivePlayerId();
		$prophecy_count = $this->countPropheciesInPlayerHand($player_id);
		if (!$this->isTakeProphecyPossible($prophecy_count)) throw new BgaVisibleSystemException("Undo.takeProphecyFromDeck: This action isn't possible now");
		$this->clearLog();
		$this->saveAction("takeProphecyFromDeck", $player_id);
		$new_card = $this->takeProphecyFromDeck($player_id);
		self::notifyAllPlayers("takeProphecy", clienttranslate('${player_name} draws a Prophecy card from the deck'), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'fromDeck' => true,
			'card_type' => $new_card["type_arg"],
			'card_id' => $new_card["id"]
		));
		self::notifyPlayer($player_id, "takeProphecy_private", '', array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName(),
			'card_type' => $new_card["type_arg"],
			'card_id' => $new_card["id"]
		));
		self::incStat(1, "taken_prophecy_cards", $player_id);
		if ($this->countPropheciesInPlayerHand($player_id) == 5) {
			$new_cards = $this->refillProphecySupply();
			if ($new_cards) {
				self::notifyAllPlayers("refillProphecySupply", clienttranslate('${player_name} reveals new Prophecy cards to the supply'), array(
					'player_name' => self::getActivePlayerName(),
					'new_cards' => $new_cards
				));
			}
			$this->gamestate->nextState('nextPlayer');
		}
		else {
			$this->gamestate->nextState('takeProphecy');
		}
	}

	/**
	 * actConfirmTakeProphecy:
	 * Action method to confirm Prophecy choosing
	 * @return void
	 */
	function actConfirmTakeProphecy(): void
	{
		self::checkAction('confirmTakeProphecy');
		$new_cards = $this->refillProphecySupply();
		if ($new_cards) {
			self::notifyAllPlayers("refillProphecySupply", clienttranslate('${player_name} reveals new Prophecy cards to the supply'), array(
				'player_name' => self::getActivePlayerName(),
				'new_cards' => $new_cards
			));
		}
		$this->gamestate->nextState('nextPlayer');
	}

	/**
	 * actAssemble:
	 * Action method to start assembling Cóatl
	 * @return void
	 */
	function actAssemble(): void
	{
		self::checkAction('assemble');
		$player_id = self::getActivePlayerId();
		if (!$this->isAssemblePossible($player_id)) throw new BgaVisibleSystemException("Actions.actAssemble: This action isn't possible now");
		$this->saveAction("startAssemble", $player_id);
		$this->gamestate->nextState('assemble');
	}

	function actSteal(): void
	{
		self::checkAction('steal');
		$player_id = self::getActivePlayerId();
		if (!$this->isStealPossible($player_id)) throw new BgaVisibleSystemException("Actions.actSteal: This action isn't possible now");
		$this->saveAction("startSteal", $player_id);
		$this->gamestate->nextState('steal');
	}

	function actConfirmSteal(string $oppenent_id, Piece $piece_1, ?Piece $piece_2 = null): void
	{
		self::checkAction('confirmSteal');
		$player_id = self::getActivePlayerId();
		if (!$this->isStealPossible($player_id, $oppenent_id, ($piece_2 === null ? 1 : 2))) throw new BgaVisibleSystemException("Actions.actConfirmSteal: This action isn't possible now");
		$gold_piece_id = $this->getUniqueValueFromDB("SELECT card_id FROM pieces WHERE card_location = 'player_{$player_id}' AND card_type_arg = 'gold' ORDER BY card_location_arg LIMIT 1");
		$this->DbQuery("UPDATE pieces SET card_location = 'panel', card_location_arg = {$oppenent_id} WHERE card_id = {$gold_piece_id}");
		$free_spaces = $this->getFreeLocationsOnPlayerBoard($player_id);
		if (count($free_spaces) === 0 || ($piece_2 !== null && count($free_spaces) === 1)) throw new BgaVisibleSystemException("Actions.actConfirmSteal: Not enough free spaces");
		$space_1 = array_shift($free_spaces);
		if ($piece_1->color === PieceColor::Gold || $piece_1->type === PieceType::Head || $piece_1->type === PieceType::Tail) throw new BgaVisibleSystemException("Actions.actConfirmSteal: Cant take gold, head or tail pieces");
		if ($piece_2 !== null && ($piece_2->color === PieceColor::Gold || $piece_2->type === PieceType::Head || $piece_2->type === PieceType::Tail)) throw new BgaVisibleSystemException("Actions.actConfirmSteal: Cant take gold, head or tail pieces");
		$this->DbQuery("UPDATE pieces SET card_location = 'player_${player_id}', card_location_arg = {$space_1} WHERE card_id = {$piece_1->id}");
		$space_2 = 0;
		if ($piece_2 !== null) {
			$space_2 = array_shift($free_spaces);
			$this->DbQuery("UPDATE pieces SET card_location = 'player_${player_id}', card_location_arg = {$space_2} WHERE card_id = {$piece_2->id}");
		}
		$this->DbQuery("UPDATE player SET player_score=player_score+" . 2 . " WHERE player_id = {$oppenent_id}");
		if ($piece_2 !== null) {
			self::notifyAllPlayers("steal_pieces", clienttranslate('${player_name} steals 2 pieces from ${opponent_name} ${piece_1}${piece_2}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'opponent_name' => self::getPlayerNameById($oppenent_id) . "_" . self::getPlayerColorById($oppenent_id),
				'opponent_id' => $oppenent_id,
				'i18n' => [
					'piece_1',
					'piece_2',
					'opponent_name'
				],
				'piece_1' => $this->pieceTypeAndColor[$piece_1->type->value][$piece_1->color->value],
				'piece_2' => $this->pieceTypeAndColor[$piece_2->type->value][$piece_2->color->value],
				'space_1' => $space_1,
				'space_2' => $space_2,
				'piece_1_id' => $piece_1->id,
				'piece_2_id' => $piece_2->id,
				'piece_1_color' => $piece_1->color->value,
				'piece_2_color' => $piece_2->color->value,
				'gold_piece_id' => $gold_piece_id
			));
		}
		else {
			self::notifyAllPlayers("steal_piece", clienttranslate('${player_name} steals a piece from ${opponent_name} ${piece_1}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'opponent_name' => self::getPlayerNameById($oppenent_id) . "_" . self::getPlayerColorById($oppenent_id),
				'opponent_id' => $oppenent_id,
				'i18n' => [
					'piece_1',
					'opponent_name'
				],
				'piece_1' => $this->pieceTypeAndColor[$piece_1->type->value][$piece_1->color->value],
				'space_1' => $space_1,
				'piece_1_id' => $piece_1->id,
				'piece_1_color' => $piece_1->color->value,
				'gold_piece_id' => $gold_piece_id
			));
		}
		self::notifyAllPlayers("score_gold", clienttranslate('${player_name} receives a Golden Feather and 2 prestige points'), array(
			'player_name' => self::getPlayerNameById($oppenent_id),
			'player_id' => $oppenent_id,
			'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $oppenent_id . "'"),
			'gold_count' => $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location = 'panel' AND card_location_arg = {$oppenent_id}"),
		));

		self::incStat(1, "gold_used", $player_id);
		self::incStat(1, "gold_received", $oppenent_id);
		$this->gamestate->nextState('nextPlayer');
	}

	/**
	 * actNewCoatl:
	 * Action method to create new Cóatl by putting down a piece
	 * @param int $piece_location_arg : the ID of the player board slot the first piece is on
	 * @return void
	 */
	function actNewCoatl(int $piece_location_arg): void
	{
		self::checkAction('newCoatl');
		$player_id = self::getActivePlayerId();
		$coatls = $this->getCoatlsOfPlayer($player_id);
		$piece_count = $this->countPiecesOnPlayerBoard($player_id, false);
		if (!$this->isNewCoatlPossible($coatls, $piece_count)) throw new BgaVisibleSystemException("Actions.actNewCoatl: This action isn't possible now");
		$piece = $this->getPieceOnPlayerBoard($player_id, $piece_location_arg);
		if ($piece->color === PieceColor::Gold) throw new BgaVisibleSystemException("Actions.actNewCoatl: Can't build with gold piece");
		$coatl_id = $this->createNewCoatl($player_id, $piece->id, $piece->type);
		$action_args = array(
			'piece_location_arg' => $piece_location_arg,
			'coatl_id' => $coatl_id,
			'piece_type' => $piece->type->value,
			'piece_id' => $piece->id
		);
		$this->saveAction("newCoatl", $player_id, $action_args);
		self::notifyAllPlayers("newCoatl", clienttranslate('${player_name} starts a new Cóatl with a piece ${piece}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'coatl_id' => $coatl_id,
			'piece_type' => $piece->type->value,
			'piece_id' => $piece->id,
			'i18n' => [
				'piece'
			],
			'piece' => $this->pieceTypeAndColor[$piece->type->value][$piece->color->value]
		));
		$this->gamestate->nextState('assemble');
	}

	/**
	 * actAddPiece:
	 * Action method to add a Piece to a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @param bool $to_left : the piece s added to the lest
	 * @param int $piece_location_arg : the ID of the player board slot the piece is on
	 * @return void
	 */
	function actAddPiece(int $coatl_id, bool $to_left, int $piece_location_arg): void
	{
		self::checkAction('addPiece');
		$player_id = self::getActivePlayerId();
		$coatl = $this->getCoatl($coatl_id);
		if ($coatl->player_id !== intval($player_id)) throw new BgaVisibleSystemException("Actions.actAddPiece: This is not the active player's Cóatl");
		$pieces = $this->getPiecesOnPlayerBoard($player_id, false);
		if (!$this->isAddPiecePossible(array($coatl), $pieces)) throw new BgaVisibleSystemException("Actions.actAddPiece: This action isn't possible now");
		$piece = $this->getPieceOnPlayerBoard($player_id, $piece_location_arg);
		if ($piece->color === PieceColor::Gold) throw new BgaVisibleSystemException("Actions.actAddPiece: Can't build with gold piece");
		$box_id = $this->addPiece($coatl_id, $piece->id, $piece->type, $to_left);
		$action_args = array(
			'piece_location_arg' => $piece_location_arg,
			'coatl_id' => $coatl_id,
			'piece_type' => $piece->type,
			'piece_id' => $piece->id,
			'piece_color' => $piece->color,
			'box_id' => $box_id,
			'to_left' => $to_left
		);
		$this->saveAction("addPiece", $player_id, $action_args);
		$coatl->addPiece($piece, $box_id);
		$this->dump("PIECES", $coatl->pieces);
		$this->dump("HAS_HEAD", $coatl->has_head);
		$this->dump("HAS_TAIL", $coatl->has_tail);
		$coatl_score = $coatl->getScore();
		self::notifyAllPlayers("addPiece", clienttranslate('${player_name} adds a piece to the ${side} side of their ${nth} Cóatl ${piece}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'coatl_id' => $coatl_id,
			'coatl_score' => $coatl_score,
			'piece_type' => $piece->type->value,
			'piece_id' => $piece->id,
			'box_id' => $box_id,
			'to_left' => $to_left,
			'i18n' => [
				'side',
				'nth',
				'piece'
			],
			'side' => $to_left ? clienttranslate("left") : clienttranslate('right'),
			'nth' => $coatl->nth === 1 ? clienttranslate('1st') : ($coatl->nth === 2 ? clienttranslate("2nd") : clienttranslate("3rd")),
			'piece' => $this->pieceTypeAndColor[$piece->type->value][$piece->color->value]
		));
		if ($coatl->has_head && $coatl->has_tail) {
			$this->DbQuery("UPDATE player SET player_score=player_score+" . $coatl_score["score"] . " WHERE player_id='" . self::getActivePlayerId() . "'");
			self::notifyAllPlayers("score_coatl", clienttranslate('${player_name} finishes their ${nth} Cóatl'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'coatl_id' => $coatl_id,
				'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $player_id . "'"),
				'activate' => true,
				'i18n' => ['nth'],
				'nth' => $coatl->nth === 1 ? clienttranslate('1st') : ($coatl->nth === 2 ? clienttranslate("2nd") : clienttranslate("3rd")),
			));
		}
		$this->gamestate->nextState('assemble');
	}

	/**
	 * actAddProphecy:
	 * Action method to add a Prophecy to a Cóatl
	 * @param Cooatl $coatl : the Cóatl object
	 * @param Prophecy $prophecy : the Prophecy object
	 * @return void
	 */
	function actAddProphecy(Cooatl $coatl, Prophecy $prophecy): void
	{
		self::checkAction('addCard');
		$player_id = self::getActivePlayerId();
		$score = $prophecy->getProphecyScorer()->score($coatl)["score"];
		if ($score === 0 || $coatl->is_locked || count($coatl->prophecies) === 4) throw new BgaVisibleSystemException("Actions.actAddProphecy: This action isn't possible now");
		$this->addProphecyToCoatl($prophecy->id, $coatl->id);
		$action_args = array(
			'coatl_id' => $coatl->id,
			'card_id' => $prophecy->id,
			'prophecy_type' => $prophecy->type
		);
		$this->saveAction("addProphecy", $player_id, $action_args);
		$coatl->addProphecy($prophecy);
		$coatl_score = $coatl->getScore();
		if ($coatl->has_head && $coatl->has_tail) {
			$this->DbQuery("UPDATE player SET player_score=player_score+" . $score . " WHERE player_id='" . $player_id . "'");
			self::notifyAllPlayers("score_coatl", '', array(
				'player_id' => $player_id,
				'coatl_id' => $coatl->id,
				'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $player_id . "'"),
				'activate' => true
			));
		}
		self::notifyAllPlayers("addProphecy", clienttranslate('${player_name} adds a Prophecy card to their ${nth} Cóatl ${prophecy}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'coatl_id' => $coatl->id,
			'coatl_score' => $coatl_score,
			'card_id' => $prophecy->id,
			'prophecy_type' => $prophecy->type,
			'i18n' => [
				'nth',
				'prophecy'
			],
			'nth' => $coatl->nth === 1 ? clienttranslate('1st') : ($coatl->nth === 2 ? clienttranslate("2nd") : clienttranslate("3rd")),
			'prophecy' => "[" . $prophecy->type . "]"
		));
		$this->gamestate->nextState('assemble');
	}

	/**
	 * actAddTemple:
	 * Action method to add a Temple to a Cóatl
	 * @param Cooatl $coatl : the Cóatl object
	 * @param Temple $temple : the Temple object
	 * @return void
	 */
	function actAddTemple(Cooatl $coatl, Temple $temple): void
	{
		self::checkAction('addCard');
		$player_id = self::getActivePlayerId();
		$score = $temple->getTempleScorer()->score($coatl)["score"];
		if ($score === 0 || !$coatl->has_head || !$coatl->has_tail || $coatl->is_locked || $coatl->temple !== null) throw new BgaVisibleSystemException("Undo.addTemple: This action isn't possible now");
		$this->addTempleToCoatl($temple->id, $coatl->id);
		self::DbQuery("UPDATE coatls SET is_locked = TRUE WHERE id = {$coatl->id}");
		$action_args = array(
			'coatl_id' => $coatl->id,
			'card_id' => $temple->id,
			'temple_type' => $temple->type,
			'temple_location' => $temple->location,
		);
		$this->saveAction("addTemple", $player_id, $action_args);
		$coatl->temple = $temple;
		self::notifyAllPlayers("addTemple", clienttranslate('${player_name} adds a Temple card from ${location} to their ${nth} Cóatl ${temple}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'coatl_id' => $coatl->id,
			'coatl_score' => $coatl->getScore(),
			'card_id' => $temple->id,
			'temple_type' => $temple->type,
			'temple_location' => $temple->location,
			'i18n' => [
				'nth',
				'location',
				'temple'
			],
			'nth' => $coatl->nth === 1 ? clienttranslate('1st') : ($coatl->nth === 2 ? clienttranslate("2nd") : clienttranslate("3rd")),
			'location' => $temple->location === "supply" ? clienttranslate("the supply") : clienttranslate("their hand"),
			'temple' => "[" . $temple->type . "]"
		));
		$this->DbQuery("UPDATE player SET player_score=player_score+" . $score . " WHERE player_id='" . $player_id . "'");
		self::notifyAllPlayers("score_coatl", '', array(
			'player_id' => $player_id,
			'coatl_id' => $coatl->id,
			'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $player_id . "'"),
			'activate' => true
		));
		$this->gamestate->nextState('assemble');
	}

	/**
	 * actConfirmAssemble:
	 * Action method to confirm and end assemble
	 * @return void
	 */
	function actConfirmAssemble(): void
	{
		self::checkAction('confirmAssemble');
		$player_id = self::getActivePlayerId();
		$coatls = $this->getCoatlsOfPlayer($player_id);
		if ($this->getLastActionType() === "startAssemble" || $this->countCoatlsWithHeadsAndTailsWithoutCard($coatls) !== 0) throw new BgaVisibleSystemException("Actions.actConfirmAssemble: This action isn't possible now");
		$this->lockCoatlsWithHeadsAndTails($coatls);
		$new_cards = $this->refillTempleSupply();
		if ($new_cards) {
			self::notifyAllPlayers("refillTempleSupply", clienttranslate('${player_name} reveals new Temple cards to the supply'), array(
				'player_name' => self::getActivePlayerName(),
				'new_cards' => $new_cards
			));
		}
		$this->gamestate->nextState('nextPlayer');
	}

	/**
	 * actUsePieceToken:
	 * Action method to use Piece Sacrifice Token
	 *    (Player can take two body pieces, or one head or tail piece from the bag)
	 * @param PieceType $piece_type : the type of the taken piece(s)
	 * @param PieceColor[] $piece_colors : the color of the taken piece(s)
	 * @return void
	 */
	function actUsePieceToken(PieceType $piece_type, array $piece_colors): void
	{
		self::checkAction('takePiece');
		$player_id = self::getActivePlayerId();
		$pieces_count = $this->countPiecesOnPlayerBoard($player_id, true);
		if ($pieces_count === 8 || ($pieces_count === 7 && $piece_type === PieceType::Body) || self::getUniqueValueFromDB("SELECT piece_token FROM player WHERE player_id={$player_id}") === false) throw new BgaVisibleSystemException("Actions.usePieceToken: This action isn't possible now");
		$player_board_locations = array_slice($this->getFreeLocationsOnPlayerBoard($player_id), 0, ($piece_type == PieceType::Body ? 2 : 1));
		self::DbQuery("UPDATE player SET piece_token=FALSE WHERE player_id=" . $player_id);
		$piece_ids = $this->takePieceFromBagToPlayerBoard($piece_type, $piece_colors, $player_id, $player_board_locations);
		if ($piece_type === PieceType::Body) {
			$piece_1 = $this->getPiece($piece_ids[0]);
			$piece_2 = $this->getPiece($piece_ids[1]);
			if ($piece_1->color === PieceColor::Gold || $piece_2->color === PieceColor::Gold) throw new BgaVisibleSystemException("Actions.usePieceToken: Can't take gold pieces");
			self::notifyAllPlayers("usePieceSacrifice", clienttranslate('${perfect_pick} ${player_name} takes Cóatl pieces from the bag ${piece_1}${piece_2}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'piece_type' => $piece_type,
				'piece_ids' => $piece_ids,
				'i18n' => [
					'piece_1',
					'piece_2'
				],
				'piece_1' => $this->pieceTypeAndColor[$piece_1->type->value][$piece_1->color->value],
				'piece_2' => $this->pieceTypeAndColor[$piece_2->type->value][$piece_2->color->value],
				'piece_colors' => $piece_colors,
				'player_board_locations' => $player_board_locations,
				'perfect_pick' => clienttranslate("Perfect Pick")
			));
		}
		else {
			$piece = $this->getPiece($piece_ids[0]);
			if ($piece->color === PieceColor::Gold) throw new BgaVisibleSystemException("Actions.usePieceToken: Can't take gold piece");
			self::notifyAllPlayers("usePieceSacrifice", clienttranslate('${perfect_pick} ${player_name} takes a Cóatl piece from the bag ${piece}'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'piece_type' => $piece_type,
				'piece_ids' => $piece_ids,
				'i18n' => [
					'piece'
				],
				'piece' => $this->pieceTypeAndColor[$piece->type->value][$piece->color->value],
				'piece_colors' => $piece_colors,
				'player_board_locations' => $player_board_locations,
				'perfect_pick' => clienttranslate("Perfect Pick")
			));
		}
		$replenish_happened = $this->replenishPieceSupply();
		if ($replenish_happened) {
			self::notifyAllPlayers("replenishPieceSupply", clienttranslate('${perfect_pick} ${player_name} replenishes the Supply board'), array(
				'player_name' => self::getActivePlayerName(),
				'pieces_on_supply' => $this->getPiecesOnSupply(),
				'piece_counts' => $this->countPiecesInBags(),
				'perfect_pick' => clienttranslate("Perfect Pick")
			));
		}
		self::incStat($piece_type === PieceType::Body ? 2 : 1, "taken_" . $piece_type->value . "_pieces", $player_id);
		self::setStat(true, "piece_sacrifice_used", $player_id);
		$this->gamestate->nextState('nextPlayer');
	}

	/**
	 * actUseProphecyToken:
	 * Action method to use Prophecy Sacrifice Token
	 *    (All Prophecy from the supply gets discarded, supply gets refilled and jump to discardProphecies action)
	 * @return void
	 */
	function actUseProphecyToken(): void
	{
		self::checkAction('useSacrifice');
		$player_id = self::getCurrentPlayerId();
		if (self::getUniqueValueFromDB("SELECT prophecy_token FROM player WHERE player_id=" . $player_id) === false) throw new BgaVisibleSystemException("Undo.useProphecyToken: This action isn't possible now");
		self::notifyAllPlayers("useProphecySacrifice", '', array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id
		));
		$this->discardAllPropheciesFromSupply();
		self::notifyAllPlayers("discardProphecySupply", clienttranslate('${see_the_future} ${player_name} discards all Prophecy cards from the supply'), array(
			'player_name' => self::getActivePlayerName(),
			'see_the_future' => clienttranslate("See the Future")
		));
		self::DbQuery("UPDATE player SET prophecy_token=FALSE WHERE player_id=" . $player_id);
		self::setStat(true, "prophecy_sacrifice_used", $player_id);
		$new_cards = $this->refillProphecySupply();
		if ($new_cards) self::notifyAllPlayers("refillProphecySupply", clienttranslate('${see_the_future} ${player_name} reveals new Prophecy cards to the supply'), array(
			'player_name' => self::getActivePlayerName(),
			'new_cards' => $new_cards,
			'see_the_future' => clienttranslate("See the Future")
		));
		if ($this->countPropheciesInPlayerHand($player_id) === 0) {
			$this->saveAction("prophecySacrifice", $player_id);
			$this->gamestate->nextState('takeProphecy');
		}
		else $this->gamestate->nextState('useProphecyToken');
	}

	/**
	 * actDiscardProphecySacrificeToken:
	 * Action method to discard any cards from player hand using Prophecy Sacrifice Token
	 * @param Prophecy[] $prophecies : the Prophecies to discard
	 * @return void
	 */
	function actDiscardProphecySacrificeToken(array $prophecies): void
	{
		self::checkAction('discardProphecySacrificeToken');
		$player_id = self::getCurrentPlayerId();
		foreach ($prophecies as $prophecy) if ($prophecy->location_arg !== intval($player_id)) throw new BgaVisibleSystemException(totranslate("Impossible card discard"));
		if (count($prophecies) === 0) self::notifyAllPlayers("noDiscardCards", clienttranslate('${see_the_future} ${player_name} doesn\'t discard Prophecy cards'), array(
			'player_name' => self::getActivePlayerName(),
			'see_the_future' => clienttranslate("See the Future")
		));
		else {
			$this->discardProphecies($prophecies);
			if (count($prophecies) === 1) self::notifyAllPlayers("discardCards", clienttranslate('${see_the_future} ${player_name} discards 1 Prophecy card'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'n' => 1,
				'see_the_future' => clienttranslate("See the Future")
			));
			else self::notifyAllPlayers("discardCards", clienttranslate('${see_the_future} ${player_name} discards ${n} Prophecy cards'), array(
				'player_name' => self::getActivePlayerName(),
				'player_id' => $player_id,
				'n' => count($prophecies),
				'see_the_future' => clienttranslate("See the Future")
			));
			self::notifyPlayer($player_id, "discardCards_private", '', array(
				'prophecy_ids' => array_map(function ($prophecy) {
					return $prophecy->id;
				}, $prophecies)
			));
		}
		if ($this->countPropheciesInPlayerHand($player_id) === 5) $this->gamestate->nextState('nextPlayer');
		else {
			$this->saveAction("prophecySacrifice", $player_id);
			$this->gamestate->nextState('discardProphecySacrificeToken');
		}
	}

	/**
	 * actUseTempleToken
	 * Action method to use Temple Sacrifice Token
	 * @return void
	 */
	function actUseTempleToken(): void
	{
		self::checkAction('useSacrifice');
		$player_id = self::getCurrentPlayerId();
		if (self::getUniqueValueFromDB("SELECT temple_token FROM player WHERE player_id=" . $player_id) === false) throw new BgaVisibleSystemException("Actions.actUseTempleToken: This action isn't possible now");
		$this->gamestate->nextState('useTempleToken');
	}

	/**
	 * actTakeTempleSacrificeToken:
	 * Action method to take Temple from supply
	 * @param Temple $temple : the Temple to take
	 * @return void
	 */
	function actTakeTempleSacrificeToken(Temple $temple): void
	{
		self::checkAction('takeTempleSacrificeToken');
		$player_id = self::getActivePlayerId();
		if (self::getUniqueValueFromDB("SELECT temple_token FROM player WHERE player_id=" . $player_id) == false) throw new BgaVisibleSystemException("Actions.actTakeTempleSacrificeToken: This action isn't possible now");
		if ($temple->location !== "supply") throw new BgaVisibleSystemException("Actions.actTakeTempleSacrificeToken: This Temple is not in the supply");
		self::DbQuery("UPDATE player SET temple_token=FALSE WHERE player_id=" . $player_id);
		self::setStat(true, "temple_sacrifice_used", $player_id);
		$this->takeTemple($player_id, $temple->id);
		self::notifyAllPlayers("takeTemple", clienttranslate('${priest_commitment} ${player_name} takes a Temple card from the supply ${temple}'), array(
			'player_name' => self::getActivePlayerName(),
			'player_id' => $player_id,
			'card_type' => $temple->type,
			'card_id' => $temple->id,
			'priest_commitment' => clienttranslate("Priest Commitment"),
			'i18n' => [
				'temple'
			],
			'temple' => "[" . $temple->type . "]"
		));
		$new_cards = $this->refillTempleSupply();
		self::notifyAllPlayers("refillTempleSupply", clienttranslate('${priest_commitment} ${player_name} reveals a new Temple card to the supply'), array(
			'player_name' => self::getActivePlayerName(),
			'new_cards' => $new_cards,
			'priest_commitment' => clienttranslate("Priest Commitment"),
		));
		$this->gamestate->nextState('nextPlayer');
	}


	/**
	 * actUndo:
	 * Action method to undo last action
	 * @return void
	 */
	function actUndo(): void
	{
		self::checkAction('undo');
		$lastAction = $this->getLastAction();
		$this->undoAction($lastAction, true);
		self::notifyAllPlayers("undo", clienttranslate('${player_name} undo their last action'), array(
			'player_name' => self::getActivePlayerName(),
		));
	}

	/**
	 * restart:
	 * Action method to undo all action
	 * @return void
	 */
	function actRestart(): void
	{
		self::checkAction('restart');
		$lastAction = $this->getLastAction();
		while ($lastAction !== null && $lastAction["action_args"] !== null) {
			$this->undoAction($lastAction, false);
			$lastAction = $this->getLastAction();
		}
		self::notifyAllPlayers("restart", clienttranslate('${player_name} resets their turn'), array(
			'player_name' => self::getActivePlayerName(),
		));
	}

	/**
	 * undoAction:
	 * Method to undo last action
	 * @param array $action : the information of the action to undo
	 * @return void
	 */
	function undoAction(array $action): void
	{
		switch ($action["action_type"]) {
			case "takePiece":
				$this->undoTakePieceToPlayerBoard(PieceType::stringToEnum($action["action_args"]["piece_type"]), $action["action_args"]["piece_ids"], $action["action_args"]["supply_location"]);
				self::notifyAllPlayers("undoTakePiece", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'piece_type' => $action["action_args"]["piece_type"],
					'piece_ids' => $action["action_args"]["piece_ids"],
					'player_board_locations' => $action["action_args"]["player_board_locations"],
					'supply_location' => $action["action_args"]["supply_location"],
					'moveIds' => intval($action["gamelog_id"])
				));
				$this->undoLastAction();
				self::incStat($action["action_args"]["piece_type"] == "body" ? (-2) : (-1), "taken_" . $action["action_args"]["piece_type"] . "_pieces", $action["player_id"]);
				$this->gamestate->nextState('playerTurn');
				break;

			case "takeProphecy":
				$this->undoTakeProphecy($action["action_args"]["card_id"]);
				self::notifyAllPlayers("undoTakeProphecy", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'card_type' => $action["action_args"]["card_type"],
					'card_id' => $action["action_args"]["card_id"],
					'moveIds' => intval($action["gamelog_id"]),
				));
				$this->undoLastAction();
				self::incStat(-1, "taken_prophecy_cards", $action["player_id"]);
				if ($this->logIsFullEmpty()) {
					$this->gamestate->nextState('playerTurn');
				}
				else {
					$this->gamestate->nextState('takeProphecy');
				}
				break;

			case "newCoatl":
				$this->undoNewCoatl($action["player_id"], $action["action_args"]["coatl_id"], $action["action_args"]["piece_location_arg"], $action["action_args"]["piece_id"], PieceType::stringToEnum($action["action_args"]["piece_type"]));
				self::notifyAllPlayers("undoNewCoatl", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'coatl_id' => $action["action_args"]["coatl_id"],
					'piece_type' => $action["action_args"]["piece_type"],
					'piece_id' => $action["action_args"]["piece_id"],
					'piece_location_arg' => $action["action_args"]["piece_location_arg"],
					'moveIds' => intval($action["gamelog_id"]),
				));
				$this->undoLastAction();
				$this->gamestate->nextState('assemble');
				break;
			case "addPiece":
				$coatl = $this->getCoatl($action["action_args"]['coatl_id']);
				if ($coatl->has_head && $coatl->has_tail) {
					$this->DbQuery("UPDATE player SET player_score=player_score-" . $coatl->getScore()["score"] . " WHERE player_id='" . self::getActivePlayerId() . "'");
					self::notifyAllPlayers("score_coatl", '', array(
						'player_id' => $action["player_id"],
						'coatl_id' => $action["action_args"]['coatl_id'],
						'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $action["player_id"] . "'"),
						'activate' => false
					));
				}
				$this->undoAddPiece($action["player_id"], $action["action_args"]['piece_location_arg'], $action["action_args"]['coatl_id'], PieceType::stringToEnum($action["action_args"]['piece_type']), $action["action_args"]['piece_id'], $action["action_args"]['to_left']);
				$coatl->removePiece($action["action_args"]['box_id']);
				self::notifyAllPlayers("undoAddPiece", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'piece_location_arg' => $action["action_args"]['piece_location_arg'],
					'coatl_id' => $action["action_args"]['coatl_id'],
					'coatl_score' => $coatl->getScore(),
					'piece_type' => $action["action_args"]['piece_type'],
					'piece_id' => $action["action_args"]['piece_id'],
					'box_id' => $action["action_args"]['box_id'],
					'to_left' => $action["action_args"]['to_left'],
					'moveIds' => intval($action["gamelog_id"])
				));
				$this->undoLastAction();
				$this->gamestate->nextState('assemble');
				break;

			case "addProphecy":
				$coatl = $this->getCoatl($action["action_args"]['coatl_id']);
				$prophecy = $this->getProphecy($action["action_args"]['card_id']);
				if ($coatl->has_head && $coatl->has_tail) {
					$this->DbQuery("UPDATE player SET player_score=player_score-" . $prophecy->getProphecyScorer()->score($coatl)["score"] . " WHERE player_id='" . $action["player_id"] . "'");
					self::notifyAllPlayers("score_coatl", '', array(
						'player_id' => $action["player_id"],
						'coatl_id' => $action["action_args"]['coatl_id'],
						'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $action["player_id"] . "'"),
						'activate' => count($coatl->prophecies) == 1 ? false : null
					));
				}
				$this->undoAddProphecyToCoatl($action["action_args"]['card_id'], $action["player_id"]);
				$coatl->removeProphecy($action["action_args"]['card_id']);
				self::notifyAllPlayers("undoAddProphecy", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'coatl_id' => $action["action_args"]['coatl_id'],
					'coatl_score' => $coatl->getScore(),
					'card_id' => $action["action_args"]['card_id'],
					'prophecy_type' => $action["action_args"]['prophecy_type'],
					'moveIds' => intval($action["gamelog_id"])
				));
				$this->undoLastAction();
				$this->gamestate->nextState('assemble');
				break;
			case "addTemple":
				$coatl = $this->getCoatl($action["action_args"]['coatl_id']);
				$temple = $this->getTemple($action["action_args"]['card_id']);
				$this->DbQuery("UPDATE player SET player_score=player_score-" . $temple->getTempleScorer()->score($coatl)["score"] . " WHERE player_id='" . $action["player_id"] . "'");
				self::notifyAllPlayers("score_coatl", '', array(
					'player_id' => $action["player_id"],
					'coatl_id' => $action["action_args"]['coatl_id'],
					'player_score' => $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='" . $action["player_id"] . "'"),
					'activate' => null
				));
				if ($action["action_args"]['temple_location'] === "supply") $this->undoAddTempleToCoatl($action["action_args"]['card_id']);
				else $this->undoAddTempleToCoatl($action["action_args"]['card_id'], $action["player_id"]);
				$coatl->temple = null;
				self::DbQuery("UPDATE coatls SET is_locked = FALSE WHERE id = {$coatl->id}");
				self::notifyAllPlayers("undoAddTemple", '', array(
					'player_name' => self::getActivePlayerName(),
					'player_id' => $action["player_id"],
					'coatl_id' => $action["action_args"]['coatl_id'],
					'coatl_score' => $coatl->getScore(),
					'card_id' => $action["action_args"]['card_id'],
					'temple_type' => $action["action_args"]['temple_type'],
					'temple_location' => $action["action_args"]['temple_location'],
					'moveIds' => intval($action["gamelog_id"])
				));
				$this->undoLastAction();
				$this->gamestate->nextState('assemble');
				break;
		}
	}

	/**
	 * actCancel:
	 * Action method to cancel started action
	 *  (Used to cancel start assemble and take Temple with Sacrifice Token)
	 * @return void
	 */
	function actCancel(): void
	{
		self::checkAction('cancel');
		$this->clearLog();
		$this->gamestate->nextState('playerTurn');
	}

	function actGiveUp(): void
	{
		self::checkAction('giveUp');
		$this->clearLog();
		$player_id = self::getActivePlayerId();
		$this->DbQuery("UPDATE `player` SET `player_score` = -1 WHERE `player_id` = {$player_id}");
		self::notifyAllPlayers("give_up", clienttranslate('${player_name} gives up the game'), array(
			'player_id' => $player_id,
			'player_name' => self::getActivePlayerName()
		));
		if ($this->getUniqueValueFromDB("SELECT count(player_id) FROM player WHERE player_eliminated = 0") <= 2) {
			$this->endGame();
		}
		else {
			$this->gamestate->nextState('nextPlayer');
			$this->eliminatePlayer($player_id);
		}
	}
}