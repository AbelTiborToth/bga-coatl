<?php

namespace TempleScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * EqualAndLength:
 * Scoring mechanism implementation for Temple Card type 15
 * For Temple Card types check misc/card_types/temple.jpg
 */
class PromoTriple implements TempleScorer
{
	/**
	 * card_color:
	 * The color of the prophecy card represented on the card
	 * @var string $card_color
	 */
	private string $card_color;

	/**
	 * color_1:
	 * The color on the left side of the equivalence represented on the card
	 * @var PieceColor $color_1
	 */
	private PieceColor $color_1;

	/**
	 * color_2:
	 * The color on the right side of the equivalence represented on the card
	 * @var PieceColor $color_2
	 */
	private PieceColor $color_2;

	/**
	 * length:
	 * The length of a Cóatl represented on the card
	 * @var int $length
	 */
	private int $length;

	/**
	 * __construct:
	 * Constructor for new ColorAndEqual object
	 * @param string $card_color : the color of the prophecy card represented on the card
	 * @param PieceColor $color_1 : the color on the left side of the equivalence represented on the card
	 * @param PieceColor $color_2 : the color on the right side of the equivalence represented on the card
	 * @param int $length : the length of a Cóatl represented on the card
	 */
	public function __construct(string $card_color, PieceColor $color_1, PieceColor $color_2, int $length)
	{
		$this->card_color = $card_color;
		$this->color_1 = $color_1;
		$this->color_2 = $color_2;
		$this->length = $length;
	}

	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array
	{
		// start from level 0
		$level = 1;
		// iterate though the Cóatl prophecy cards
		foreach ($coatl->prophecies as $prophecy) {
			if ($prophecy->color === $this->card_color) {
				$level = 0;
				break;
			}
		}
		$n_1 = 0;
		$n_2 = 0;
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			// count pieces with color_1 and color_2
			if ($piece->color === $this->color_1) $n_1++;
			else if ($piece->color === $this->color_2) $n_2++;
		}
		// in the end if color_1 count is not 0 and color_1 count equals color_2 count, fulfill a level
		if ($n_1 !== 0 && $n_1 === $n_2) $level++;
		// if the Cóatl length equals the length represented on the card, fulfill a level
		if (count($coatl->pieces) === $this->length) $level++;
		// return the score and level, depending on the level
		if ($level == 0) return array(
			"score" => 0,
			"level" => 0
		);
		else if ($level == 1) return array(
			"score" => 3,
			"level" => 1
		);
		else if ($level == 2) return array(
			"score" => 6,
			"level" => 2
		);
		else return array(
			"score" => 10,
			"level" => 3
		);
	}
}