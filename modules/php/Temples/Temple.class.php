<?php

require_once("TempleScorer/TempleScorer.interface.php");

require_once("TempleScorer/ColorAndLength.class.php");
require_once("TempleScorer/EqualAndLength.class.php");
require_once("TempleScorer/ColorAndEqual.class.php");
require_once("TempleScorer/PromoTriple.class.php");
require_once("TempleScorer/PromoEqual.class.php");
require_once("TempleScorer/PromoLength.class.php");
require_once("TempleScorer/PromoColor.class.php");

use Enums\PieceColor;
use TempleScorer\ColorAndEqual;
use TempleScorer\ColorAndLength;
use TempleScorer\EqualAndLength;
use TempleScorer\PromoColor;
use TempleScorer\PromoEqual;
use TempleScorer\PromoLength;
use TempleScorer\PromoTriple;
use TempleScorer\TempleScorer;

/**
 * Temple:
 * Class for Temple card objects
 */
readonly class Temple
{
	/**
	 * id:
	 * The ID of the Temple card
	 * (assigned automatically by the database)
	 * @var int
	 */
	public int $id;

	/**
	 * type:
	 * The type of the Temple card
	 * For Temple Card types check misc/card_types/temple.jpg
	 * @var int
	 */
	public int $type;

	/**
	 * location:
	 * The location of the Temple card
	 * (e.g. "supply", "hand")
	 * @var string
	 */
	public string $location;

	/**
	 * location_arg:
	 * The location argument of the Temple card
	 * (e.g. the id of a player)
	 * @var int
	 */
	public int $location_arg;

	/**
	 * __construct:
	 * Constructor for new Prophecy object
	 * @param int $id : the ID of the Temple card
	 * @param int $type : the type of the Temple card
	 * @param string $location : the location of the Temple card
	 * @param int $location_arg : the location argument of the Temple card
	 */
	public function __construct(int $id, int $type, string $location, int $location_arg)
	{
		$this->id = $id;
		$this->type = $type;
		$this->location = $location;
		$this->location_arg = $location_arg;
	}

	/**
	 * getTempleScorer:
	 * Function to return the TempleScorer according to the Temple card type
	 * For Temple Card types check misc/card_types/temple.jpg
	 * @return TempleScorer
	 */
	public function getTempleScorer(): TempleScorer
	{
		return match ($this->type) {
			0 => new ColorAndLength(PieceColor::Yellow, 7),
			1 => new ColorAndLength(PieceColor::Black, 8),
			2 => new ColorAndLength(PieceColor::Green, 9),
			3 => new ColorAndLength(PieceColor::Blue, 10),
			4 => new ColorAndLength(PieceColor::Red, 11),

			5 => new EqualAndLength(PieceColor::Red, PieceColor::Yellow, 12),
			6 => new EqualAndLength(PieceColor::Yellow, PieceColor::Green, 6),
			7 => new EqualAndLength(PieceColor::Blue, PieceColor::Black, 9),
			8 => new EqualAndLength(PieceColor::Red, PieceColor::Green, 6),
			9 => new EqualAndLength(PieceColor::Blue, PieceColor::Yellow, 12),

			10 => new ColorAndEqual(PieceColor::Black, PieceColor::Blue, PieceColor::Red),
			11 => new ColorAndEqual(PieceColor::Blue, PieceColor::Black, PieceColor::Red),
			12 => new ColorAndEqual(PieceColor::Green, PieceColor::Black, PieceColor::Yellow),
			13 => new ColorAndEqual(PieceColor::Red, PieceColor::Blue, PieceColor::Green),
			14 => new ColorAndEqual(PieceColor::Yellow, PieceColor::Black, PieceColor::Green),

			15 => new PromoTriple("blue", PieceColor::Red, PieceColor::Blue, 10),

			16 => new PromoEqual("red", PieceColor::Yellow, PieceColor::Black),

			17 => new PromoLength("black", 11),
			18 => new PromoLength("green", 8),

			19 => new PromoColor("yellow", PieceColor::Green),

			default => throw new BgaVisibleSystemException("Temple.class.php: Undefined Temple card type")
		};
	}
}