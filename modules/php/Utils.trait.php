<?php

use Enums\PieceType;

/**
 * Utils:
 * Trait for isolating Utility methods and functions in the main game logic
 */
trait Utils
{
	/**
	 * isTakePiecePossible:
	 * Function to get take piece action is possible
	 * @param int $piece_count : number Pieces on the board of the active player
	 * @return int : number free board slots
	 *    if >= 2 : can take any pieces
	 *    if == 1 : can take head or tail pieces
	 *    if == 0 : can't take any pieces
	 */
	function isTakePiecePossible(int $piece_count): int
	{
		return 8 - $piece_count;
	}

	/**
	 * isTakePiecePossible:
	 * Function to get take prophecy action is possible
	 * @param int $prophecy_count : number Prophecies in the hand of the active player
	 * @return bool : take prophecy action is possible
	 */
	function isTakeProphecyPossible(int $prophecy_count): bool
	{
		return $prophecy_count != 5;
	}

	/**
	 * isNewCoatlPossible:
	 * Function to get start new Cóatl action possible
	 * @param Cooatl[] $coatls : Cóatls of the active player
	 * @param int $piece_count : number Pieces on the board of the active player
	 * @return bool : start new Cóatl action possible
	 */
	function isNewCoatlPossible(array $coatls, int $piece_count): bool
	{
		return $piece_count !== 0 && count($coatls) !== 3 && $this->countUnfinishedCoatls($coatls) !== 2;
	}

	/**
	 * isAddPiecePossible:
	 * Function to get add Piece to Cóatl action possible
	 * @param Cooatl[] $coatls : Cóatls of the active player
	 * @param Piece[] $pieces : Pieces on the board of the active player
	 * @return bool : add Piece to Cóatl action possible
	 */
	function isAddPiecePossible(array $coatls, array $pieces): bool
	{
		$head_count = count(array_filter($pieces, function ($piece) {
			return $piece->type === PieceType::Head;
		}));
		$tail_count = count(array_filter($pieces, function ($piece) {
			return $piece->type === PieceType::Tail;
		}));
		return (count($pieces) !== 0 && !(count($pieces) === $head_count && $this->countHeadlessCoatls($coatls) === 0) && !(count($pieces) === $tail_count && $this->countTaillessCoatls($coatls) == 0));
	}

	/**
	 * isAddProphecyPossible:
	 * Function to get add Prophecy to Cóatl action possible
	 * @param Cooatl[] $coatls : Cóatls of the active player
	 * @param Prophecy[] $prophecies : Prophecies in the hand of the active player
	 * @return bool : add Prophecy to Cóatl action possible
	 */
	function isAddProphecyPossible(array $coatls, array $prophecies): bool
	{
		if (count($prophecies) === 0) return false;
		return !empty($this->getProphecyScores($coatls, $prophecies));
	}

	/**
	 * isAddTemplePossible:
	 * Function to get add Prophecy to Cóatl action possible
	 * @param Cooatl[] $coatls : Cóatls of the active player
	 * @param Temple[] $temples : available Temples for the active player
	 * @return bool : add Temple to Cóatl action possible
	 */
	function isAddTemplePossible(array $coatls, array $temples): bool
	{
		if (count($temples) === 0) return false;
		return !empty($this->getTempleScores($coatls, $temples));
	}

	/**
	 * @param int $player_id
	 * @return bool
	 */
	function isPlayerTurnPossible(int $player_id): bool
	{
		$piece_count = $this->countPiecesOnPlayerBoard($player_id, true);
		if ($this->isTakePiecePossible($piece_count)) return true;
		$prophecy_count = $this->countPropheciesInPlayerHand($player_id);
		if ($this->isTakeProphecyPossible($prophecy_count) || $this->isAssemblePossible($player_id)) return true;
		$sacrifice_tokens = self::getNonEmptyObjectFromDB("SELECT prophecy_token, temple_token FROM player WHERE player_id={$player_id}");
		return ($sacrifice_tokens["prophecy_token"] || $sacrifice_tokens["temple_token"]);
	}

	/**
	 * isAssemblePossible:
	 * Function to get start assemble action possible for a player
	 * @param int $player_id : the ID of the player
	 * @return bool start assemble action possible for the player
	 */
	function isAssemblePossible(int $player_id): bool
	{
		$coatls = $this->getCoatlsOfPlayer($player_id);
		$pieces = $this->getPiecesOnPlayerBoard($player_id, false);
		if ($this->isNewCoatlPossible($coatls, count($pieces))) return true;
		else if ($this->isAddPiecePossible($coatls, $pieces)) return true;
		else {
			$prophecies = $this->getPropheciesInPlayerHand($player_id);
			return $this->isAddProphecyPossible($coatls, $prophecies);
		}
	}

	function isStealPossible(int $player_id, string $opponent = null, int $count = 1): bool
	{
		$gold_piece_count = $this->countGoldPiecesOnPlayerBoard($player_id);
		$opponents_piece_count = $this->countPiecesOnOpponentBoards($player_id, $opponent);
		if ($count === 1) return ($gold_piece_count > 0 && $opponents_piece_count > 0);
		else return ($gold_piece_count > 0 && $opponents_piece_count > 1);
	}

	/**
	 * getPossiblePlayerTurnActions:
	 * Function to get possible player turn actions for a player
	 * @param int $player_id : the ID of the player
	 * @return array : possible player turn actions for the player
	 */
	function getPossiblePlayerTurnActions(int $player_id): array
	{
		$piece_count = $this->countPiecesOnPlayerBoard($player_id, true);
		$prophecy_count = $this->countPropheciesInPlayerHand($player_id);
		return array(
			"takePiece" => $this->isTakePiecePossible($piece_count),
			"takeProphecy" => $this->isTakeProphecyPossible($prophecy_count),
			"assemble" => $this->isAssemblePossible($player_id),
			"steal" => $this->isStealPossible($player_id)
		);
	}

	/**
	 * getPossibleTakeProphecyActions:
	 * Function to get possible take prophecy actions for a player
	 * @param int $player_id : the ID of the player
	 * @param string $last_action_type : the type of the last action
	 * @return array: possible take prophecy actions for the player
	 */
	function getPossibleTakeProphecyActions(int $player_id, string $last_action_type): array
	{
		$prophecy_count = $this->countPropheciesInPlayerHand($player_id);
		return array(
			"undo" => $last_action_type != "takeProphecyFromDeck" && $last_action_type != "prophecySacrifice",
			"restart" => $this->getLogRealLength() > 1,
			"takeProphecy" => $this->isTakeProphecyPossible($prophecy_count)
		);
	}

	/**
	 * getPossibleAssembleActions:
	 * Function to get possible assemble actions for a player
	 * @param $player_id : the ID of the player
	 * @return array possible assemble actions for the player
	 */
	function getPossibleAssembleActions(string $player_id): array
	{
		$pieces = $this->getPiecesOnPlayerBoard($player_id, false);
		$prophecies = $this->getPropheciesInPlayerHand($player_id);
		$temples = $this->getAvailableTemples($player_id);
		$coatls = $this->getCoatlsOfPlayer($player_id);
		return array(
			"new" => $this->isNewCoatlPossible($coatls, count($pieces)),
			"piece" => $this->isAddPiecePossible($coatls, $pieces),
			"prophecy" => $this->getProphecyScores($coatls, $prophecies),
			"temple" => $this->getTempleScores($coatls, $temples),
			"confirmAssemble" => $this->getLastActionType() != "startAssemble" && $this->countCoatlsWithHeadsAndTailsWithoutCard($coatls) == 0,
			"undo" => $this->getLastActionType() != "startAssemble",
			"restart" => $this->getLogRealLength() > 1
		);
	}

	/**
	 * getFirstPlayerId:
	 * Function to get the ID of the first player in table order
	 * @return int : the ID of the first player in table order
	 */
	function getFirstPlayerId(): int
	{
		return $this->getNextPlayerTable()[0];
	}

	/**
	 * isBoardReplenishNeeded:
	 * Function to get is Piece supply replenish needed
	 * @return bool : is Piece supply replenish needed
	 */
	function isBoardReplenishNeeded(): bool
	{
		$countPieces = $this->countPiecesOnSupply();
		return (($countPieces["head"] == 0 && $countPieces["tail"] == 0) || $countPieces["body"] == 0);
	}
}