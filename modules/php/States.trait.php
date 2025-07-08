<?php

/**
 * States:
 *
 *  Trait for isolating Game state actions and functions in the main game logic
 *
 * Here we crate methods as "game state actions" (see "action" property in states.inc.php).
 * The action method of state X is called everytime the current game state is set to X.
 */
trait States
{
	/**
	 * stEveryoneButFirstPlayerActive:
	 * Method to make everyone but first player active
	 * @return void
	 */
	function stEveryoneButFirstPlayerActive(): void
	{
		$this->gamestate->setAllPlayersMultiactive();
		$this->gamestate->setPlayerNonMultiactive($this->getFirstPlayerId(), "firstPlayer");
	}

	/**
	 * stFirstPlayer:
	 * Method to make first player active
	 * @return void
	 */
	function stFirstPlayer(): void
	{
		$this->gamestate->changeActivePlayer($this->getFirstPlayerId());
		$this->gamestate->nextState('playerTurn');
	}

	/**
	 * stNextPlayer:
	 * Method to make next player active (relative to current player)
	 * @return void
	 */
	function stNextPlayer(): void
	{
		$player_id = self::getActivePlayerId();
		self::incStat(1, "turns_number");
		self::incStat(1, "turns_number", $player_id);
		$final_round = $this->globals->get("final_round");
		// If not final round, check game and
		if ($final_round == 0) {
			$final_round_begins = $this->countFinishedCoatls($this->getCoatlsOfPlayer($player_id)) === 3;
			if ($final_round_begins) {
				$final_player_no = $this->getPlayerNoById($player_id);
				$this->globals->set("final_round", intval($final_player_no));
				self::notifyAllPlayers("finalRound", clienttranslate('The final turns begins!'));
				$next_player_id = self::getPlayerAfter($player_id);
				$this->globals->set("final_turn_no", ($this->globals->get("final_round") < $this->getPlayerNoById($next_player_id) ? 2 : 1));
			}
			$this->clearLog();
			$this->activateNextPlayer();
			$this->gamestate->nextState('playerTurn');
		}
		// If final round with empty body piece bag
		else if ($final_round == 5) {
			if ($this->globals->get("final_turn_no") == 3) {
				$next_player_id = self::getPlayerAfter($player_id);
				if ($this->getPlayerNoById($next_player_id) == 1) {
					$this->globals->set("final_turn_no", 1);
				}
				$this->clearLog();
				$this->activateNextPlayer();
				$this->gamestate->nextState('playerTurn');
			}
			else {
				$next_player_id = self::getPlayerAfter($player_id);
				if ($this->getPlayerNoById($next_player_id) == 1) {
					$this->endGame();
				}
				$this->clearLog();
				$this->activateNextPlayer();
				$this->gamestate->nextState('playerTurn');
			}
		}
		// If final round with 3rd finished Cóatl
		else {
			if ($this->globals->get("final_turn_no") == 2) {
				$this->globals->set("final_turn_no", 1);
				$this->clearLog();
				if (!$this->isPlayerTurnPossible($player_id)) {
					if ($this->getUniqueValueFromDB("SELECT count(player_id) FROM player WHERE player_eliminated = 0") == 1) $this->endGame();
					else {
						self::activeNextPlayer();
						$next_player_id = self::getActivePlayerId();
						$final_player_no = $this->globals->get("final_round");
						$this->globals->set("final_turn_no", ($final_player_no < $this->getPlayerNoById($next_player_id) ? 2 : 1));
						$this->eliminatePlayer($player_id);
					}
				}
				$this->gamestate->nextState('playerTurn');
			}
			else {
				$next_player_id = self::getPlayerAfter($player_id);
				$final_player_no = $this->globals->get("final_round");
				if ($this->getPlayerNoById($next_player_id) == $final_player_no) $this->endGame();
				else {
					$this->globals->set("final_turn_no", ($final_player_no < $this->getPlayerNoById($next_player_id) ? 2 : 1));
					$this->clearLog();
					$this->activateNextPlayer();
					$this->gamestate->nextState('playerTurn');
				}
			}
		}

	}

	/**
	 * activateNextPlayer:
	 * Method to activate next player and eliminate current if no possible actions left
	 * @return void
	 */
	function activateNextPlayer(): void
	{
		$player_id = self::getActivePlayerId();
		if (!$this->isPlayerTurnPossible($player_id)) {
			if ($this->getUniqueValueFromDB("SELECT count(player_id) FROM player WHERE player_eliminated = 0") == 1) $this->endGame();
			else {
				self::activeNextPlayer();
				$next_player_id = self::getActivePlayerId();
				$this->eliminatePlayer($player_id);
				$this->giveExtraTime($next_player_id);
			}
		}
		else {
			self::activeNextPlayer();
			$next_player_id = self::getActivePlayerId();
			$this->giveExtraTime($next_player_id);
		}
	}

	/**
	 * endGame:
	 * Method to create game statistics and end the game
	 * @return void
	 */
	function endGame(): void
	{
		$players = self::getCollectionFromDb("SELECT player_id id, player_score score FROM player");
		foreach ($players as $player_id_ => $player) {
			if ($player['score'] >= 0) {
				$fulfilled_prophecy_cards = 0;
				$fulfilled_temple_cards = 0;
				$coatl_score_max = 0;
				$coatls = $this->getCoatlsOfPlayer($player_id_);
				$coatl_n = 0;
				$length_sum = 0;
				foreach ($coatls as $coatl) {
					if ($coatl->is_locked) {
						$fulfilled_prophecy_cards += count($coatl->prophecies);
						$fulfilled_temple_cards += $coatl->temple !== null ? 1 : 0;
						$score = $coatl->getScore()["score"];
						if ($score > $coatl_score_max) $coatl_score_max = $score;
						$length_sum += count($coatl->pieces);
						self::incStat(1, "completed_coatls", $player_id_);
						self::setStat($score, "coatl_score_" . ++$coatl_n, $player_id_);
					}
				}
				$score_aux = ($fulfilled_prophecy_cards + $fulfilled_temple_cards) * 100 + $coatl_score_max;
				self::DbQuery("UPDATE player SET player_score_aux = " . $score_aux . " WHERE player_id = " . $player_id_);
				if ($coatl_n != 0) {
					self::setStat($player["score"] / $coatl_n, "coatl_score_average", $player_id_);
					self::setStat($length_sum / $coatl_n, "coatl_length_average", $player_id_);
				}
				else {
					self::setStat(0, "coatl_score_average", $player_id_);
					self::setStat(0, "coatl_length_average", $player_id_);
				}
				self::setStat($coatl_score_max, "coatl_score_max", $player_id_);
				self::setStat($fulfilled_prophecy_cards, "fulfilled_prophecy_cards", $player_id_);
				self::setStat($fulfilled_temple_cards, "fulfilled_temple_cards", $player_id_);
			}
		}
		$this->gamestate->nextState('endGame');
	}
}
