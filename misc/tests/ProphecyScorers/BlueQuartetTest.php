<?php

namespace misc\misc\tests;

require_once("../../modules/php/Enums/PieceColor.enum.php");
require_once("../../modules/php/Enums/PieceType.enum.php");
require_once("../../modules/php/Prophecies/Prophecy.class.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/ProphecyScorer.interface.php");
require_once("../../modules/php/Prophecies/ProphecyScorer/BlueQuartet.class.php");
require_once('../../modules/php/Pieces/Piece.class.php');
require_once('../../modules/php/Coatls/Cooatl.class.php');

use Cooatl;
use Enums\PieceColor;
use Enums\PieceType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Piece;
use ProphecyScorer\BlueQuartet;

/**
 * BlueQuartetTest:
 * BlueQuartetTest class is responsible for testing the functionality of the BlueQuartet class.
 * It validates the scoring mechanism for the game elements based on specific test cases.
 */
class BlueQuartetTest extends TestCase
{
	/**
	 * test_BlueQuartet:
	 * Tests the scoring and level calculation for the BlueQuartet class based on input parameters.
	 * @param PieceColor $piece_color_1 The color of the first piece used for scoring.
	 * @param PieceColor $piece_color_2 The color of the second piece used for scoring.
	 * @param Cooatl $coatl The Cooatl object that the scoring is applied to.
	 * @param int $score The expected score for the given parameters.
	 * @param int $level The expected level for the given parameters.
	 * @return void
	 */
	#[DataProvider('provider_BlueQuartet')]
	public function test_BlueQuartet(PieceColor $piece_color_1, PieceColor $piece_color_2, Cooatl $coatl, int $score, int $level)
	{
		$scorer = new BlueQuartet($piece_color_1, $piece_color_2);
		$result = $scorer->score($coatl);
		$this->assertEquals($score, $result["score"]);
		$this->assertEquals($level, $result["level"]);
	}

	/**
	 * provider_BlueQuartet:
	 * Provides test data combinations for the `provider_BlueQuartet` method.
	 * @return array
	 */
	public static function provider_BlueQuartet(): array
	{
		$data = array();
		foreach ([
						[
							PieceColor::Black,
							PieceColor::Blue,
							PieceColor::Yellow
						],
						[
							PieceColor::Black,
							PieceColor::Blue,
							PieceColor::Red
						],
						[
							PieceColor::Black,
							PieceColor::Blue,
							PieceColor::Green
						],
						[
							PieceColor::Yellow,
							PieceColor::Red,
							PieceColor::Blue
						],
						[
							PieceColor::Red,
							PieceColor::Green,
							PieceColor::Yellow
						]
					] as $piece_colors) {
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
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
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				0,
				0
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
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
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
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
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[0]),
					new Piece(6, PieceType::Body, $piece_colors[1]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[0]),
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[2]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[0]),
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
					new Piece(2, PieceType::Body, $piece_colors[0]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
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
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[1]),
					new Piece(5, PieceType::Body, $piece_colors[2]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[2]),
					new Piece(8, PieceType::Body, $piece_colors[2]),
				], [], null, false),
				5,
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
					new Piece(7, PieceType::Body, $piece_colors[0]),
					new Piece(8, PieceType::Body, $piece_colors[1]),
				], [], null, false),
				5,
				1
			];
			$data[] = [
				$piece_colors[0],
				$piece_colors[1],
				new Cooatl(0, 0, [
					new Piece(1, PieceType::Body, $piece_colors[2]),
					new Piece(2, PieceType::Body, $piece_colors[2]),
					new Piece(3, PieceType::Body, $piece_colors[1]),
					new Piece(4, PieceType::Body, $piece_colors[2]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[2]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
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
					new Piece(2, PieceType::Body, $piece_colors[1]),
					new Piece(3, PieceType::Body, $piece_colors[0]),
					new Piece(4, PieceType::Body, $piece_colors[0]),
					new Piece(5, PieceType::Body, $piece_colors[1]),
					new Piece(6, PieceType::Body, $piece_colors[0]),
					new Piece(7, PieceType::Body, $piece_colors[1]),
					new Piece(8, PieceType::Body, $piece_colors[0]),
				], [], null, false),
				5,
				1
			];
		}
		return $data;
	}
}