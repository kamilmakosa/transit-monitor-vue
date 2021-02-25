<template>
	<div>
		<div class="searcher">
			<input type="text" id="searcher" v-model="text" placeholder="Wyszukaj przystanek" autocomplete="off">
			<i class="search-icon material-icons">search</i>
			<i @click="switchStop(); navigate('/monitor')" v-if="stop_id" class="icon change-icon material-icons">compare_arrows</i>
			<i @click="addUsersStop()" v-if="stop_id && !isUsersStopVar" class="icon star-icon material-icons">star_outline</i>
			<i @click="removeUsersStop()" v-if="stop_id && isUsersStopVar" class="icon star-icon material-icons">star</i>
			<i @click="unsetStop(); navigate('/monitor')" class="icon close-icon material-icons">close</i>
			<ul class="list listStops" v-show="stop == undefined">
				<li v-for="(stop, index) in getStopsNames(text)" @click="setStopFirst(stop)">
					<a v-html="autocompleteTextFormat(stop, text)"></a>
				</li>
	  		</ul>
			<ul class="list listStopCodes" v-if="stop && stop.stop_name && !stop.stop_id">
				<li v-for="(stop, index) in getStopsByName(stop.stop_name)" @click="setStop(stop); navigate('/monitor/'+stop.stop_code)">
					<a>[{{stop.stop_code}}] <b>{{stop.stop_name}}</b>
					<div><i>
						<span style="display: inline-block; margin-right: 10px;" v-for="headsign in headsigns" v-if="headsign.stop_id == stop.stop_id">{{headsign.route_id}} -> {{headsign.trip_headsign}},</span>
					</i></div>
					</a>
				</li>
	  		</ul>
		</div>
		<div class="usersStops" v-if="stop == undefined && usersStops.length > 0">
			<div class="header">Ulubione przystanki:</div>
			<div class="row" v-for="stop in usersStops" style="position: relative;">
				<a @click="setStop(stop); navigate('/monitor/'+stop.stop_code)">[{{stop.stop_code}}] <b>{{stop.stop_name}}</b></a>
				<i @click="removeUsersStopById(stop.stop_id)" class="icon star-icon material-icons">star</i>
			</div>
		</div>
	</div>
</template>

<script>
module.exports = {
	data: function () {
		return {
			stops: [],
			usersStops: [],
			stopNamesMap: [],
			headsigns: [],
			stop: undefined,
			text: '',
			isUsersStopVar: false,
			navigation: true
	  }
	},
	props: ['stop_id'],
	created: function() {
		this.getUsersStops();
		this.getStops();
	},
	watch: {
		$route: function() {
			if(!this.navigation) {
				this.navigation = true;
				return;
			}
			var path = this.$route.path;
			path = path.replace(/^\/+|\/+$/g, ''); //trim slashes
			path = path.split("/");
			if (path[0] == 'monitor' || path[0] == "") {
				if (path.length == 1) {
					this.unsetStop();
				}
				if (path.length > 1) {
					for(var i=0; i<this.stops.length; i++) {
						if(this.stops[i].stop_code == path[1]) {
							this.setStopSecond(this.stops[i]);
							break;
						}
					}
				}
			}
		}
	},
	methods: {
		init() {
			var path = this.$route.path;
			path = path.replace(/^\/+|\/+$/g, '');
			path = path.split("/");
			if(path[0] == 'monitor') {
				if (path.length == 1) {
					this.unsetStop();
				}
				if(path.length > 1) {
					console.log(this.stops.length);
					for(var i=0; i<this.stops.length; i++) {
						if(this.stops[i].stop_code == path[1]) {
							this.setStopSecond(this.stops[i]);
							break;
						}
					}
				}
			}
		},
		navigate(path) {
			this.navigation = false;
			if ((this.$route.path != "/" && this.$route.path !== path) || (path != "/monitor")) {
				this.$router.push(path);
			}
		},
		unsetStop() {
			this.text = '';
			this.stop = undefined;
			this.$emit('update:stop_id', undefined);
		},
		setStopFirst(stop_name) {
			this.text = stop_name;
			this.stop = {stop_name: stop_name};
			this.getHeadsignsByStopName(stop_name);
		},
		setStopSecond(stop) {
			this.text = '['+stop.stop_code+'] '+stop.stop_name;
			this.stop = stop;
			this.$emit('update:stop_id', stop.stop_id);
			this.isUsersStop();
		},
		setStop(stop) {
			this.setStopSecond(stop);
		},
		switchStop() {
			this.text = this.stop.stop_name;
			this.stop = {stop_name: this.stop.stop_name};
			this.$emit('update:stop_id', undefined);
		},
		autocompleteTextFormat(text, boldtext) {
			if(boldtext.length != 0) {
				stopName = text;
				text = this.convertText(text);
				boldtext = this.convertText(boldtext);
				mask = '/'+boldtext+'/i';
				regEx = new RegExp(boldtext, "i");
				part = text.split(regEx);
				s = text.search(regEx);
				start = stopName.slice(0, s);
				text = stopName.slice(s, s+boldtext.length);
				end = stopName.slice(s+boldtext.length);
				return start+'<strong>'+text+'</strong>'+end;
			}
			return text;
		},
		getStops() {
			url = 'http://51.178.29.39/api/stops';
		  	this.$http.get(url).then(response => {
				this.stops = response.body;
				unique = function(value, index, self) {
					return self.indexOf(value) === index;
				}
				var stopNames = this.stops.map(stop => stop.stop_name).filter(unique);
				stopNames = stopNames.sort(function(a, b) {
					return ((a > b) ? 1 : -1);
				});
				var stopNamesMap = new Map();
				for(i=0; i<stopNames.length; i++) {
					key = this.convertText(stopNames[i].toLowerCase());
					value = stopNames[i];
					stopNamesMap.set(key, value);
				}
				this.stopNamesMap = stopNamesMap;
				this.init();
			});
		},
		getStopsNames(text) {
			if (text) {
				text = this.convertText(text.toLowerCase());
			}
			var stopsList = [];
			for(let [key, value] of this.stopNamesMap) {
				if (key.indexOf(text) > -1) {
					stopsList.push(value)
				}
				if (stopsList.length == 10) {
					break;
				}
			}
			return stopsList;
		},
		getStopsByName(stop_name) {
			match = function(value, index, self) { //currentValue, index, arr
				return value.stop_name == stop_name; //pass if true
      		}
			return this.stops.filter(match).sort(function(a, b) {
        		return ((a.stop_code > b.stop_code) ? 1 : -1);
      		});
		},
		getHeadsignsByStopName(stop_name) {
			url = 'http://51.178.29.39/api/routes/'+encodeURIComponent(stop_name)+'/headsignsByStopName';
		  this.$http.get(url).then(response => {
				this.headsigns = response.body;
		  });
		},
		getUsersStops() {
			cname = 'users_stops';
			name = cname + "=";
			decodedCookie = decodeURIComponent(document.cookie);
			ca = decodedCookie.split(';');
			for(var i = 0; i <ca.length; i++) {
				c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					this.usersStops = JSON.parse(c.substring(name.length, c.length));
					if(!Array.isArray(this.usersStops)) {
						this.usersStops = [];
					}
					return;
				}
			}
			this.usersStops = [];
		},
		addUsersStop() {
			cname = 'users_stops';
			for(index = 0; index < this.usersStops.length; index++) {
				if (this.usersStops[index].stop_code == this.stop.stop_code) {
					return;
				}
			}
			newUsersStop = {
				stop_id: this.stop.stop_id,
				stop_name: this.stop.stop_name,
				stop_code: this.stop.stop_code
			};
			var usersStops = this.usersStops;
			usersStops.push(newUsersStop);
			usersStops.sort(function(a, b) {
				if (a.stop_name === b.stop_name) {
					return (a.stop_code < b.stop_code) ? -1 : 1;
				} else {
					return (a.stop_name < b.stop_name) ? -1 : 1;
				}
			});
			cvalue = JSON.stringify(usersStops);
			var d = new Date();
			d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			this.getUsersStops();
			this.isUsersStopVar = true;
		},
		removeUsersStop() {
			this.removeUsersStopById(this.stop.stop_id);
		},
		removeUsersStopById(stop_id) {
			for(index = 0; index < this.usersStops.length; index++) {
				if (this.usersStops[index].stop_id == stop_id) {
					this.usersStops.splice(index, 1);
					index--;
				}
			}
			cname = 'users_stops';
			cvalue = JSON.stringify(this.usersStops);
			var d = new Date();
			d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
			var expires = "expires="+d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			this.getUsersStops();
			this.isUsersStopVar = false;
		},
		isUsersStop() {
			for(index = 0; index < this.usersStops.length; index++) {
				if (this.usersStops[index].stop_id == this.stop.stop_id) {
					this.isUsersStopVar = true;
					return;
				}
			}
			this.isUsersStopVar = false;
		},
		convertText(text) {
			var tablica_zastap = [/ę/ig, /ó/ig, /ą/ig, /ś/ig, /ł/ig, /ż/ig, /ź/ig, /ć/ig, /ń/ig];
			var tablica_robocze = ['e', 'o', 'a', 's', 'l', 'z', 'z', 'c', 'n'];
			for(var i=0; i<tablica_zastap.length; i++) {
				text = text.replace(tablica_zastap[i], tablica_robocze[i]);
			}
			return text;
		},
	},
}
</script>

<style scoped>
	.searcher {
		position: relative;
	}

	#searcher {
	  width: 100%;
	  box-sizing: border-box;
	  padding: 8px;
	  padding-left: 40px;
	  padding-right: 60px;
	  height: auto;
		position: relative;
		font-size: 0.88rem;
	  letter-spacing: 0.01rem;
	  line-height: 1.4;
	  color: #fff;
	  line-height: normal;
	  -o-text-overflow: ellipsis;
	  text-overflow: ellipsis;
	  white-space: nowrap;
	  overflow: hidden;
	}

	#searcher::placeholder {
		color: white;
	}

	input[type=text] {
		display: block;
		box-sizing: content-box;
		width: 100%;
		margin: 0;
		border: 0;
		padding: 0 14px 0 14px;
		background-color: #F9A800;
		background-color: rgb(70,70,70);
		color: #F4F4F4;
	}

	input[type=text]:focus {
  	outline: none;
	}

	.search-icon {
	  position: absolute;
	  top: 0;
	  left: 0;
	  font-size: 23px;
	  padding: 6px;
	}
	.icon {
		position: absolute;
	  top: 0;
		font-size: 19px;
	  padding: 8px;
		cursor: pointer;
	}
	.close-icon { right: 0; }
	.star-icon { right: 35px; }
	.change-icon { right: 70px; }

	ul.list {
		margin: 0;
		padding: 0;
		list-style-type: none;
		position: absolute;
		left: 0;
		right: 0;
		top: 100%;
		/* border: 4px solid #F9A800; */
		/* border-top: 0; */
		border-top: 4px solid transparent;
	}

	.listStops {
		display: none;
		z-index: 10;
	}

	.list .listStopCodes {
		display: none;
		z-index: 20;
	}

	ul.list:hover,
	input#searcher:focus ~ ul.list {
		display: block;
	}

	ul.list li {
		background-color: rgb(70, 70, 70);
		cursor: pointer;
		padding: 4px;
		padding-left: 40px;
		border-bottom: 1px solid #1B2021;
	}

	.usersStops {
		margin-top: 15px;
	}

	.usersStops .header {
		width: 100%;
	  box-sizing: border-box;
	  padding: 8px;
	  padding-left: 40px;
	  padding-right: 60px;
	  height: auto;
		position: relative;
		font-size: 0.88rem;
	  letter-spacing: 0.01rem;
	  line-height: 16px;
	  color: #fff;
	  /* line-height: normal; */
	  -o-text-overflow: ellipsis;
	  text-overflow: ellipsis;
	  white-space: nowrap;
	  overflow: hidden;
		font-weight: bold;
		background-color: #F9A800;
	}

	.usersStops .row {
	  margin-top: 5px;
	  font-size: 16px;
		line-height: 16px;
		padding: 8px;
		padding-left: 20px;
		padding-right: 60px;
	  background-color: #2B2B2B;
	}

	.usersStops .star-icon {
		right: 0;
	}
</style>
