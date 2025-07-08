<?php

namespace TempleScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * ColorAndLength:
 * Scoring mechanism implementation for Temple Card type 0, 1, 2, 3, 4
 * For Temple Card types check misc/card_types/temple.jpg
 */
class ColorAndLength implements TempleScorer
{
	/**
	 * color_x:
	 * The color of the crossed piece represented on the card
	 * @var PieceColor $color
	 */
	private PieceColor $color_x;

	/**
	 * length:
	 * The length of a Cóatl represented on the card
	 * @var int $length
	 */
	private int $length;

	/**
	 * __construct:
	 * Constructor for new ColorAndLength object
	 * @param PieceColor $color_x : the color of the crossed piece represented on the card
	 * @param int $length : the length of a Cóatl represented on the card
	 */
	public function __construct(PieceColor $color_x, int $length)
	{
		$this->color_x = $color_x;
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
		// start from level 1
		$level = 1;
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			// if the Cóatl contains color_x, set level to 0
			if ($piece->color === $this->color_x) {
				$level = 0;
				break;
			}
		}
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
		else return array(
			"score" => 7,
			"level" => 2
		);
	}
}