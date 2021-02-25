<template>
	<div class="timetable">
		<div class="header">Rozkład jazdy</div>
		<div class="content">
			<div style="margin: 12px 24px; display: flex; flex-flow: row nowrap; justify-content: space-between;">
				<!-- <div class="button" v-show="route_id" style="line-height: 16px; display:inline-block; position: relative" @click="goBack()">
					<i class="material-icons" style="position:absolute; left:0; top:0;">keyboard_arrow_left</i>
					<span style="line-height: 16px;">Powrót</span>
				</div> -->
				<dropdown
					v-show="stop_id"
					:days="days"
					:service_id.sync="service_id"
				></dropdown>
				<span id="switch-route-plan" v-if="route_id != undefined" v-show="stop_id == undefined">
					<span v-if="!moreStops" @click="moreStops = true">Pokaż przystanki dodatkowe</span>
					<span v-if="moreStops" @click="moreStops = false">Ukryj przystanki dodatkowe</span>
				</span>
			</div>
			<routes-list
				v-show="route_id == undefined"
				:route.sync="route_id"
			></routes-list>
			<div>
			<div id="stops-lists" v-if="route_id != undefined" v-show="stop_id == undefined">
				<stop-list
					v-for="(direction, index) in directions"
					:route_id="route_id"
					:direction_id="direction.direction_id"
					:direction.sync="direction_id"
					:stop.sync="stop_id"
					:headsign="directions[direction.direction_id].trip_headsign"
					:route="route"
					:more_stops="moreStops"
				></stop-list>
			</div>
			<timetable-stop
				v-if="stop_id != undefined"
				:service_id="service_id"
				:route_id="route_id"
				:direction_id="direction_id"
				:stop_id="stop_id"
				:headsign="directions[direction_id].trip_headsign"
			></timetable-stop>
		</div>
	</div>
</template>

<script>
module.exports = {
  data: function() {
    return {
			service_id: undefined,
			route_id: undefined,
			direction_id: undefined,
			stop_id: undefined,
			directions: [],
			route: {},
			days: {},
			moreStops: true
    }
  },
	components: {
		'dropdown': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Dropdown.vue'),
		'routes-list': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Routes-List.vue'),
		'stop-list': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Stop-List.vue'),
		'timetable-stop': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Timetable-Stop.vue')
	},
	created: function() {
		this.getService();
		var path = this.$route.path;
		path = path.replace(/^\/+|\/+$/g, '');
		path = path.split("/");
		console.log(path);
		if(path[0] == 'timetable') {
			if(path.length > 1) {
				this.route_id = path[1];
			}
		}
		console.log(this.$route.path);
		var path = this.$route.path;
		path = path.replace(/^\/+|\/+$/g, '');
		path = path.split("/");
		if(path[0] == 'timetable') {
			if(path.length > 1) {
				this.route_id = path[1];
			}
		}
	},
	watch: {
		route_id: function() {
			this.getDirections();
			this.getRoute();
		},
		$route: function() {
      		var path = this.$route.path;
      		path = path.replace(/^\/+|\/+$/g, '');
      		path = path.split("/");
      		console.log(path);
      		if(path[0] == 'timetable') {
        		if(path.length > 1) {
          			this.route_id = path[1];
	        	}
				if(path.length == 1) {
					this.route_id = undefined;
					this.stop_id = undefined;
				}
	      	}
      	console.log(this.$route.path);
    	},
	},
	methods: {
		goBack() {
			if(this.stop_id != undefined) {
				this.stop_id = undefined;
			} else if(this.route_id != undefined) {
				this.route_id = undefined;
			}
		},
		getService() {
			days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']; //js 0->sunday
			const now = new Date();
			day = (now.getDay()+6)%7;
			url = 'http://51.178.29.39/api/calendar/key=day';
		  this.$http.get(url).then(response => {
				this.days = response.body;
				this.service_id = response.body[day].service_id;
		  });
		},
		getDirections() {
			url = 'http://51.178.29.39/api/routes/'+this.route_id+'/headsigns';
			this.$http.get(url).then(response => {
				this.directions = response.body;
			});
		},
		getRoute() {
			url = 'http://51.178.29.39/api/routes/'+this.route_id;
			this.$http.get(url).then(response => {
				this.route = response.body;
			});
		}
	}
}
</script>

<style scoped>
#switch-route-plan {
	background-color: #ffbc14;
	color: white;
	padding: 3px 12px;
	font-size: 16px;
	font-weight: bold;
	border: none;
	cursor: pointer;
}
	.button {
		background-color: #ffbc14;
	  color: white;
		padding: 3px 12px;
		font-size: 16px;
		font-weight: bold;
	  border: none;
	  cursor: pointer;
		width: 120px;
		box-sizing: border-box;
		text-align: center;
	}

	.timetable {
		display: flex;
		flex-direction: column;
		flex-wrap: nowrap;

	}

	.timetable > .header {
		display: flex;
		flex-direction: row;
		flex-wrap: nowrap;
		font-weight: bold;
	  background-color: rgb(249, 168, 0);
		font-size: 20px;
		padding: 4px 16px;
	}

	.timetable > .content {
		background-color: rgb(43, 43, 43);
	}

	#stops-lists {
		display: flex;
		flex-direction: row;
		flex-wrap: nowrap;
		flex-flow: wrap;
	}

	#stops-lists .stop-list {
		flex: 0 0 50%;
	}
</style>
