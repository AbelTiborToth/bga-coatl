<?php


namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * ProphecyScorer\YellowRainbow:
 * Scoring mechanism implementation for Prophecy Card type 32, 33 and 34
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class YellowRainbow implements ProphecyScorer
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
	 * color_3:
	 * The color of the 3rd piece represented on the card
	 * @var PieceColor $color_3
	 */
	private PieceColor $color_3;

	/**
	 * color_4:
	 * The color of the 4th piece represented on the card
	 * @var PieceColor $color_4
	 */
	private PieceColor $color_4;

	/**
	 * color_5:
	 * The color of the 5th piece represented on the card
	 * @var PieceColor $color_5
	 */
	private PieceColor $color_5;

	/**
	 * __construct:
	 * Constructor for new YellowRainbow object
	 * @param PieceColor $color_1 : the color of the 1st piece represented on the card
	 * @param PieceColor $color_2 : the color of the 2nd piece represented on the card
	 * @param PieceColor $color_3 : the color of the 3rd piece represented on the card
	 * @param PieceColor $color_4 : the color of the 4th piece represented on the card
	 * @param PieceColor $color_5 : the color of the 5th piece represented on the card
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_2, PieceColor $color_3, PieceColor $color_4, PieceColor $color_5)
	{
		$this->color_1 = $color_1;
		$this->color_2 = $color_2;
		$this->color_3 = $color_3;
		$this->color_4 = $color_4;
		$this->color_5 = $color_5;
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
					else if ($piece->color !== $this->color_1) $state = 1;
					// otherwise (piece has the 1st color) stay in state 1
					break;
				case 2:
					// if the piece has the 3rd color represented on the card, go to state 3
					if ($piece->color === $this->color_3) $state = 3;
					// otherwise if the piece has the 1st color represented on the card, go back to state 1
					else if ($piece->color === $this->color_1) $state = 1;
					// otherwise go to state 0
					else $state = 0;
					break;
				case 3:
					// if the piece has the 4th color represented on the card, go to state 4
					if ($piece->color === $this->color_4) $state = 4;
					// otherwise if the piece has the 1st color represented on the card, go back to state 1
					else if ($piece->color === $this->color_1) $state = 1;
					// otherwise go to state 0
					else $state = 0;
					break;
				case 4:
					// if the piece has the 5th color represented on the card, return the score and level (remaining pieces doesn't affect the score)
					if ($piece->color === $this->color_5) return array(
						"score" => 7,
						"level" => 1
					);
					// otherwise if the piece has the 1st color represented on the card, go back to state 1
					else if ($piece->color === $this->color_1) $state = 1;
					// otherwise go to state 0
					else $state = 0;
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