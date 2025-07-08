<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * coatl.view.php
 *
 * This is the "view" file of the BGA framework.
 * https://en.doc.boardgamearena.com/Game_layout:_view_and_template:_yourgamename.view.php_and_yourgamename_yourgamename.tpl
 */

require_once(APP_BASE_PATH . "view/common/game.view.php");

class view_coatl_coatl extends game_view
{
	/**
	 * getGameName:
	 *
	 * Used for translations and stuff. Please do not modify.
	 * @return string
	 */
	protected function getGameName()
	{
		return "coatl";
	}

	/**
	 * build_page:
	 *
	 * Method is called each time the game interface is displayed to a player, ie:
	 *  _ when the game starts
	 *  _ when a player refreshes the game page (F5)
	 * Allows you to dynamically modify the HTML generated for the game interface.
	 * In particular, you can set here the values of variables elements defined in coatl_coatl.tpl
	 * (elements like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
	 * @param $viewArgs
	 * @return void
	 */
	function build_page($viewArgs)
	{
		/* Get players & players number */

		$players = $this->game->loadPlayersBasicInfos();
		$players_nbr = count($players);

		if (!$this->game->isSpectator()) {
			$current_player_id = $this->getCurrentPlayerId();
			$current_player_color = $this->game->getPlayerColorById($current_player_id);

			/* Display a specific number / string */

			$this->tpl['YOUR_COATL_PIECES_STR'] = self::_("Your Cóatl pieces");
			$this->tpl['YOUR_HAND_STR'] = self::_("Your hand");
			$this->tpl['YOUR_CÓATLS_STR'] = self::_("Your Cóatls");
			$this->tpl['YOU_DONT_HAVE_CÓATLS'] = self::_("(You don't have Cóatls yet)");
			$this->tpl['CURRENT_PLAYER_COLOR'] = $current_player_color;
		}

/////////////// Supply piece

		// $space_style_args for positioning supply board spaces
		$space_style_args = array(
			'head' => array(
				1 => array(
					245,
					172
				),
				2 => array(
					250,
					330
				)
			),
			'body' => array(
				1 => array(
					185,
					76
				),
				-1 => array(
					135,
					104
				),
				2 => array(
					62,
					224
				),
				-2 => array(
					62,
					279
				),
				3 => array(
					132,
					397
				),
				-3 => array(
					181,
					421
				),
				4 => array(
					317,
					427
				),
				-4 => array(
					364,
					398
				),
				5 => array(
					438,
					279
				),
				-5 => array(
					437,
					223
				),
				6 => array(
					368,
					107
				),
				-6 => array(
					323,
					75
				)
			),
			'tail' => array(
				1 => array(
					167,
					249
				),
				2 => array(
					326,
					254
				)
			)
		);

		// $selector_style_args for positioning supply board selectors
		$selector_style_args = array(
			'head' => array(
				1 => array(
					178,
					107,
					90
				),
				2 => array(
					178,
					279,
					90
				)
			),
			'body' => array(
				1 => array(
					121,
					24,
					-62
				),
				2 => array(
					25,
					183,
					0
				),
				3 => array(
					121,
					342,
					62
				),
				4 => array(
					299,
					348,
					117
				),
				5 => array(
					400,
					185,
					-180
				),
				6 => array(
					313,
					31,
					-123
				)
			),
			'tail' => array(
				1 => array(
					90,
					193,
					0
				),
				2 => array(
					266,
					193,
					0
				)
			)
		);

		$this->page->begin_block("coatl_coatl", "supply_piece_spaces");
		$this->page->begin_block("coatl_coatl", "piece_supply_selector");

		// Setup head and tail piece spaces and selectors
		for ($i = 1; $i <= 2; $i++) {
			foreach (array(
							'head',
							'tail'
						) as $type) {
				// Add spaces where pieces will float to
				$this->page->insert_block("supply_piece_spaces", array(
					'TYPE' => $type,
					'LOCATION' => $i,

					'TOP' => $space_style_args[$type][$i][0],
					'LEFT' => $space_style_args[$type][$i][1]
				));
				// Add selectors for parts of the supply board
				$this->page->insert_block("piece_supply_selector", array(
					'TYPE' => $type,
					'LOCATION' => $i,

					'TOP' => $selector_style_args[$type][$i][0],
					'LEFT' => $selector_style_args[$type][$i][1],
					'ROTATE' => $selector_style_args[$type][$i][2]
				));
			}
		}

		// Setup body piece spaces and selectors
		for ($i = 1; $i <= 6; $i++) {
			for ($j = $i * -1; $j <= $i; $j += $i * 2) {
				// Add spaces where pieces will float to
				$this->page->insert_block("supply_piece_spaces", array(
					'TYPE' => 'body',
					'LOCATION' => $j,

					'TOP' => $space_style_args['body'][$j][0],
					'LEFT' => $space_style_args['body'][$j][1]
				));
			}
			// Add selectors for parts of the supply board
			$this->page->insert_block("piece_supply_selector", array(
				'TYPE' => 'body',
				'LOCATION_ARG' => $i,

				'TOP' => $selector_style_args['body'][$i][0],
				'LEFT' => $selector_style_args['body'][$i][1],
				'ROTATE' => $selector_style_args['body'][$i][2]
			));
		}

/////////////// Player board pieces


		if (!$this->game->isSpectator()) {
			$this->page->begin_block("coatl_coatl", "current_player_board_pieces");
			for ($i = 1; $i <= 8; $i++) {
				$this->page->insert_block("current_player_board_pieces", array(
					'CURRENT_PLAYER_ID' => $current_player_id,
					'LOCATION_ARG' => $i
				));
			}
		}

		$this->page->begin_block("coatl_coatl", "other_player_pieces");
		for ($i = 1; $i <= 8; $i++) {
			$this->page->insert_block("other_player_pieces", array('LOCATION_ARG' => $i));
		}

		$this->page->begin_block("coatl_coatl", "other_player_areas");
		if ($this->game->isSpectator()) {
			$current = $this->game->getNextPlayerTable()[0];
			$i = 1;
		}
		else {
			$current = $this->game->getPlayerAfter($this->getCurrentPlayerId());
			$i = 2;
		}
		for (; $i <= $players_nbr; $i++) {
			$this->page->insert_block("other_player_areas", array(
				'OTHER_PLAYER_COLOR' => $this->game->getPlayerColorById($current),
				'OTHER_PLAYER_NAME' => $players [$current] ['player_name'],
				'OTHER_PLAYER_ID' => $current
			));
			$current = $this->game->getPlayerAfter($current);
		}

		/*********** Do not change anything below this line  ************/
	}
}
