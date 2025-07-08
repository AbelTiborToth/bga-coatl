<?php

use Enums\PieceType;

/**
 * Temple:
 * Class for Cóatl objects
 * (using double oo in name to have different name from BGA framework main game class [also Coatl as game name])
 */
#[AllowDynamicProperties] class Cooatl
{
	/**
	 * id:
	 * The ID of the Cóatl
	 * (assigned automatically by the database)
	 * @var int
	 */
	public int $id;

	/**
	 * player_id:
	 * The ID of the player owning the Cóatl
	 * @var int
	 */
	public int $player_id;

	/**
	 * pieces:
	 * Array of Pieces located in the Cóatl sorted by their left to right appearance in the Cóatl
	 * @var Piece[]
	 */
	public array $pieces;

	/**
	 * prophecies:
	 * Array of Prophecy cards played next to the Cóatl
	 * If no Prophecy cards have been played yet, the array is empty
	 * @var Prophecy[]
	 */
	public array $prophecies;

	/**
	 * temple:
	 * The Temple card played next to the Cóatl
	 * If no Temple card has been played yet, the value is null
	 * @var Temple|null
	 */
	public ?Temple $temple;

	/**
	 * has_head:
	 * Bool to indicate if the Cóatl has a head piece
	 * true : if the Cóatl has a head piece
	 * false : if the Cóatl doesn't have a head piece
	 * @var bool
	 */
	public bool $has_head;

	/**
	 * has_tail:
	 * Bool to indicate if the Cóatl has a tail piece
	 * true : if the Cóatl has a tail piece
	 * false : if the Cóatl doesn't have a tail piece
	 * @var bool
	 */
	public bool $has_tail;

	/**
	 * has_locked:
	 * Bool to indicate if the Cóatl is locked
	 * true : if the Cóatl is locked
	 * false : if the Cóatl is not locked
	 * The Cóatl becomes locked at the end of a turn, when the player finishes it during the turn, so it can't be modified anymore
	 * @var bool
	 */
	public bool $is_locked;

	/**
	 * nth:
	 * Shows which Cóatl is it of the player (1st, 2nd, 3rd)
	 * @var int
	 */
	public int $nth;

	/**
	 * __construct:
	 * @param int $id : the ID of the Cóatl
	 * Constructor for new Cooatl object
	 * @param int $player_id : the ID of the player owning the Cóatl
	 * @param Piece[] $pieces : array of Pieces located in the Cóatl
	 * @param Prophecy[] $prophecies : array of Prophecy cards played next to the Cóatl
	 * @param ?Temple $temple : the Temple card played next to the Cóatl
	 * @param bool $is_locked : bool to indicate if the Cóatl is locked
	 * @param int $nth : shows which Cóatl is it of the player (1st, 2nd, 3rd)
	 */
	public function __construct(int $id, int $player_id, array $pieces, array $prophecies, ?Temple $temple, bool $is_locked, int $nth)
	{
		$this->id = $id;
		$this->player_id = $player_id;
		$this->pieces = $pieces;
		$this->prophecies = $prophecies;
		$this->temple = $temple;
		$this->has_head = $pieces[array_key_first($pieces)]->type === PieceType::Head;
		$this->has_tail = $pieces[array_key_last($pieces)]->type === PieceType::Tail;
		$this->is_locked = $is_locked;
		$this->nth = $nth;
	}

	/**
	 * addPiece:
	 * Method to add a Piece card to the Cóatl's Prophecy cards
	 * @param Piece $piece : the Piece to add
	 * @param int $box_id : the ID of the box for the new Piece
	 * @return void
	 */
	public function addPiece(Piece $piece, int $box_id): void
	{
		$this->pieces[$box_id] = $piece;
		ksort($this->pieces);
		if ($piece->type === PieceType::Head) $this->has_head = true;
		else if ($piece->type === PieceType::Tail) $this->has_tail = true;
	}

	public function removePiece(int $box_id): void
	{
		if ($this->pieces[$box_id]->type === PieceType::Head) $this->has_head = false;
		else if ($this->pieces[$box_id]->type === PieceType::Tail) $this->has_tail = false;
		unset($this->pieces[$box_id]);
	}

	/**
	 * addProphecy:
	 * Method to add a Prophecy card to the Cóatl's Prophecy cards
	 * @param Prophecy $prophecy : the Prophecy to add
	 * @return void
	 */
	public function addProphecy(Prophecy $prophecy): void
	{
		$this->prophecies[$prophecy->id] = $prophecy;
	}

	/**
	 * removeProphecy:
	 * Method to remove a Prophecy card from the Cóatl's Prophecy cards
	 * @param int $prophecy_id
	 * @return void
	 */
	public function removeProphecy(int $prophecy_id): void
	{
		unset($this->prophecies[$prophecy_id]);
	}

	/**
	 * hasDuplicateProphecy:
	 * Function to get if the Cóatl has a duplicate of the new Prophecy given
	 * @param Prophecy $new_prophecy : the new Prophecy
	 * @return bool : if the Cóatl has a duplicate of the Prophecy
	 */
	public function hasDuplicateProphecy(Prophecy $new_prophecy): bool
	{
		foreach ($this->prophecies as $prophecy) {
			if ($prophecy->id !== $new_prophecy->id && $prophecy->type === $new_prophecy->type) return true;
		}
		return false;
	}

	/**
	 * getScore:
	 * Function the get the overall score and card levels for a Cóatl
	 * @return array ["score" => int, "levels" => ["prophecy_levels" => [prophecy_id => int], "temple_level" => int]]
	 */
	public function getScore(): array
	{
		$res = array(
			"score" => 0,
			"levels" => array(),
		);
		foreach ($this->prophecies as $prophecy) {
			$score_array = $prophecy->getProphecyScorer()->score($this);
			$res["score"] += $score_array["score"];
			$res["prophecy_levels"][$prophecy->id] = $score_array["level"];
		}
		if ($this->temple !== null) {
			$score_array = $this->temple->getTempleScorer()->score($this);
			$res["score"] += $score_array["score"];
			$res["temple_level"] = $score_array["level"];
		}
		return $res;
	}

	/**
	 * getAllData:
	 * Function to get all data about the Cóatl object
	 * @return array
	 */
	function getAllData(): array
	{
		return array(
			"id" => $this->id,
			"player_id" => $this->player_id,
			"pieces" => array_map(function ($piece) {
				return $piece->toArray();
			}, $this->pieces),
			"prophecies" => array_map(function ($prophecy) {
				return $prophecy->toArray();
			}, $this->prophecies),
			"score" => $this->getScore(),
			"temple" => $this->temple,
			"has_head" => $this->has_head,
			"has_tail" => $this->has_tail,
			"is_finished" => $this->has_head && $this->has_tail,
			"is_locked" => $this->is_locked
		);
	}
}