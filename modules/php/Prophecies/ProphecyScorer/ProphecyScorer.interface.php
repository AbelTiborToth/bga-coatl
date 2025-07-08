<?php

namespace ProphecyScorer;

use Cooatl;

/**
 * ProphecyScorer:
 * Interface for implementing individual scoring mechanisms for each Prophecy Card type
 */
interface ProphecyScorer
{
	/**
	 * score:
	 * Function to calculate the score and level for a Cóatl
	 * @param Cooatl $coatl : the Cóatl to check
	 * @return array ["score" => int, "level" => int]
	 */
	public function score(Cooatl $coatl): array;
}