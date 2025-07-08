<?php


namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * ProphecyScorer\GreenColorfulInfinity:
 * Scoring mechanism implementation for Prophecy Card type 35, 36, 37, 38, 39, 40, 41, 42 and 43
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class GreenColorfulInfinity implements ProphecyScorer
{
	/**
	 * color_1:
	 * The color of the 1st piece represented on the card
	 * @var PieceColor $color_1
	 */
	private PieceColor $color_1;

	/**
	 * color_8:
	 * The color of the piece with the infinity symbol represented on the card
	 * @var PieceColor $color_8
	 */
	private PieceColor $color_8;

	/**
	 * color_2:
	 *  The color of the 2nd piece represented on the card
	 * @var PieceColor $color_2
	 */
	private PieceColor $color_2;

	/**
	 * __construct:
	 * Constructor for new ProphecyScorer\GreenColorfulInfinity object
	 * @param PieceColor $color_1 the color of the 1st piece represented on the card
	 * @param PieceColor $color_8 the color of the piece with the infinity symbol represented on the card
	 * @param ?PieceColor $color_2 the color of the 1st piece represented on the card
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_8, PieceColor $color_2 = null)
	{
		$this->color_1 = $color_1;
		$this->color_8 = $color_8;
		// if the 2nd color is null, the 2nd piece has the same color as the 1st piece
		// see Prophecy Type 42 and 43 on misc/card_types/prophecy.jpg
		if ($color_2 === null) $this->color_2 = $color_1;
		else $this->color_2 = $color_2;
	}

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
					// if the piece has the infinity color represented on the card, go to state 2
					if ($piece->color === $this->color_8) $state = 2;
					// otherwise if the piece doesn't have the 1st color represented on the card, go back to state 0
					else if ($piece->color !== $this->color_1) $state = 0;
					// otherwise (piece has the 1st color) stay in state 1
					break;
				case 2:
					// if the piece has the 2nd color represented on the card, fulfill a level and go back to state 0
					if ($piece->color === $this->color_2) {
						$level++;
						// if the 2nd level is fulfilled, return the score and level (remaining pieces doesn't affect the score)
						if ($level === 2) return array(
							"score" => 6,
							"level" => 2
						);
						$state = 0;
					}
					// otherwise if the piece has the 1st color represented on the card, go back to state 1
					else if ($piece->color === $this->color_1) $state = 1;
					// otherwise if the piece doesn't have the infinity color represented on the card, go back to state 0
					else if ($piece->color !== $this->color_8) $state = 0;
					// otherwise (piece has the infinity color) stay in state 2
					break;
			}
		}
		// in the end return the score and level, depending on the level
		if ($level == 0) return array(
			"score" => 0,
			"level" => 0
		);
		else return array(
			"score" => 2,
			"level" => 1
		);
	}
}