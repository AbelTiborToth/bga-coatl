<?php

namespace TempleScorer;

use Cooatl;
use Enums\PieceColor;

/**
 * PromoColor:
 * Scoring mechanism implementation for Temple Card type 15
 * For Temple Card types check misc/card_types/temple.jpg
 */
class PromoColor implements TempleScorer
{
	/**
	 * card_color:
	 * The color of the prophecy card represented on the card
	 * @var string $card_color
	 */
	private string $card_color;

	/**
	 * color_x:
	 * The color of the crossed piece represented on the card
	 * @var PieceColor $color_x
	 */
	private PieceColor $color_x;

	/**
	 * __construct:
	 * Constructor for new ColorAndEqual object
	 * @param string $card_color : the color of the prophecy card represented on the card
	 * @param PieceColor $color_x : the length of a Cóatl represented on the card
	 */
	public function __construct(string $card_color, PieceColor $color_x)
	{
		$this->card_color = $card_color;
		$this->color_x = $color_x;
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
		$level = 2;
		// iterate though the Cóatl prophecy cards
		foreach ($coatl->prophecies as $prophecy) {
			if ($prophecy->color === $this->card_color) {
				$level--;
				break;
			}
		}
		// iterate though the Cóatl pieces
		foreach ($coatl->pieces as $piece) {
			if ($piece->color === $this->color_x) {
				$level--;
				break;
			}
		}
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