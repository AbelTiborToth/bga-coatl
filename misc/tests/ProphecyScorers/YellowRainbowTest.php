<?php

namespace misc\misc\tests;

require_once("../../modules/php/Enums/PieceColor.enum.php");
require_once("../../modules/php/Enums/PieceType.enum.php");
require_once("../../modules/php/Prophecies/Prophecy.class.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/ProphecyScorer.interface.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/YellowRainbow.class.php");
require_once('../../modules/php/Pieces/Piece.class.php');
require_once('../../modules/php/Coatls/Cooatl.class.php');

use Cooatl;
use Enums\PieceColor;
use Enums\PieceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Piece;
use ProphecyScorer\YellowRainbow;

/**
 * YellowRainbowTest:
 * YellowRainbowTest class is responsible for testing the functionality of the YellowRainbow class.
 * It validates the scoring mechanism for the game elements based on specific test cases.
 */
class YellowRainbowTest extends TestCase
{
	/**
	 * test_YellowRainbow:
	 * Tests the scoring and level calculation for the YellowRainbow class based on input parameters.
	 * @param PieceColor $piece_color_1 The color of the first piece used for scoring.
	 * @param PieceColor $piece_color_2 The color of the second piece used for scoring.
	 * @param PieceColor $piece_color_3 The color of the third piece used for scoring.
	 * @param PieceColor $piece_color_4 The color of the fourth piece used for scoring.
	 * @param PieceColor $piece_color_5 The color of the fifth piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_YellowRainbow')]
	public function test_YellowRainbow(PieceColor $piece_color_1, PieceColor $piece_color_2, PieceColor $piece_color_3, PieceColor $piece_color_4, PieceColor $piece_color_5, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new YellowRainbow($piece_color_1, $piece_color_2, $piece_color_3, $piece_color_4, $piece_color_5);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_YellowRainbow:
	 * Provides test data combinations for the `provider_YellowRainbow` method.
	 * @return array
	 */
	public static function provider_YellowRainbow(): array
	{
		$data = array();
		foreach ([
						[
							PieceColor::Yellow,
							PieceColor::Black,
							PieceColor::Red,
							PieceColor::Green,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Black,
							PieceColor::Blue,
							PieceColor::Yellow,
							PieceColor::Green,
						],
						[
							PieceColor::Black,
							PieceColor::Green,
							PieceColor::Red,
							PieceColor::Blue,
							PieceColor::Yellow
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
					new Piece(9, PieceType::Body, $piece_colors[3]),
					new Piece(10, PieceType::Body, $piece_colors[4]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
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
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
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
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
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
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[3]),
					new Piece(2, PieceType::Body, $piece_colors[3]),
					new Piece(3, PieceType::Body, $piece_colors[3]),
					new Piece(4, PieceType::Body, $piece_colors[3]),
					new Piece(5, PieceType::Body, $piece_colors[3]),
					new Piece(6, PieceType::Body, $piece_colors[3]),
					new Piece(7, PieceType::Body, $piece_colors[3]),
					new Piece(8, PieceType::Body, $piece_colors[3]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[4]),
					new Piece(2, PieceType::Body, $piece_colors[4]),
					new Piece(3, PieceType::Body, $piece_colors[4]),
					new Piece(4, PieceType::Body, $piece_colors[4]),
					new Piece(5, PieceType::Body, $piece_colors[4]),
					new Piece(6, PieceType::Body, $piece_colors[4]),
					new Piece(7, PieceType::Body, $piece_colors[4]),
					new Piece(8, PieceType::Body, $piece_colors[4]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[2]),
					new Piece(8, PieceType::Body, $piece_colors[3]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[3]),
					new Piece(8, PieceType::Body, $piece_colors[4]),
					new Piece(9, PieceType::Body, $piece_colors[4]),
					new Piece(10, PieceType::Body, $piece_colors[4]),
				], [], null, false),
				7,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[3]),
					new Piece(7, PieceType::Body, $piece_colors[4]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[3]),
					new Piece(7, PieceType::Body, $piece_colors[4]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[3]),
					new Piece(6, PieceType::Body, $piece_colors[3]),
					new Piece(7, PieceType::Body, $piece_colors[4]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				$piece_colors[2],
				$piece_colors[3],
				$piece_colors[4],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[3]),
					new Piece(6, PieceType::Body, $piece_colors[4]),
					new Piece(7, PieceType::Body, $piece_colors[4]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				7,
				1
			];
		}
		return $data;
	}
}