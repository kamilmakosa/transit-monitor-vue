<template>
	<div class="row" @click="display_info()">
		<span class="line" v-if="line < 100">
			<div v-bind:style="routeStyle(route_color, route_text_color)">{{line}}</div>
		</span>
		<span class="line" v-if="line >= 100">
			{{line}}
		</span>
		<span class="direction">{{direction}}</span>
		<span class="time" :class="flashing" v-html="departure_time"></span>
		<i class="material-icons collapse-icon" v-show="realtime" :style="rotate_icon">keyboard_arrow_down</i>
		<transition name="smooth">
			<div class="info" v-if="realtime" v-show="show">
				<div class="info-wrapper">
					<div class="left">
						<span v-show="delay >= 0">Opóźnienie: {{this.delay}} sekund</span>
						<span v-show="delay < 0">Przyśpieszenie: {{-this.delay}} sekund</span>
						<span>Pojazd: {{this.vehicle}}</span>
						<!-- <span>Zmierza do: {{this.next_stop}}</span> -->
					</div>
					<div class="center">
						<i v-if="veh_details.air_conditioner" class="icon material-icons" title="Kurs obsługiwany pojazdem wyposażonym w klimatyzację.">ac_unit</i>
						<i v-if="veh_details.place_for_transp_bicycles" class="icon material-icons" title="Możliwość przewozu rowerów zgodnie z zapisami w Regulaminie Przewozów.">pedal_bike</i>
						<img v-if="veh_details.ramp && veh_details.hf_lf_he != 2" width="24px" src="https://www.peka.poznan.pl/vm/img/npr.png" title="Kurs obsługiwany pojazdem niskopodłogowym z rampą.">
						<img v-if="veh_details.ramp && veh_details.hf_lf_he == 2" width="24px" src="https://www.peka.poznan.pl/vm/img/nw.png" title="Kurs obsługiwany pojazdem z niską podłogą w środkowym członie z rampą.">
						<img v-if="!veh_details.ramp && veh_details.hf_lf_he == 1" width="24px" src="https://www.peka.poznan.pl/vm/img/np.png" title="Kurs obsługiwany pojazdem niskopodłogowym.">
						<img v-if="veh_details.ticket_machine" width="24px" src="https://www.peka.poznan.pl/vm/img/ticket.png" title="Możliwość zakupu biletów w pojeździe.">
					</div>
					<div>
						<router-link style="color: white; ":to="{path: '/map/'+this.vehicle}" class="nav-link">
							<i class="icon material-icons" title="Pokaż pojazd na mapie.">map</i>
						</router-link>
					</div>
				</div>
			</div>
		</transition>
	</div>
</template>

<script>
module.exports = {
	data () {
		return {
			show: false,
			angle: 0,
    	}
	},
	props: {
		line: {
			type: [String, Number],
			required: true
		},
		direction: {
			type: String,
			required: true
		},
		time: {
			type: [Number, String],
			required: false
		},
		departuretime: String,
		realtime: Boolean,
		delay: Number,
		next_stop: String,
		vehicle: String,
		veh_details: Object,
		route_color: String,
		route_text_color: String,
	},
	methods: {
		routeStyle: function(color, text_color) {
			return {
				'backgroundColor': '#'+color,
				'color': '#'+text_color
			}
		},
		display_info() {
			this.show = !this.show;
			this.angle = (this.angle) ? 0 : 180;
		},
		getTextTime(seconds) {
			if(seconds < 60) return '<1 min';
			min = Math.floor((seconds+10)/60);
			return min + ' min';
		}
	},
	computed: {
		rotate_icon() {
			return {
				transform: 'rotate('+this.angle+'deg)',
			}
		},
		flashing() {
			x = new Date();
			x.setMinutes(x.getMinutes()+1);
			minute_now = x.getTime();
			timestamp = Math.floor(x.getTime()/1000);
			if(this.time < timestamp) {
				return 'flashing'
			} else {
				return ''
			}
		},
		departure_time() {
			if(this.realtime) {
				x = Math.floor(new Date().getTime()/1000);
				return this.getTextTime(this.time-x);
			}
			return this.departuretime;
		}
	}
}
</script>

<style scoped>
.smooth-enter-active, .smooth-leave-active {
  transition: height .5s;
  overflow: hidden;
}
.smooth-enter, .smooth-leave-to {
  height: 0;
}
.smooth-enter-to, .smooth-leave {
  height: 72px;
}
</style>
