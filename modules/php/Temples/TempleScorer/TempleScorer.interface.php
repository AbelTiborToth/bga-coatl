<?php

namespace TempleScorer;

use Cooatl;

/**
 * TempleScorer:
 * Interface for implementing individual scoring mechanisms for each Temple Card type
 */
interface TempleScorer
{
	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array;
}