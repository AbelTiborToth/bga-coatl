<?php

use Enums\PieceColor;
use Enums\PieceType;

/**
 * Pieces:
 * Trait for isolating Piece related methods and functions in the main game logic
 */
trait Pieces
{
	/**
	 * setupPieces:
	 * Method to create the Piece elements and shuffle the bags using the deck component of the BGA framework
	 * https://en.doc.boardgamearena.com/Deck
	 * @param int $body_piece_count : number of body pieces of each color in the bag
	 * @return void
	 */
	function setupPieces(int $body_piece_count, int $golden_piece_count = 0): void
	{
		foreach (PieceType::cases() as $type) {
			$nbr = $type === PieceType::Body ? $body_piece_count : 3;
			$values = [];
			foreach (PieceColor::cases() as $color) {
				if ($color === PieceColor::Gold) {
					if ($type === PieceType::Body) {
						$values[] = array(
							"type" => $type->value,
							"type_arg" => $color->value,
							"nbr" => $golden_piece_count
						);
					}
				}
				else {
					$values[] = array(
						"type" => $type->value,
						"type_arg" => $color->value,
						"nbr" => $nbr
					);
				}
			}
			$this->pieces->createCards($values, "bag_{$type->value}");
			$this->pieces->shuffle("bag_{$type->value}");
		}
	}

	/**
	 * getPiece:
	 * Function to get the Piece with the given ID
	 * @param int $piece_id : ID of the Piece
	 * @return Piece : the Piece with the given ID
	 */
	function getPiece(int $piece_id): Piece
	{
		$db_piece = $this->pieces->getCard($piece_id);
		return new Piece($db_piece["id"], PieceType::stringToEnum($db_piece["type"]), PieceColor::stringToEnum($db_piece["type_arg"]));
	}

	/**
	 * countPiecesInBags:
	 * Function to get the number of remaining Pieces in each type bag
	 * @return array : ["head" => int, "body" => int, "tail" => int]
	 */
	function countPiecesInBags(): array
	{
		$res = array();
		foreach (PieceType::cases() as $type) {
			$res[$type->value] = $this->pieces->countCardInLocation("bag_{$type->value}");
		}
		return $res;
	}

	/**
	 * countPiecesInBags:
	 * Function to get the number of remaining Pieces in each type bag grouped by types and colors
	 * Used for Piece sacrifice token piece availability showing
	 * @return array : ["head" => ["yellow" => int, "red" => int ...], "body" => [ ... ], "tail" => [ ... ]]
	 */
	public function countAvailablePiecesInBags(): array
	{
		return $this->getDoubleKeyCollectionFromDB("
			SELECT a.card_type, a.card_type_arg, ifnull(c, 0)
			FROM (
				SELECT card_type, card_type_arg
				FROM pieces
				GROUP BY card_type, card_type_arg )
				AS a
			LEFT JOIN (
				SELECT card_type, card_type_arg, count(card_id) c
				FROM pieces
				WHERE card_location LIKE 'bag_%' GROUP BY card_type, card_type_arg )
				AS b
			ON a.card_type = b.card_type AND a.card_type_arg = b.card_type_arg", true);
	}

	/**
	 * countPiecesOnSupply:
	 * Function to get the number of Pieces in each type supply
	 * @return array : ["head" => int, "body" => int, "tail" => int]
	 */
	function countPiecesOnSupply(): array
	{
		$res = array();
		foreach (PieceType::cases() as $type) {
			$res[$type->value] = $this->pieces->countCardInLocation("supply_{$type->value}");
		}
		return $res;
	}

	/**
	 * getPiecesOnSupply:
	 * Function to get the Pieces from each type supply
	 * @return array : ["head" => Piece[], "body" => Piece[], "tail" => Piece[]]
	 */
	function getPiecesOnSupply(): array
	{
		$res = array();
		foreach (PieceType::cases() as $type) {
			$res[$type->value] = array();
			$db_pieces = $this->pieces->getCardsInLocation("supply_{$type->value}");
			foreach ($db_pieces as $piece) {
				$res[$type->value][$piece["location_arg"]] = new Piece($piece["id"], PieceType::stringToEnum($piece["type"]), PieceColor::stringToEnum($piece["type_arg"]));
			}
		}
		return $res;
	}

	/**
	 * replenishPieceSupply:
	 * Function to replenish the piece supply, filling all empty spaces
	 * @return bool : true if any new piece came to board, false if not (used when a bag is empty, or when piece sacrifice token used while the board is full)
	 */
	function replenishPieceSupply(): bool
	{
		$res = false;
		foreach (PieceType::cases() as $type) {
			$n_pieces = $this->pieces->countCardsByLocationArgs("supply_{$type->value}");
			if (($type === PieceType::Body ? 12 : 2) > count($n_pieces)) {
				$spaces = ($type === PieceType::Body ? 6 : 2);
				for ($i = 1; $i <= $spaces; $i++) {
					if (!isset($n_pieces[$i])) {
						$this->pieces->pickCardForLocation("bag_{$type->value}", "supply_{$type->value}", $i);
						if ($type === PieceType::Body) {
							$this->pieces->pickCardForLocation("bag_{$type->value}", "supply_{$type->value}", $i * (-1));
						}
					}
				}
				$new_count = $this->pieces->countCardInLocation("supply_{$type->value}");
				if ($new_count - count($n_pieces) !== 0) $res = true;
				if ($type === PieceType::Body && $new_count == 0) {
					if ($this->globals->get("final_round") == 0) {
						$this->globals->set("final_round", 5);
						$this->globals->set("final_turn_no", 3);
						self::notifyAllPlayers("finalRound", clienttranslate("The final turns begins!"));
					}
				}
			}
		}
		return $res;
	}

	/**
	 * takePieceToPlayerBoard:
	 * Function to take pieces from supply to player board
	 *      (Player can take two body pieces, or one head or tail piece)
	 * @param PieceType $type : the type of the piece(s)
	 * @param int $location_arg : the slot IDs of the player's board
	 * @param int $player_id : the ID of the player
	 * @param int[] $player_board_locations : the supply location of the pieces
	 * @return int[] : the ids of the taken pieces
	 */
	function takePieceToPlayerBoard(PieceType $type, int $location_arg, int $player_id, array $player_board_locations): array
	{
		$piece_ids = array(array_keys($this->pieces->getCardsInLocation("supply_{$type->value}", $location_arg))[0]);
		$this->pieces->moveCard($piece_ids[0], "player_{$player_id}", $player_board_locations[0]);
		if ($type === PieceType::Body) {
			$second = $this->pieces->getCardsInLocation("supply_{$type->value}", $location_arg * (-1));
			if (!empty($second)) {
				$piece_ids[] = array_keys($second)[0];
				$this->pieces->moveCard($piece_ids[1], "player_{$player_id}", $player_board_locations[1]);
			}
		}
		return $piece_ids;
	}

	/**
	 * takePieceFromBagToPlayerBoard:
	 * Function to take pieces from bag to player board
	 *     (Player can take two body pieces, or one head or tail piece)
	 * @param PieceType $type : the type of the taken piece(s)
	 * @param PieceColor[] $colors : the color of the taken piece(s)
	 * @param int $player_id : the ID of the player
	 * @param int[] $player_board_locations : the slot IDs of the player's board
	 * @return int[] : the ids of the taken pieces
	 */
	function takePieceFromBagToPlayerBoard(PieceType $type, array $colors, int $player_id, array $player_board_locations): array
	{
		$piece_ids = array();
		$pieces = $this->pieces->getCardsOfTypeInLocation($type->value, $colors[0]->value, "bag_{$type->value}");
		$piece_ids[] = array_pop($pieces)["id"];
		$this->pieces->moveCard($piece_ids[0], "player_{$player_id}", $player_board_locations[0]);
		if ($type === PieceType::Body) {
			$pieces2 = $this->pieces->getCardsOfTypeInLocation($type->value, $colors[1]->value, "bag_{$type->value}");
			$piece_ids[] = array_pop($pieces2)["id"];
			$this->pieces->moveCard($piece_ids[1], "player_{$player_id}", $player_board_locations[1]);
		}
		$this->pieces->shuffle("bag_{$type->value}");
		return $piece_ids;
	}

	/**
	 * undoTakePieceToPlayerBoard:
	 * Function to undo take pieces from supply to player board
	 * @param PieceType $type : the type of the taken piece(s)
	 * @param array $piece_ids : the ID of the taken piece(s)
	 * @param int $location_arg : the slot IDs of the player's board
	 * @return void
	 */
	function undoTakePieceToPlayerBoard(PieceType $type, array $piece_ids, int $location_arg): void
	{
		$this->pieces->moveCard($piece_ids[0], "supply_{$type->value}", $location_arg);
		if ($type === PieceType::Body && count($piece_ids) === 2) $this->pieces->moveCard($piece_ids[1], "supply_{$type->value}", $location_arg * (-1));
	}

	/**
	 * countPiecesOnPlayerBoard:
	 * Function to get the number of Pieces on a player's board
	 * @param string $player_id : the ID of the player
	 * @return int : the number of Pieces on the player's board
	 */
	function countPiecesOnPlayerBoard(string $player_id, bool $with_gold): int
	{
		if ($with_gold) return $this->pieces->countCardsInLocation("player_{$player_id}");
		else return $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location = 'player_{$player_id}' AND card_type_arg != 'gold'");
	}

	function countGoldPiecesOnPlayerBoard(string $player_id): int
	{
		return $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location = 'player_{$player_id}' AND card_type_arg = 'gold'");
	}

	function countPiecesOnOpponentBoards(string $player_id, string $opponent = null): int
	{
		if ($opponent === null) return $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location IN (SELECT concat('player_', player_id) FROM player WHERE player_id != {$player_id}) AND card_type_arg != 'gold'");
		else return $this->getUniqueValueFromDB("SELECT COUNT(*) FROM pieces WHERE card_location IN (SELECT concat('player_', player_id) FROM player WHERE player_id = {$opponent}) AND card_type_arg != 'gold'");
	}

	/**
	 * getPieceOnPlayerBoard:
	 * Function a Pieces on a player's board
	 * @param string $player_id : the player whose board is checked
	 * @param int $location_arg : the slot id on the board
	 * @return Piece|null : the piece on the board, if null there is no piece on the given location
	 */
	function getPieceOnPlayerBoard(string $player_id, int $location_arg): ?Piece
	{
		$db_pieces = $this->pieces->getCardsInLocation("player_{$player_id}", $location_arg, "location_arg");
		if (empty($db_pieces)) return null;
		else return new Piece($db_pieces[0]["id"], PieceType::stringToEnum($db_pieces[0]["type"]), PieceColor::stringToEnum($db_pieces[0]["type_arg"]));
	}

	/**
	 * getPiecesOnPlayerBoard:
	 * Function to get all Pieces on a player's board
	 * @param string $player_id : the player whose board is checked
	 * @return Piece[] : the pieces on the board
	 */
	function getPiecesOnPlayerBoard(string $player_id, bool $with_gold): array
	{
		if ($with_gold) $db_pieces = $this->getCollectionFromDB("SELECT card_location_arg location_arg, card_id id, card_type type, card_type_arg type_arg FROM pieces WHERE card_location = 'player_{$player_id}' ORDER BY location_arg");
		else $db_pieces = $this->getCollectionFromDB("SELECT card_location_arg location_arg, card_id id, card_type type, card_type_arg type_arg FROM pieces WHERE card_location = 'player_{$player_id}' AND card_type_arg != 'gold' ORDER BY location_arg");
		if (empty($db_pieces)) return $db_pieces;
		else return array_map(function ($piece) {
			return new Piece($piece["id"], PieceType::stringToEnum($piece["type"]), PieceColor::stringToEnum($piece["type_arg"]));
		}, $db_pieces);
	}

	/**
	 * getFreeLocationsOnPlayerBoard:
	 * Function to get free slots on a player's board
	 * @param string $player_id : the ID of the player whose board is checked
	 * @return int[] : the ids of the free slots
	 */
	function getFreeLocationsOnPlayerBoard(string $player_id): array
	{
		$this->dump("WHAZ", array_diff(range(1, 8), $this->getObjectListFromDB("SELECT card_location_arg location_arg FROM pieces WHERE card_location = 'player_{$player_id}'", true)));
		return array_diff(range(1, 8), $this->getObjectListFromDB("SELECT card_location_arg location_arg FROM pieces WHERE card_location = 'player_{$player_id}'", true));
	}

	/**
	 * addPieceToCoatl:
	 * Method to add a piece to a Cóatl
	 * @param int $piece_id : the id of the piece
	 * @param int $coatl_id : the id of the Cóatl
	 * @param int $location_arg : the id of the new piece's box in the Cóatl
	 */
	function addPieceToCoatl(int $piece_id, int $coatl_id, int $location_arg): void
	{
		$this->pieces->moveCard($piece_id, "coatl_{$coatl_id}", $location_arg);
	}

	/**
	 * addPieceToCoatl:
	 * Method to undo add a piece to a Cóatl
	 * @param int $piece_id : the ID of the piece
	 * @param int $player_id : the ID of the player
	 * @param int $location_arg : the ID of the piece's previous player board slot
	 */
	function undoAddPieceToCoatl(int $piece_id, int $player_id, int $location_arg): void
	{
		$this->pieces->moveCard($piece_id, "player_{$player_id}", $location_arg);
	}

	/**
	 * countPiecesOnCoatl:
	 * Function to get the number of pieces in a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return int : the number of pieces in the Cóatl
	 */
	function countPiecesOnCoatl(int $coatl_id): int
	{
		return $this->pieces->countCardsInLocation("coatl_{$coatl_id}");
	}

	/**
	 * countPiecesOnCoatl:
	 * Function to get the number of body pieces in a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @return int : the number of body pieces in the Cóatl
	 */
	function countBodyPiecesOnCoatl(int $coatl_id): int
	{
		return $this->getUniqueValueFromDB("SELECT COUNT(card_id) FROM pieces WHERE card_location =  'coatl_{$coatl_id}' AND card_type = 'body';");
	}

	/**
	 * getPiecesOnCoatl:
	 * Function to get the pieces in a Cóatl
	 * @param $coatl_id : the ID of the Cóatl
	 * @return Piece[] : the pieces with their box IDs as keys
	 */
	function getPiecesOnCoatl(int $coatl_id): array
	{
		$db_pieces = $this->getCollectionFromDB("SELECT card_location_arg location_arg, card_id id, card_type type, card_type_arg type_arg FROM pieces WHERE card_location = 'coatl_{$coatl_id}' ORDER BY location_arg");
		if (empty($db_pieces)) return $db_pieces;
		else return array_map(function ($piece) {
			return new Piece($piece["id"], PieceType::stringToEnum($piece["type"]), PieceColor::stringToEnum($piece["type_arg"]));
		}, $db_pieces);
	}
}