<?php

/**
 * Prophecies:
 * Trait for isolating Prophecy related methods and functions in the main game logic
 */
trait Prophecies
{
	/**
	 * setupProphecies:
	 * Method to create the Prophecy elements and shuffle the deck using the deck component of the BGA framework
	 * https://en.doc.boardgamearena.com/Deck
	 * @param bool $with_promo : true if the promo cards variant is on, otherwise false
	 * @return void
	 */
	function setupProphecies(bool $with_promo): void
	{
		$values = [];
		$nbr = 2;
		$color = "red";
		$type_count = $with_promo ? 54 : 48;
		for ($type = 0; $type <= $type_count; $type++) {
			$values[] = array(
				'type' => $color,
				'type_arg' => $type,
				'nbr' => $nbr
			);
			switch ($type) {
				case 4:
					$nbr = 1;
					$color = "black";
					break;
				case 14:
					$color = "blue";
					break;
				case 24:
					$color = "yellow";
					break;
				case 34:
					$color = "green";
					break;
				case 48:
					$color = "red";
					break;
			}
		}
		$this->prophecies->createCards($values, "deck");
		$this->prophecies->shuffle("deck");
	}

	/**
	 * getProphecy:
	 * Function to get the Prophecy with the given ID
	 * @param int $prophecy_id : ID of the Prophecy
	 * @return Prophecy : the Prophecy with the given ID
	 */
	function getProphecy(int $prophecy_id): Prophecy
	{
		$db_prophecy = $this->prophecies->getCard($prophecy_id);
		return new Prophecy($db_prophecy["id"], $db_prophecy["type"], $db_prophecy["type_arg"], $db_prophecy["location"], $db_prophecy["location_arg"]);
	}

	/**
	 * getProphecies:
	 * Function to get the Prophecies with the given IDs
	 * @param string[] $prophecy_ids : the IDs of the Prophecies
	 * @return Prophecy[] : the Prophecies with the given IDs
	 */
	function getProphecies(array $prophecy_ids): array
	{
		return array_map(function ($prophecy_id) {
			return $this->getProphecy(intval($prophecy_id));
		}, $prophecy_ids);
	}

	/**
	 * refillProphecySupply:
	 * Function to refill the Prophecy supply, filling card count to 6
	 * @return array|null : the IDs of the new Prophecies, or null if no refill needed
	 */
	function refillProphecySupply(): ?array
	{
		$n = 6 - $this->prophecies->countCardsInLocation('supply');
		if ($n === 0) return null;
		else return $this->prophecies->pickCardsForLocation($n, 'deck', 'supply');
	}

	/**
	 * discardAllPropheciesFromSupply:
	 * Method to discard all Prophecies from the supply
	 * @return void
	 */
	function discardAllPropheciesFromSupply(): void
	{
		$this->prophecies->moveAllCardsInLocation('supply', 'discard');
	}

	/**
	 * getPropheciesInSupply:
	 * Function to get all Prophecies in supply
	 * @return Prophecy[] : all Prophecies in supply
	 */
	function getPropheciesInSupply(): array
	{
		$db_prophecies = $this->prophecies->getCardsInLocation('supply');
		// no empty check, because the Prophecy supply is never empty
		return array_map(function ($prophecy) {
			return new Prophecy($prophecy["id"], $prophecy["type"], $prophecy["type_arg"], $prophecy["location"], $prophecy["location_arg"]);
		}, $db_prophecies);
	}

	/**
	 * setupPlayerProphecyHand:
	 * Method to deal a player Prophecies according to the player order
	 *    1st player: 3 prophecies,
	 *    2nd player: 4 prophecies,
	 *    3rd player: 5 prophecies,
	 *    4th player: 6 prophecies,
	 * @param int $player_id : the id of the player
	 * @return void
	 */
	function setupPlayerProphecyHand(int $player_id): void
	{
		$n = $this->getPlayerNoById($player_id) + 2;
		$this->prophecies->pickCards($n, 'deck', $player_id);
	}

	/**
	 * countPropheciesInPlayerHand:
	 * Function to get the number of Prophecies in a player's hand
	 * @param int $player_id : the ID of the player
	 * @return int : the number of Prophecies in the player's hand
	 */
	function countPropheciesInPlayerHand(int $player_id): int
	{
		return $this->prophecies->countCardsInLocation('hand', $player_id);
	}

	/**
	 * getPropheciesInPlayerHand:
	 * Function to get the Prophecies in a player's hand
	 * @param int $player_id : the ID of the player
	 * @return Prophecy[] : the Prophecies in the player's hand
	 */
	function getPropheciesInPlayerHand(int $player_id): array
	{
		$db_prophecies = $this->prophecies->getCardsInLocation('hand', $player_id);
		if (empty($db_prophecies)) return $db_prophecies;
		else return array_map(function ($prophecy) {
			return new Prophecy($prophecy["id"], $prophecy["type"], $prophecy["type_arg"], $prophecy["location"], $prophecy["location_arg"]);
		}, $db_prophecies);
	}

	/**
	 * getProphecyScores:
	 * Function to get scores for the given Cóatls with the given Prophecies
	 * @param Cooatl[] $coatls : the Cóatls to check
	 * @param Prophecy[] $prophecies : the Prophecies to evaluate
	 * @return array : multi array of scores
	 */
	function getProphecyScores(array $coatls, array $prophecies): array
	{
		$result = array();
		foreach ($coatls as $coatl) {
			if (!$coatl->is_locked && count($coatl->prophecies) !== 4) {
				foreach ($prophecies as $prophecy) {
					if (!$coatl->hasDuplicateProphecy($prophecy)) {
						$score = $prophecy->getProphecyScorer()->score($coatl);
						if ($score["score"] != 0) {
							if (!array_key_exists($coatl->id, $result)) {
								$result[$coatl->id] = array();
							}
							$result[$coatl->id][$prophecy->id] = array(
								$score,
								$prophecy->type
							);
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * discardProphecies:
	 * Method to discard the given Prophecies
	 * @param Prophecy[] $prophecies
	 * @return void
	 */
	function discardProphecies(array $prophecies): void
	{
		foreach ($prophecies as $prophecy) $this->prophecies->playCard($prophecy->id);
	}

	/**
	 * takeProphecy:
	 * Method to take a Prophecy to a player's hand
	 * @param int $player_id : the ID of the player
	 * @param int $prophecy_id : the ID of the Prophecy to take
	 * @return void
	 */
	function takeProphecy(int $player_id, int $prophecy_id): void
	{
		$this->prophecies->moveCard($prophecy_id, 'hand', $player_id);
	}

	/**
	 * undoTakeProphecy:
	 * Method to undo take a Prophecy to a player's hand (back to Prophecy supply)
	 * @param int $prophecy_id : the ID of Prophecy taken
	 * @return void
	 */
	function undoTakeProphecy(int $prophecy_id): void
	{
		$this->prophecies->moveCard($prophecy_id, 'supply');
	}

	/**
	 * takeProphecyFromDeck:
	 * Function to take a Prophecy from the deck to a player's hand
	 * @param int $player_id : the ID of the player
	 * @return array : the information of the new card
	 */
	function takeProphecyFromDeck(int $player_id): array
	{
		return $this->prophecies->pickCard('deck', $player_id);
	}

	/**
	 * addProphecyToCoatl:
	 * Method to add a Prophecy to a Cóatl
	 * @param int $prophecy_id : the ID of the Prophecy
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return void
	 */
	function addProphecyToCoatl(int $prophecy_id, int $coatl_id): void
	{
		$this->prophecies->moveCard($prophecy_id, 'coatl_' . $coatl_id);
	}

	/**
	 * undoAddProphecyToCoatl:
	 * Method to undo add a Prophecy to a Cóatl
	 * @param int $prophecy_id : the ID of the Prophecy
	 * @param int $player_id : the ID of the player who gets back the Prophecy
	 * @return void
	 */
	function undoAddProphecyToCoatl(int $prophecy_id, int $player_id): void
	{
		$this->prophecies->moveCard($prophecy_id, 'hand', $player_id);
	}

	/**
	 * getCoatlProphecies
	 * Function to get the Prophecies played to a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return Prophecy[] : the Prophecies played to the Cóatl
	 */
	function getCoatlProphecies(int $coatl_id): array
	{
		$db_prophecies = $this->prophecies->getCardsInLocation('coatl_' . $coatl_id);
		if (empty($db_prophecies)) return $db_prophecies;
		else return array_map(function ($prophecy) {
			return new Prophecy($prophecy["id"], $prophecy["type"], $prophecy["type_arg"], $prophecy["location"], $prophecy["location_arg"]);
		}, $db_prophecies);
	}
}