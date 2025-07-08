<?php

/**
 * Prophecies:
 * Trait for isolating Temple related methods and functions in the main game logic
 */
trait Temples
{
	/**
	 * setupTemples:
	 * Method to create the Temple elements and shuffle the deck using the deck component of the BGA framework
	 * https://en.doc.boardgamearena.com/Deck
	 * @param bool $with_promo : true if the promo cards variant is on, otherwise false
	 * @return void
	 */
	function setupTemples(bool $with_promo): void
	{
		$values = [];
		$type_count = $with_promo ? 19 : 14;
		for ($type = 0; $type <= $type_count; $type++) {
			$values[] = array(
				"type" => $type,
				"type_arg" => null,
				"nbr" => 1
			);
		}
		$this->temples->createCards($values, "deck");
		$this->temples->shuffle("deck");
	}

	/**
	 * getTemple:
	 * Function to get the Temple with the given ID
	 * @param int $temple_id : ID of the Temple
	 * @return Temple : the Temple with the given ID
	 */
	function getTemple(int $temple_id): Temple
	{
		$db_temple = $this->temples->getCard($temple_id);
		return new Temple($db_temple["id"], $db_temple["type"], $db_temple["location"], $db_temple["location_arg"]);
	}

	/**
	 * refillTempleSupply:
	 * Function to refill the Temple supply, filling card count to 2
	 * @return array|null : the IDs of the new Temples, or null if no refill needed
	 */
	function refillTempleSupply(): ?array
	{
		$n = 2 - count($this->temples->getCardsInLocation("supply"));
		if ($n === 0) return null;
		return $this->temples->pickCardsForLocation($n, "deck", "supply");
	}

	/**
	 * getTempleInSupply:
	 * Function to get all Prophecies in supply
	 * @return Prophecy[] : all Prophecies in supply
	 */
	function getTemplesInSupply(): array
	{
		$db_temples = $this->temples->getCardsInLocation("supply");
		if (empty($db_temples)) return $db_temples;
		else return array_map(function ($temple) {
			return new Temple($temple["id"], $temple["type"], $temple["location"], $temple["location_arg"]);
		}, $db_temples);
	}

	/**
	 * setupPlayerTempleHand:
	 * Method to deal a player a Temple
	 * @param int $player_id : the id of the player
	 * @return void
	 */
	function setupPlayerTempleHand(int $player_id): void
	{
		$this->temples->pickCard("deck", $player_id);
	}

	/**
	 * countTemplesInPlayerHand:
	 * Function to get the number of Temples in a player's hand
	 * @param int $player_id : the ID of the player
	 * @return int : the number of Temples in the player's hand
	 */
	function countTemplesInPlayerHand(int $player_id): int
	{
		return $this->temples->countCardsInLocation("hand", $player_id);
	}

	/**
	 * getTemplesInPlayerHand:
	 * Function to get the Temples in a player's hand
	 * @param int $player_id : the ID of the player
	 * @return Temple[] : the Temples in the player's hand
	 */
	function getTemplesInPlayerHand(int $player_id): array
	{
		$db_temples = $this->temples->getCardsInLocation("hand", $player_id);
		if (empty($db_temples)) return $db_temples;
		else return array_map(function ($temple) {
			return new Temple($temple["id"], $temple["type"], $temple["location"], $temple["location_arg"]);
		}, $db_temples);
	}

	/**
	 * getAvailableTemples:
	 * Function to get the available Temples for a player
	 * @param int $player_id : the ID of the player
	 * @return Temple[] : the available Temples for the player
	 */
	function getAvailableTemples(int $player_id): array
	{
		$db_temples_by_locations = $this->getDoubleKeyCollectionFromDB("SELECT card_location location, card_id id, card_type type, card_location_arg location_arg FROM temples WHERE card_location = 'supply' OR (card_location = 'hand' AND card_location_arg = {$player_id})");
		if (empty($db_temples_by_locations)) return $db_temples_by_locations;
		else return array_map(function ($db_temples) {
			return array_map(function ($temple) {
				return new Temple($temple["id"], $temple["type"], $temple["location"], $temple["location_arg"]);
			}, $db_temples);
		}, $db_temples_by_locations);
	}

	/**
	 * getTempleScores:
	 * Function to get scores for the given Cóatls with the given Temples
	 * @param Cooatl[] $coatls : the Cóatls to check
	 * @param Temple[] $temples : the Temples to evaluate
	 * @return array : multi array of scores
	 */
	function getTempleScores(array $coatls, array $temples): array
	{
		$result = array();
		foreach ($coatls as $coatl) {
			if (!$coatl->is_locked && $coatl->has_head && $coatl->has_tail && $coatl->temple === null && count($coatl->prophecies) !== 0) {
				foreach ([
								"supply",
								"hand"
							] as $location) {
					if (array_key_exists($location, $temples)) {
						foreach ($temples[$location] as $temple) {
							$score = $temple->getTempleScorer()->score($coatl);
							if ($score["score"] !== 0) {
								if (!array_key_exists($coatl->id, $result)) $result[$coatl->id] = array();
								if (!array_key_exists($location, $result[$coatl->id])) $result[$coatl->id][$location] = array();
								$result[$coatl->id][$location][$temple->id] = array(
									$score,
									$temple->type
								);
							}
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * addTempleToCoatl:
	 * Method to add a Temple to a Cóatl
	 * @param int $temple_id : the ID of the Temple
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return void
	 */
	function addTempleToCoatl(int $temple_id, int $coatl_id): void
	{
		$this->temples->moveCard($temple_id, "coatl_" . $coatl_id);
	}

	/**
	 * undoAddTempleToCoatl:
	 * Method to undo add a Temple to a Cóatl
	 * @param int $temple_id : the ID of the Temple
	 * @param ?int $player_id : the ID of the player who gets back the Temple
	 *                            if null the Temple goes back to the supply
	 * @return void
	 */
	function undoAddTempleToCoatl(int $temple_id, ?int $player_id = null): void
	{
		if ($player_id === null) $this->temples->moveCard($temple_id, "supply");
		else $this->temples->moveCard($temple_id, "hand", $player_id);
	}

	/**
	 * getCoatlTemple
	 * Function to get the Temple played to a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return ?Temple : the Temple played to the Cóatl
	 */
	function getCoatlTemple(int $coatl_id): ?Temple
	{
		$db_temple = $this->temples->getCardsInLocation("coatl_" . $coatl_id);
		if (empty($db_temple)) return null;
		else {
			$temple = array_pop($db_temple);
			return new Temple($temple["id"], $temple["type"], $temple["location"], $temple["location_arg"]);
		}
	}

	/**
	 * takeProphecy:
	 * Method to take a Temple to a player's hand
	 * @param int $player_id : the ID of the player
	 * @param int $temple_id : the ID of the Prophecy to take
	 * @return void
	 */
	function takeTemple(int $player_id, int $temple_id): void
	{
		$this->temples->moveCard($temple_id, "hand", $player_id);
	}
}