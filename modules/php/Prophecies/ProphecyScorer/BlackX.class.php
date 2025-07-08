<?php

namespace ProphecyScorer;

use Cooatl;
use Enums\PieceColor;
use Enums\PieceType;

/**
 * BlackX:
 * Scoring mechanism implementation for Prophecy Card type 5, 6, 7, 8 and 9
 * For Prophecy Card types check misc/card_types/prophecy.jpg
 */
class BlackX implements ProphecyScorer
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
		$state = $coatl->pieces[array_key_first($coatl->pieces)]->type === PieceType::Head ? 0 : -1;
		$level = 0;
		// iterate though Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			switch ($state) {
				case -1:
					// if the piece DOESN'T have the color represented on the card, go to state 0
					if ($piece->color !== $this->color) $state = 0;
					// otherwise stay in state -1
					break;
				case 0:
					// if the piece has the color represented on the card, go to state 1
					// (at the beginning we ignore the first crossed piece, since it can be null)
					if ($piece->color === $this->color) $state = 1;
					break;
				case 1:
					// if the piece has the color represented on the card, go to state 2
					if ($piece->color === $this->color) $state = 2;
					// otherwise go back to state 0
					else $state = 0;
					break;
				case 2:
					// if the piece DOESN'T have the color represented on the card, fulfill a level
					if ($piece->color !== $this->color) {
						$level++;
						// if the 2nd level is fulfilled, return the score and level (remaining pieces doesn't affect the score)
						if ($level === 2) return array(
							"score" => 5,
							"level" => 2
						);
						// go back to state 0
						$state = 0;
					}
					else $state = -1; // otherwise go back to state -1
					break;
			}
		}
		// at the end if last state is state 2, fulfill a level (since crossed piece can be null)
		if ($state == 2 && $coatl->pieces[array_key_last($coatl->pieces)]->type === PieceType::Tail) {
			$level++;
			// if the 2nd level is fulfilled, return the score and level
			if ($level === 2) return array(
				"score" => 5,
				"level" => 2
			);
		}
		// return the score and level, depending on the level
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