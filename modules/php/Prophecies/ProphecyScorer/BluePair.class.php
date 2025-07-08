<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * BlackTrio:
 * Scoring mechanism implementation for Prophecy Card type 15, 16, 17, 18, 19, 20 and 21
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class BluePair implements ProphecyScorer
{
	/**
	 * color_1:
	 * The color of the 1st piece represented on the card
	 * @var PieceColor $color_1
	 */
	private PieceColor $color_1;

	/**
	 * color_2:
	 * The color of the 2nd piece represented on the card
	 * @var PieceColor $color_1
	 */
	private PieceColor $color_2;

	/**
	 * __construct:
	 * Constructor for new BlackTrio object
	 * @param PieceColor $color_1 : the color of the 1st piece represented on the card
	 * @param PieceColor $color_2 : the color of the 2nd piece represented on the card
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_2)
	{
		$this->color_1 = $color_1;
		$this->color_2 = $color_2;
	}

	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array
	{
		// start at state 0
		$state = 0;
		$level = 0;
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			switch ($state) {
				case 0:
					// if the piece has the 1st color represented on the card, go to state 1
					if ($piece->color === $this->color_1) $state = 1;
					// otherwise stay in state 0
					break;
				case 1:
					// if the piece has the 2nd color represented on the card, fulfill a level and go back to state 0
					if ($piece->color === $this->color_2) {
						$level++;
						// if the 3rd level is fulfilled, return the score and level (remaining pieces doesn't affect the score)
						if ($level === 3) return array(
							"score" => 5,
							"level" => 3
						);
						$state = 0;
					}
					// otherwise if the piece doesn't have the 1st color represented on the card, go back to state 0
					else if ($piece->color !== $this->color_1) $state = 0;
					// otherwise (piece has the 1st color) stay in state 1
					break;
			}
		}
		// in the end return the score and level, depending on the level
		if ($level == 0) return array(
			"score" => 0,
			"level" => 0
		);
		else if ($level == 1) return array(
			"score" => 1,
			"level" => 1
		);
		else if ($level == 2) return array(
			"score" => 3,
			"level" => 2
		);
		else return array(
			"score" => 5,
			"level" => 3
		);
	}
}