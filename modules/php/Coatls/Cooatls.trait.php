<?php

use Enums\PieceType;

/**
 * Pieces:
 * Trait for isolating Cóatl related methods and functions in the main game logic
 * (using double oo in name to have different name from BGA framework main game class [also Coatl as game name])
 */
trait Cooatls
{
	/**
	 * getCoatl:
	 * Function to get the Cóatl with the given ID
	 * @param int $coatl_id : ID of the Cóatl
	 * @return Cooatl : the Cóatl with the given ID
	 */
	function getCoatl(int $coatl_id): Cooatl
	{
		$db_coatl = $this->getNonEmptyObjectFromDB("SELECT player_id, is_locked  FROM coatls WHERE id = {$coatl_id}");
		$nth = $this->getUniqueValueFromDB("SELECT COUNT(player_id)  FROM coatls WHERE id <= {$coatl_id} && player_id = {$db_coatl["player_id"]}");
		return new Cooatl($coatl_id, $db_coatl["player_id"], $this->getPiecesOnCoatl($coatl_id), $this->getCoatlProphecies($coatl_id), $this->getCoatlTemple($coatl_id), $db_coatl["is_locked"], $nth);
	}

	/**
	 * countCoatlsOfPlayer:
	 * Function to get the number of Cóatls a player has
	 * @param int $player_id : the ID of the player
	 * @return int : the number of Cóatls the player has
	 */
	function countCoatlsOfPlayer(int $player_id): int
	{
		return $this->getUniqueValueFromDB("SELECT count(id) FROM coatls WHERE player_id = {$player_id}");
	}

	/**
	 * getCoatls:
	 * Function to get all Cóatls in game
	 * @return Cooatl[] : all Cóatls in game
	 */
	function getCoatls(): array
	{
		$coatl_ids = $this->getObjectListFromDB("SELECT id FROM coatls", true);
		if (empty($coatl_ids)) return $coatl_ids;
		else return array_map(function ($coatl_id) {
			return $this->getCoatl($coatl_id);
		}, $coatl_ids);
	}

	/**
	 * getCoatls:
	 * Function to get Cóatls of a player
	 * @param int $player_id : the ID of the player
	 * @return Cooatl[] : all Cóatls of the player
	 */
	function getCoatlsOfPlayer(int $player_id): array
	{
		$coatl_ids = $this->getObjectListFromDB("SELECT id FROM coatls WHERE player_id = {$player_id}", true);
		if (empty($coatl_ids)) return $coatl_ids;
		else return array_map(function ($coatl_id) {
			return $this->getCoatl($coatl_id);
		}, $coatl_ids);
	}

	/**
	 * countHeadlessCoatls:
	 * Function to get the number of headless Cóatls in a given array
	 * @param Cooatl[] $coatls : array of the Cóatls
	 * @return int : the number of headless Cóatls in the given array
	 */
	function countHeadlessCoatls(array $coatls): int
	{
		$res = 0;
		foreach ($coatls as $coatl) if (!$coatl->has_head) $res++;
		return $res;
	}

	/**
	 * countTaillessCoatls:
	 * Function to get the number of tailless Cóatls in a given array
	 * @param Cooatl[] $coatls : array of the Cóatls
	 * @return int : the number of tailless Cóatls in the given array
	 */
	function countTaillessCoatls(array $coatls): int
	{
		$res = 0;
		foreach ($coatls as $coatl) if (!$coatl->has_tail) $res++;
		return $res;
	}

	/**
	 * countFinishedCoatls:
	 * Function to get the number of Cóatls with heads and tails in a given array
	 * @param Cooatl[] $coatls : array of the Cóatls
	 * @return int : the number of Cóatls with heads and tails in the given array
	 */
	function countFinishedCoatls(array $coatls): int
	{
		$res = 0;
		foreach ($coatls as $coatl) if ($coatl->has_head && $coatl->has_tail) $res++;
		return $res;
	}

	/**
	 * countFinishedCoatls:
	 * Function to get the number of Cóatls with heads and tails but without prophecy card in a given array
	 * @param Cooatl[] $coatls : array of the Cóatls
	 * @return int : the number of Cóatls with heads and tails but without prophecy card in the given array
	 */
	function countCoatlsWithHeadsAndTailsWithoutCard(array $coatls): int
	{
		$res = 0;
		foreach ($coatls as $coatl) if ($coatl->has_head && $coatl->has_tail && !(count($coatl->prophecies) !== 0)) $res++;
		return $res;
	}

	/**
	 * countUnfinishedCoatls:
	 * Function to get the number of headless or tailless Cóatls with heads in a given array
	 * @param Cooatl[] $coatls : array of the Cóatls
	 * @return int : the number of the number of headless or tailless Cóatls in the given array
	 */
	function countUnfinishedCoatls(array $coatls): int
	{
		$res = 0;
		foreach ($coatls as $coatl) if (!$coatl->has_head || !$coatl->has_tail) $res++;
		return $res;
	}


	/**
	 * createNewCoatl:
	 * Function to create new Cóatl in database and add the first piece
	 * @param int $player_id : the ID of the player who owns the new Cóatl
	 * @param int $piece_id : the ID of the first piece in the new Cóatl
	 * @param PieceType $piece_type : the type of the first piece in the new Cóatl
	 * @return int : the ID of the new Cóatl
	 */
	function createNewCoatl(int $player_id, int $piece_id, PieceType $piece_type): int
	{
		$last_coatl_id = $this->getUniqueValueFromDB("SELECT id FROM coatls ORDER BY id DESC LIMIT 1");
		$coatl_id = $last_coatl_id === null ? 0 : $last_coatl_id + 1;
		$this->DbQuery("INSERT INTO coatls (id, player_id) VALUES ({$coatl_id}, {$player_id});");
		$this->addPiece($coatl_id, $piece_id, $piece_type, null);
		return $coatl_id;
	}

	/**
	 * undoNewCoatl:
	 * Method to undo create new Cóatl in database and take back the first piece to player board
	 * @param int $player_id : the ID of the player who owns the new Cóatl
	 * @param int $coatl_id : the ID of the new Cóatl
	 * @param int $pre_piece_location_arg : the ID of the player board slot the piece has been on
	 * @param int $piece_id : the ID of the first piece
	 * @param PieceType $piece_type : the type of the first piece
	 * @return void
	 */
	function undoNewCoatl(int $player_id, int $coatl_id, int $pre_piece_location_arg, int $piece_id, PieceType $piece_type): void
	{
		$this->undoAddPiece($player_id, $pre_piece_location_arg, $coatl_id, $piece_type, $piece_id, false);
		$this->DbQuery("DELETE FROM coatls WHERE id = {$coatl_id}");
	}

	/**
	 * addPiece:
	 * Function to add a Piece to a Cóatl
	 * @param int $coatl_id : the ID of the Cóatl
	 * @param int $piece_id : the ID of the Piece
	 * @param PieceType $piece_type : the type of the Piece
	 * @param bool|null $to_left : the side of the Piece [true: left, false: right, null: first piece]
	 * @return int : the Piece's box ID (every Piece is inside a box inside the Cóatl)
	 */
	function addPiece(int $coatl_id, int $piece_id, PieceType $piece_type, ?bool $to_left): int
	{
		if ($to_left !== null) {
			if ((($piece_type == PieceType::Tail && $to_left) || ($piece_type == PieceType::Head && !$to_left))) throw new BgaVisibleSystemException("Coatl.class.php::addPiece Invalid piece type and Coatl side to place piece");
			$coatl = $this->getCoatl($coatl_id);
			if (($coatl->has_head && $piece_type == PieceType::Tail) || ($coatl->has_tail && $piece_type == PieceType::Head)) if ($this->countBodyPiecesOnCoatl($coatl->id) == 0) throw new BgaVisibleSystemException("Coatl.class.php::addPiece Cóatl needs at least one body piece");
		}
		$start_box_id = $this->getUniqueValueFromDB("SELECT start_box_id FROM coatls WHERE id = {$coatl_id}");
		switch ($piece_type) {
			case (PieceType::Head):
				if ($to_left !== null) {
					$start_box_id--;
					$this->DbQuery("UPDATE coatls SET start_box_id = {$start_box_id} WHERE id = {$coatl_id}");
				}
				$box_id = $start_box_id;
				break;
			case (PieceType::Body):
				if ($to_left) {
					$start_box_id--;
					$this->DbQuery("UPDATE coatls SET start_box_id = {$start_box_id} WHERE id = {$coatl_id}");
					$box_id = $start_box_id;
				}
				else {
					$box_id = $start_box_id + $this->countPiecesOnCoatl($coatl_id);
				}
				break;
			case (PieceType::Tail):
				$box_id = $start_box_id + $this->countPiecesOnCoatl($coatl_id);
				break;
		}
		$this->addPieceToCoatl($piece_id, $coatl_id, $box_id);
		return $box_id;
	}

	/**
	 * undoAddPiece:
	 * Method to undo add a Piece to a Cóatl
	 * @param int $player_id : the ID of the player who is the owner of the Cóatl
	 * @param int $pre_piece_location_arg : the ID of the player board slot the piece has been on
	 * @param int $coatl_id : the ID of the Cóatl
	 * @param PieceType $piece_type : the type of the Piece
	 * @param int $piece_id : the ID of the Piece
	 * @param bool $to_left : the side of the Piece [true: left, false: right or first piece]
	 * @return void
	 */
	function undoAddPiece(int $player_id, int $pre_piece_location_arg, int $coatl_id, PieceType $piece_type, int $piece_id, bool $to_left): void
	{
		$start_box_id = $this->getUniqueValueFromDB("SELECT start_box_id FROM coatls WHERE id = {$coatl_id}");
		switch ($piece_type) {
			case (PieceType::Head):
				$start_box_id++;
				$this->DbQuery("UPDATE coatls SET start_box_id = {$start_box_id} WHERE id = {$coatl_id}");
				break;
			case (PieceType::Body):
				if ($to_left) {
					$start_box_id++;
					$this->DbQuery("UPDATE coatls SET start_box_id = {$start_box_id} WHERE id = {$coatl_id}");
				}
				break;
		}
		$this->undoAddPieceToCoatl($piece_id, $player_id, $pre_piece_location_arg);
	}

	/**
	 * lockCoatlsWithHeadsAndTails:
	 *
	 * @param Cooatl[] $coatls
	 * @return void
	 */
	function lockCoatlsWithHeadsAndTails(array $coatls): void
	{
		foreach ($coatls as $coatl) if (!$coatl->is_locked && $coatl->has_head && $coatl->has_tail) self::DbQuery("UPDATE coatls SET is_locked = TRUE WHERE id = {$coatl->id}");
	}
}