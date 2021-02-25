<template>
	<div class="timetable-stop">
		<div class="header">
			<div class="line">{{route_id}}</div>
			<div class="info">
				<div class="direction" v-if="headsign">
					<div class="desc">kierunek:</div>
					<div class="title">{{headsign}}</div>
				</div>
				<div class="stop" v-if="stop">
					<div class="desc">przystanek:</div>
					<div class="title">{{stop.stop_name}}</div>
				</div>
				<div class="route_desc" v-if="route_desc">
					<div class="desc">trasa podstawowa:</div>
					<div class="title">{{route_desc}}</div>
				</div>
			</div>
		</div>
		<div id="select-trip" v-if="ready">
			<div class="row" v-for="(departures, index) in departuresHA" v-if="departuresHL[index] > 0">
				<span class="header">{{index%24}}</span>
				<span class="minute" v-for="(departure, index_dep) in departures" :data-trip="departure.trip_id">
					<span>{{departure.m}}</span><span class="extra">{{getExtraSub(departure.trip_id)}}</span>
				</span>
			</div>
		</div>
		<div id="extra-info">
			<div v-for="(extra, index) in extras">
				<span>{{index}}</span>
				<span>- {{extra}}</span>
			</div>
		</div>
	</div>
</template>

<script>
module.exports = {
  data: function() {
    return {
			departures: [],
			departuresHA: [],
			departuresHL: [],
			ready: false,
			extras: [],
			route_desc: '',
			stop: {}
    }
  },
	watch: {
		service_id: function() {
			this.ready = false;
			this.prepareTimetable();
			console.log('service change ');
		}
	},
	props: {
		service_id: {type: [Number, String], required: true },
		route_id: { type: [Number, String],	required: true },
		direction_id: { type: [Number, String], required: true },
		stop_id: { type: [Number, String], required: true },
		headsign: {required: false}
	},
	created: function() {
		this.getStopInfos();
		this.getRouteInfos();
		url = 'http://51.178.29.39/api/stop_times/route_id='+this.route_id+'&stop_id='+this.stop_id;
		console.log(url);
		console.log(this.service_id);
		this.$http.get(url).then(response => {
			this.departures = response.body;
			for(i=0; i<24; i++) {
				this.departuresHA = [];
				this.departuresHL = [];
			}
			for(departure of this.departures) {
				if(departure.service_id == this.service_id) {
					[h, m, s] = departure.departure_time.split(':');
					h = parseInt(h);
					m = parseInt(m);
					departure.h = h;
					departure.m = m;
					if(this.departuresHA[h] == undefined) {
						this.departuresHA[h] = [];
						this.departuresHL[h] = 0;
					}
					this.departuresHA[h].push(departure);
					this.departuresHL[h]++;
				}
			}
			this.ready = true;
			this.getExtraInfos();
		});
	},
	methods: {
		prepareTimetable() {
			for(i=0; i<24; i++) {
				this.departuresHA = [];
				this.departuresHL = [];
			}
			for(departure of this.departures) {
				if(departure.service_id == this.service_id) {
					[h, m, s] = departure.departure_time.split(':');
					h = parseInt(h);
					m = parseInt(m);
					departure.h = h;
					departure.m = m;
					if(this.departuresHA[h] == undefined) {
						this.departuresHA[h] = [];
						this.departuresHL[h] = 0;
					}
					this.departuresHA[h].push(departure);
					this.departuresHL[h]++;
				}
			}
			this.ready = true;
		},
		getExtraSub(trip_id) {
			if(trip_id.indexOf('^') < 0) {
				return '';
			}
			if(trip_id.indexOf('+') < 0) {
				return trip_id.substring(trip_id.indexOf('^')+1).replace(',', '');
			}
			return trip_id.substring(trip_id.indexOf('^')+1, trip_id.indexOf('+')).replace(',', '');
		},
		getExtraInfos() {
			url = 'http://51.178.29.39/api/routes/'+this.route_id+'/extrainfos';
			this.$http.get(url).then(response => {
				this.route_desc = response.body[this.direction_id]['track'];
				this.extras = response.body[this.direction_id]['extra']
			});
		},
		getStopInfos() {
			url = 'http://51.178.29.39/api/stops/'+this.stop_id;
			this.$http.get(url).then(response => {
				this.stop = response.body;
				console.log(response.body);
			});
		},
		getRouteInfos() {
			url = 'http://51.178.29.39/api/routes/'+this.route_id;
			this.$http.get(url).then(response => {
				console.log(response.body);
			});
		}
	}
}
</script>

<style scoped>
.header {
	display: flex;
	flex-flow: row nowrap;
}

.header .line {
	min-width: 40px;
	height: 40px;
	margin: 16px;
	line-height: 40px;
	font-size: 32px;
	text-align: center;
	font-weight: bold;
}

.header .info {
	margin: 16px;
	display: flex;
	flex-flow: column nowrap;
}

.header .info .desc {
	height: 16px;
	line-height: 16px;
	font-size: 12px;
}

.header .info .title {
	min-height: 24px;
	line-height: 24px;
	font-size: 16px;
	font-weight: bold;
}

.header .info .route_desc .title {
	font-weight: bold;
	line-height: 24px;
	font-size: 16px;
}

#select-trip {
	padding: 16px;
	display: table;
}

#select-trip .row {
	box-sizing: border-box;
	display: table-row;
}

#select-trip .row > span {
	display: inline-block;
	min-width: 20px;
	padding: 5px;
	text-align: center;
	display: table-cell;
}

#select-trip .row > span .extra {
	font-size: 75%;
	opacity: 0.5;
}

#select-trip .row > span.header {
	width: 30px;
	border-right: 2px white solid;
	font-weight: bold;
}

#select-trip .row:first-child > span {
	padding-top: 0;
	white-space: nowrap;
}

#select-trip .row:last-child > span {
	padding-bottom: 0;
}

#extra-info {
	padding: 16px;
	font-size: 75%;
}
</style>
