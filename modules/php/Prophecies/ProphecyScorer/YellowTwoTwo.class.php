<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * ProphecyScorer\YellowRainbow:
 * Scoring mechanism implementation for Prophecy Card type 25, 26, 27, 28, 29, 30 and 31
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class YellowTwoTwo implements ProphecyScorer
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
	 * middle:
	 * Maybe bool to indicate the type of the middle piece in the pattern
	 * true : color_1 on middle
	 * false : color_2 on middle
	 * null : nothing on middle.
	 * @var ?bool
	 */
	private ?bool $middle;

	/**
	 * __construct:
	 * Constructor for new ProphecyScorer\YellowRainbow object
	 * @param PieceColor $color_1 : the color of the 1st piece represented on the card
	 * @param PieceColor $color_2 : the color of the 2nd piece represented on the card
	 * @param bool $middle : indicator of the type of middle piece in the pattern
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_2, bool $middle = null)
	{
		$this->color_1 = $color_1;
		$this->color_2 = $color_2;
		$this->middle = $middle;
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
					// if the piece has the 1st color represented on the card, go to state 2 or 3
					if ($piece->color === $this->color_1) {
						// if there is a piece in the middle, go to state 2
						if ($this->middle !== null) $state = 2;
						// otherwise, go to state 3
						else $state = 3;
					}
					// otherwise, go back to state 0
					else $state = 0;
					break;
				case 2:
					// if the middle piece has the correct color according to the indicator $middle, go to state 3
					if (($this->middle && $piece->color === $this->color_1) || (!$this->middle && $piece->color === $this->color_2)) $state = 3;
					// otherwise if the piece doesn't have the 1st color represented on the card, go to state 1
					else if ($piece->color !== $this->color_1) $state = 0;
					// otherwise (piece has the 1st color but not color_1 on middle) stay in state 2
					break;
				case 3:
					// if the piece has the 2nd color represented on the card, go to state 4
					if ($piece->color === $this->color_2) $state = 4;
					// otherwise if the piece has the 1st color represented on the card
					else if ($piece->color === $this->color_1) {
						// if 2nd color has been on middle, go back to state 1
						if ($this->middle !== null && !$this->middle) $state = 1;
						//otherwise stay in state 3
					}
					// otherwise, go to state 0
					else $state = 0;
					break;
				case 4:
					// if the piece has the 2nd color represented on the card, return the score and level (remaining pieces doesn't affect the score)
					if ($piece->color === $this->color_2) {
						if ($this->middle !== null) return array(
							"score" => 6,
							"level" => 1
						);
						else return array(
							"score" => 4,
							"level" => 1
						);
					}
					// otherwise if the piece has the 1st color represented on the card, go to state 1
					else if ($piece->color === $this->color_1) $state = 1;
					// otherwise, go to state 0
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