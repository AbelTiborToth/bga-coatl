<?php

/**
 * ActionLog:
 * Trait for isolating Action Log related methods and functions in the main game logic
 * Action log is used for the undo feature
 */
trait ActionLog
{
	/**
	 * NOTE: We consider some actions as phantom such like drawing a Prophecy card from the deck
	 * (A player can't undo this action, but we have to log it anyway)
	 */

	/**
	 * logIsFullEmpty:
	 * Function to get if the log is full empty, counting phantom entries as well
	 * NOTE: We save some actions with phantom entries: the player can't undo them, but we need to log them
	 *    (A phantom entry's action_args filed is NULL)
	 * @return bool true: if the log is full empty, false : otherwise
	 */
	function logIsFullEmpty(): bool
	{
		return $this->getUniqueValueFromDB("SELECT action_id FROM action_log LIMIT 1") === null;
	}

	/**
	 * logIsFullEmpty:
	 * Function to get the number of entries, not counting phantom entries
	 * NOTE: We save some actions with phantom entries: the player can't undo them, but we need to log them
	 *     (A phantom entry's action_args filed is NULL)
	 * @return int : the number of entries, not counting phantom entries
	 */
	function getLogRealLength(): int
	{
		return $this->getUniqueValueFromDB("SELECT count(action_id) FROM action_log WHERE action_args IS NOT NULL");
	}

	/**
	 * saveAction:
	 * Method to save an action as an entry in the log
	 * @param string $action_type : the type of the action
	 * @param int $player_id : the ID of the player who does the action
	 * @param array|null $action_args : the arguments of the action (piece or card IDs, etc.)
	 * @return void
	 */
	function saveAction(string $action_type, int $player_id, array $action_args = null): void
	{
		// we save to gamelog_move_id for canceling them later
		$gamelog_id = $this->getUniqueValueFromDB("SELECT gamelog_move_id FROM gamelog ORDER BY gamelog_move_id DESC LIMIT 1") + 1;
		if ($action_args === null) $this->DbQuery("INSERT INTO action_log (gamelog_id, action_type, player_id) VALUES ({$gamelog_id},'{$action_type}',{$player_id})");
		else $this->DbQuery("INSERT INTO action_log (gamelog_id, action_type, player_id, action_args) VALUES ({$gamelog_id},'{$action_type}',{$player_id},'" . json_encode($action_args) . "')");
	}

	/**
	 * getLastActionGamelogId:
	 * Function to get the gamelog_id from the last action entry
	 * @return int : the gamelog_id from the last action entry
	 */
	function getLastActionGamelogId(): int
	{
		return $this->getUniqueValueFromDB("SELECT gamelog_id FROM action_log ORDER BY action_id DESC LIMIT 1");
	}

	/**
	 * getLastActionType:
	 * Function to get the action_type from the last action entry
	 * @return ?string
	 */
	function getLastActionType(): ?string
	{
		return $this->getUniqueValueFromDB("SELECT action_type FROM action_log ORDER BY action_id DESC LIMIT 1");
	}

	/**
	 * getLastAction:
	 * Function the get the last action from the last saved entry
	 * @return ?array
	 */
	function getLastAction(): ?array
	{
		$res = $this->getObjectFromDB("SELECT gamelog_id, action_type, player_id, action_args FROM action_log ORDER BY action_id DESC LIMIT 1");
		if (empty($res)) return null;
		if ($res["action_args"] !== null) $res["action_args"] = json_decode($res["action_args"], true);
		return $res;
	}

	/**
	 * undoLastAction:
	 * Method to undo the last action from the last saved entry
	 * @return void
	 */
	function undoLastAction(): void
	{
		// Cancel the move in the gamelog with the gamelog_id in the action entry
		$this->DbQuery("UPDATE gamelog SET `cancel` = true WHERE gamelog_move_id = {$this->getLastActionGamelogId()}");
		// Delete the entry
		$this->DbQuery("DELETE FROM action_log ORDER BY action_id DESC LIMIT 1 ");
	}

	/**
	 * clearLog:
	 * Method to delete all entries from the action log
	 * @return void
	 */
	function clearLog(): void
	{
		$this->DbQuery("DELETE FROM action_log");
	}
}