<?php

namespace TempleScorer;

use Cooatl;

/**
 * PromoLength:
 * Scoring mechanism implementation for Temple Card type 15
 * For Temple Card types check misc/card_types/temple.jpg
 */
class PromoLength implements TempleScorer
{
	/**
	 * card_color:
	 * The color of the prophecy card represented on the card
	 * @var string $card_color
	 */
	private string $card_color;

	/**
	 * length:
	 * The length of a Cóatl represented on the card
	 * @var int $length
	 */
	private int $length;

	/**
	 * __construct:
	 * Constructor for new ColorAndEqual object
	 * @param string $card_color : the color of the prophecy card represented on the card
	 * @param int $length : the length of a Cóatl represented on the card
	 */
	public function __construct(string $card_color, int $length)
	{
		$this->card_color = $card_color;
		$this->length = $length;
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
		$level = 1;
		// iterate though the Cóatl prophecy cards
		foreach ($coatl->prophecies as $prophecy) {
			if ($prophecy->color === $this->card_color) {
				$level = 0;
				break;
			}
		}
		// if the Cóatl length equals the length represented on the card, fulfill a level
		if (count($coatl->pieces) === $this->length) $level++;
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