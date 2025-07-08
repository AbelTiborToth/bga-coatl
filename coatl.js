/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * coatl.js
 *
 * This is the file for user interface script in BGA framework.
 * https://en.doc.boardgamearena.com/Game_interface_logic:_yourgamename.js
 */

define([
		"dojo",
		"dojo/_base/declare",
		"ebg/core/gamegui",
		"ebg/counter",
		"ebg/stock",
		g_gamethemeurl + "modules/js/bga-jump-to.js",
	],
	function (dojo, declare) {

		/**
		 * override_setLoader:
		 * Overriding setLoader, used for canceled game logs
		 * Used from BGA Santorini by Tisaac & Quietmint & Morgalad
		 * https://github.com/AntonioSoler/bga-santorini/blob/master/santorini.js
		 * @param value
		 * @param max
		 */
		function override_setLoader(value, max) {
			this.inherited(override_setLoader, arguments);
			if (value >= 100) {
				this.onLoadingComplete();
			}
		}

		var dockedlog_to_move_id = {};

		/**
		 * override_onPlaceLogOnChannel:
		 * Overriding onPlaceLogOnChannel, used for canceled game logs
		 * Used from BGA Santorini by Tisaac & Quietmint & Morgalad
		 * https://github.com/AntonioSoler/bga-santorini/blob/master/santorini.js
		 * @param msg
		 */
		function override_onPlaceLogOnChannel(msg) {
			// [Undocumented] Called by BGA framework on any notification message
			// Handle cancelling log messages for restart turn
			var currentLogId = this.next_log_id;
			this.inherited(override_onPlaceLogOnChannel, arguments);
			if (msg.move_id && this.next_log_id != currentLogId) {
				var moveId = +msg.move_id;
				dockedlog_to_move_id[currentLogId] = moveId;
				if (this.gamedatas.cancelMoveIds != null && this.gamedatas.cancelMoveIds.includes(moveId)) {
					this.cancelLogs([moveId]);
				}
			}
		}

		/**
		 * bgagame.coatl:
		 * Main component of the user interface
		 */
		return declare("bgagame.coatl", ebg.core.gamegui, {

			setLoader: override_setLoader,
			onPlaceLogOnChannel: override_onPlaceLogOnChannel,

			/**
			 * constructor:
			 * Constructor of the user interface
			 */
			constructor: function () {
				this.finalTurns = false;
				this.stateArgs = {};
				this.assembleParams = {
					type: null,
					action: null,
					action_button: null,
					action_args: null,
					element: null
				};
				this.stealParams = {
					player_id: null,
					cell_ids: [],
				};
				this.coatlProphecyStocks = {};
				this.coatlTempleStocks = {};
				this.coatlScoreCounters = {};
				this.coatlLengthCounters = {};
				this.sacrificeTokens = {};
			},

			/* @Override */
			updatePlayerOrdering() {
				this.inherited(arguments);
				dojo.place('player_board_title', 'player_boards', 'first');
			},

			getPieceToken: function (id = this.player_id) {
				return document.getElementById(`${id}_piece_token`);
			},

			getProphecyToken: function (id = this.player_id) {
				return document.getElementById(`${id}_prophecy_token`);
			},

			getTempleToken: function (id = this.player_id) {
				return document.getElementById(`${id}_temple_token`);
			},

			/**
			 * setup:
			 * Method to setup the user interface
			 * @param gamedatas
			 */
			setup: function (gamedatas) {
				// Setting up player boards
				for (const player_id in gamedatas.players) {
					const player = gamedatas.players[player_id];
					dojo.addClass('overall_player_board_' + player_id, 'player_board_color_' + player.color)
					this.getPlayerPanelElement(player_id).innerHTML = this.format_block('jstpl_first_player_token', {
						"player_id": player_id,
						"first_player": player.player_no === "1" ? "auto" : "none"
					});

					document.getElementById('counter_' + player_id + '_prophecy').innerHTML = player.prophecy_card_count;
					document.getElementById('counter_' + player_id + '_temple').innerHTML = player.temple_card_count;

					const hasPieceToken = player.piece_token === "1"
					const hasProphecyToken = player.prophecy_token === "1"
					const hasTempleToken = player.temple_token === "1"
					this.getPlayerPanelElement(player_id).innerHTML += this.format_block('jstpl_sacrifice_tokens', {
						"player_id": player_id,
						"piece": hasPieceToken ? "block" : "none",
						"prophecy": hasProphecyToken ? "block" : "none",
						"temple": hasTempleToken ? "block" : "none",
						"pointer": parseInt(player_id) === this.player_id
					});
					this.sacrificeTokens[player_id] = {};
					if (hasPieceToken) {
						this.sacrificeTokens[player_id]["piece"] = true;
					} else {
						this.sacrificeTokens[player_id]["piece"] = null
					}
					if (hasProphecyToken) {
						this.sacrificeTokens[player_id]["prophecy"] = true;
					} else {
						this.sacrificeTokens[player_id]["prophecy"] = null
					}
					if (hasTempleToken) {
						this.sacrificeTokens[player_id]["temple"] = true;
					} else {
						this.sacrificeTokens[player_id]["temple"] = null
					}
					let gold_str = '';
					for (i = 1; i <= player.gold; i++) {
						gold_str += `<gold_icon display="block"></gold_icon>`;
					}
					this.getPlayerPanelElement(player_id).innerHTML += `<div id="gold_icons_${player_id}">${gold_str}</div>`;
				}
				this.addTooltipHtmlToClass("prophecy_icon", `<h5>${_("Number of Prophecy cards in hand")}</h5>`);
				this.addTooltipHtmlToClass("temple_icon", `<h5>${_("Number of Temple cards in hand")}</h5>`);
				this.addTooltipHtmlToClass("first_player_token", `<h5>${_("First Player marker")}</h5>`);
				this.addTooltipHtmlToClass("piece_token", this.sacrifice_token_tooltip("piece"));
				this.addTooltipHtmlToClass("prophecy_token", this.sacrifice_token_tooltip("prophecy"));
				this.addTooltipHtmlToClass("temple_token", this.sacrifice_token_tooltip("temple"));

				if (!this.isSpectator) {
					if (this.sacrificeTokens[this.player_id]["piece"] !== null) {
						this.bagPiecesDialog = new ebg.popindialog();
						this.bagPiecesDialog.create('bagPieces');
						this.bagPiecesDialog.setTitle(_("Perfect Pick"));
						dojo.connect(this.getPieceToken(), 'onclick', this, (event) => {
							this.selectedPieces = {
								"type": null,
								"element": null
							};
							this.bagPiecesDialog.setContent(this.format_block('jstpl_bagPiecesDialog', {
								"available": this.inBagAvailable,
								"button_label": _("Confirm and replenish the board")
							}));
							this.dialogWindow = document.getElementById("bag_pieces_dialog");
							this.bagPiecesDialog.show();
						});
						this.bagPiecesDialog.replaceCloseCallback((event) => {
							this.bagPiecesDialog.hide();
						});
						dojo.connect(document.getElementById("popin_bagPieces"), 'onclick', (evt) => {
							if (evt.target.tagName === "PIECE") {
								if (evt.target.attributes.type.value === "head" || evt.target.attributes.type.value === "tail") {
									this.dialogWindow.setAttribute("type", "null");
									if (this.selectedPieces["element"] !== null) {
										if (this.selectedPieces["type"] === "body") {
											for (const i in this.selectedPieces["element"]) {
												this.selectedPieces["element"][i].setAttribute("shadow", "none");
											}
										} else {
											this.selectedPieces["element"].setAttribute("shadow", "none");
										}
									}
									if (this.selectedPieces["type"] === evt.target.attributes.type.value && this.selectedPieces["element"].attributes.color.value === evt.target.attributes.color.value) {
										this.selectedPieces["type"] = null;
										this.selectedPieces["element"] = null;
										dojo.addClass("use_piece_token_button", "disabled");
									} else {
										this.selectedPieces["type"] = evt.target.attributes.type.value;
										this.selectedPieces["element"] = evt.target;
										this.dialogWindow.setAttribute("type", "all");
										evt.target.setAttribute("shadow", "orange");
										dojo.removeClass("use_piece_token_button", "disabled");
									}
								} else {
									if (this.selectedPieces["type"] === "head" || this.selectedPieces["type"] === "tail") {
										this.dialogWindow.setAttribute("type", "null");
										this.selectedPieces["element"].setAttribute("shadow", "none");
										this.selectedPieces["type"] = null;
										this.selectedPieces["element"] = null;
										dojo.addClass("use_piece_token_button", "disabled");
									}
									if (this.selectedPieces["type"] === null) {
										this.selectedPieces["type"] = "body";
										this.selectedPieces["element"] = {};
									}
									let isClickAgain = false;
									for (const i in this.selectedPieces["element"]) {
										if (this.selectedPieces["element"][i].attributes.color.value === evt.target.attributes.color.value && this.selectedPieces["element"][i].attributes.pair.value === evt.target.attributes.pair.value) {
											this.selectedPieces["element"][i].setAttribute("shadow", "none");
											delete this.selectedPieces["element"][i];
											dojo.addClass("use_piece_token_button", "disabled");
											if (!(0 in this.selectedPieces["element"]) && !(1 in this.selectedPieces["element"])) {
												this.dialogWindow.setAttribute("type", "null");
											} else {
												this.dialogWindow.setAttribute("type", "body");
											}
											isClickAgain = true;
											break;
										}
									}
									if (!isClickAgain) {
										if (!(0 in this.selectedPieces["element"])) {
											this.selectedPieces["element"][0] = evt.target;
											this.dialogWindow.setAttribute("type", "body");
											evt.target.setAttribute("shadow", "orange");
										} else if (!(1 in this.selectedPieces["element"])) {
											this.selectedPieces["element"][1] = evt.target;
											this.dialogWindow.setAttribute("type", "all");
											evt.target.setAttribute("shadow", "orange");
											dojo.removeClass("use_piece_token_button", "disabled");
										} else {
											this.selectedPieces["element"][0].setAttribute("shadow", "none");
											this.selectedPieces["element"][0] = this.selectedPieces["element"][1];
											this.selectedPieces["element"][1] = evt.target;
											evt.target.setAttribute("shadow", "orange");
										}
									}
								}
							}
							if (evt.target.tagName === "B" || evt.target.tagName === "SPAN") {
								if (this.dialogWindow.getAttribute("type") === "all") {
									let piece_colors;
									if (this.selectedPieces["type"] !== "body") {
										piece_colors = this.selectedPieces["element"].attributes.color.value;
									} else {
										piece_colors = this.selectedPieces["element"][0].attributes.color.value + ' ' + this.selectedPieces["element"][1].attributes.color.value
									}
									this.ajaxcall("/coatl/coatl/actUsePieceToken.html", {
											lock: true,
											piece_type: this.selectedPieces["type"],
											piece_colors: piece_colors
										}, function (is_error) {
										}
									);
									this.bagPiecesDialog.hide();
								}
							}
						});
					}
					if (this.sacrificeTokens[this.player_id]["prophecy"] !== null) {
						dojo.connect(this.getProphecyToken(), 'onclick', this, (event) => {
							if (this.checkAction('useSacrifice', true)) {
								this.confirmationDialog(_('Do you want to use the "See the Future" sacrifice token?<br>(You will discard all Prophecy cards from the supply and won\'t be able to undo this action.)'), () => {
									this.ajaxcall("/coatl/coatl/actUseProphecyToken.html", {
										lock: true
									}, function (is_error) {
									});
								});
							}
						});
					}
					if (this.sacrificeTokens[this.player_id]["temple"] !== null) {
						dojo.connect(this.getTempleToken(), 'onclick', this, (event) => {
							if (this.checkAction('useSacrifice', true)) {
								this.ajaxcall("/coatl/coatl/actUseTempleToken.html", {
									lock: true
								}, function (is_error) {
								});
							}
						});
					}
				}

				document.querySelector("#page-title").innerHTML += "<div id='help-bar-blue'></div>"
				if (this.gamedatas.final_round) {
					this.finalTurns = true;
					document.querySelector("#page-title").innerHTML += "<div id='help-bar'></div>"
					document.querySelector("#help-bar").innerHTML = _("These are the game's final turns!");
				}
				if (this.player_id === 92894721) document.querySelector("#page-title").innerHTML += "<div id='love-bar'>Szeretlek <3 (Hihi)</div>"

				this.setupBagCounters(this.gamedatas.piece_counters.head, this.gamedatas.piece_counters.body, this.gamedatas.piece_counters.tail);

				this.replenishPieceSupplyBoard(this.gamedatas.pieces_on_supply, false);

				for (const player in this.gamedatas.players) {
					for (const location_arg in this.gamedatas.players[player].pieces_on_player_board) {
						const piece = gamedatas.players[player].pieces_on_player_board[location_arg];
						this.addPieceToPlayerBoard(piece.id, piece.type, player, location_arg, piece.color);
					}
				}
				this.prophecySupply = this.setupProphecyCardStock('supply_prophecy', this.getGameUserPreference(105) === 2);
				for (let i in this.gamedatas.card_supply) {
					this.prophecySupply.addToStockWithId(this.gamedatas.card_supply[i].type, this.gamedatas.card_supply[i].id);
				}

				this.templeSupply = this.setupTempleStock('supply_temple', this.getGameUserPreference(105) === 2);
				for (let i in this.gamedatas.temple_supply) {
					this.templeSupply.addToStockWithId(this.gamedatas.temple_supply[i].type, this.gamedatas.temple_supply[i].id);
				}
				this.templeSupply.autowidth = true;
				this.templeSupply.setSelectionAppearance('class');

				if (!this.isSpectator) {
					this.prophecyHand = this.setupProphecyCardStock('hand_prophecy', this.getGameUserPreference(105) === 2);
					for (let i in this.gamedatas.prophecy_cards_hand) {
						this.prophecyHand.addToStockWithId(this.gamedatas.prophecy_cards_hand[i].type, this.gamedatas.prophecy_cards_hand[i].id);
					}
					this.prophecyHand.centerItems = true
					this.prophecyHand.setSelectionAppearance('class');

					this.templeHand = this.setupTempleStock('hand_temple', this.getGameUserPreference(105) === 2);
					for (let i in this.gamedatas.temple_hand) {
						document.getElementById("hand").setAttribute("temples", parseInt(document.getElementById("hand").getAttribute("temples")) + 1);
						this.templeHand.addToStockWithId(this.gamedatas.temple_hand[i].type, this.gamedatas.temple_hand[i].id);
					}
					this.templeHand.setSelectionAppearance('class');
				}
				for (var i in this.gamedatas.coatls) {
					this.newCoatl(this.gamedatas.coatls[i].id, this.gamedatas.coatls[i].player_id);
					for (var location_arg in this.gamedatas.coatls[i]["pieces"]) {
						const piece = this.gamedatas.coatls[i]["pieces"][location_arg];
						this.addPieceToCoatl(
							this.gamedatas.coatls[i].id,
							this.gamedatas.coatls[i].player_id,
							piece.id,
							piece.type,
							piece.color,
							location_arg,
							false
						)
					}
					this.coatlScoreCounters[`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}`].setValue(this.gamedatas.coatls[i].score.score);
					if (this.gamedatas.coatls[i].is_finished) {
						document.querySelector(`#coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}_score_token`).setAttribute("active", "true");
						if (this.gamedatas.coatls[i].temple !== null) {
							this.coatlTempleStocks[`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}`].addToStockWithId(this.gamedatas.coatls[i].temple.type, this.gamedatas.coatls[i].temple.id);
							const divId = this.coatlTempleStocks[`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}`].getItemDivId(this.gamedatas.coatls[i].temple.id)
							document.getElementById(`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}_temple_cards`).setAttribute("active", "true");
							document.getElementById(divId).setAttribute("level", this.gamedatas.coatls[i].score.temple_level);
						}
					}
					for (const j in this.gamedatas.coatls[i].prophecies) {
						this.coatlProphecyStocks[`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}`].addToStockWithId(this.gamedatas.coatls[i].prophecies[j].type, this.gamedatas.coatls[i].prophecies[j].id);
						const divId = this.coatlProphecyStocks[`coatl_${this.gamedatas.coatls[i].player_id}_${this.gamedatas.coatls[i].id}`].getItemDivId(this.gamedatas.coatls[i].prophecies[j].id)
						document.getElementById(divId).setAttribute("level", this.gamedatas.coatls[i].score.prophecy_levels[this.gamedatas.coatls[i].prophecies[j].id]);
					}
				}
				if (!this.isSpectator) {
					dojo.query('selector').connect('onclick', this, 'onTakePiece');

					dojo.connect(this.prophecyHand, 'onChangeSelection', this, 'onSelectProphecyFromHand');
					dojo.connect(this.prophecySupply, 'onChangeSelection', this, 'onSelectProphecyFromSupply');
					dojo.connect(this.templeHand, 'onChangeSelection', this, 'onSelectTempleFromHand');
					dojo.connect(this.templeSupply, 'onChangeSelection', this, 'onSelectTempleFromSupply');
					dojo.query('#deck_prophecy').connect('onclick', this, 'onChooseProphecyCardFromDeck');

					dojo.query('.cellSelector').connect('onclick', this, 'onCellClick');

					this.addActionButton('assemble_button_play_area', _('Assemble'), 'onAssemble', 'build_area');
					document.querySelector("#assemble_button_play_area").style.display = "none";
					this.addActionButton('new_coatl_button', _('New Cóatl'), 'onNewCoatl', 'build_area');
					document.querySelector("#new_coatl_button").style.display = "none";
				}

				this.setupNotifications();

				// Tooltips
				this.addTooltipHtmlToClass(`bag`, `<div>` +
					`<h3>${_("Supply bags")}</h3>` +
					`<h5>${_("Three Supply bags for the corresponding Cóatl piece types. The number on them shows the number of Cóatl pieces left in each bag")}.</h5>` +
					`<h4>${_("Remember")}</h4>` +
					`<h5>${_("You must replenish the Supply board whenever")}:</h5>` +
					`<h5>• ${_("its supply of body segments is emptied")}</h5>` +
					`<h5 style="margin-left: 7px">${_("OR")}</h5>` +
					`<h5>• ${_("its supply of heads and tails are emptied")}.</h5>` +
					`<h5>${_("Fill all empty spaces of the Supply board with randomly drawn pieces of the matching type")}.</h5>` +
					`</div>`);

				this.addTooltipHtml(`deck_prophecy`, `<div>` +
					`<h3>${_("Prophecy card deck")}</h3>` +
					`<h5>${_("The primary method of scoring points is by fulfilling Prophecy cards")}.</h5>` +
					`<h5>${_("Click here to draw a Prophecy card")}.</h5>` +
					`<h4>${_("Remember")}</h4>` +
					`<h5>${_("You can take several cards during your turn, but you cannot exceed the 5-card hand limit")}.</h5>` +
					`</div>`);

				this.jumpToManager = new JumpToManager(this, {
					localStorageFoldedKey: 'coatl-jumpto-folded',
					topEntries: [
						new JumpToEntry(_('Supplies'), 'supplies', {'color': '#D48D15'})
					],
					entryClasses: 'round-point',
					toggleColor: '#545151'
				});

				document.querySelectorAll(".coatl_overall:has(.box[type='head']):has(.box[type='tail'])").forEach(
					(e) => e.setAttribute("finished", "true")
				);
			},

			/**
			 * onLoadingComplete:
			 * Method called from override_setLoader, used for canceled game logs
			 * Used from BGA Santorini by Tisaac & Quietmint & Morgalad
			 * https://github.com/AntonioSoler/bga-santorini/blob/master/santorini.js
			 */
			onLoadingComplete: function () {
				this.cancelLogs(this.gamedatas.cancelMoveIds);
			},


			///////////////////////////////////////////////////
			//// GameTraits & client states

			/**
			 * onEnteringState:
			 * Part of the BGA framework.
			 * This method is called each time we are entering into a new game state.
			 * You can use this method to perform some user interface changes at this moment.
			 * @param stateName name of the state the game is entering
			 * @param args args of the state (can be defined in states.php)
			 */
			onEnteringState: function (stateName, args) {
				if (this.isCurrentPlayerActive()) {
					switch (stateName) {
						case 'discardProphecy':
							this.stateArgs["discard_prophecy_n"] = args.args[this.player_id];
							if (this.stateArgs["discard_prophecy_n"] === 1) {
								this.gamedatas.gamestate.descriptionmyturn = _('${you} must discard 1 Prophecy card from your hand');
								this.prophecyHand.setSelectionMode(1);
							} else {
								this.gamedatas.gamestate.descriptionmyturn = dojo.string.substitute(_('${you} must discard ${n} Prophecy cards from your hand'), {
									n: this.stateArgs["discard_prophecy_n"],
									you: _('${you}')
								});
								this.prophecyHand.setSelectionMode(2);
							}
							this.prophecyHand.selectionClass = "discard";
							this.updatePageTitle();
							break;
						case 'playerTurn':
							this.stateArgs["possibleActions"] = args.args.possibleActions;
							if (this.sacrificeTokens[this.player_id]["piece"] !== null) {
								this.inBagAvailable = {
									"head": {
										"yellow": parseInt(args.args.piece_in_bag_available.head.yellow) !== 0,
										"red": parseInt(args.args.piece_in_bag_available.head.red) !== 0,
										"black": parseInt(args.args.piece_in_bag_available.head.black) !== 0,
										"green": parseInt(args.args.piece_in_bag_available.head.green) !== 0,
										"blue": parseInt(args.args.piece_in_bag_available.head.blue) !== 0
									},
									"body": {
										"yellow_1": parseInt(args.args.piece_in_bag_available.body.yellow) !== 0,
										"red_1": parseInt(args.args.piece_in_bag_available.body.red) !== 0,
										"black_1": parseInt(args.args.piece_in_bag_available.body.black) !== 0,
										"green_1": parseInt(args.args.piece_in_bag_available.body.green) !== 0,
										"blue_1": parseInt(args.args.piece_in_bag_available.body.blue) !== 0,
										"yellow_2": parseInt(args.args.piece_in_bag_available.body.yellow) > 1,
										"red_2": parseInt(args.args.piece_in_bag_available.body.red) > 1,
										"black_2": parseInt(args.args.piece_in_bag_available.body.black) > 1,
										"green_2": parseInt(args.args.piece_in_bag_available.body.green) > 1,
										"blue_2": parseInt(args.args.piece_in_bag_available.body.blue) > 1
									},
									"tail": {
										"yellow": parseInt(args.args.piece_in_bag_available.tail.yellow) !== 0,
										"red": parseInt(args.args.piece_in_bag_available.tail.red) !== 0,
										"black": parseInt(args.args.piece_in_bag_available.tail.black) !== 0,
										"green": parseInt(args.args.piece_in_bag_available.tail.green) !== 0,
										"blue": parseInt(args.args.piece_in_bag_available.tail.blue) !== 0
									},
								};
							}
							if (this.stateArgs["possibleActions"]["takePiece"] === 1) {
								document.querySelector("supply_piece").setAttribute("active", "single");
								if (this.sacrificeTokens[this.player_id]["piece"] !== null) {
									this.getPieceToken().setAttribute("active", "true");
									for (const i in this.inBagAvailable.body) {
										this.inBagAvailable.body[i] = false;
									}
								}
							} else if (this.stateArgs["possibleActions"]["takePiece"] > 1) {
								document.querySelector("supply_piece").setAttribute("active", "true");
								if (this.sacrificeTokens[this.player_id]["piece"] !== null) {
									this.getPieceToken().setAttribute("active", "true");
								}
							} else if (this.sacrificeTokens[this.player_id]["piece"] !== null) {
								this.getPieceToken().setAttribute("active", "false");
							}
							if (this.stateArgs["possibleActions"]["takeProphecy"]) {
								this.prophecySupply.setSelectionMode(1);
								document.querySelector("deck_prophecy").setAttribute("active", "true");
							}
							if (this.stateArgs["possibleActions"]["assemble"]) {
								document.querySelector("#assemble_button_play_area").style.display = "block";
							}
							if (this.sacrificeTokens[this.player_id]["prophecy"] !== null) this.getProphecyToken().setAttribute("active", "true");
							if (this.sacrificeTokens[this.player_id]["temple"] !== null) this.getTempleToken().setAttribute("active", "true");
							break;
						case 'takeProphecy':
							this.stateArgs["possibleActions"] = args.args.possibleActions;
							if (this.stateArgs["possibleActions"]["takeProphecy"]) {
								this.prophecySupply.setSelectionMode(1);
								document.querySelector("deck_prophecy").setAttribute("active", "true");
							}
							break;
						case 'assemble':
							this.stateArgs["possibleActions"] = args.args.possibleActions;
							if (this.stateArgs["possibleActions"]["new"]) {
								document.querySelector("#selector_grid").setAttribute("active", "true");
								document.querySelector("#new_coatl_button").style.display = "block";
							}
							if (this.stateArgs["possibleActions"]["piece"]) {
								document.querySelector("#selector_grid").setAttribute("active", "true");
								document.querySelectorAll("build_area coatls .coatl_overall .coatl_container").forEach(
									(e) => e.setAttribute("active", "true")
								)
							}
							if (this.stateArgs["possibleActions"]["prophecy"].length !== 0) {
								this.prophecyHand.setSelectionMode(1);
								this.prophecyHand.selectionClass = "addProphecy";
								dojo.setAttr('hand_prophecy', "active", "addProphecy");
								const allProphecy = this.prophecyHand.getAllItems();
								for (const i in allProphecy) {
									dojo.addClass(`hand_prophecy_item_${allProphecy[i].id}`, "stockitem_unselectable");
								}
								for (const coatl_id in this.stateArgs["possibleActions"]["prophecy"]) {
									document.querySelector(`build_area coatls #coatl_${this.getActivePlayerId()}_${coatl_id}_coatl_overall .card_container`).setAttribute("active", "true");
									for (const card_id in this.stateArgs["possibleActions"]["prophecy"][coatl_id]) {
										dojo.removeClass(`hand_prophecy_item_${card_id}`, "stockitem_unselectable")
									}
								}
							}
							if (this.stateArgs["possibleActions"]["temple"].length !== 0) {
								this.templeSupply.setSelectionMode(1);
								this.templeHand.setSelectionMode(1);
								this.templeSupply.selectionClass = "addTemple";
								this.templeHand.selectionClass = "addTemple";
								dojo.setAttr('supply_temple', "active", "addTemple");
								dojo.setAttr('hand_temple', "active", "addTemple");
								const allTempleSupply = this.templeSupply.getAllItems();
								for (const i in allTempleSupply) {
									dojo.addClass(`supply_temple_item_${allTempleSupply[i].id}`, "stockitem_unselectable");
								}
								this.templeHand.setSelectionMode(1);
								const allTempleHand = this.templeHand.getAllItems();
								for (const i in allTempleHand) {
									dojo.addClass(`hand_temple_item_${allTempleHand[i].id}`, "stockitem_unselectable");
								}
								for (const coatl_id in this.stateArgs["possibleActions"]["temple"]) {
									document.querySelector(`build_area coatls #coatl_${this.getActivePlayerId()}_${coatl_id}_coatl_overall .card_container`).setAttribute("active", "true");
									if (this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"] !== undefined) {
										for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"]) {
											dojo.removeClass(`supply_temple_item_${card_id}`, "stockitem_unselectable")
										}
									}
									if (this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"] !== undefined) {
										for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"]) {
											dojo.removeClass(`hand_temple_item_${card_id}`, "stockitem_unselectable")
										}
									}
								}
							}
							if (this.stateArgs["possibleActions"]["undo"] && !this.stateArgs["possibleActions"]["confirmAssemble"]) {
								document.querySelector("#help-bar-blue").innerHTML = _(`You can't confirm your turn, because you have a completed Cóatl without any Prophecy cards`);
							}
							break;
						case 'steal':
							this.stateArgs["freeSpaces"] = args.args.freeSpaces;
							const selector_grids = document.getElementsByClassName("other_selector_grid");
							for (let i = 0; i < selector_grids.length; i++) {
								selector_grids.item(i).setAttribute("active", "true");
							}
							break;
						case 'discardProphecySacrificeToken':
							this.prophecyHand.setSelectionMode(2);
							this.prophecyHand.selectionClass = "discard";
							break;
						case 'takeTempleSacrificeToken':
							this.templeSupply.setSelectionMode(1);
							break;
					}
					if (this.finalTurns) {
						let str;
						if (args.args.final_turn_no === 1) str = _(`(You have 1 turn left)`);
						else if (args.args.final_turn_no === 2) str = _(`(You have 2 consecutive turns left)`);
						else str = _(`(You have 2 turns left)`);
						document.querySelector("#help-bar").innerHTML += ' ' + str;
					}
				}
				// Setting game state description
				switch (stateName) {
					case 'playerTurn':
						if (args.args.possibleActions["takePiece"] || args.args.possibleActions["takeProphecy"] || args.args.possibleActions["assemble"]) this.setStateDescription(`${args.args.possibleActions["takePiece"] ? "Piece" : ""}${args.args.possibleActions["takeProphecy"] ? "Prophecy" : ""}${args.args.possibleActions["assemble"] ? "Assemble" : ""}`)
						else this.setStateDescription(`SacrificeToken`)
						document.querySelectorAll(".coatl_overall:has(.box[type='head']):has(.box[type='tail'])").forEach(
							(e) => e.setAttribute("finished", "true")
						);
						break;
					case 'pieceTake':
						if (args.args.replenish) {
							this.setStateDescription(`Replenish`);
						}
						break;
					case 'takeProphecy':
						if (!args.args.possibleActions["takeProphecy"]) {
							this.setStateDescription(`Confirm`);
						} else if (args.args.sacrificeToken) {
							this.setStateDescription(`SacrificeToken`);
						}
						break;
					case 'assemble':
						if (!args.args.possibleActions["new"] && !args.args.possibleActions["piece"] && args.args.possibleActions["prophecy"].length === 0 && args.args.possibleActions["temple"].length === 0) this.setStateDescription(`Confirm`);
						break;
					case 'discardProphecySacrificeToken':
						let see_the_future = `<see_the_future_statusbar_icon></see_the_future_statusbar_icon>`
						if (this.isCurrentPlayerActive()) {
							this.gamedatas.gamestate.descriptionmyturn =
								`${see_the_future} ${_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][`descriptionmyturn`])}`;
						} else {
							this.gamedatas.gamestate.description =
								`${see_the_future} ${_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][`description`])}`;
						}
						this.updatePageTitle();
						break;
					case 'takeTempleSacrificeToken':
						this.getTempleToken(this.getActivePlayerId()).setAttribute("display", "none");
						let priest_commitment = `<priest_commitment_statusbar_icon></priest_commitment_statusbar_icon>`
						if (this.isCurrentPlayerActive()) {
							this.gamedatas.gamestate.descriptionmyturn =
								`${priest_commitment} ${_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][`descriptionmyturn`])}`;
						} else {
							this.gamedatas.gamestate.description =
								`${priest_commitment} ${_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][`description`])}`;
						}
						this.updatePageTitle();
						break;
					case 'gameEnd':
						dojo.destroy("help-bar");
						break;
				}
			},

			/**
			 * onLeavingState:
			 * Part of the BGA framework.
			 * This method is called each time we are leaving a game state.
			 * You can use this method to perform some user interface changes at this moment.
			 * @param stateName name of the state the game is leaving
			 */
			onLeavingState: function (stateName) {
				if (this.isCurrentPlayerActive()) {
					switch (stateName) {
						case 'playerTurn':
							document.querySelector("supply_piece").setAttribute("active", "false");
							this.prophecySupply.setSelectionMode(0);
							document.querySelector("deck_prophecy").setAttribute("active", "false");
							document.querySelector("#assemble_button_play_area").style.display = "none";
							if (this.sacrificeTokens[this.player_id]["piece"] !== null) this.getPieceToken().setAttribute("active", "false");
							if (this.sacrificeTokens[this.player_id]["prophecy"] !== null) this.getProphecyToken().setAttribute("active", "false");
							if (this.sacrificeTokens[this.player_id]["temple"] !== null) this.getTempleToken().setAttribute("active", "false");
							break;
						case 'takeProphecy':
							this.prophecySupply.setSelectionMode(0);
							document.querySelector("deck_prophecy").setAttribute("active", "false");
							break;
						case 'assemble':
							document.querySelector("selector_grid").setAttribute("active", "false");
							document.querySelector("#new_coatl_button").style.display = "none";
							document.querySelector("selector_grid").setAttribute("active", "false");
							document.querySelectorAll("build_area coatls .coatl_overall .coatl_container").forEach(
								(e) => e.setAttribute("active", "false")
							)
							document.querySelectorAll("build_area coatls .coatl_overall .card_container").forEach(
								(e) => e.setAttribute("active", "false")
							)
							this.prophecyHand.setSelectionMode(0);
							this.prophecyHand.selectionClass = "";
							dojo.setAttr('hand_prophecy', "active", "false");
							this.templeSupply.setSelectionMode(0);
							this.templeSupply.selectionClass = "";
							dojo.setAttr('supply_temple', "active", "false");
							this.templeHand.setSelectionMode(0);
							this.templeHand.selectionClass = "";
							dojo.setAttr('hand_temple', "active", "false");
							document.querySelector("#help-bar-blue").innerHTML = '';
							break;
						case 'steal':
							const selector_grids = document.getElementsByClassName("other_selector_grid");
							for (let i = 0; i < selector_grids.length; i++) {
								selector_grids.item(i).setAttribute("active", "false");
							}
							this.setStealParamsNull();
							break;
						case 'takeTempleSacrificeToken':
							this.templeSupply.setSelectionMode(0);
							break;
					}
					this.stateArgs = {};
					if (this.finalTurns) {
						document.querySelector("#help-bar").innerHTML = _("These are the game's final turns!");
					}
				}
				if (stateName === 'takeTempleSacrificeToken' && this.sacrificeTokens[this.getActivePlayerId()]["temple"] !== null) {
					this.getTempleToken(this.player_id).setAttribute("display", "auto");
				}
				if (stateName === 'assemble') {
					document.querySelectorAll(".coatl_overall:not(:has(.box[type='head']):has(.box[type='tail']))").forEach(
						(e) => e.setAttribute("finished", "false")
					)
				}
			},

			/**
			 * onUpdateActionButtons
			 * Part of the BGA framework.
			 * In this method you can manage "action buttons" that are displayed in the action status bar
			 * (ie: the HTML links in the status bar).
			 * @param stateName name of the state the game is in
			 * @param args args of the state (can be defined in states.php)
			 */
			onUpdateActionButtons: function (stateName, args) {
				if (this.isCurrentPlayerActive()) {
					switch (stateName) {
						case 'discardProphecy':
							if (args[this.player_id] === 1) {
								this.addActionButton('discard_prophecies_button', _('Discard selected card'), "onDiscardProphecy", null, false, 'red');
							} else {
								this.addActionButton('discard_prophecies_button', _('Discard selected cards'), "onDiscardProphecy", null, false, 'red');
							}
							dojo.addClass('discard_prophecies_button', 'disabled');
							break;

						case 'playerTurn':
							if (args.possibleActions["assemble"]) {
								this.addActionButton('assemble_button', _('Assemble'), 'onAssemble');
							}
							if (args.possibleActions["steal"]) {
								this.addActionButton('steal_button', _('Steal'), 'onSteal');
							}
							if (args.possibleActions["undo"]) {
								this.addActionButton('on_undo', _('Undo'), 'onUndo', null, false, 'gray');
							}
							this.addActionButton('on_give_up', _('Give up'), 'onGiveUp', null, false, 'red');
							break;
						case 'takePiece':
							if (args.replenish) this.addActionButton('confirm_piece_with_replenishing_button', _('Confirm and replenish the board'), () => this.onConfirmTakePiece(true));
							else this.addActionButton('confirm_piece_button', _('Confirm'), () => this.onConfirmTakePiece(false));
							this.addActionButton('on_undo', _('Undo'), 'onUndo', null, false, 'gray');
							break;

						case "takeProphecy":
							this.addActionButton('confirm_prophecy_card_button', _('Confirm'), 'onConfirmTakeProphecy');
							if (args.possibleActions["undo"]) this.addActionButton('on_undo', _('Undo'), 'onUndo', null, false, 'gray');
							if (args.possibleActions["restart"]) this.addActionButton('on_restart', _('Restart'), 'onRestart', null, false, 'gray');
							break;
						case 'assemble':
							if (!args.possibleActions["undo"]) this.addActionButton('on_cancel', _('Cancel'), 'onCancel', null, false, 'gray')
							else {
								if (args.possibleActions["confirmAssemble"]) this.addActionButton('confirm_assemble_button', _('Confirm'), 'onConfirmAssemble');
								this.addActionButton('on_undo', _('Undo'), 'onUndo', null, false, 'gray')
								if (args.possibleActions["restart"]) this.addActionButton('on_restart', _('Restart'), 'onRestart', null, false, 'gray');
							}
							break;
						case 'discardProphecySacrificeToken':
							this.addActionButton('discard_prophecy_sacrifice_token_button', _("Don't discard cards"), "onDiscardProphecySacrificeToken", null, false);
							break;
						case 'steal':
							this.addActionButton('steal_pieces_button', _('Steal selected piece'), "onConfirmSteal", null, false);
							dojo.addClass('steal_pieces_button', 'disabled');
						case 'takeTempleSacrificeToken':
							this.addActionButton('on_cancel', _('Cancel'), 'onCancel', null, false, 'gray')
							break;
					}
				}
			},

			///////////////////////////////////////////////////
			//// Utility methods

			/**
			 * setStateDescription:
			 * Method to set state description depending on the possible actions
			 * @param param
			 */
			setStateDescription: function (param) {
				if (this.isCurrentPlayerActive()) {
					this.gamedatas.gamestate.descriptionmyturn =
						_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][
							`descriptionmyturn-${param}`
							]);
				} else {
					this.gamedatas.gamestate.description =
						_(this.gamedatas.gamestates[this.gamedatas.gamestate.id][
							`description-${param}`
							]);
				}
				this.updatePageTitle();
			},

			/**
			 * setupProphecyCardStock:
			 * Function to setup a prophecy card stock component
			 * BGA Stock component: https://en.doc.boardgamearena.com/Stock
			 * @param container_id
			 * @param isLabel
			 * @returns {ebg.stock}
			 */
			setupProphecyCardStock: function (container_id, isLabel) {
				const res = new ebg.stock();
				const width = isLabel ? 155.125 : 160;
				const height = isLabel ? 65 : 223;
				const image = isLabel ? 'prophecy_cards_label.jpg' : 'prophecy_cards.jpg';
				res.create(this, $(container_id), width, height);
				res.item_margin = 14;
				res.image_items_per_row = 8;
				res.setSelectionMode(0);
				if (isLabel) res.onItemCreate = dojo.hitch(this, 'setupNewProphecyLabel');
				else res.onItemCreate = dojo.hitch(this, 'setupNewProphecy');
				let id = 0;
				let until = 8;
				for (let i = 0; i < 7; i++) {
					for (let j = 0; j < until; j++) {
						res.addItemType(id, id, g_gamethemeurl + 'img/' + image, id);
						id++;
					}
					if (i === 5) until = 7;
				}
				return res;

			},

			/**
			 * setupNewProphecy:
			 * Method that runs on new prophecy card creation
			 * @param card_div
			 * @param card_type_id
			 * @param card_id
			 */
			setupNewProphecy: function (card_div, card_type_id, card_id) {
				this.addTooltipHtml(card_div.id, this.prophecy_tooltip(parseInt(card_type_id)));
				card_div.innerHTML += this.getProphecyCardColorblindSymbols(parseInt(card_type_id));
				card_div.innerHTML += this.getProphecyCardSymbols(parseInt(card_type_id));
			},

			getProphecyCardSymbols: function (card_type_id) {
				if (card_type_id <= 4 || card_type_id >= 49) {
					return `<card_color_symbol>A<card_color_symbol/>`;
				} else if (card_type_id <= 14) {
					return `<card_color_symbol>B<card_color_symbol/>`;
				} else if (card_type_id <= 24) {
					return `<card_color_symbol>C<card_color_symbol/>`;
				} else if (card_type_id <= 34) {
					return `<card_color_symbol>D<card_color_symbol/>`;
				} else if (card_type_id <= 48) {
					return `<card_color_symbol>E<card_color_symbol/>`;
				}
			},

			getProphecyCardColorblindSymbols: function (card_type_id) {
				switch (card_type_id) {
					case 0:
						return `<symbols>◆<symbols/>`;
					case 1:
						return `<symbols>⬟<symbols/>`;
					case 2:
						return `<symbols>■<symbols/>`;
					case 3:
						return `<symbols>●<symbols/>`;
					case 4:
						return `<symbols>▲<symbols/>`;
					case 5:
						return `<symbols>(◆x)◆◆(◆x)<symbols/>`;
					case 6:
						return `<symbols>(⬟x)⬟⬟(⬟x)<symbols/>`;
					case 7:
						return `<symbols>(■x)■■(■x)<symbols/>`;
					case 8:
						return `<symbols>(●x)●●(●x)<symbols/>`;
					case 9:
						return `<symbols>(▲x)▲▲(▲x)<symbols/>`;
					case 10:
						return `<symbols>◆◆◆<symbols/>`;
					case 11:
						return `<symbols>⬟⬟⬟<symbols/>`;
					case 12:
						return `<symbols>■■■<symbols/>`;
					case 13:
						return `<symbols>●●●<symbols/>`;
					case 14:
						return `<symbols>▲▲▲<symbols/>`;
					case 15:
						return `<symbols>◆●<symbols/>`;
					case 16:
						return `<symbols>⬟■<symbols/>`;
					case 17:
						return `<symbols>◆▲<symbols/>`;
					case 18:
						return `<symbols>●▲<symbols/>`;
					case 19:
						return `<symbols>■●<symbols/>`;
					case 20:
						return `<symbols>◆■<symbols/>`;
					case 21:
						return `<symbols>⬟▲<symbols/>`;
					case 22:
						return `<symbols>■▲■▲<symbols/>`;
					case 23:
						return `<symbols>◆⬟◆⬟<symbols/>`;
					case 24:
						return `<symbols>⬟●⬟●<symbols/>`;
					case 25:
						return `<symbols>⬟⬟⬟●●<symbols/>`;
					case 26:
						return `<symbols>■■●●●<symbols/>`;
					case 27:
						return `<symbols>◆◆▲▲▲<symbols/>`;
					case 28:
						return `<symbols>▲▲◆◆<symbols/>`;
					case 29:
						return `<symbols>⬟⬟▲▲<symbols/>`;
					case 30:
						return `<symbols>■■◆◆<symbols/>`;
					case 31:
						return `<symbols>●●⬟⬟<symbols/>`;
					case 32:
						return `<symbols>◆■⬟●▲<symbols/>`;
					case 33:
						return `<symbols>⬟■▲◆●<symbols/>`;
					case 34:
						return `<symbols>■●⬟▲◆<symbols/>`;
					case 35:
						return `<symbols>◆(■∞)⬟<symbols/>`;
					case 36:
						return `<symbols>●(◆∞)■<symbols/>`;
					case 37:
						return `<symbols>◆(▲∞)●<symbols/>`;
					case 38:
						return `<symbols>▲(●∞)■<symbols/>`;
					case 39:
						return `<symbols>⬟(■∞)▲<symbols/>`;
					case 40:
						return `<symbols>⬟(▲∞)◆<symbols/>`;
					case 41:
						return `<symbols>●(⬟∞)■<symbols/>`;
					case 42:
						return `<symbols>●(◆∞)●<symbols/>`;
					case 43:
						return `<symbols>▲(●∞)▲<symbols/>`;
					case 44:
						return `<symbols>■◆(∞)◆■<symbols/>`;
					case 45:
						return `<symbols>●■(∞)■●<symbols/>`;
					case 46:
						return `<symbols>■▲(∞)▲■<symbols/>`;
					case 47:
						return `<symbols>◆⬟(∞)⬟◆<symbols/>`;
					case 48:
						return `<symbols>⬟▲(∞)▲⬟<symbols/>`;
					case 49:
						return `<symbols>2◆ 2● 1⬟<symbols/>`;
					case 50:
						return `<symbols>2■ 2▲ 1⬟<symbols/>`;
					case 51:
						return `<symbols>2⬟ 2■ 1●<symbols/>`;
					case 52:
						return `<symbols>2◆ 2■ 1⬟ 1▲<symbols/>`;
					case 53:
						return `<symbols>2● 2▲ 1◆ 1■<symbols/>`;
					case 54:
						return `<symbols>2◆ 2⬟ 1● 1▲<symbols/>`;
				}
			},

			/**
			 * setupNewProphecyLabel:
			 * Method that runs on new prophecy label card creation
			 * @param card_div
			 * @param card_type_id
			 * @param card_id
			 */
			setupNewProphecyLabel: function (card_div, card_type_id, card_id) {
				let color;
				let table;
				if (card_type_id < 5) {
					color = "#A74545";
					table = `<div>3X=2</div><div>4X=3</div><div>5X=4</div><div>6X=5</div>`;
				} else if (card_type_id < 15) {
					color = "#000";
					if (card_type_id < 10) {
						table = `<div>1X=2</div><div>2X=5</div>`;
					} else {
						table = `<div>1X=3</div><div>2X=7</div>`;
					}
				} else if (card_type_id < 25) {
					color = "#3F8AA4";
					if (card_type_id < 22) {
						table = `<div>1X=1</div><div>2X=3</div><div>3X=5</div>`;
					} else {
						table = `<div>1X=5</div>`;
					}
				} else if (card_type_id < 35) {
					color = "#FAB859";
					if (card_type_id < 28) {
						table = `<div>1X=6</div>`;
					} else if (card_type_id < 32) {
						table = `<div>1X=4</div>`;
					} else {
						table = `<div>1X=7</div>`;
					}
				} else if (card_type_id < 49) {
					color = "#799C61";
					if (card_type_id < 44) {
						table = `<div>1X=2</div><div>2X=6</div>`;
					} else {
						table = `<div>1X=4</div>`;
					}
				} else {
					color = "#A74545";
					table = `<div>3X=2</div><div>4X=3</div><div>5X=4</div><div>6X=5</div>`;
					if (card_type_id < 52) {
						table = `<div>1X=3</div>`;
					} else {
						table = `<div>1X=4</div>`;
					}
				}
				card_div.innerHTML += `<div class="label_score_table" style="background-color: ${color};">${table}</div>`;
				card_div.classList.add("prophecy_label");
				this.addTooltipHtml(card_div.id, this.prophecy_tooltip(parseInt(card_type_id)));
				card_div.innerHTML += this.getProphecyCardColorblindSymbols(parseInt(card_type_id));
				card_div.innerHTML += this.getProphecyCardSymbols(parseInt(card_type_id));
			},

			/**
			 * setupTempleStock:
			 * Function to setup a temple card stock component
			 * BGA Stock component: https://en.doc.boardgamearena.com/Stock
			 * @param container_id
			 * @param isLabel
			 * @returns {ebg.stock}
			 */
			setupTempleStock: function (container_id, is_label) {
				const res = new ebg.stock();
				const width = is_label ? 190.25 : 172.65;
				const height = is_label ? 80 : 271.8;
				const image = is_label ? 'temple_cards_label.jpg' : 'temple_cards.jpg';
				res.create(this, $(container_id), width, height);
				res.item_margin = 14;
				res.image_items_per_row = 6;
				res.setSelectionMode(0);
				if (is_label) res.onItemCreate = dojo.hitch(this, 'setupNewTempleLabel');
				else res.onItemCreate = dojo.hitch(this, 'setupNewTemple');
				let id = 0;
				let until = 6;
				for (let i = 0; i < 4; i++) {
					for (let j = 0; j < until; j++) {
						res.addItemType(id, id, g_gamethemeurl + 'img/' + image, id);
						id++;
					}
					if (i === 3) until = 2
				}
				return res;
			},

			/**
			 * setupNewTemple:
			 * Method that runs on new temple card creation
			 * @param card_div
			 * @param card_type_id
			 * @param card_id
			 */
			setupNewTemple: function (card_div, card_type_id, card_id) {
				this.addTooltipHtml(card_div.id, this.temple_tooltip(parseInt(card_type_id)));
				this.addTempleCardColorblindSymbols(card_div, parseInt(card_type_id), false)
			},

			addTempleCardColorblindSymbols(card_div, card_type_id, is_label) {
				switch (card_type_id) {
					case 0:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">◆</temple_symbol>`;
						break;
					case 1:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">■</temple_symbol>`;
						break;
					case 2:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">●</temple_symbol>`;
						break;
					case 3:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">▲</temple_symbol>`;
						break;
					case 4:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">⬟</temple_symbol>`;
						break;
					case 5:
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="1" label="${is_label}">⬟</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="2" label="${is_label}">◆</temple_symbol>`;
						break;
					case 6:
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="1" label="${is_label}">◆</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="2" label="${is_label}">●</temple_symbol>`;
						break;
					case 7:
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="1" label="${is_label}">▲</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="2" label="${is_label}">■</temple_symbol>`;
						break;
					case 8:
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="1" label="${is_label}">⬟</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="2" label="${is_label}">●</temple_symbol>`;
						break;
					case 9:
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="1" label="${is_label}">▲</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="up" item="2" label="${is_label}">◆</temple_symbol>`;
						break;
					case 10:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">■</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="1" label="${is_label}">▲</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="2" label="${is_label}">⬟</temple_symbol>`;
						break;
					case 11:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">▲</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="1" label="${is_label}">■</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="2" label="${is_label}">⬟</temple_symbol>`;
						break;
					case 12:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">●</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="1" label="${is_label}">■</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="2" label="${is_label}">◆</temple_symbol>`;
						break;
					case 13:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">⬟</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="1" label="${is_label}">▲</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="2" label="${is_label}">●</temple_symbol>`;
						break;
					case 14:
						card_div.innerHTML += `<temple_symbol type="block" position="up" label="${is_label}">◆</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="1" label="${is_label}">■</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="bottom" item="2" label="${is_label}">●</temple_symbol>`;
						break;
					case 15:
						card_div.innerHTML += `<temple_symbol type="card" position="t-top" label="${is_label}">C</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="middle" item="1" label="${is_label}">⬟</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="middle" item="2" label="${is_label}">▲</temple_symbol>`;
						break
					case 16:
						card_div.innerHTML += `<temple_symbol type="card" position="top" label="${is_label}">A</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="s-bottom" item="1" label="${is_label}">◆</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="equal" position="s-bottom" item="2" label="${is_label}">■</temple_symbol>`;
						break;
					case 17:
						card_div.innerHTML += `<temple_symbol type="card" position="top" label="${is_label}">B</temple_symbol>`;
						break
					case 18:
						card_div.innerHTML += `<temple_symbol type="card" position="top" label="${is_label}">E</temple_symbol>`;
						break
					case 19:
						card_div.innerHTML += `<temple_symbol type="card" position="top" label="${is_label}">D</temple_symbol>`;
						card_div.innerHTML += `<temple_symbol type="block" position="bottom" label="${is_label}">●</temple_symbol>`;
						break;
				}
			},

			/**
			 * setupNewTempleLabel:
			 * Method that runs on new temple label card creation
			 * BGA Counter component: https://en.doc.boardgamearena.com/Counter
			 * @param card_div
			 * @param card_type_id
			 * @param card_id
			 */
			setupNewTempleLabel: function (card_div, card_type_id, card_id) {
				let color = "#C54965";
				let table = (card_type_id === 15) ? `<div>1/3=3</div><div>2/3=6</div><div>3/3=10</div>` : `<div>1/2=3</div><div>2/2=7</div>`;
				card_div.innerHTML = `<div class="label_score_table" style="background-color: ${color};">${table}</div>`;
				card_div.classList.add("temple_label");
				this.addTooltipHtml(card_div.id, this.temple_tooltip(parseInt(card_type_id)));
				this.addTempleCardColorblindSymbols(card_div, parseInt(card_type_id), true)
			},

			/**
			 * setupBagCounters:
			 * Method to setup counters on Piece bags
			 * @param head_count
			 * @param body_count
			 * @param tail_count
			 */
			setupBagCounters: function (head_count, body_count, tail_count) {
				this.counterHead = new ebg.counter();
				this.counterHead.create(`counter_head`);
				this.counterHead.setValue(head_count);
				this.counterBody = new ebg.counter();
				this.counterBody.create(`counter_body`);
				this.counterBody.setValue(body_count);
				this.counterBody.speed = 0;
				this.counterTail = new ebg.counter();
				this.counterTail.create(`counter_tail`);
				this.counterTail.setValue(tail_count);
			},

			/**
			 * bagCountersToValue:
			 * Method to set counters on Piece bags to value
			 * @param head_count
			 * @param body_count
			 * @param tail_count
			 */
			bagCountersToValue(head_count, body_count, tail_count) {
				this.counterHead.toValue(head_count);
				this.counterBody.toValue(body_count);
				this.counterTail.toValue(tail_count);
			},

			/**
			 * replenishPieceSupplyBoard:
			 * Method to put pieces to supply board
			 * @param pieces_on_supply
			 * @param animate
			 */
			replenishPieceSupplyBoard: function (pieces_on_supply, animate) {
				for (let type in pieces_on_supply) {
					for (let location_arg in pieces_on_supply[type]) {
						const current = pieces_on_supply[type][location_arg]
						this.addPieceToSupplyBoard(current.id, current.type, current.color, location_arg, animate);
					}
				}
			},

			/**
			 * addPieceToSupplyBoard:
			 * Method to put one piece to supply board
			 * @param id
			 * @param type
			 * @param color
			 * @param location
			 * @param animate
			 */
			addPieceToSupplyBoard: function (id, type, color, location, animate) {
				const element_id = `piece_${type}_${id}`
				if (!dojo.exists(element_id)) {
					dojo.place(this.format_block('jstpl_piece', {
						id: element_id,
						type: type,
						color: color,
						selector: `supply_piece_${Math.abs(location)}`,
						rotate: `supply_piece_${location}`
					}), 'supply_piece');
					this.placeOnObject(element_id, 'bag_' + type);
				} else {
					this.attachToNewParent(element_id, 'supply_piece');
					dojo.setAttr(element_id, `selector`, `supply_piece_${Math.abs(location)}`);
					dojo.setAttr(element_id, `rotate`, `supply_piece_${location}`);
				}
				this.slideToObject(element_id, 'space_' + type + '_' + location, (animate ? 500 : 0)).play();
				dojo.setAttr(`selector_${type}_${Math.abs(location)}`, "active", "true");
			},

			/**
			 * addPieceToPlayerBoard:
			 * Adds a game piece to a player's board at the specified location.
			 * @param id - The unique identifier for the game piece.
			 * @param type - The type of the game piece.
			 * @param player_id - The ID of the player whose board the piece is being added to.
			 * @param player_board_location - The location on the player's board where the piece will be placed.
			 * @param color - The color of the game piece.
			 */
			addPieceToPlayerBoard: function (id, type, player_id, player_board_location, color) {
				const element_id = `piece_${type}_${id}`
				if (!dojo.exists(element_id)) {
					let piece = dojo.place(this.format_block(`jstpl_piece`, {
						id: element_id,
						type: type,
						color: color,
						selector: `cell_${player_id}_${player_board_location}`,
						rotate: `player_board`
					}), `cell_${player_id}_${player_board_location}`);
					this.placeOnObject(piece, `cell_${player_id}_${player_board_location}`);
				} else {
					this.attachToNewParent(element_id, `cell_${player_id}_${player_board_location}`);
					dojo.setAttr(element_id, `selector`, `cell_${player_id}_${player_board_location}`);
					dojo.setAttr(element_id, `rotate`, `player_board`);
					dojo.setAttr(element_id, `shadow`, `none`);
					this.slideToObject(element_id, `cell_${player_id}_${player_board_location}`).play();
				}
				dojo.setAttr(`cellSelector_${player_id}_${player_board_location}`, "active", "true");
				dojo.setAttr(`cellSelector_${player_id}_${player_board_location}`, "type", type);
				dojo.setAttr(`cellSelector_${player_id}_${player_board_location}`, "color", color);
			},

			/**
			 * setAssembleParamsNull:
			 * Resets parameters related to the assembly process in the application to null or inactive states.
			 */
			setAssembleParamsNull: function () {
				dojo.query(`selector_grid cell piece_container piece[shadow="orange"]`).forEach(function (node) {
					dojo.setAttr(node, "shadow", "none");
				});
				dojo.query(`#${this.assembleParams.action_button}_button`).forEach(function (node) {
					dojo.removeClass(node, "orange");
				});
				dojo.query(".button_container a").forEach(e => {
					e.classList.remove("disabled");
				});
				dojo.query(`.cellSelector[active="disabled"]`).forEach(e => {
					dojo.setAttr(e, "active", "true");
				});
				dojo.query(`.add_card_button_container a`).forEach(e => {
					e.classList.remove('disabled');
				});
				if (this.stateArgs["possibleActions"] !== undefined) {
					if (this.stateArgs["possibleActions"]["prophecy"] !== undefined) {
						for (const coatl_id in this.stateArgs["possibleActions"]["prophecy"]) {
							for (const card_id in this.stateArgs["possibleActions"]["prophecy"][coatl_id]) {
								dojo.removeClass(`hand_prophecy_item_${card_id}`, "stockitem_unselectable")
							}
						}
					}
					if (this.stateArgs["possibleActions"]["temple"] !== undefined) {
						for (const coatl_id in this.stateArgs["possibleActions"]["temple"]) {
							for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"]) {
								dojo.removeClass(`hand_temple_item_${card_id}`, "stockitem_unselectable")
							}
							for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"]) {
								dojo.removeClass(`supply_temple_item_${card_id}`, "stockitem_unselectable")
							}
						}
					}
				}
				this.assembleParams.type = null;
				this.assembleParams.element = null;
				this.assembleParams.action = null;
				this.assembleParams.action_button = null;
				this.assembleParams.action_args = null;
			},

			/**
			 * setAssembleParamsAction:
			 * Updates the assemble parameters based on the provided action, button, and optional arguments.
			 * @param action
			 * @param button
			 * @param args
			 * */
			setAssembleParamsAction: function (action, button, args = null) {
				if (this.assembleParams.action_button === button) {
					dojo.removeClass(document.querySelector(`#${this.assembleParams.action_button}_button`), "orange");
					this.assembleParams.action = null;
					this.assembleParams.action_button = null;
					this.assembleParams.action_args = null;
				} else if (this.assembleParams.action == null) {
					this.assembleParams.action = action;
					this.assembleParams.action_button = button;
					this.assembleParams.action_args = args;
					dojo.addClass(document.querySelector(`#${button}_button`), "orange");
				} else {
					dojo.removeClass(document.querySelector(`#${this.assembleParams.action_button}_button`), "orange");
					this.assembleParams.action = action;
					this.assembleParams.action_button = button;
					this.assembleParams.action_args = args;
					dojo.addClass(document.querySelector(`#${button}_button`), "orange");
				}
			},

			/**
			 * newCoatl:
			 * Creates a new Cóatl for a player, initializes its related UI elements, and sets up counters and stocks.
			 * @param coatl_id
			 * @param player_id
			 */
			newCoatl: function (coatl_id, player_id) {
				const id = `coatl_${player_id}_${coatl_id}`;
				if (!dojo.exists(`${id}_container`)) {
					dojo.place(this.format_block('jstpl_coatl_container', {
						id: id
					}), `coatls_${player_id}`);
					this.coatlProphecyStocks[id] = this.setupProphecyCardStock(`${id}_cards`, true);
					this.coatlProphecyStocks[id].autowidth = true;
					this.coatlTempleStocks[id] = this.setupTempleStock(`${id}_temple_cards`, true);
					this.coatlScoreCounters[id] = new ebg.counter();
					this.coatlScoreCounters[id].create(`counter_${id}_score`);
					this.coatlScoreCounters[id].setValue(0);
					this.coatlLengthCounters[id] = new ebg.counter();
					this.coatlLengthCounters[id].create(`counter_${id}_length`);
					this.coatlLengthCounters[id].setValue(0);
					if (player_id == this.player_id) {
						this.addActionButton(`add_piece_${coatl_id}_left_button`, '+', () => this.onAddPieceToCoatl(coatl_id, 'left'), `${id}_button_container_left`);
						this.addActionButton(`add_piece_${coatl_id}_right_button`, '+', () => this.onAddPieceToCoatl(coatl_id, 'right'), `${id}_button_container_right`);
						this.addActionButton(`add_card_${coatl_id}_button`, _('Add Card'), () => this.onAddProphecyToCoatl(coatl_id), `${id}_add_card_button_container`);
					}
					// TOOL TIP
					this.addTooltipHtml(`${id}_score_token`, this.score_token_tooltip());
					this.addTooltipHtml(`${id}_length_counter`, `<h5>${_("Cóatl length")}</h5>`);
				}
			},

			/**
			 * addPieceToCoatl:
			 * Adds a piece to a specified "Coatl" structure belonging to a player.
			 * @param {number} coatl_id
			 * @param {number} player_id
			 * @param {number} piece_id
			 * @param {string} piece_type
			 * @param {string} piece_color
			 * @param {number} box_id
			 * @param {boolean} to_left
			 */
			addPieceToCoatl: async function (coatl_id, player_id, piece_id, piece_type, piece_color, box_id, to_left) {
				const box = `box_${player_id}_${coatl_id}_${box_id}`;
				const piece = `piece_${piece_type}_${piece_id}`;
				this.coatlLengthCounters[`coatl_${player_id}_${coatl_id}`].incValue(1);
				if (player_id == this.player_id) {
					if (piece_type === "head") {
						dojo.destroy(`add_piece_${coatl_id}_left_button`);
					} else if (piece_type === "tail") {
						dojo.destroy(`add_piece_${coatl_id}_right_button`);
					}
				}
				if (!dojo.exists(box)) {
					let position;
					if (to_left) position = "first";
					else position = "last";
					dojo.place(this.format_block('jstpl_box', {
						id: box,
						type: piece_type
					}), `coatl_${player_id}_${coatl_id}`, position);
				}
				if (!dojo.exists(piece)) {
					let piece_element = dojo.place(this.format_block(`jstpl_piece`, {
						id: piece,
						type: piece_type,
						color: piece_color,
						selector: `none`,
						rotate: `none`
					}), box);
					this.placeOnObject(piece_element, box);
				} else {
					dojo.setAttr(piece, "selector", "none");
					dojo.setAttr(piece, "rotate", "none");
					dojo.setAttr(piece, "shadow", "none");
					let t = dojo.getAttr(piece, "type");
					let c = dojo.getAttr(piece, "color");
					this.slideToObjectAndDestroy(piece, box).play();
					await this.wait(500);
					let piece_element = dojo.place(this.format_block(`jstpl_piece`, {
						id: piece,
						type: t,
						color: c,
						selector: `none`,
						rotate: `none`
					}), box);
					this.placeOnObject(piece_element, box);
				}
			},

			///////////////////////////////////////////////////
			//// Player's action

			/*
			
				Here, you are defining methods to handle player's action (ex: results of mouse click on 
				game objects).
				
				Most of the time, these methods:
				_ check the action is possible at this game state.
				_ make a call to the game server
			
			*/

			//// Piece actions

			/**
			 * onDiscardProphecy:
			 * Handler function to discard Prophecy cards from hand
			 *  (Used at the game start, to discard cards from players)
			 */
			onDiscardProphecy: function (evt) {
				if (this.isCurrentPlayerActive() && this.checkAction('discardProphecy')) {
					const selected_prophecies = this.prophecyHand.getSelectedItems();
					if (selected_prophecies.length === this.stateArgs["discard_prophecy_n"]) {
						var prophecy_cards = '';
						for (let i in selected_prophecies) {
							prophecy_cards += selected_prophecies[i].id + ' ';
						}
						this.ajaxcall("/coatl/coatl/actDiscardProphecy.html", {
							lock: true,
							prophecy_cards: prophecy_cards,
						}, function (is_error) {
						});
					}
				}
			},

			/**
			 * onTakePiece:
			 * Handler function to select Cóatl pieces from board
			 * @param evt event to handler
			 */
			onTakePiece: function (evt) {
				dojo.stopEvent(evt);
				if (this.isCurrentPlayerActive() && this.checkAction('takePiece')) {
					let target = evt.currentTarget.id;
					if (dojo.getAttr(target, "active") === "true") {
						let target_id_tags = evt.currentTarget.id.split('_');
						if (this.stateArgs["possibleActions"]["takePiece"] > 1 ||
							(this.stateArgs["possibleActions"]["takePiece"] === 1 && target_id_tags[1] !== "body"))
							this.ajaxcall("/coatl/coatl/actTakePiece.html", {
									lock: true,
									piece_type: target_id_tags[1],
									board_space: parseInt(target_id_tags[2])
								}, function (is_error) {
								}
							);
					}
				}
			},

			/**
			 * onConfirmTakePiece:
			 * Handler function to confirm Cóatl pieces selection
			 * @param replanish
			 */
			onConfirmTakePiece: function (replanish) {
				if (this.isCurrentPlayerActive() && this.checkAction('confirmTakePiece')) {
					this.ajaxcall("/coatl/coatl/actConfirmTakePiece.html", {
							lock: true,
							replanish: replanish
						}, function (is_error) {
						}
					);
				}
			},

			//// Prophecy card actions

			/**
			 * onSelectProphecyFromHand:
			 * Handler function to select Prophecy cards from hand
			 * @param evt event to handler
			 */
			onSelectProphecyFromHand: function (evt) {
				if (this.isCurrentPlayerActive()) {
					switch (this.gamedatas.gamestate.name) {
						case 'discardProphecy':
							if (this.prophecyHand.getSelectedItems().length === this.stateArgs["discard_prophecy_n"])
								dojo.removeClass('discard_prophecies_button', 'disabled');
							else
								dojo.addClass('discard_prophecies_button', 'disabled');
							break;
						case 'assemble':
							const cards = this.prophecyHand.getSelectedItems();
							if (this.assembleParams.type === "piece") this.setAssembleParamsNull();
							if (cards.length === 0) {
								this.setAssembleParamsNull();
							} else {
								dojo.query(`.add_card_button_container a`).forEach(e => {
									e.classList.remove('disabled');
								});
								if (this.assembleParams.action === "add_card") {
									this.ajaxcall("/coatl/coatl/actAddProphecy.html", {
										lock: true,
										coatl_id: this.assembleParams.action_args.coatl_id,
										prophecy: parseInt(cards[0].id)
									}, function (is_error) {
									});
									this.setAssembleParamsNull();
									this.prophecyHand.unselectAll();
								} else {
									this.templeSupply.unselectAll();
									this.templeHand.unselectAll();
									this.assembleParams.type = "prophecy_card";
									this.assembleParams.element = cards[0].id;
									for (const coatl_id in this.stateArgs["possibleActions"]["temple"]) {
										if (this.stateArgs["possibleActions"]["prophecy"][coatl_id] === undefined) document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
									}
									for (const coatl_id in this.stateArgs["possibleActions"]["prophecy"]) {
										if (this.stateArgs["possibleActions"]["prophecy"][coatl_id][this.assembleParams.element] === undefined)
											document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
									}
								}
							}
							break;
						case "discardProphecySacrificeToken":
							if (this.prophecyHand.getSelectedItems().length === 0) {
								document.getElementById("discard_prophecy_sacrifice_token_button").innerHTML = _("Don't discard cards");
								dojo.addClass('discard_prophecy_sacrifice_token_button', 'bgabutton_blue');
								dojo.removeClass('discard_prophecy_sacrifice_token_button', 'bgabutton_red');
							} else if (this.prophecyHand.getSelectedItems().length === 1) {
								document.getElementById("discard_prophecy_sacrifice_token_button").innerHTML = _('Discard selected card');
								dojo.addClass('discard_prophecy_sacrifice_token_button', 'bgabutton_red');
								dojo.removeClass('discard_prophecy_sacrifice_token_button', 'bgabutton_blue');
							} else {
								document.getElementById("discard_prophecy_sacrifice_token_button").innerHTML = _('Discard selected cards');
							}
							break;
					}
				}
			},

			/**
			 * onSelectProphecyFromSupply:
			 * Action to select Prophecy card from supply
			 * @param evt event to handler
			 */
			onSelectProphecyFromSupply: function (evt) {
				if (this.checkAction('takeProphecy') && this.stateArgs["possibleActions"]["takeProphecy"]) {
					const selected_card = this.prophecySupply.getSelectedItems()[0];
					this.ajaxcall("/coatl/coatl/actTakeProphecy.html", {
						lock: true,
						card_type: selected_card.type,
						card_id: selected_card.id
					}, function (is_error) {
					});
				}
			},

			/**
			 * onSelectTempleFromHand:
			 * Action to select Temple card from hand
			 * @param evt event to handler
			 */
			onSelectTempleFromHand: function (evt) {
				if (this.gamedatas.gamestate.name === "assemble") {
					const cards = this.templeHand.getSelectedItems();
					if (this.assembleParams.type === "piece") this.setAssembleParamsNull();
					if (cards.length === 0) {
						this.setAssembleParamsNull();
					} else {
						dojo.query(`.add_card_button_container a`).forEach(e => {
							e.classList.remove('disabled');
						});
						if (this.assembleParams.action === "add_card") {
							this.ajaxcall("/coatl/coatl/actAddTemple.html", {
								lock: true,
								coatl_id: this.assembleParams.action_args.coatl_id,
								temple: parseInt(cards[0].id)
							}, function (is_error) {
							});
							this.setAssembleParamsNull();
							this.templeHand.unselectAll();
						} else {
							this.prophecyHand.unselectAll();
							this.templeSupply.unselectAll();
							this.assembleParams.type = "temple_card";
							this.assembleParams.element = cards[0].id;
							for (const coatl_id in this.stateArgs["possibleActions"]["prophecy"]) {
								if (this.stateArgs["possibleActions"]["temple"][coatl_id] === undefined)
									document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
							}
							for (const coatl_id in this.stateArgs["possibleActions"]["temple"]) {
								if (this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"][this.assembleParams.element] === undefined)
									document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
							}
						}
					}
				}
			},

			/**
			 * onSelectTempleFromSupply:
			 * Action to select Temple card from supply
			 * @param evt event to handler
			 */
			onSelectTempleFromSupply: function (evt) {
				switch (this.gamedatas.gamestate.name) {
					case "assemble":
						const cards = this.templeSupply.getSelectedItems();
						if (this.assembleParams.type === "piece") this.setAssembleParamsNull();
						if (cards.length === 0) {
							this.setAssembleParamsNull();
						} else {
							dojo.query(`.add_card_button_container a`).forEach(e => {
								e.classList.remove('disabled');
							});
							if (this.assembleParams.action === "add_card") {
								this.ajaxcall("/coatl/coatl/actAddTemple.html", {
									lock: true,
									coatl_id: this.assembleParams.action_args.coatl_id,
									temple: parseInt(cards[0].id)
								}, function (is_error) {
								});
								this.setAssembleParamsNull();
								this.templeSupply.unselectAll();
							} else {
								this.prophecyHand.unselectAll();
								this.templeHand.unselectAll();
								this.assembleParams.type = "temple_card";
								this.assembleParams.element = cards[0].id;
								for (const coatl_id in this.stateArgs["possibleActions"]["prophecy"]) {
									if (this.stateArgs["possibleActions"]["temple"][coatl_id] === undefined)
										document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
								}
								for (const coatl_id in this.stateArgs["possibleActions"]["temple"]) {
									if (this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"][this.assembleParams.element] === undefined)
										document.getElementById(`add_card_${coatl_id}_button`).classList.add("disabled");
								}
							}
						}
						break;
					case "takeTempleSacrificeToken":
						if (this.checkAction('takeTempleSacrificeToken')) {
							const selected_card = this.templeSupply.getSelectedItems()[0];
							this.ajaxcall("/coatl/coatl/actTakeTempleSacrificeToken.html", {
								lock: true,
								card_type: selected_card.type,
								card_id: selected_card.id
							}, function (is_error) {
							});
						}
						break;
				}
			},

			/**
			 * onChooseProphecyCardFromDeck:
			 * Handler function to select Prophecy card from deck
			 * @param evt
			 */
			onChooseProphecyCardFromDeck: function (evt) {
				if (this.checkAction('takeProphecy')) {
					this.confirmationDialog(_("Do you want to draw a card from the deck?<br>(You won't be able to undo this action.)"), () => {
						this.ajaxcall("/coatl/coatl/actTakeProphecyFromDeck.html", {
							lock: true,
						}, function (is_error) {
						});
					});
				}
			},

			/**
			 * onConfirmTakeProphecy:
			 * Handler function to end player turn
			 * @param evt
			 */
			onConfirmTakeProphecy: function (evt) {
				if (this.checkAction('confirmTakeProphecy')) {
					this.ajaxcall("/coatl/coatl/actConfirmTakeProphecy.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			//// Assemble actions

			/**
			 * onAssemble:
			 * Handler function to start assembling Cóatl
			 * @param evt
			 */
			onAssemble: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('assemble')) {
					this.ajaxcall("/coatl/coatl/actAssemble.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			onSteal: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('steal')) {
					this.ajaxcall("/coatl/coatl/actSteal.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			/**
			 * onNewCoatl:
			 * Handler function to start new Cóatl
			 * @param evt
			 */
			onNewCoatl: function (evt) {
				if (this.assembleParams.type !== "piece") {
					this.setAssembleParamsNull();
					this.prophecyHand.unselectAll();
					this.templeHand.unselectAll();
					this.templeSupply.unselectAll();
				}
				if (this.assembleParams.element != null) {
					this.ajaxcall("/coatl/coatl/actnewCoatl.html", {
						lock: true,
						piece: parseInt(this.assembleParams.element)
					}, function (is_error) {
					});
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "active", "false");
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "type", "null");
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "color", "null");
					this.setAssembleParamsNull();
				} else {
					this.assembleParams.type = "piece";
					this.setAssembleParamsAction("new_coatl", "new_coatl");
				}
			},

			/**
			 * onAddPieceToCoatl:
			 * Handler function to add piece to a Cóatl
			 * @param coatl_id
			 * @param side
			 */
			onAddPieceToCoatl: function (coatl_id, side) {
				if (this.assembleParams.type !== "piece") {
					this.setAssembleParamsNull();
					this.prophecyHand.unselectAll();
					this.templeHand.unselectAll();
					this.templeSupply.unselectAll();
				}
				dojo.query(`.cellSelector[active="disabled"]`).forEach(e => {
					dojo.setAttr(e, "active", "true");
				})
				if (this.assembleParams.element != null) {
					this.ajaxcall("/coatl/coatl/actAddPiece.html", {
						lock: true,
						coatl_id: coatl_id,
						to_left: (side === "left"),
						piece: parseInt(this.assembleParams.element)

					}, function (is_error) {
					});
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "active", "false");
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "type", "null");
					dojo.setAttr(`cellSelector_${this.player_id}_${this.assembleParams.element}`, "color", "null");
					this.setAssembleParamsNull();
				} else {
					this.assembleParams.type = "piece";
					this.setAssembleParamsAction(`add_piece`, `add_piece_${coatl_id}_${side}`, {"coatl_id": coatl_id, "side": side});
					if (this.assembleParams.action !== null) {
						if (side === "left") {
							dojo.query(`.cellSelector[type="tail"]`).forEach(e => {
								dojo.setAttr(e, "active", "disabled");
							})
						} else {
							dojo.query(`.cellSelector[type="head"]`).forEach(e => {
								dojo.setAttr(e, "active", "disabled");
							})
						}
					}
				}
			},

			/**
			 * onAddPieceToCoatl:
			 * Handler function to add prophecy card to a Cóatl
			 * @param coatl_id
			 */
			onAddProphecyToCoatl: function (coatl_id) {
				if (this.assembleParams.type === "piece") {
					this.setAssembleParamsNull();
				}
				if (this.assembleParams.element != null) {
					switch (this.assembleParams.type) {
						case "prophecy_card":
							this.ajaxcall("/coatl/coatl/actAddProphecy.html", {
								lock: true,
								coatl_id: coatl_id,
								prophecy: parseInt(this.assembleParams.element)
							}, function (is_error) {
							});
							break;
						case "temple_card":
							this.ajaxcall("/coatl/coatl/actAddTemple.html", {
								lock: true,
								coatl_id: coatl_id,
								temple: parseInt(this.assembleParams.element)
							}, function (is_error) {
							});
							break;
					}
					this.setAssembleParamsNull();
				} else {
					this.assembleParams.type = "card";
					this.setAssembleParamsAction(`add_card`, `add_card_${coatl_id}`, {"coatl_id": coatl_id});
					if (this.assembleParams.action != null) {
						if (this.stateArgs["possibleActions"]["prophecy"] !== undefined) {
							const allProphecy = this.prophecyHand.getAllItems();
							for (const i in allProphecy) {
								dojo.addClass(`hand_prophecy_item_${allProphecy[i].id}`, "stockitem_unselectable");
							}
							if (this.stateArgs["possibleActions"]["prophecy"][coatl_id] !== undefined) {
								for (const card_id in this.stateArgs["possibleActions"]["prophecy"][coatl_id]) {
									dojo.removeClass(`hand_prophecy_item_${card_id}`, "stockitem_unselectable");
								}
							}
						}
						if (this.stateArgs["possibleActions"]["temple"] !== undefined) {
							const allTempleSupply = this.templeSupply.getAllItems();
							const allTempleHand = this.templeHand.getAllItems();
							for (const i in allTempleSupply) {
								dojo.addClass(`supply_temple_item_${allTempleSupply[i].id}`, "stockitem_unselectable");
							}
							for (const i in allTempleHand) {
								dojo.addClass(`hand_temple_item_${allTempleHand[i].id}`, "stockitem_unselectable");
							}
							if (this.stateArgs["possibleActions"]["temple"][coatl_id] !== undefined) {
								if (this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"] !== undefined) {
									for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["supply"]) {
										dojo.removeClass(`supply_temple_item_${card_id}`, "stockitem_unselectable");
									}
								}
								if (this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"] !== undefined) {
									for (const card_id in this.stateArgs["possibleActions"]["temple"][coatl_id]["hand"]) {
										dojo.removeClass(`hand_temple_item_${card_id}`, "stockitem_unselectable");
									}
								}
							}
						}
					} else {
						if (this.stateArgs["possibleActions"]["prophecy"] !== undefined) {
							for (const coatl_id_i in this.stateArgs["possibleActions"]["prophecy"]) {
								for (const card_id_i in this.stateArgs["possibleActions"]["prophecy"][coatl_id_i]) {
									dojo.removeClass(`hand_prophecy_item_${card_id_i}`, "stockitem_unselectable")
								}
							}
						}
						if (this.stateArgs["possibleActions"]["temple"] !== undefined) {
							for (const coatl_id_i in this.stateArgs["possibleActions"]["temple"]) {
								if (this.stateArgs["possibleActions"]["temple"][coatl_id_i]["supply"] !== undefined) {
									for (const card_id_i in this.stateArgs["possibleActions"]["temple"][coatl_id_i]["supply"]) {
										dojo.removeClass(`supply_temple_item_${card_id_i}`, "stockitem_unselectable")
									}
								}
								if (this.stateArgs["possibleActions"]["temple"][coatl_id_i]["hand"] !== undefined) {
									for (const card_id_i in this.stateArgs["possibleActions"]["temple"][coatl_id_i]["hand"]) {
										dojo.removeClass(`hand_temple_item_${card_id_i}`, "stockitem_unselectable")
									}
								}
							}
						}
					}
				}
			},

			/**
			 * onCellClick:
			 * Handler function to precess player board cell click
			 * @param evt
			 */
			onCellClick: function (evt) {
				if (this.checkAction('newCoatl', true) || this.checkAction('addPiece', true)) {
					if (this.assembleParams.type !== "piece") {
						this.setAssembleParamsNull();
						this.prophecyHand.unselectAll();
					}
					let target = evt.currentTarget.id;
					if (dojo.getAttr(target, "active") === "true") {
						const split = evt.currentTarget.id.split('_');
						const player_id = split[1];
						const cell_id = split[2];
						let cell_type = dojo.getAttr(`cellSelector_${player_id}_${cell_id}`, "type");
						dojo.query(".button_container a").forEach(e => {
							e.classList.remove("disabled");
						})
						if (this.assembleParams.action != null) {
							switch (this.assembleParams.action) {
								case "new_coatl":
									this.ajaxcall("/coatl/coatl/actNewCoatl.html", {
										lock: true,
										piece: cell_id
									}, function (is_error) {
									});
									break;
								case "add_piece":
									this.ajaxcall("/coatl/coatl/actAddPiece.html", {
										lock: true,
										coatl_id: this.assembleParams.action_args.coatl_id,
										to_left: (this.assembleParams.action_args.side === "left"),
										piece: cell_id
									}, function (is_error) {
									});
							}
							this.setAssembleParamsNull();
						} else if (this.assembleParams.element === parseInt(cell_id)) {
							this.assembleParams.type = null;
							this.assembleParams.element = null;
							dojo.query(`selector_grid cell piece_container piece[shadow="orange"]`)
								.forEach(function (node) {
									dojo.setAttr(node, "shadow", "white");
								});
						} else if (this.assembleParams.element == null) {
							this.assembleParams.type = "piece";
							this.assembleParams.element = parseInt(cell_id);
							dojo.query(`selector_grid cell piece_container piece[selector="cell_${player_id}_${cell_id}"]`)
								.forEach(function (node) {
									dojo.setAttr(node, "shadow", "orange");
								});
							if (cell_type === "head") {
								dojo.query(".button_container_right a").forEach(e => {
									e.classList.add("disabled");
								})
							} else if (cell_type === "tail") {
								dojo.query(".button_container_left a").forEach(e => {
									e.classList.add("disabled");
								})
							}
						} else {
							dojo.query(`selector_grid cell piece_container piece[selector="cell_${player_id}_${this.assembleParams.element}"]`)
								.forEach(function (node) {
									dojo.setAttr(node, "shadow", "none");
								});
							this.assembleParams.type = "piece";
							this.assembleParams.element = parseInt(cell_id);
							dojo.query(`selector_grid cell piece_container piece[selector="cell_${player_id}_${cell_id}"]`)
								.forEach(function (node) {
									dojo.setAttr(node, "shadow", "orange");
								});
							if (cell_type === "head") {
								dojo.query(".button_container_right a").forEach(e => {
									e.classList.add("disabled");
								})
							} else if (cell_type === "tail") {
								dojo.query(".button_container_left a").forEach(e => {
									e.classList.add("disabled");
								})
							}
						}
					}
				} else if (this.checkAction('confirmSteal', true)) {
					let split = evt.currentTarget.id.split('_');
					const player_id = split[1];
					const cell_id = split[2];
					if (this.stealParams.player_id === null || this.stealParams.player_id !== player_id) {
						this.setStealParamsNull();
						this.stealParams.player_id = player_id;
					}
					if (this.stealParams.cell_ids.includes(cell_id)) {
						const remIndex = this.stealParams.cell_ids.indexOf(cell_id);
						this.stealParams.cell_ids.splice(remIndex, 1);
						document.querySelector(`other_selector_grid cell piece_container piece[selector="cell_${player_id}_${cell_id}"]`).setAttribute("shadow", "none");
						if (this.stealParams.cell_ids.length === 0) dojo.addClass('steal_pieces_button', 'disabled');
						else document.getElementById("steal_pieces_button").innerHTML = _("Steal selected piece");
					} else {
						if (this.stealParams.cell_ids.length === 2 || (this.stateArgs["freeSpaces"] == 1 && this.stealParams.cell_ids.length === 1)) {
							const rem = this.stealParams.cell_ids.shift();
							document.querySelector(`other_selector_grid cell piece_container piece[selector="cell_${player_id}_${rem}"]`).setAttribute("shadow", "none");
						}
						this.stealParams.cell_ids.push(cell_id);
						dojo.removeClass('steal_pieces_button', 'disabled');
						document.querySelector(`other_selector_grid cell piece_container piece[selector="cell_${player_id}_${cell_id}"]`).setAttribute("shadow", "orange");
						if (this.stealParams.cell_ids.length === 2) document.getElementById("steal_pieces_button").innerHTML = _("Steal selected pieces");
					}
				}
			},

			setStealParamsNull() {
				for (const i in this.stealParams.cell_ids) {
					document.querySelector(`other_selector_grid cell piece_container piece[selector="cell_${this.stealParams.player_id}_${this.stealParams.cell_ids[i]}"]`).setAttribute("shadow", "none");
				}
				this.stealParams.player_id = null;
				this.stealParams.cell_ids = [];
				dojo.addClass('steal_pieces_button', 'disabled');
				document.getElementById("steal_pieces_button").innerHTML = _("Steal selected piece");
			},

			/**
			 * onConfirmAssemble:
			 * Handler function to confirm Cóatl assemble
			 * @param evt
			 */
			onConfirmAssemble: function (evt) {
				if (this.checkAction('confirmAssemble')) {
					this.ajaxcall("/coatl/coatl/actConfirmAssemble.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			/**
			 * onDiscardProphecySacrificeToken:
			 * Handler function to discard any prophecy cards
			 * @param evt
			 */
			onDiscardProphecySacrificeToken: function (evt) {
				if (this.isCurrentPlayerActive() && this.checkAction('discardProphecySacrificeToken')) {
					const selected_prophecies = this.prophecyHand.getSelectedItems();
					var prophecy_cards = '';
					for (let i in selected_prophecies) {
						prophecy_cards += selected_prophecies[i].id + ' ';
					}
					this.ajaxcall("/coatl/coatl/actDiscardProphecySacrificeToken.html", {
						lock: true,
						prophecy_cards: prophecy_cards,
					}, function (is_error) {
					});
				}

			},

			onConfirmSteal: function (evt) {
				if (this.checkAction('confirmSteal')) {
					let cells = '';
					for (let i in this.stealParams.cell_ids) {
						cells += this.stealParams.cell_ids[i] + ' ';
					}
					this.ajaxcall("/coatl/coatl/actConfirmSteal.html", {
						lock: true,
						opponent: this.stealParams.player_id,
						cell_ids: cells
					}, function (is_error) {
					});
					this.setStealParamsNull();
				}
			},


			//// Undo actions

			/**
			 * onUndo:
			 * Handler function to undo turn
			 * @param evt
			 */
			onUndo: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('undo')) {
					this.setAssembleParamsNull();
					this.prophecyHand.unselectAll();
					this.ajaxcall("/coatl/coatl/actUndo.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			/**
			 * onRestart:
			 * Handler function to undo turn
			 * @param evt
			 */
			onRestart: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('restart')) {
					this.setAssembleParamsNull();
					this.prophecyHand.unselectAll();
					this.ajaxcall("/coatl/coatl/actRestart.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			/**
			 * onCancel:
			 * Handler function to undo turn
			 * @param evt
			 */
			onCancel: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('cancel')) {
					this.setAssembleParamsNull();
					this.prophecyHand.unselectAll();
					this.ajaxcall("/coatl/coatl/actCancel.html", {
						lock: true
					}, function (is_error) {
					});
				}
			},

			/**
			 * oGiveUp:
			 * Handler function to give up the game
			 * @param evt
			 */
			onGiveUp: function (evt) {
				dojo.stopEvent(evt);
				if (this.checkAction('giveUp')) {
					this.confirmationDialog(_("It has the chance that you are unable to perform a valid action, therefore, you can give up the game.</br>Please only use this if you REALLY can't do anything!</br>Your score will be set to -1 and you will be removed from the game!"), () => {
						this.ajaxcall("/coatl/coatl/actGiveUp.html", {
							lock: true
						}, function (is_error) {
						});
					});
				}
			},

			///////////////////////////////////////////////////
			//// Reaction to cometD notifications

			/**
			 * setupNotifications:
			 *
			 * Part of the BGA framework.
			 * In this method, you associate each of your game notifications with your local method to handle it.
			 * Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in your coatl.game.php file.
			 */
			setupNotifications: function () {
				dojo.subscribe('discardCards', this, "notif_discardCards");
				dojo.subscribe('discardCards_private', this, "notif_discardCards_private");
				dojo.subscribe('takePiece', this, "notif_takePiece");
				dojo.subscribe('undoTakePiece', this, "notif_undoTakePiece");
				dojo.subscribe('replenishPieceSupply', this, "notif_replenishPieceSupply");
				dojo.subscribe('takeProphecy', this, "notif_takeProphecy");
				dojo.subscribe('takeProphecy_private', this, "notif_takeProphecy_private");
				dojo.subscribe('undoTakeProphecy', this, "notif_undoTakeProphecy");
				dojo.subscribe('refillProphecySupply', this, "notif_refillProphecySupply");
				dojo.subscribe('refillTempleSupply', this, "notif_refillTempleSupply");
				dojo.subscribe('discardProphecySupply', this, "notif_discardProphecySupply");
				dojo.subscribe('newCoatl', this, "notif_newCoatl");
				dojo.subscribe('undoNewCoatl', this, "notif_undoNewCoatl");
				dojo.subscribe('addPiece', this, "notif_addPiece");
				dojo.subscribe('undoAddPiece', this, "notif_undoAddPiece");
				dojo.subscribe('addProphecy', this, "notif_addProphecy");
				dojo.subscribe('undoAddProphecy', this, "notif_undoAddProphecy");
				dojo.subscribe('addTemple', this, "notif_addTemple");
				dojo.subscribe('undoAddTemple', this, "notif_undoAddTemple");
				dojo.subscribe('usePieceSacrifice', this, "notif_usePieceSacrifice");
				dojo.subscribe('useProphecySacrifice', this, "notif_useProphecySacrifice");
				dojo.subscribe('takeTemple', this, "notif_takeTemple");
				dojo.subscribe('finalRound', this, "notif_finalRound");
				dojo.subscribe('score_coatl', this, "notif_score_coatl");
				dojo.subscribe('score_gold', this, "notif_score_gold");
				dojo.subscribe('give_up', this, "notif_give_up");
				dojo.subscribe('steal_pieces', this, "notif_steal_pieces");
				dojo.subscribe('steal_piece', this, "notif_steal_piece");
			},

			notif_discardCards: function (notif) {
				document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML) - notif.args.n;
			},

			notif_discardCards_private: function (notif) {
				for (let i in notif.args.prophecy_ids) {
					this.prophecyHand.removeFromStockById(notif.args.prophecy_ids[i]);
				}
				this.prophecyHand.setSelectionMode(0);
				this.prophecyHand.selectionClass = "";
			},

			notif_takePiece: function (notif) {
				dojo.setAttr(`selector_${notif.args.piece_type}_${notif.args.supply_location}`, "active", "false");
				for (var i = 0; i < notif.args.piece_ids.length; i++) {
					this.addPieceToPlayerBoard(
						notif.args.piece_ids[i],
						notif.args.piece_type,
						notif.args.player_id,
						notif.args.player_board_locations[i],
						notif.args.colors[i]);
				}
			},

			notif_undoTakePiece: function (notif) {
				this.addPieceToSupplyBoard(
					notif.args.piece_ids[0],
					notif.args.piece_type,
					null,
					notif.args.supply_location,
					true);
				if (notif.args.piece_type === "body" && notif.args.piece_ids.length === 2) {
					this.addPieceToSupplyBoard(
						notif.args.piece_ids[1],
						notif.args.piece_type,
						null,
						notif.args.supply_location * (-1),
						true);
				}
				for (const i in notif.args.player_board_locations) {
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "active", "false");
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "type", "null");
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "color", "null");
				}
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_replenishPieceSupply: function (notif) {
				this.replenishPieceSupplyBoard(notif.args.pieces_on_supply, true);
				this.bagCountersToValue(notif.args.piece_counts.head, notif.args.piece_counts.body, notif.args.piece_counts.tail);
			},

			notif_takeProphecy: function (notif) {
				if (this.player_id !== parseInt(notif.args.player_id) && notif.args.fromDeck) {
					this.slideTemporaryObject("<deck_prophecy id=deck_prophecy_temp></deck_prophecy>", "leftright_page_wrapper", "deck_prophecy", 'overall_player_board_' + notif.args.player_id);
				} else {
					if (this.isCurrentPlayerActive()) {
						this.prophecySupply.removeFromStockById(notif.args.card_id, 'hand_prophecy', true);
						this.prophecyHand.addToStockWithId(notif.args.card_type, notif.args.card_id);
					} else {
						this.prophecySupply.removeFromStockById(notif.args.card_id, 'overall_player_board_' + notif.args.player_id, true);
					}
				}
				document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML) + 1;
			},

			notif_takeProphecy_private: function (notif) {
				this.prophecyHand.addToStockWithId(notif.args.card_type, notif.args.card_id, "deck_prophecy");
			},

			notif_undoTakeProphecy: function (notif) {
				if (this.isCurrentPlayerActive()) {
					dojo.setStyle("supply_prophecy", "z-index", 20);
					this.prophecyHand.removeFromStockById(notif.args.card_id, 'supply_prophecy');
					this.prophecySupply.addToStockWithId(notif.args.card_type, notif.args.card_id);
					setTimeout(function () {
						dojo.setStyle("supply_prophecy", "z-index", "unset");
					}, 800);
				} else {
					this.prophecySupply.addToStockWithId(notif.args.card_type, notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
				}
				document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML) - 1;
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_refillProphecySupply: function (notif) {
				for (let i in notif.args.new_cards) {
					const new_card = notif.args.new_cards[i];
					this.prophecySupply.addToStockWithId(new_card.type_arg, new_card.id, 'deck_prophecy');
				}
			},

			notif_refillTempleSupply: function (notif) {
				for (let i in notif.args.new_cards) {
					const new_card = notif.args.new_cards[i];
					this.templeSupply.addToStockWithId(new_card.type, new_card.id);
				}
			},

			notif_discardProphecySupply: function (notif) {
				this.prophecySupply.removeAllTo('deck_prophecy');
			},

			notif_newCoatl: function (notif) {
				this.newCoatl(notif.args.coatl_id, notif.args.player_id);
				this.addPieceToCoatl(notif.args.coatl_id, notif.args.player_id, notif.args.piece_id, notif.args.piece_type, null, 150, false);
			},

			notif_undoNewCoatl: function (notif) {
				this.addPieceToPlayerBoard(notif.args.piece_id, notif.args.piece_type, notif.args.player_id, notif.args.piece_location_arg, null);
				dojo.destroy(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_coatl_overall`);
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_addPiece: function (notif) {
				this.addPieceToCoatl(notif.args.coatl_id, notif.args.player_id, notif.args.piece_id, notif.args.piece_type, null, notif.args.box_id, notif.args.to_left);
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
				for (const card_id in notif.args.coatl_score.prophecy_levels) {
					document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_cards_item_${card_id}`).setAttribute("level", notif.args.coatl_score.prophecy_levels[card_id]);
				}
			},

			notif_undoAddPiece: function (notif) {
				this.addPieceToPlayerBoard(notif.args.piece_id, notif.args.piece_type, notif.args.player_id, notif.args.piece_location_arg, null);
				dojo.destroy(`box_${notif.args.player_id}_${notif.args.coatl_id}_${notif.args.box_id}`);
				if (this.isCurrentPlayerActive()) {
					if (notif.args.piece_type === "head")
						this.addActionButton(`add_piece_${notif.args.coatl_id}_left_button`, _('+'), () => this.onAddPieceToCoatl(notif.args.coatl_id, 'left'), `coatl_${notif.args.player_id}_${notif.args.coatl_id}_button_container_left`);
					else if (notif.args.piece_type === "tail")
						this.addActionButton(`add_piece_${notif.args.coatl_id}_right_button`, _('+'), () => this.onAddPieceToCoatl(notif.args.coatl_id, 'right'), `coatl_${notif.args.player_id}_${notif.args.coatl_id}_button_container_right`);
				}
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
				this.coatlLengthCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].incValue(-1);
				for (const card_id in notif.args.coatl_score.prophecy_levels) {
					document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_cards_item_${card_id}`).setAttribute("level", notif.args.coatl_score.prophecy_levels[card_id]);
				}
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_addProphecy: function (notif) {
				if (this.isCurrentPlayerActive()) {
					this.prophecyHand.removeFromStockById(notif.args.card_id, `coatl_${notif.args.player_id}_${notif.args.coatl_id}_cards`);
					this.coatlProphecyStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].addToStockWithId(parseInt(notif.args.prophecy_type), notif.args.card_id);
				} else {
					this.coatlProphecyStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].addToStockWithId(parseInt(notif.args.prophecy_type), notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
				}
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
				document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_cards_item_${notif.args.card_id}`).setAttribute("level", notif.args.coatl_score.prophecy_levels[notif.args.card_id]);
				document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML) - 1;
			},

			notif_undoAddProphecy: function (notif) {
				if (this.isCurrentPlayerActive()) {
					this.coatlProphecyStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].removeFromStockById(notif.args.card_id, `hand_prophecy`);
					this.prophecyHand.addToStockWithId(parseInt(notif.args.prophecy_type), notif.args.card_id);
				} else {
					this.coatlProphecyStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].removeFromStockById(notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
				}
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
				document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_prophecy').innerHTML) + 1;
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_addTemple: function (notif) {
				if (notif.args.temple_location === "supply") {
					this.templeSupply.removeFromStockById(notif.args.card_id, `coatl_${notif.args.player_id}_${notif.args.coatl_id}_temple_cards`);
					this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].addToStockWithId(parseInt(notif.args.temple_type), notif.args.card_id);
				} else {
					if (this.isCurrentPlayerActive()) {
						document.getElementById("hand").setAttribute("temples", parseInt(document.getElementById("hand").getAttribute("temples")) - 1);
						this.prophecyHand.updateDisplay();
						this.templeHand.removeFromStockById(notif.args.card_id, `coatl_${notif.args.player_id}_${notif.args.coatl_id}_temple_cards`);
						this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].addToStockWithId(parseInt(notif.args.temple_type), notif.args.card_id);
					} else {
						this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].addToStockWithId(parseInt(notif.args.temple_type), notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
					}
					document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML) - 1;
				}
				document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_temple_cards`).setAttribute("active", "true");
				document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_temple_cards_item_${notif.args.card_id}`).setAttribute("level", notif.args.coatl_score.temple_level);
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
			},

			notif_undoAddTemple: function (notif) {
				if (notif.args.temple_location === "supply") {
					this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].removeFromStockById(notif.args.card_id, `supply_temple`);
					this.templeSupply.addToStockWithId(parseInt(notif.args.temple_type), notif.args.card_id);
				} else {
					if (this.isCurrentPlayerActive()) {
						document.getElementById("hand").setAttribute("temples", parseInt(document.getElementById("hand").getAttribute("temples")) + 1);
						this.prophecyHand.updateDisplay();
						this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].removeFromStockById(notif.args.card_id, `hand_temple`);
						this.templeHand.addToStockWithId(parseInt(notif.args.temple_type), notif.args.card_id);
					} else {
						this.coatlTempleStocks[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].removeFromStockById(notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
					}
					document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML) + 1;
				}
				document.getElementById(`coatl_${notif.args.player_id}_${notif.args.coatl_id}_temple_cards`).setAttribute("active", "false");
				this.coatlScoreCounters[`coatl_${notif.args.player_id}_${notif.args.coatl_id}`].toValue(parseInt(notif.args.coatl_score.score));
				this.cancelLogs([notif.args.moveIds]);
			},

			notif_usePieceSacrifice: function (notif) {
				this.getPieceToken(this.getActivePlayerId()).setAttribute("display", "none");
				this.sacrificeTokens[notif.args.player_id]["piece"] = null;
				for (var i = 0; i < notif.args.piece_ids.length; i++) {
					const element_id = `piece_${notif.args.piece_type}_${notif.args.piece_ids[i]}`
					if (!dojo.exists(element_id)) {
						let piece = dojo.place(this.format_block(`jstpl_piece`, {
							id: element_id,
							type: notif.args.piece_type,
							color: notif.args.piece_colors[i],
							selector: `player_board_${notif.args.player_board_locations[i]}`,
							rotate: `player_board`
						}), `cell_${notif.args.player_id}_${notif.args.player_board_locations[i]}`);
						this.placeOnObject(piece, `bag_${notif.args.piece_type}`);
						this.slideToObject(element_id, `cell_${notif.args.player_id}_${notif.args.player_board_locations[i]}`).play();
					}
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "active", "true");
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "type", notif.args.piece_type);
					dojo.setAttr(`cellSelector_${notif.args.player_id}_${notif.args.player_board_locations[i]}`, "color", notif.args.piece_colors[i]);
				}
			},

			notif_useProphecySacrifice: function (notif) {
				this.getProphecyToken(this.getActivePlayerId()).setAttribute("display", "none");
				this.sacrificeTokens[notif.args.player_id]["prophecy"] = null;
			},

			notif_takeTemple: function (notif) {
				if (this.isCurrentPlayerActive()) {
					document.getElementById("hand").setAttribute("temples", parseInt(document.getElementById("hand").getAttribute("temples")) + 1);
					this.prophecyHand.updateDisplay();
					this.templeSupply.removeFromStockById(notif.args.card_id, 'hand_temple', true);
					this.templeHand.addToStockWithId(notif.args.card_type, notif.args.card_id);
				} else {
					this.templeSupply.removeFromStockById(notif.args.card_id, 'overall_player_board_' + notif.args.player_id);
				}
				document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML = parseInt(document.getElementById('counter_' + notif.args.player_id + '_temple').innerHTML) + 1;
				this.sacrificeTokens[notif.args.player_id]["temple"] = null;
			},

			notif_finalRound: function (notif) {
				this.finalTurns = true;
				document.querySelector("#page-title").innerHTML += "<div id='help-bar'></div>"
				document.querySelector("#help-bar").innerHTML = _("These are the game's final turns!");
			},

			notif_score_coatl: function (notif) {
				this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);
				if (notif.args.activate !== null) {
					if (notif.args.activate) {
						document.querySelector(`#coatl_${notif.args.player_id}_${notif.args.coatl_id}_score_token`).setAttribute("active", "true");
					} else {
						document.querySelector(`#coatl_${notif.args.player_id}_${notif.args.coatl_id}_score_token`).setAttribute("active", "false");
					}
				}
			},

			notif_steal_pieces: function (notif) {
				this.slideToObjectAndDestroy(document.getElementById(`piece_body_${notif.args.gold_piece_id}`), `overall_player_board_${notif.args.opponent_id}`);
				const cell_1 = document.getElementById(`piece_body_${notif.args.piece_1_id}`).getAttribute("selector").split("_")[2];
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "active", "false");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "type", "null");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "color", "null");
				this.addPieceToPlayerBoard(notif.args.piece_1_id, "body", notif.args.player_id, notif.args.space_1, notif.args.piece_1_color)
				const cell_2 = document.getElementById(`piece_body_${notif.args.piece_2_id}`).getAttribute("selector").split("_")[2];
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_2}`, "active", "false");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_2}`, "type", "null");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_2}`, "color", "null");
				this.addPieceToPlayerBoard(notif.args.piece_2_id, "body", notif.args.player_id, notif.args.space_2, notif.args.piece_2_color)
			},

			notif_steal_piece: function (notif) {
				this.slideToObjectAndDestroy(document.getElementById(`piece_body_${notif.args.gold_piece_id}`), `overall_player_board_${notif.args.opponent_id}`);
				const cell_1 = document.getElementById(`piece_body_${notif.args.piece_1_id}`).getAttribute("selector").split("_")[2];
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "active", "false");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "type", "null");
				dojo.setAttr(`cellSelector_${notif.args.opponent_id}_${cell_1}`, "color", "null");
				this.addPieceToPlayerBoard(notif.args.piece_1_id, "body", notif.args.player_id, notif.args.space_1, notif.args.piece_1_color)
			},

			notif_score_gold: function (notif) {
				this.scoreCtrl[notif.args.player_id].toValue(notif.args.player_score);
				let gold_str = '';
				for (let i = 1; i <= notif.args.gold_count; i++) {
					gold_str += `<gold_icon display="block"></gold_icon>`;
				}
				document.getElementById(`gold_icons_${notif.args.player_id}`).innerHTML = gold_str;
			},

			notif_give_up: function (notif) {
				this.scoreCtrl[notif.args.player_id].toValue(-1);
			},

			cancelLogs: function (moveIds) {
				if (Array.isArray(moveIds)) {
					var elements = [];
					// Desktop logs
					for (var logId in this.log_to_move_id) {
						var moveId = +this.log_to_move_id[logId];
						if (moveIds.includes(moveId)) {
							elements.push($('log_' + logId));
						}
					}
					// Mobile logs
					for (var logId in dockedlog_to_move_id) {
						var moveId = +dockedlog_to_move_id[logId];
						if (moveIds.includes(moveId)) {
							elements.push($('dockedlog_' + logId));
						}
					}
					// Add strikethrough
					elements.forEach(function (e) {
						if (e != null) {
							dojo.addClass(e, 'cancel');
						}
					});
				}
			},

			///////////////////////////////////////////////////
			//// Tooltips

			/**
			 * Returns a tooltip string for the Prophecy card type
			 * @param card_type
			 * @returns {string}
			 */
			prophecy_tooltip: function (card_type) {
				let x = "<span style='color: darkred'>X</span>";
				return `<div>` +
					`<h3>${_("Prophecy card")}</h3>` +
					`<h5>${_("The primary method of scoring points is by fulfilling Prophecy cards")}.</h5>` +
					`<h4>${_("Symbols")}</h4>` +
					`<div style="clear: left""><span class="prophecy_tooltip_piece" style="float: left"></span><h5 style="height: auto">${_("A Cóatl piece (head, tail, or body segment) of the depicted color")}.</h5></div>` +
					((5 <= card_type && card_type <= 9) ? `<div style="clear: left"><span class="prophecy_tooltip_x" style="float: left"></span><h5 style="height: auto">${dojo.string.substitute(_("The sequence is only valid if the piece depicted in the red ${x} symbol is not present. In other words, the piece in this position is either of a different color or there is no piece in this position"), {x: x,})}</h5></div>` : '') +
					((35 <= card_type && card_type <= 48) ? `<div style="clear: left"><span class="prophecy_tooltip_infinity" style="float: left"></span><h5 style="height: auto">${_("One or more pieces of the depicted color. If the symbol is white, the piece(s) can be of any colors")}.</h5></div>` : '') +
					((card_type >= 49) ? `<div style="clear: left"><h5 style="height: auto">${_("The pieces can be placed in any order and do not need to be adjacent")}.</h5></div>` : '') +
					`<h4 style="clear: left;">${_("Important")}</h4>` +
					`<h5 style="clear: left;">${_("The body segments depicted in a card’s requirement can be satisfied by any type of piece, including a head or a tail")}.</h5>` +
					`<h4 style="clear: left;">${_("Scoring")}</h4>` +
					(((22 <= card_type && card_type <= 34) || (44 <= card_type && card_type <= 54)) ?
						`<h5 style="clear: left;">${_("Score the indicated number of points if you satisfied the Cóatl card’s requirements at least once")}.</h5>` :
						`<h5 style="clear: left;">${_("Score the number of points corresponding to the number of times the Cóatl satisfies the card’s requirements, up to the listed maximum")}<br><i>${_("Note: You can score only one requirement level")}</i></h5>`) +
					`</div>`;
			},

			/**
			 * Returns a tooltip string for the Temple card type
			 * @param card_type
			 * @returns {string}
			 */
			temple_tooltip: function (card_type) {
				let str = `<div>` +
					`<h3>${_("Temple card")}</h3>` +
					`<h5>${_("When you complete a Cóatl, you can fulfill one of the personal or common Temple cards, if able")}.</h5>` +
					`<h4>${_("Requirements")}</h4>`;
				if (card_type >= 15) {
					if (card_type === 15) color = _("blue");
					else if (card_type === 16) color = _("red");
					else if (card_type === 17) color = _("black");
					else if (card_type === 18) color = _("green");
					else color = _("yellow");
					str += `<div style="clear: left; margin-bottom: 5px"><span class="temple_tooltip_prophecy" style="float: left"></span><h5 style="height: auto">• ${dojo.string.substitute(_("The Cóatl has no ${color} Prophecy cards"), {color: color})}.</h5></div>`
				}
				if (card_type <= 4 || (10 <= card_type && card_type <= 14) || card_type === 19) {
					if (card_type === 0 || card_type === 14) color = _("Yellow");
					else if (card_type === 4 || card_type === 13) color = _("Red");
					else if (card_type === 1 || card_type === 10) color = _("Black");
					else if (card_type === 2 || card_type === 12 || card_type === 19) color = _("Green");
					else color = _("Blue");
					str += `<div style="clear: left; margin-bottom: 5px"><span class="temple_tooltip_color" style="float: left"></span><h5 style="height: auto">• ${dojo.string.substitute(_("${color} piece cannot be present"), {color: color})}.</h5></div>`
				}
				if (5 <= card_type && card_type <= 16) {
					let colors;
					switch (card_type) {
						case (5):
							colors = [_("red"), _("yellow")];
							break;
						case (6):
							colors = [_("yellow"), _("green")];
							break;
						case (7):
							colors = [_("blue"), _("black")];
							break;
						case (8):
							colors = [_("red"), _("green")];
							break;
						case (9):
							colors = [_("blue"), _("yellow")];
							break;
						case (10):
							colors = [_("blue"), _("red")];
							break;
						case (11):
							colors = [_("black"), _("red")];
							break;
						case (12):
							colors = [_("black"), _("yellow")];
							break;
						case (13):
							colors = [_("blue"), _("green")];
							break;
						case (14):
							colors = [_("black"), _("green")];
							break;
						case (15):
							colors = [_("red"), _("blue")];
							break;
						default:
							colors = [_("yellow"), _("black")];
							break;
					}
					str += `<div style="clear: left; margin-bottom: 5px"><span class="temple_tooltip_equal" style="float: left"></span><h5 style="height: auto">• ${
						dojo.string.substitute(_("The number of ${color_1} pieces must be equal to the number of ${color_2} pieces (minimum 1 piece)"), {
							color_1: colors[0],
							color_2: colors[1]
						})}.</h5></div>`;
				}
				if (card_type <= 9 || card_type === 15 || card_type === 17 || card_type === 18) {
					let length;
					if (card_type <= 5) length = parseInt(card_type) + 7;
					else if (card_type === 6 || card_type === 8) length = 6;
					else if (card_type === 7) length = 9;
					else if (card_type === 15) length = 10;
					else if (card_type === 17) length = 11;
					else if (card_type === 18) length = 8;
					else length = 12;
					str += `<div style="clear: left; margin-bottom: 5px"><span class="temple_tooltip_length" style="float: left"></span><h5 style="height: auto">• ${dojo.string.substitute(_("The Cóatl must consist of exactly ${length} pieces (including its head and tail)"), {length: length})}.</h5></div>`;
				}
				str += `<h4>${_("Important")}</h4>` +
					`<h5>${_("The body segments depicted in a card’s requirement can be satisfied by any type of piece, including a head or a tail")}.</h5>` +
					`<h4 style="clear: left;">${_("Scoring")}</h4>` +
					`<h5 style="clear: left;">${_("Score points according to how many of the card’s requirements the Cóatl satisfies")}.</h5>`;
				return str;
			},

			/**
			 * Returns a tooltip string for the score token
			 * @returns {string}
			 */
			score_token_tooltip: function () {
				return `<div>` +
					`<h3>${_("Score token")}</h3>` +
					`<h5>${_("You get this score when you complete this Cóatl with the current Prophecy cards")}.</h5>` +
					`<h5>${_("You can increase this number by fulfilling more cards and requirement levels, while the Cóatl is not incomplete")}.</h5>` +
					`<h4>${_("Warning")}</h4>` +
					`<h5>${_("You get zero points for this Cóatl if it is incomplete at the end of the game!")}</h5>` +
					`<h4>${_("Remember")}</h4>` +
					`<h5>• ${_("To complete a Cóatl, it must have 1 Head, 1 Tail, and at least 1 Body part")}.</h5>` +
					`<h5>• ${_("You need 1 to 4 Prophecy cards to complete a Cóatl")}.</h5>` +
					`<h5>• ${_("A Cóatl cannot be used to fulfill identical Prophecy cards")}.</h5>` +
					`</div>`;
			},

			/**
			 * Returns a tooltip string for the Sacrifice token type
			 * @param type
			 * @returns {string}
			 */
			sacrifice_token_tooltip: function (type) {
				let type_tooltip = "";
				switch (type) {
					case "piece":
						type_tooltip = `<h4>${_("Perfect Pick")}</h4>` +
							`<h5>${_("Draw 1 head, 1 tail, or 2 body segments of your choice from the corresponding bag, then refill all empty spaces of the Supply board")}.</h5>`;
						break;
					case "prophecy":
						type_tooltip = `<h4>${_("See the Future")}</h4>` +
							`<h5>${_("Discard all Prophecy cards in the faceup supply, then refill it. Discard any number of cards from your hand, then perform a ‘Choose Prophecy cards’ action")}.</h5>`;
						break;
					case "temple":
						type_tooltip = `<h4>${_("Priest Commitment")}</h4>` +
							`<h5>${_("Take one of the faceup Temple cards and add it to your hand. When completing a Cóatl, you can fulfill this personal Temple card instead of a common Temple card")}.</h5>`;
						break;
				}
				return `<div>` +
					`<h3>${_("Sacrifice Token")}</h3>` +
					`<h5>${_("Each player begins the game with 3 Sacrifice tokens. Instead of playing a standard action, you may discard a Sacrifice token to perform its action")}.</h5>` +
					type_tooltip +
					`</div>`;
			},

			/* @Override */
			format_string_recursive: function format_string_recursive(log, args) {
				try {
					if (log && args && !args.processed) {
						args.processed = true;
						const keys = ['piece', 'piece_1', 'piece_2', 'prophecy', 'temple', 'perfect_pick', 'see_the_future', 'priest_commitment', 'opponent_name'];
						for (const i in keys) {
							if (keys[i] in args) {
								if (keys[i] === "piece" || keys[i] === "piece_1" || keys[i] === "piece_2") {
									let j = args[keys[i]].replace("[", "").replace("]", "").split(" ");
									args[keys[i]] = args[keys[i]] = `<piece_log_icon type="${j[1]}" color="${[j[0]]}"></piece_log_icon>`;
								} else if (keys[i] === "prophecy") {
									let j = args[keys[i]].replace("[", "").replace("]", "");
									args[keys[i]] = `<prophecy_log_icon type="${j}"></prophecy_log_icon>`;
								} else if (keys[i] === "temple") {
									let j = args[keys[i]].replace("[", "").replace("]", "");
									args[keys[i]] = `<temple_log_icon type="${j}"></temple_log_icon>`;
								} else if (keys[i] === "perfect_pick") args[keys[i]] = `<perfect_pick_log_icon></perfect_pick_log_icon>`;
								else if (keys[i] === "see_the_future") args[keys[i]] = `<see_the_future_log_icon></see_the_future_log_icon>`;
								else if (keys[i] === "priest_commitment") args[keys[i]] = `<priest_commitment_log_icon></priest_commitment_log_icon>`;
								else if (keys[i] === "opponent_name") args[keys[i]] = `<span class="playername" style="color:#${args[keys[i]].split('_')[1]};">${args[keys[i]].split('_')[0]}</span>`;
							}
						}
					}
				} catch (e) {
					console.error(log, args, "Exception thrown", e.stack);
				}
				return this.inherited({callee: format_string_recursive}, arguments);
			},
		});
	});