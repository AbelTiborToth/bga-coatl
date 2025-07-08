var __spreadArray = (this && this.__spreadArray) || function (to, from, pack) {
	if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
		if (ar || !(i in from)) {
			if (!ar) ar = Array.prototype.slice.call(from, 0, i);
			ar[i] = from[i];
		}
	}
	return to.concat(ar || Array.prototype.slice.call(from));
};
/**
 * Jump to entry.
 */
var JumpToEntry = /** @class */ (function () {
	function JumpToEntry(
		/**
		 * Label shown on the entry. For players, it's player name.
		 */
		label,
		/**
		 * HTML Element id, to scroll into view when clicked.
		 */
		targetId,
		/**
		 * Any element that is useful to customize the link.
		 * Basic ones are 'color' and 'colorback'.
		 */
		data) {
		if (data === void 0) {
			data = {};
		}
		this.label = label;
		this.targetId = targetId;
		this.data = data;
	}

	return JumpToEntry;
}());
var JumpToManager = /** @class */ (function () {
	function JumpToManager(game, settings) {
		var _a, _b, _c, _d;
		this.game = game;
		this.settings = settings;
		var entries = __spreadArray(__spreadArray(__spreadArray([], ((_a = settings === null || settings === void 0 ? void 0 : settings.topEntries) !== null && _a !== void 0 ? _a : []), true), ((_b = settings === null || settings === void 0 ? void 0 : settings.playersEntries) !== null && _b !== void 0 ? _b : this.createEntries(Object.values(game.gamedatas.players))), true), ((_c = settings === null || settings === void 0 ? void 0 : settings.bottomEntries) !== null && _c !== void 0 ? _c : []), true);
		this.createPlayerJumps(entries);
		var folded = (_d = settings === null || settings === void 0 ? void 0 : settings.defaultFolded) !== null && _d !== void 0 ? _d : false;
		if (settings === null || settings === void 0 ? void 0 : settings.localStorageFoldedKey) {
			var localStorageValue = localStorage.getItem(settings.localStorageFoldedKey);
			if (localStorageValue) {
				folded = localStorageValue == 'true';
			}
		}
		document.getElementById('bga-jump-to_controls').classList.toggle('folded', folded);
	}

	JumpToManager.prototype.createPlayerJumps = function (entries) {
		var _this = this;
		var _a, _b, _c, _d;
		document.getElementById("game_play_area_wrap").insertAdjacentHTML('afterend', "\n        <div id=\"bga-jump-to_controls\">        \n            <div id=\"bga-jump-to_toggle\" class=\"bga-jump-to_link ".concat((_b = (_a = this.settings) === null || _a === void 0 ? void 0 : _a.entryClasses) !== null && _b !== void 0 ? _b : '', " toggle\" style=\"--color: ").concat((_d = (_c = this.settings) === null || _c === void 0 ? void 0 : _c.toggleColor) !== null && _d !== void 0 ? _d : 'black', "\">\n                \u21D4\n            </div>\n        </div>"));
		document.getElementById("bga-jump-to_toggle").addEventListener('click', function () {
			return _this.jumpToggle();
		});
		entries.forEach(function (entry) {
			var _a, _b, _c, _d, _e, _f, _g, _h, _j;
			var html = "<div id=\"bga-jump-to_".concat(entry.targetId, "\" class=\"bga-jump-to_link ").concat((_b = (_a = _this.settings) === null || _a === void 0 ? void 0 : _a.entryClasses) !== null && _b !== void 0 ? _b : '', "\">");
			if ((_d = (_c = _this.settings) === null || _c === void 0 ? void 0 : _c.showEye) !== null && _d !== void 0 ? _d : true) {
				html += "<div class=\"eye\"></div>";
			}
			if (((_f = (_e = _this.settings) === null || _e === void 0 ? void 0 : _e.showAvatar) !== null && _f !== void 0 ? _f : true) && ((_g = entry.data) === null || _g === void 0 ? void 0 : _g.id)) {
				var cssUrl = (_h = entry.data) === null || _h === void 0 ? void 0 : _h.avatarUrl;
				if (!cssUrl) {
					var img = document.getElementById("avatar_".concat(entry.data.id));
					var url = img === null || img === void 0 ? void 0 : img.src;
					// ? Custom image : Bga Image
					//url = url.replace('_32', url.indexOf('data/avatar/defaults') > 0 ? '' : '_184');
					if (url) {
						cssUrl = "url('".concat(url, "')");
					}
				}
				if (cssUrl) {
					html += "<div class=\"bga-jump-to_avatar\" style=\"--avatar-url: ".concat(cssUrl, ";\"></div>");
				}
			}
			html += "\n                <span class=\"bga-jump-to_label\">".concat(entry.label, "</span>\n            </div>");
			//
			document.getElementById("bga-jump-to_controls").insertAdjacentHTML('beforeend', html);
			var entryDiv = document.getElementById("bga-jump-to_".concat(entry.targetId));
			Object.getOwnPropertyNames((_j = entry.data) !== null && _j !== void 0 ? _j : []).forEach(function (key) {
				entryDiv.dataset[key] = entry.data[key];
				entryDiv.style.setProperty("--".concat(key), entry.data[key]);
			});
			entryDiv.addEventListener('click', function () {
				return _this.jumpTo(entry.targetId);
			});
		});
		var jumpDiv = document.getElementById("bga-jump-to_controls");
		jumpDiv.style.marginTop = "-".concat(Math.round(jumpDiv.getBoundingClientRect().height / 2), "px");
	};
	JumpToManager.prototype.jumpToggle = function () {
		var _a;
		var jumpControls = document.getElementById('bga-jump-to_controls');
		jumpControls.classList.toggle('folded');
		if ((_a = this.settings) === null || _a === void 0 ? void 0 : _a.localStorageFoldedKey) {
			localStorage.setItem(this.settings.localStorageFoldedKey, jumpControls.classList.contains('folded').toString());
		}
	};
	JumpToManager.prototype.jumpTo = function (targetId) {
		document.getElementById(targetId).scrollIntoView({behavior: 'smooth', block: 'center', inline: 'center'});
	};
	JumpToManager.prototype.getOrderedPlayers = function (unorderedPlayers) {
		var _this = this;
		var players = unorderedPlayers.sort(function (a, b) {
			return Number(a.player_no) - Number(b.player_no);
		});
		var playerIndex = players.findIndex(function (player) {
			return Number(player.id) === Number(_this.game.player_id);
		});
		var orderedPlayers = playerIndex > 0 ? __spreadArray(__spreadArray([], players.slice(playerIndex), true), players.slice(0, playerIndex), true) : players;
		return orderedPlayers;
	};
	JumpToManager.prototype.createEntries = function (players) {
		var orderedPlayers = this.getOrderedPlayers(players);
		return orderedPlayers.map(function (player) {
			return new JumpToEntry(player.name, "player-table-".concat(player.id), {
				'color': '#' + player.color,
				'colorback': player.color_back ? '#' + player.color_back : null,
				'id': player.id,
			});
		});
	};
	return JumpToManager;
}());
define({
	JumpToManager: JumpToManager,
});
