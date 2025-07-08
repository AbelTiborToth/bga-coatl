<?php


namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * ProphecyScorer\GreenBlankInfinity:
 * Scoring mechanism implementation for Prophecy Card type 44, 45, 46, 47 and 48
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class GreenBlankInfinity implements ProphecyScorer
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
	 * @var PieceColor $color_2
	 */
	private PieceColor $color_2;

	/**
	 * __construct:
	 * Constructor for new ProphecyScorer\GreenBlankInfinity object
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
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			switch ($state) {
				case 0:
					// if the piece has the 1st color represented on the card, go to state 1
					if ($piece->color === $this->color_1) $state = 1;
					// otherwise stay in state 0
					break;
				case 1:
					// if the piece has the 2nd color represented on the card, go to state 2
					if ($piece->color === $this->color_2) $state = 2;
					// otherwise if the piece doesn't have the 1st color represented on the card, go back to state 0
					else if ($piece->color !== $this->color_1) $state = 0;
					// otherwise (piece has the 1st color) stay in state 1
					break;
				case 2:
					// always go to state 3
					$state = 3;
					break;
				case 3:
					// if the piece has the 2nd color represented on the card, go to state 4
					if ($piece->color === $this->color_2) $state = 4;
					// otherwise stay in state 3
					break;
				case 4:
					// if the piece has the 1st color represented on the card, return the score and level (remaining pieces doesn't affect the score)
					if ($piece->color === $this->color_1) return array(
						"score" => 4,
						"level" => 1
					);
					// otherwise if the piece doesn't have the 2nd color represented on the card, go back to state 3
					else if ($piece->color !== $this->color_2) $state = 3;
					// otherwise (piece has the 2nd color) stay in state 4
					break;
			}
		}
		// in the end return 0 score and 0 level, pattern haven't found
		return array(
			"score" => 0,
			"level" => 0
		);
	}
}