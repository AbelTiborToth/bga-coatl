<?php

namespace Enums;

/**
 * PieceType:
 * Enumerator for Piece type values
 */
enum PieceType: string
{
	case Head = "head";
	case Body = "body";
	case Tail = "tail";

	/**
	 * stringToEnum:
	 * Function to transform a string to PieceType
	 * @param string $type
	 * @return PieceType
	 */
	static function stringToEnum(string $type): PieceType
	{
		return match (strtolower($type)) {
			"head" => PieceType::Head,
			"body" => PieceType::Body,
			"tail" => PieceType::Tail,
			default => throw new BgaVisibleSystemException("PieceType.enum.php::stringToEnum Invalid piece type string"),
		};
	}
}
