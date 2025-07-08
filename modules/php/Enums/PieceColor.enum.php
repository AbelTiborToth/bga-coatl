<?php

namespace Enums;

/**
 * PieceColor:
 * Enumerator for Piece color values
 */
enum PieceColor: string
{
	case Yellow = "yellow";
	case Red = "red";
	case Black = "black";
	case Green = "green";
	case Blue = "blue";
	case Gold = "gold";

	/**
	 * stringToEnum:
	 * Function to transform a string to PieceColor
	 * @param string $color
	 * @return PieceColor
	 */
	static function stringToEnum(string $color): PieceColor
	{
		return match (strtolower($color)) {
			"yellow" => PieceColor::Yellow,
			"red" => PieceColor::Red,
			"black" => PieceColor::Black,
			"green" => PieceColor::Green,
			"blue" => PieceColor::Blue,
			"gold" => PieceColor::Gold,
			default => throw new BgaVisibleSystemException("PieceColor.enum.php::stringToEnum Invalid piece color string"),
		};
	}
}

