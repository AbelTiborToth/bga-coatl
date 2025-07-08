<?php

use Enums\PieceColor;
use Enums\PieceType;

/**
 * Piece:
 * Class for Piece objects
 */
readonly class Piece
{
	/**
	 * id:
	 * The ID of the Piece
	 * (assigned automatically by the database)
	 * @var int
	 */
	public int $id;

	/**
	 * type:
	 * The type of the Piece
	 * (e.g. Head, Body, Tail)
	 * @var PieceType
	 */
	public PieceType $type;

	/**
	 * color:
	 * The color of the Piece
	 * (e.g. Yellow, Red, Black, Green, Blue)
	 * @var PieceColor
	 */
	public PieceColor $color;

	/**
	 * __construct:
	 * Constructor for new Piece object
	 * @param int $id : the ID of the Piece
	 * @param PieceType $type : the type of the Piece
	 * @param PieceColor $color : the color of the Piece
	 */
	public function __construct(int $id, PieceType $type, PieceColor $color)
	{
		$this->id = $id;
		$this->type = $type;
		$this->color = $color;
	}

	/**
	 * toArray:
	 * Function to return the Piece information as an array
	 * @return array
	 */
	public function toArray(): array
	{
		return array(
			"id" => $this->id,
			"type" => $this->type->value,
			"color" => $this->color->value
		);
	}
}