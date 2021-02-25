<template>
	<div class="monitor" v-if="this.stops.length > 0">
		<div class="header">
			<div class="row">
				<span class="line">Linia</span>
				<span class="direction">Kierunek</span>
				<span class="time">Odjazd</span>
			</div>
		</div>
		<div class="content">
			<monitor-row
				v-for="(stop, index) in stops"
				:line="stop.route_short_name"
				:direction="stop.trip_headsign"
				:time="stop.real_departure_timestamp"
				:departuretime="stop.departure_time"
				:realtime="stop.realtime"
				:delay="stop.arrival_delay"
				:vehicle="stop.vehicle_id"
				:veh_details="stop.vehicle"
				:route_color="stop.route_color"
				:route_text_color="stop.route_text_color">
			</monitor-row>
		</div>
	</div>
</template>

<script>
module.exports = {
	data () {
	    return {
			stops: [],
			interval: 0
		}
 	},
	props: ['stop_id'],
	components: {
		'monitor-row': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Monitor-Row.vue')
	},
	watch: {
		stop_id: function() {
			if(this.stop_id) {
				this.timer();
				this.update();
			} else {
				clearInterval(this.interval);
				this.stops = [];
			}
		}
	},
	mounted: function() {
		if(this.stop_id) {
			this.timer();
			this.update();
		}
	},
	methods: {
		update() {
			url = 'http://51.178.29.39/api/stops/' + this.stop_id + '/departures';
			this.$http.get(url).then(response => {
				this.stops = response.body;
			});
		},
		timer() {
			this.interval = setInterval(() => {
				console.log('Update Monitor');
				this.update();
				this.counter++;
			}, 15000); // 15 seconds
		},
	}
}
</script>

<style scoped>
.row {
	background-color: #2B2B2B;
	font-size: 20px;
	margin-top: 5px;
	display: -ms-flexbox;
	display: flex;
	-ms-flex-wrap: wrap;
    flex-wrap: wrap;
	-ms-flex-direction: row;
	flex-direction: row;
	box-sizing: border-box;
	position: relative;
}

.header .row {
  font-weight: bold;
  background-color: #F9A800;
}

.row .line {
  display: block;
  width: 50px;
  padding: 0 15px;
  text-align: center;
}

.row .line div {
	display: inline-block;
	width: 30px;
	height: 30px;
	border-radius: 15px;
	line-height: 30px;
	font-size: 15px;
	text-align: center;
	font-weight: bold;
}

.row .direction {
	display: block;
	flex-grow: 1;
	box-sizing: border-box;
	padding: 0 15px;
	flex: 1;
	overflow: hidden;
}

.row .time {
	display: block;
	width: 100px;
	padding: 0 15px;
	/* margin-left: auto; */
	text-align: center;
	padding-right: 39px;
}

.row .time.flashing {
  color: #F9A800;
  font-weight: bold;
  animation: flashing 2s cubic-bezier(0, 0, 0.3, 1) infinite
}

@keyframes flashing {
  50% {
    opacity: 0;
  }
}

.row .collapse-icon {
	transition: transform .5s;
	position: absolute;
	right: 0;
}

.row .info {
    display: block;
    width: 100%;
    padding: 0 15px;
    font-size: 12px;
}

.row .info .info-wrapper {
	padding: 20px;
	display: flex;
    flex-flow: row wrap;
	justify-content: space-between;
}

.row .info div {
	flex: 0 1 0%;
}

.row .info div.left {
	flex: 1 1 0%;
}

.row .info div.center {
	flex: 1 1 0%;
	text-align:center;
}

.row .info span {
  display: block;
  width: 100%;
  padding: 0 15px;
}

@media (max-width: 800px) {
	.line, .direction {
		padding-left: 5px!important;
		padding-right: 5px!important;

	} .time {
		padding-left: 5px!important;
		padding-right: 29px!important;
	}
}
</style>
