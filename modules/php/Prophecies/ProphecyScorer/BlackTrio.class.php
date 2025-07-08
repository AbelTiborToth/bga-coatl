<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * BlackTrio:
 * Scoring mechanism implementation for Prophecy Card type 10, 11, 12, 13 and 14
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class BlackTrio implements ProphecyScorer
{
	/**
	 * color:
	 * The color of the piece represented on the card
	 * @var PieceColor $color
	 */
	private PieceColor $color;

	/**
	 * __construct:
	 * Constructor for new BlackTrio object
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
		// start at state 0
		$state = 0;
		$level = 0;
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			switch ($state) {
				case 0:
					// if the piece has the color represented on the card, go to state 1
					if ($piece->color === $this->color) $state = 1;
					// otherwise stay in state 0
					break;
				case 1:
					// if the piece has the color represented on the card, go to state 2
					if ($piece->color === $this->color) $state = 2;
					// otherwise go back to state 0
					else $state = 0;
					break;
				case 2:
					// if the piece has the color represented on the card, fulfill a level
					if ($piece->color === $this->color) {
						$level++;
						// if the 2nd level is fulfilled, return the score and level (remaining pieces doesn't affect the score)
						if ($level === 2) return array(
							"score" => 7,
							"level" => 2
						);
					}
					// always go back to state 0
					$state = 0;
					break;
			}
		}
		// in the end return the score and level, depending on the level
		if ($level == 0) return array(
			"score" => 0,
			"level" => 0
		);
		else return array(
			"score" => 3,
			"level" => 1
		);
	}
}