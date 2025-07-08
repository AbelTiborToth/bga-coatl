<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * Red:
 * Scoring mechanism implementation for Prophecy Card type 0, 1, 2, 3 and 4
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class Red implements ProphecyScorer
{
	/**
	 * color:
	 * The color of the piece represented on the card
	 * @var PieceColor $color
	 */
	private PieceColor $color;

	/**
	 * __construct:
	 * Constructor for new Red object
	 * @param PieceColor $color : the color of the piece represented on the card
	 */
	public function __construct(PieceColor $color)
	{
		$this->color = $color;
	}

	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array
	{
		$level = 0;
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			// if the piece has the color represented on the card, fulfill a level
			if ($piece->color === $this->color) {
				$level++;
				// if the 6th level is fulfilled, return the score and level (remaining pieces doesn't affect the score)
				if ($level === 6) return array(
					"score" => 5,
					"level" => 4
				);
			}
		}
		// in the end return the score and level, depending on the level
		if ($level < 3) return array(
			"score" => 0,
			"level" => 0
		);
		else if ($level == 3) return array(
			"score" => 2,
			"level" => 1
		);
		else if ($level == 4) return array(
			"score" => 3,
			"level" => 2
		);
		else return array(
			"score" => 4,
			"level" => 3
		);
	}
}