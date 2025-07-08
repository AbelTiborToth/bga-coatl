{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

coatl_coatl.tpl

This is the HTML template of the game in BGA framework.
https://en.doc.boardgamearena.com/Game_layout:_view_and_template:_yourgamename.view.php_and_yourgamename_yourgamename.tpl

Everything written in this file will be displayed in the HTML page of your game user interface,
in the "main game zone" of the screen.
-->

<div id="player_board_title">
</div>

<!--
    Supplies
-->
<supplies id="supplies">
    <supply_piece id="supply_piece" active="false">
        <bag id="bag_tail" class="bag" type="tail"><span id="counter_tail"></span></bag>
        <bag id="bag_body" class="bag" type="body"><span id="counter_body"></span></bag>
        <bag id="bag_head" class="bag" type="head"><span id="counter_head"></span></bag>
        <!-- BEGIN supply_piece_spaces -->
        <space id="space_{TYPE}_{LOCATION}" style="top:{TOP}px;left:{LEFT}px"></space>
        <!-- END supply_piece_spaces -->
        <!-- BEGIN piece_supply_selector -->
        <selector id="selector_{TYPE}_{LOCATION}" type="{TYPE}" active="false"
                  style="top:{TOP}px;left:{LEFT}px;transform:rotate({ROTATE}deg)"
                  onmouseover="overSupplySelector(this, '{TYPE}', 'supply_piece_{LOCATION}')"
                  onmouseout="outSupplySelector(this, '{TYPE}', 'supply_piece_{LOCATION}')">
        </selector>
        <!-- END piece_supply_selector -->
    </supply_piece>
    <supply_prophecy id="supply_prophecy" active="false"></supply_prophecy>
    <deck_prophecy id="deck_prophecy"></deck_prophecy>
    <supply_temple id="supply_temple" active="false"></supply_temple>
</supplies>

<!--
    Current Player Area 
-->
<player_area id="player-table-{CURRENT_PLAYER_ID}" class="no_spectator whiteblock">
    <player_board_container>
        <h2>{YOUR_COATL_PIECES_STR}</h2>
        <player_board id="player_board_{CURRENT_PLAYER_ID}" color="{CURRENT_PLAYER_COLOR}">
            <selector_grid id="selector_grid" active="false">
                <!-- BEGIN current_player_board_pieces -->
                <cell>
                    <piece_container id="cell_{CURRENT_PLAYER_ID}_{LOCATION_ARG}"></piece_container>
                    <cellSelector id="cellSelector_{CURRENT_PLAYER_ID}_{LOCATION_ARG}" active="false" type="none" class="cellSelector"
                                  onmouseover="overCell(this, 'cell_{CURRENT_PLAYER_ID}_{LOCATION_ARG}' )"
                                  onmouseout="outCell(this, 'cell_{CURRENT_PLAYER_ID}_{LOCATION_ARG}' )">
                    </cellSelector>
                </cell>
                <!-- END current_player_board_pieces -->
            </selector_grid>
        </player_board>
    </player_board_container>
    <hand_container id="hand_container">
        <h2 id="your_hand_label">{YOUR_HAND_STR}</h2>
        <hand id="hand" temples="0">
            <hand_prophecy id="hand_prophecy"></hand_prophecy>
            <hand_temple id="hand_temple"></hand_temple>
        </hand>
    </hand_container>
</player_area>
<build_area id="build_area" class="whiteblock no_spectator">
    <h2 id="your_coatls_str">{YOUR_CÓATLS_STR}</h2>
    <coatls id="coatls_{CURRENT_PLAYER_ID}" color="{CURRENT_PLAYER_COLOR}">
        <p id="you_dont_have_coatls_label"> {YOU_DONT_HAVE_CÓATLS}</p>
    </coatls>
</build_area>

<!-- Other PLayer Area -->
<!-- BEGIN other_player_areas -->
<player_area id="player-table-{OTHER_PLAYER_ID}" class="whiteblock">
    <player_board_container>
        <h2>{OTHER_PLAYER_NAME}</h2>
        <player_board id="player_board_{OTHER_PLAYER_ID}" color="{OTHER_PLAYER_COLOR}">
            <other_selector_grid id="other_selector_grid_{OTHER_PLAYER_ID}" class="other_selector_grid" active="false">
                <!-- BEGIN other_player_pieces -->
                <cell>
                    <piece_container id="cell_{OTHER_PLAYER_ID}_{LOCATION_ARG}"></piece_container>
                    <cellSelector id="cellSelector_{OTHER_PLAYER_ID}_{LOCATION_ARG}" active="false" type="none" class="cellSelector"
                                  onmouseover="overCell(this, 'cell_{OTHER_PLAYER_ID}_{LOCATION_ARG}' )"
                                  onmouseout="outCell(this, 'cell_{OTHER_PLAYER_ID}_{LOCATION_ARG}' )">
                    </cellSelector>
                </cell>
                <!-- END other_player_pieces -->
            </other_selector_grid>
        </player_board>
    </player_board_container>
    <other_build id="other_player_build_{OTHER_PLAYER_ID}">
        <coatls id="coatls_{OTHER_PLAYER_ID}" color="{OTHER_PLAYER_COLOR}"></coatls>
    </other_build>
</player_area>
<!-- END other_player_areas -->

<script type="text/javascript">

	///////////////////////////////////////////////////
	//// Javascript templates

	/**
	 * jstpl_first_player_token:
	 *
	 * Template for first player token div.
	 * @returns {string}
	 */
	const jstpl_first_player_token = '<div>' +
		'<p><span id=counter_${player_id}_prophecy></span></p><prophecy_icon id=${player_id}_prophecy_icon class=prophecy_icon display="block"></prophecy_icon>' +
		'<p><span id=counter_${player_id}_temple></span></p><temple_icon id=${player_id}_temple_icon class=temple_icon></temple_icon>' +
		'<player_token id=first_player_token_${player_id} type=first_player display=${first_player} class="first_player_token"></player_token>' +
		'</div>';

	const jstpl_sacrifice_tokens = '<div>' +
		'<player_token id=${player_id}_piece_token class="piece_token" type=piece display=${piece} active=false pointer=${pointer}></player_token>' +
		'<player_token id=${player_id}_prophecy_token class="prophecy_token" type=prophecy display=${prophecy} active=false pointer=${pointer}></player_token>' +
		'<player_token id=${player_id}_temple_token class="temple_token" type=temple display=${temple} active=false pointer=${pointer}></player_token>' +
		'</div>';

	/**
	 * jstpl_piece:
	 *
	 * Template for piece div.
	 * @type {string}
	 */
	const jstpl_piece = '<piece id=${id} type=${type} color=${color} selector=${selector} rotate=${rotate} shadow=none></piece>';

	const jstpl_bagPiecesDialog =
		'<bagPiecesDialog id="bag_pieces_dialog">' +
		'<div type=head><piece type=head color=yellow available=${available.head.yellow}></piece></div>' +
		'<div type=head><piece type=head color=red available=${available.head.red}></piece></div>' +
		'<div type=head><piece type=head color=black available=${available.head.black}></piece></div>' +
		'<div type=head><piece type=head color=green available=${available.head.green}></piece></div>' +
		'<div type=head><piece type=head color=blue available=${available.head.blue}></piece></div>' +
		'<div type=body><piece type=body color=yellow pair="1" available=${available.body.yellow_1}></piece></div>' +
		'<div type=body><piece type=body color=red pair="1" available=${available.body.red_1}></piece></div>' +
		'<div type=body><piece type=body color=black pair="1" available=${available.body.black_1}></piece></div>' +
		'<div type=body><piece type=body color=green pair="1" available=${available.body.green_1}></piece></div>' +
		'<div type=body><piece type=body color=blue pair="1" available=${available.body.blue_1}></piece></div>' +
		'<div type=body><piece type=body color=yellow pair="2" available=${available.body.yellow_2}></piece></div>' +
		'<div type=body><piece type=body color=red pair="2" available=${available.body.red_2}></piece></div>' +
		'<div type=body><piece type=body color=black pair="2" available=${available.body.black_2}></piece></div>' +
		'<div type=body><piece type=body color=green pair="2" available=${available.body.green_2}></piece></div>' +
		'<div type=body><piece type=body color=blue pair="2" available=${available.body.blue_2}></piece></div>' +
		'<div type=tail><piece type=tail color=yellow available=${available.tail.yellow}></piece></div>' +
		'<div type=tail><piece type=tail color=red available=${available.tail.red}></piece></div>' +
		'<div type=tail><piece type=tail color=black available=${available.tail.black}></piece></div>' +
		'<div type=tail><piece type=tail color=green available=${available.tail.green}></piece></div>' +
		'<div type=tail><piece type=tail color=blue available=${available.tail.blue}></piece></div>' +
		'</bagPiecesDialog>' +
		'<b id="use_piece_token_button" class="bgabutton bgabutton_blue disabled"><span>${button_label}</span></b>';

	/**
	 * jstpl_coatl:
	 *
	 * Template for coatl div.
	 * @type {string}
	 */
	const jstpl_coatl_container =
		'<div id="${id}_coatl_overall" class="coatl_overall">' +
		'<div id="${id}_container" class="coatl_container" active="false" onmouseenter="onCoatlMouseEnter(this)" onmouseleave="onCoatlMouseLeave(this)">' +
		'<div id="${id}_button_container_left" class="button_container button_container_left"></div>' +
		'<div id="${id}" class="coatl"></div>' +
		'<div id="${id}_button_container_right" class="button_container button_container_right"></div>' +
		'</div>' +
		'<div id="${id}_card_container" class="card_container" active="false">' +
		'<length_counter id="${id}_length_counter"><span id="counter_${id}_length">12</span></length_counter>' +
		'<score_token id="${id}_score_token" active="false"><span id="counter_${id}_score"></span></score_token>' +
		'<div id="${id}_cards" class="coatl_cards"></div>' +
		'<div id="${id}_temple_cards" class="coatl_temple_cards" active="false"></div>' +
		'<div id="${id}_add_card_button_container" class="button_container add_card_button_container"></div>' +
		'</div>' +
		'</div>';

	/**
	 * jstpl_box:
	 *
	 * Template for box div.
	 * @type {string}
	 */
	const jstpl_box = '<div id="${id}" class="box" type="${type}"></div>';

	///////////////////////////////////////////////////
	//// Piece shadows

	function overSupplySelector(e, type, selector) {
		if ((dojo.getAttr("supply_piece", "active") === "true" && dojo.getAttr(e, "active") === "true") ||
			(dojo.getAttr("supply_piece", "active") === "single" && type !== "body" && dojo.getAttr(e, "active") === "true")) {
			dojo.query(`supply_piece piece[type=${type}][selector=${selector}]`)
				.forEach(function (node) {
					dojo.setAttr(node, "shadow", "white");
				});
		}
	}


	function outSupplySelector(e, type, selector) {
		dojo.query(`supply_piece piece[type=${type}][selector=${selector}]`)
			.forEach(function (node) {
				dojo.setAttr(node, "shadow", "node");
			});
	}

	function overCell(e, selector) {
		dojo.query(`piece[selector=${selector}]:not([shadow="orange"])`).forEach(function (node) {
			dojo.setAttr(node, "shadow", "white");
		});
	}

	function outCell(e, selector) {
		dojo.query(`piece[selector=${selector}]:not([shadow="orange"])`).forEach(function (node) {
			dojo.setAttr(node, "shadow", "none");
		});
	}

	///////////////////////////////////////////////////
	//// Cóatl scroll

	let elem = null;
	let pos = {
		left: 0,
		x: 0,
	};

	const onCoatlMouseEnter = function (e) {
		if (e.scrollWidth > e.clientWidth) {
			e.style.cursor = 'grab';
			e.addEventListener('mousedown', onCoatlMouseDown);
			elem = e;
		} else {
			e.style.cursor = 'default';
			e.removeEventListener('mousedown', onCoatlMouseDown);
		}
	};

	const onCoatlMouseLeave = function (e) {
		e.removeEventListener('mousedown', onCoatlMouseDown);
		document.removeEventListener('mousemove', onCoatlMouseMove);
		document.removeEventListener('mouseup', onCoatlMouseUp);
	};

	/**
	 * onCoatlMouseDown:
	 *
	 * Handler function to grab and scroll Cóatl
	 * @param evt
	 */
	const onCoatlMouseDown = function (evt) {
		elem.style.cursor = 'grabbing';
		elem.style.userSelect = 'none';
		pos = {
			left: elem.scrollLeft,
			x: evt.clientX,
		};
		document.addEventListener('mousemove', onCoatlMouseMove);
		document.addEventListener('mouseup', onCoatlMouseUp);
	};

	/**
	 * onCoatlMouseMove:
	 *
	 * Handler function to grab and scroll Cóatl
	 * @param evt
	 */
	const onCoatlMouseMove = function (evt) {
		const dx = evt.clientX - pos.x;
		elem.scrollLeft = pos.left - dx;
	};

	/**
	 * onCoatlMouseUp:
	 *
	 * Handler function to grab and scroll Cóatl
	 * @param evt
	 */
	const onCoatlMouseUp = function (evt) {
		elem.style.cursor = 'grab';
		elem.style.removeProperty('user-select');
		document.removeEventListener('mousemove', onCoatlMouseMove);
		document.removeEventListener('mouseup', onCoatlMouseUp);
	};

</script>

{OVERALL_GAME_FOOTER}