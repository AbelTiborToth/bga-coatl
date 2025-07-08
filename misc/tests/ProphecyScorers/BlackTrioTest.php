<?php

namespace misc\misc\tests;

require_once("../../modules/php/Enums/PieceColor.enum.php");
require_once("../../modules/php/Enums/PieceType.enum.php");
require_once("../../modules/php/Prophecies/Prophecy.class.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/ProphecyScorer.interface.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/BlackTrio.class.php");
require_once('../../modules/php/Pieces/Piece.class.php');
require_once('../../modules/php/Coatls/Cooatl.class.php');

use Cooatl;
use Enums\PieceColor;
use Enums\PieceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Piece;
use ProphecyScorer\BlackTrio;

/**
 * BlackTrioTest:
 * BlackTrioTest class is responsible for testing the functionality of the BlackTrio class.
 * It validates the scoring mechanism for the game elements based on specific test cases.
 */
class BlackTrioTest extends TestCase
{
	/**
	 * test_BlackTrio:
	 * Tests the scoring and level calculation for the BlackTrio class based on input parameters.
	 * @param PieceColor $piece_color The color of the piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_BlackTrio')]
	public function test_BlackTrio(PieceColor $piece_color, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new BlackTrio($piece_color);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_BlackTrio:
	 * Provides test data combinations for the test_BlackTrio` method.
	 * @return array
	 */
	public static function provider_BlackTrio(): array
	{
		$data = array();
		foreach (PieceColor::cases() as $piece_color) {
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
				], [], null, false),
				3,
				1
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
					new Piece(4, PieceType::Body, $piece_color),
				], [], null, false),
				3,
				1
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
					new Piece(4, PieceType::Body, $piece_color),
					new Piece(5, PieceType::Body, $piece_color),
				], [], null, false),
				3,
				1
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
					new Piece(4, PieceType::Body, $piece_color),
					new Piece(5, PieceType::Body, $piece_color),
					new Piece(6, PieceType::Body, $piece_color),
				], [], null, false),
				7,
				2
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
					new Piece(4, PieceType::Body, $piece_color),
					new Piece(5, PieceType::Body, $piece_color),
					new Piece(6, PieceType::Body, $piece_color),
					new Piece(7, PieceType::Body, $piece_color),
				], [], null, false),
				7,
				2
			];
			$data[] = [
				$piece_color,
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_color),
					new Piece(2, PieceType::Body, $piece_color),
					new Piece(3, PieceType::Body, $piece_color),
					new Piece(4, PieceType::Body, $piece_color),
					new Piece(5, PieceType::Body, $piece_color),
					new Piece(6, PieceType::Body, $piece_color),
					new Piece(7, PieceType::Body, $piece_color),
					new Piece(8, PieceType::Body, $piece_color),
				], [], null, false),
				7,
				2
			];
		}

		foreach ([
						[
							PieceColor::Yellow,
							PieceColor::Red
						],
						[
							PieceColor::Red,
							PieceColor::Black
						],
						[
							PieceColor::Black,
							PieceColor::Green
						],
						[
							PieceColor::Green,
							PieceColor::Blue
						],
						[
							PieceColor::Blue,
							PieceColor::Yellow
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
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
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
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
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				3,
				1
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				7,
				2
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				7,
				2
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				3,
				1
			];
			$data[] = [
				$piece_colors[0],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[1]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				7,
				2
			];
		}
		return $data;
	}
}