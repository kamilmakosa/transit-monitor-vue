<template>
	<div class="stop-list">
		<div class="header">
			<div class="line" :style="routeColor" :class="{tram: (route.route_type == 0)}">{{route_id}}</div>
			<div class="info">
				<div class="direction" v-if="headsign">
					<div class="desc">kierunek:</div>
					<div class="title">{{headsign}}</div>
				</div>
			</div>
		</div>
		<div class="list">
			<div class="stop" v-for="(stop, index) in stops">
				<div class="list sublist before" v-if="stop.before" v-show="more_stops" v-bind:class="{ oneelement: (stop.before.length==1) }">
					<div class="stop" v-for="(extra_stop, extra_index) in stop.before">
						<span class="stop-name" @click="setStop(extra_stop.stop_id, extra_stop.stop_name)">{{extra_stop.stop_name}}</span>
						<div class="circle"></div>
					</div>
				</div>
				<span class="stop-name" @click="setStop(stop.stop_id, stop.stop_name)">{{stop.stop_name}}</span>
				<div class="circle"></div>
				<div class="list sublist after" v-if="stop.after" v-show="more_stops" v-bind:class="{ oneelement: (stop.after.length==1) }">
					<div class="stop" v-for="(extra_stop, extra_index) in stop.after">
						<span class="stop-name" @click="setStop(extra_stop.stop_id, extra_stop.stop_name)">{{extra_stop.stop_name}}</span>
						<div class="circle"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
module.exports = {
	data: function() {
		return {
			stops: []
		}
	},
	props: {
		route_id: { type: [Number, String],	required: true },
		direction_id: { type: [Number, String], required: true },
		headsign: {required: false},
		route: {required: false},
		more_stops: { type: Boolean, required: false},
	},
	computed: {
		routeColor() {
			if(this.route.route_type === 0) {
				return {
					color: '#'+this.route.route_text_color,
					backgroundColor: '#'+this.route.route_color,
					borderRadius: '50%',
				}
			}
		}
	},
	created: function() {
		this.getStops();
	},
	methods: {
		getStops() {
			url = 'http://51.178.29.39/api/routes/'+this.route_id+'/stop-plan?direction='+this.direction_id;
			console.log(url);
			this.$http.get(url).then(response => {
				this.stops = response.body;
			});
		},
		setStop(stop_id) {
			console.log('Select stop: [stop_id='+stop_id+', direction_id='+this.direction_id+']');
			this.$emit('update:direction', this.direction_id);
			this.$emit('update:stop', stop_id);
		}
	}
}
</script>

<style scoped>
.header {
	display: flex;
	flex-direction: row;
	flex-wrap: nowrap;
}

.header .line {
	min-width: 40px;
	height: 40px;
	margin: 16px;
	line-height: 40px;
	font-size: 20px;
	text-align: center;
	font-weight: bold;
	box-sizing: border-box;
}

.header .info {
	margin: 16px;
	display: flex;
	flex-direction: column;
	flex-wrap: nowrap;
}

.header .info .desc {
	height: 16px;
	line-height: 16px;
	font-size: 12px;
}

.header .info .title {
	height: 24px;
	line-height: 24px;
	font-size: 16px;
	font-weight: bold;
}

.list {
	width: 200px;
	padding-left: 24px;
	border-left: 12px solid transparent; /* #F9A800 */
	margin-left: 24px;
	margin-bottom: 48px;
}

.list .stop {
	position: relative;
}

.list .stop .stop-name {
	display: inline-block;
	position: relative;
	line-height: 16px;
	font-size: 16px;
	padding: 4px 0;
}

/* circle  */
.list .stop .stop-name::before {
	position: absolute;
	display: block;
	width: 12px;
  height: 12px;
  background: #F9A800;
  border-radius: 50%;
	top: 8px;
	left: -36px;
	content: "";
}

/* line between circles */
.list .stop:not(:last-child)::after {
	position: absolute;
	top: 12px;
	left: -33px;
	content: "";
	width: 6px;
	bottom: -12px;
	background: rgb(249, 168, 0);
	display: block;
}

.list .stop:first-child::after {
    top: auto;
    height: 24px;
    bottom: -12px;
}

 /* last stop  */
.list:not(.sublist) > .stop:last-child > .stop-name {
	font-size: 24px;
	line-height: 24px;
	font-weight: bold;
}

.sublist {
	/* margin: 12px 0; */
	padding-left: 12px;
	margin: 0;
	padding-top: 12px;
	padding-bottom: 12px;
	position: relative;
}

/* circle  */
.sublist .stop .stop-name::before {
	left: -24px;
}

/* line between circles */
.sublist .stop:not(:last-child)::after {
	left: -21px;
}

.sublist.after:before {
	content: '';
	position: absolute;
	display: block;
	border-top: 6px solid #F9A800;
	/* top: 27px; */
	top: 6px;
	/* left: -37px; */
	left: -48px;
	width: 48px;
	transform: rotate(45deg);
}

.sublist.before:before {
	content: '';
	position: absolute;
	display: block;
	border-bottom: 6px solid #F9A800;
	/* bottom: 25px; */
	bottom: 0px;
	/* left: -37px; */
	left: -48px;
	width: 48px;
	transform: rotate(-45deg);
}
/*
.sublist .stop span:before {
	background: #F9A800;
}

.sublist.oneelement {
	border-left: 0;
	margin-top: 16px;
	margin-bottom: 8px;
	left: 6px;
	height: 24px;
	border: 0;
}

*/

/* .sublist.before:before {
	content: '';
	position: absolute;
	border-bottom: 6px solid #F9A800;
	width: 26px;
	display: block;
	bottom: -3px;
	left: -26px;
}

.sublist.after:before {
	content: '';
	position: absolute;
	border-top: 6px solid #F9A800;
	width: 26px;
	display: block;
	top: -3px;
	left: -26px;
	width: 30px;
}

/*
.sublist .stop:last-child {
	padding-bottom: 0px;
}



.sublist .stop:last-child span:before {
	top: 0;
}

.sublist.oneelement .stop {
	top: 0;
}

.sublist.oneelement.before:before {
	top: 4px;
	left: -28px;
  top: -6px;
  transform: rotate(-45deg);
}

.sublist.oneelement.after:before {
	top: 4px;
	left: -28px;
  top: -6px;
  transform: rotate(45deg);
}

.sublist.oneelement .stop:last-child span {
	position: relative
} */






/*
.sublist.before .stop:first-child {
	padding-bottom: 0;
	top: 7px;
	font-size: 175%;
	line-height: 100%;
	position: relative;
}

.sublist.after .stop:last-child {
	padding-bottom: 0;
	top: 7px;
	font-size: 175%;
	line-height: 100%;
	position: relative;
} */
</style>
