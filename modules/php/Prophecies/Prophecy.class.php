<?php

require_once("ProphecyScorer/ProphecyScorer.interface.php");

require_once("ProphecyScorer/Red.class.php");
require_once("ProphecyScorer/BlackX.class.php");
require_once("ProphecyScorer/BlackTrio.class.php");
require_once("ProphecyScorer/BluePair.class.php");
require_once("ProphecyScorer/BlueQuartet.class.php");
require_once("ProphecyScorer/YellowTwoTwo.class.php");
require_once("ProphecyScorer/YellowRainbow.class.php");
require_once("ProphecyScorer/GreenColorfulInfinity.class.php");
require_once("ProphecyScorer/GreenBlankInfinity.class.php");
require_once("ProphecyScorer/RedPromo.class.php");

use Enums\PieceColor;
use ProphecyScorer\BlackTrio;
use ProphecyScorer\BlackX;
use ProphecyScorer\BluePair;
use ProphecyScorer\BlueQuartet;
use ProphecyScorer\GreenBlankInfinity;
use ProphecyScorer\GreenColorfulInfinity;
use ProphecyScorer\ProphecyScorer;
use ProphecyScorer\Red;
use ProphecyScorer\RedPromo;
use ProphecyScorer\YellowRainbow;
use ProphecyScorer\YellowTwoTwo;

/**
 * Prophecy:
 * Class for Prophecy card objects
 */
readonly class Prophecy
{
	/**
	 * id:
	 * The ID of the Prophecy card
	 * (assigned automatically by the database)
	 * @var int
	 */
	public int $id;

	/**
	 * color:
	 * The color of the Prophecy card
	 * (unused, will be needed for solo mode and promo cards, haven't discussed with the publisher yet)
	 * @var string
	 */
	public string $color;

	/**
	 * type:
	 * The type of the Prophecy card
	 * For Prophecy Card types check misc/card_types/prophecy.jpg
	 * @var int
	 */
	public int $type;

	/**
	 * location:
	 * The location of the Prophecy card
	 * (e.g. "supply", "hand")
	 * @var string
	 */
	public string $location;

	/**
	 * location_arg:
	 * The location argument of the Prophecy card
	 * (e.g. the id of a player)
	 * @var int
	 */
	public int $location_arg;

	/**
	 * __construct:
	 * Constructor for new Prophecy object
	 * @param int $id : the ID of the Prophecy card
	 * @param string $color : the color of the Prophecy card
	 * @param int $type : the type of the Prophecy card
	 * @param string $location : the location of the Prophecy card
	 * @param int $location_arg : the location argument of the Prophecy card
	 */
	public function __construct(int $id, string $color, int $type, string $location, int $location_arg)
	{
		$this->id = $id;
		$this->color = $color;
		$this->type = $type;
		$this->location = $location;
		$this->location_arg = $location_arg;
	}

	/**
	 * toArray:
	 * Function to return the Prophecy card information as an array
	 * @return array
	 */
	public function toArray(): array
	{
		return array(
			"id" => $this->id,
			"type" => $this->type,
			"color" => $this->color
		);
	}

	/**
	 * getProphecyScorer:
	 * Function to return the ProphecyScorer according to the Prophecy card type
	 * For Prophecy Card types check misc/card_types/prophecy.jpg
	 * @return ProphecyScorer
	 */
	public function getProphecyScorer(): ProphecyScorer
	{
		return match ($this->type) {
			0 => new Red(PieceColor::Yellow),
			1 => new Red(PieceColor::Red),
			2 => new Red(PieceColor::Black),
			3 => new Red(PieceColor::Green),
			4 => new Red(PieceColor::Blue),

			5 => new BlackX(PieceColor::Yellow),
			6 => new BlackX(PieceColor::Red),
			7 => new BlackX(PieceColor::Black),
			8 => new BlackX(PieceColor::Green),
			9 => new BlackX(PieceColor::Blue),

			10 => new BlackTrio(PieceColor::Yellow),
			11 => new BlackTrio(PieceColor::Red),
			12 => new BlackTrio(PieceColor::Black),
			13 => new BlackTrio(PieceColor::Green),
			14 => new BlackTrio(PieceColor::Blue),

			15 => new BluePair(PieceColor::Yellow, PieceColor::Green),
			16 => new BluePair(PieceColor::Red, PieceColor::Black),
			17 => new BluePair(PieceColor::Yellow, PieceColor::Blue),
			18 => new BluePair(PieceColor::Green, PieceColor::Blue),
			19 => new BluePair(PieceColor::Black, PieceColor::Green),
			20 => new BluePair(PieceColor::Yellow, PieceColor::Black),
			21 => new BluePair(PieceColor::Red, PieceColor::Blue),

			22 => new BlueQuartet(PieceColor::Black, PieceColor::Blue),
			23 => new BlueQuartet(PieceColor::Yellow, PieceColor::Red),
			24 => new BlueQuartet(PieceColor::Red, PieceColor::Green),

			25 => new YellowTwoTwo(PieceColor::Red, PieceColor::Green, true),
			26 => new YellowTwoTwo(PieceColor::Black, PieceColor::Green, false),
			27 => new YellowTwoTwo(PieceColor::Yellow, PieceColor::Blue, false),
			28 => new YellowTwoTwo(PieceColor::Blue, PieceColor::Yellow),
			29 => new YellowTwoTwo(PieceColor::Red, PieceColor::Blue),
			30 => new YellowTwoTwo(PieceColor::Black, PieceColor::Yellow),
			31 => new YellowTwoTwo(PieceColor::Green, PieceColor::Red),

			32 => new YellowRainbow(PieceColor::Yellow, PieceColor::Black, PieceColor::Red, PieceColor::Green, PieceColor::Blue),
			33 => new YellowRainbow(PieceColor::Red, PieceColor::Black, PieceColor::Blue, PieceColor::Yellow, PieceColor::Green),
			34 => new YellowRainbow(PieceColor::Black, PieceColor::Green, PieceColor::Red, PieceColor::Blue, PieceColor::Yellow),

			35 => new GreenColorfulInfinity(PieceColor::Yellow, PieceColor::Black, PieceColor::Red),
			36 => new GreenColorfulInfinity(PieceColor::Green, PieceColor::Yellow, PieceColor::Black),
			37 => new GreenColorfulInfinity(PieceColor::Yellow, PieceColor::Blue, PieceColor::Green),
			38 => new GreenColorfulInfinity(PieceColor::Blue, PieceColor::Green, PieceColor::Black),
			39 => new GreenColorfulInfinity(PieceColor::Red, PieceColor::Black, PieceColor::Blue),
			40 => new GreenColorfulInfinity(PieceColor::Red, PieceColor::Blue, PieceColor::Yellow),
			41 => new GreenColorfulInfinity(PieceColor::Green, PieceColor::Red, PieceColor::Black),
			42 => new GreenColorfulInfinity(PieceColor::Green, PieceColor::Yellow),
			43 => new GreenColorfulInfinity(PieceColor::Blue, PieceColor::Green),

			44 => new GreenBlankInfinity(PieceColor::Black, PieceColor::Yellow),
			45 => new GreenBlankInfinity(PieceColor::Green, PieceColor::Black),
			46 => new GreenBlankInfinity(PieceColor::Black, PieceColor::Blue),
			47 => new GreenBlankInfinity(PieceColor::Yellow, PieceColor::Red),
			48 => new GreenBlankInfinity(PieceColor::Red, PieceColor::Blue),

			49 => new RedPromo(PieceColor::Yellow, PieceColor::Green, PieceColor::Red),
			50 => new RedPromo(PieceColor::Black, PieceColor::Blue, PieceColor::Red),
			51 => new RedPromo(PieceColor::Red, PieceColor::Black, PieceColor::Green),

			52 => new RedPromo(PieceColor::Yellow, PieceColor::Black, PieceColor::Red, PieceColor::Blue),
			53 => new RedPromo(PieceColor::Green, PieceColor::Blue, PieceColor::Yellow, PieceColor::Black),
			54 => new RedPromo(PieceColor::Yellow, PieceColor::Red, PieceColor::Green, PieceColor::Blue),

			default => throw new BgaVisibleSystemException("Prophecy.class.php: Undefined Prophecy card type")
		};
	}
}