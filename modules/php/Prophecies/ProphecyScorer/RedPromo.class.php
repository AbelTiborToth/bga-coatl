<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * RedPromo:
 * Scoring mechanism implementation for Prophecy Card type 49, 50 and 51
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class RedPromo implements ProphecyScorer
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
	 * @param ?PieceColor $color_4 : the color of the 3rd piece represented on the card if exists, otherwise null
	 * @var ?PieceColor $color_4
	 */
	private ?PieceColor $color_4;

	/**
	 * __construct:
	 * Constructor for new RedPromo object
	 * @param PieceColor $color_1 : the color of the 1st piece represented on the card
	 * @param PieceColor $color_2 : the color of the 2nd piece represented on the card
	 * @param PieceColor $color_3 : the color of the 3rd piece represented on the card
	 * @param ?PieceColor $color_4 : the color of the 3rd piece represented on the card if exists, otherwise null
	 */
	public function __construct(PieceColor $color_1, PieceColor $color_2, PieceColor $color_3, PieceColor $color_4 = null)
	{
		$this->color_1 = $color_1;
		$this->color_2 = $color_2;
		$this->color_3 = $color_3;
		$this->color_4 = $color_4;
	}

	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array
	{
		$counts = ($this->color_4 === null) ? [
			$this->color_1->value => 2,
			$this->color_2->value => 2,
			$this->color_3->value => 1
		] : [
			$this->color_1->value => 2,
			$this->color_2->value => 2,
			$this->color_3->value => 1,
			$this->color_4->value => 1
		];
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			// if the piece has the color represented on the card, fulfill a level
			if (isset($counts[$piece->color->value])) {
				$counts[$piece->color->value] = $counts[$piece->color->value] - 1;
				if ($counts[$piece->color->value] === 0) {
					unset($counts[$piece->color->value]);
					if (empty($counts)) return array(
						"score" => ($this->color_4 === null) ? 3 : 4,
						"level" => 1
					);
				}
			}
		}
		// in the end return 0 score and 0 level, pattern haven't found
		return array(
			"score" => 0,
			"level" => 0
		);
	}
}