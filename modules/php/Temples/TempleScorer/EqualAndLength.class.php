<?php

namespace TempleScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * EqualAndLength:
 * Scoring mechanism implementation for Temple Card type 10, 11, 12, 13 and 14
 * For Temple Card types check misc/card_types/temple.jpg
 */
class EqualAndLength implements TempleScorer
{
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
	 * @param PieceColor $color_1 : the color on the left side of the equivalence represented on the card
	 * @param PieceColor $color_2 : the color on the right side of the equivalence represented on the card
	 * @param int $length : the length of a Cóatl represented on the card
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_2, int $length)
	{
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
		$level = 0;
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
		else return array(
			"score" => 7,
			"level" => 2
		);
	}
}