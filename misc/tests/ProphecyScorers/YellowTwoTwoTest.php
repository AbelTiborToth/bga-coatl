<?php

namespace misc\misc\tests;

require_once("../../modules/php/Enums/PieceColor.enum.php");
require_once("../../modules/php/Enums/PieceType.enum.php");
require_once("../../modules/php/Prophecies/Prophecy.class.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/ProphecyScorer.interface.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/YellowTwoTwo.class.php");
require_once('../../modules/php/Pieces/Piece.class.php');
require_once('../../modules/php/Coatls/Cooatl.class.php');

use Cooatl;
use Enums\PieceColor;
use Enums\PieceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Piece;
use ProphecyScorer\YellowTwoTwo;

/**
 * YellowTwoTwoTest:
 * YellowTwoTwoTest class is responsible for testing the functionality of the YellowTwoTwo class.
 * It validates the scoring mechanism for the game elements based on specific test cases.
 */
class YellowTwoTwoTest extends TestCase
{
	/**
	 * test_YellowTwoTwo_1:
	 * Tests the scoring and level calculation for the YellowTwoTwo class based on input parameters.
	 * @param PieceColor $piece_color_1 The color of the first piece used for scoring.
	 * @param PieceColor $piece_color_2 The color of the second piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_YellowTwoTwo_1')]
	public function test_YellowTwoTwo_1(PieceColor $piece_color_1, PieceColor $piece_color_2, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new YellowTwoTwo($piece_color_1, $piece_color_2);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_YellowTwoTwo_1:
	 * Provides test data combinations for the `provider_YellowTwoTwo_1` method.
	 * @return array
	 */
	public static function provider_YellowTwoTwo_1(): array
	{
		$data = array();
		foreach ([
						[
							PieceColor::Blue,
							PieceColor::Yellow,
							PieceColor::Red
						],
						[
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Yellow
						],
						[
							PieceColor::Black,
							PieceColor::Yellow,
							PieceColor::Green
						],
						[
							PieceColor::Green,
							PieceColor::Red,
							PieceColor::Black
						],
						[
							PieceColor::Black,
							PieceColor::Green,
							PieceColor::Yellow
						],
						[
							PieceColor::Yellow,
							PieceColor::Black,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Black
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[2]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				4,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				4,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				4,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				4,
				1
			];
		}
		return $data;
	}

	/**
	 * test_YellowTwoTwo_2:
	 * Tests the scoring and level calculation for the YellowTwoTwo class based on input parameters.
	 * @param PieceColor $piece_color_1 The color of the first piece used for scoring.
	 * @param PieceColor $piece_color_2 The color of the second piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_YellowTwoTwo_2')]
	public function test_YellowTwoTwo_2(PieceColor $piece_color_1, PieceColor $piece_color_2, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new YellowTwoTwo($piece_color_1, $piece_color_2, true);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_YellowTwoTwo_2:
	 * Provides test data combinations for the `provider_YellowTwoTwo_2` method.
	 * @return array
	 */
	public static function provider_YellowTwoTwo_2(): array
	{
		$data = array();
		foreach ([
						[
							PieceColor::Red,
							PieceColor::Green,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Yellow
						],
						[
							PieceColor::Black,
							PieceColor::Yellow,
							PieceColor::Green
						],
						[
							PieceColor::Green,
							PieceColor::Red,
							PieceColor::Black
						],
						[
							PieceColor::Black,
							PieceColor::Green,
							PieceColor::Yellow
						],
						[
							PieceColor::Yellow,
							PieceColor::Black,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Black
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[2]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
		}
		return $data;
	}

	/**
	 * test_YellowTwoTwo_3:
	 * Tests the scoring and level calculation for the YellowTwoTwo class based on input parameters.
	 * @param PieceColor $piece_color_1 The color of the first piece used for scoring.
	 * @param PieceColor $piece_color_2 The color of the second piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_YellowTwoTwo_3')]
	public function test_YellowTwoTwo_3(PieceColor $piece_color_1, PieceColor $piece_color_2, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new YellowTwoTwo($piece_color_1, $piece_color_2, false);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_YellowTwoTwo_3:
	 * Provides test data combinations for the `provider_YellowTwoTwo_3` method.
	 * @return array
	 */
	public static function provider_YellowTwoTwo_3(): array
	{
		$data = array();
		foreach ([
						[
							PieceColor::Black,
							PieceColor::Green,
							PieceColor::Blue
						],
						[
							PieceColor::Yellow,
							PieceColor::Blue,
							PieceColor::Red
						],
						[
							PieceColor::Black,
							PieceColor::Yellow,
							PieceColor::Green
						],
						[
							PieceColor::Green,
							PieceColor::Red,
							PieceColor::Black
						],
						[
							PieceColor::Black,
							PieceColor::Green,
							PieceColor::Yellow
						],
						[
							PieceColor::Yellow,
							PieceColor::Black,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Black
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[2]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				6,
				1
			];
		}
		return $data;
	}
}