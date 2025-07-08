<?php

/**
 * Args:
 * Trait for isolating Game State Arguments related methods and functions in the main game logic
 *
 * Here we create functions defined as "game state arguments" (see "args" property in states.inc.php).
 * These methods function is to return some additional information that is specific to the current game state.
 */
trait Args
{
	/**
	 * argDiscardProphecy:
	 * Arguments for game state "discardProphecy", returns the card count they must discard for each player
	 * @return array [player_ids => int (the card count the player must discard)]
	 */
	function argDiscardProphecy(): array
	{
		$player_ids = array_keys($this->loadPlayersBasicInfos());
		$args = array();
		foreach ($player_ids as $player_id) $args[$player_id] = $this->getPlayerNoById($player_id) - 1;
		return $args;
	}

	/**
	 * argPlayerTurn:
	 * Arguments for game state "playerTurn"
	 * @return array of the args
	 */
	function argPlayerTurn(): array
	{
		$player_id = $this->getActivePlayerId();
		$args = array();
		$args["possibleActions"] = $this->getPossiblePlayerTurnActions($player_id);
		// if the player still has the piece token, we give out the information about the number available pieces in bags for each piece type
		if (self::getUniqueValueFromDB("SELECT piece_token FROM player WHERE player_id = {$player_id}")) $args["piece_in_bag_available"] = $this->countAvailablePiecesInBags();
		$args["final_turn_no"] = $this->globals->get("final_turn_no");
		return $args;
	}

	/**
	 * argTakePiece:
	 * Arguments for game state "takePiece"
	 * @return array of the args
	 */
	function argTakePiece(): array
	{
		return array(
			"replenish" => $this->isBoardReplenishNeeded(),
			"final_turn_no" => $this->globals->get("final_turn_no")
		);
	}

	/**
	 * argTakeProphecy:
	 * Arguments for game state "takePiece"
	 * @return array of the args
	 */
	function argTakeProphecy(): array
	{
		$last_action_type = $this->getLastActionType();
		return array(
			"possibleActions" => $this->getPossibleTakeProphecyActions($this->getActivePlayerId(), $last_action_type),
			"sacrificeToken" => $last_action_type == "prophecySacrifice",
			"final_turn_no" => $this->globals->get("final_turn_no")
		);
	}

	/**
	 * argAssemble:
	 * Arguments for game state "assemble"
	 * @return array of the args
	 */
	function argAssemble(): array
	{
		$player_id = $this->getActivePlayerId();
		return array(
			"possibleActions" => $this->getPossibleAssembleActions($player_id),
			"otherActionPossible" => count($this->getPossiblePlayerTurnActions($player_id)) === 1,
			"final_turn_no" => $this->globals->get("final_turn_no"),
		);
	}

	function argSteal(): array
	{
		$player_id = $this->getActivePlayerId();
		return array(
			"freeSpaces" => count($this->getFreeLocationsOnPlayerBoard($player_id)) + 1,
			"final_turn_no" => $this->globals->get("final_turn_no")
		);
	}

	/**
	 * argAssemble:
	 * Arguments for game state "discardProphecySacrificeToken" and "takeTempleSacrificeToken"
	 *    where only the final_turn_no global is needed for "You have x turns left" label
	 * @return array of the args
	 */
	function argFinalTurn(): array
	{
		return array(
			"final_turn_no" => $this->globals->get("final_turn_no")
		);
	}
}