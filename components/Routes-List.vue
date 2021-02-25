<template>
	<div id="routes-list">
		<div class="route"
			v-for="(route, index) in routes"
			@click="setRoute(route.route_id)"
			:title="routeName(route.route_long_name)"
			:class="{tram: (route.route_type == 0)}"
			v-bind:style="routeStyle(route.route_color, route.route_text_color)">
			{{route.route_short_name}}
		</div>
	</div>
</template>

<script>
module.exports = {
	data: function() {
		return {
			routes: [],
		}
	},
	created: function() {
		url = 'http://51.178.29.39/api/routes';
	  this.$http.get(url).then(response => {
			this.routes = response.body;
			console.log('Download routes:', this.routes);
	  });
	},
	methods: {
		routeStyle: function(color, text_color) {
			return {
				'backgroundColor': '#'+color,
				'color': '#'+text_color
			}
		},
		routeName(long_name) {
			if(long_name.indexOf("|") == -1) {
				return long_name;
			} else {
				return long_name.slice(0, long_name.indexOf("|"));
			}
		},
		setRoute(route_id) {
			console.log('Select route: [route_id='+route_id+']');
			this.$emit('update:route', route_id);
			router.push({ path: '/timetable/'+route_id })
		},
	}
}
</script>

<style scoped>
#routes-list {
	display: flex;
	flex-wrap: wrap;
	flex-direction: row;
	justify-content: center;
	margin: 8px;
}

#routes-list .route {
	display: inline-block;
	width: 40px;
	height: 40px;
	margin: 8px 16px;
	line-height: 40px;
	font-size: 20px;
	text-align: center;
	font-weight: bold;
	cursor: pointer;
}

#routes-list .route.tram {
	border-radius: 20px;
}
</style>
