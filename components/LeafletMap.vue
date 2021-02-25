<template>
	<div id="map">
		<div style="margin: 12px 24px; display: flex; flex-flow: row nowrap; justify-content: space-between;">
			<switcher
				texton="Pokaż przystanki" textoff="Ukryj przystanki"
				:controller.sync="stopsLayerOn"
				width=160
				:bgcolor="stopMarker.fillColor">
			</switcher>
			<switcher
				texton="Pokaż pojazdy" textoff="Ukryj pojazdy"
				:controller.sync="vehiclesLayerOn"
				width=160
				:bgcolor="vehicleMarker.fillColor">
			</switcher>
		</div>
		<!-- <div style="margin: 12px 24px; display: flex; flex-flow: row nowrap; justify-content: space-between;">
			<span class="switcher">
				<span class="grey" v-if="!stopsLayerOn" @click="stopsLayerOn = true">Pokaż przystanki</span>
				<span class="no-grey"v-if="stopsLayerOn" @click="stopsLayerOn = false">Ukryj przystanki</span>
			</span>
			<span class="switcher">
				<span class="grey" v-if="!vehiclesLayerOn" @click="vehiclesLayerOn = true">Pokaż pojazdy</span>
				<span class="no-grey" v-if="vehiclesLayerOn" @click="vehiclesLayerOn = false">Ukryj pojazdy</span>
			</span>
		</div> -->
		<l-map class="map" ref="mymap" :options="options" :zoom="zoom" @update:zoom="zoomUpdated" style="width: 600px; height: 500px;">
			<l-layer-group ref="features" :visible="stopsLayerOn">
				<l-circle-marker v-for="stop in layers[0].features"
					:lat-lng="stop.coords"
					:color="stopMarker.color"
					:fill-color="stopMarker.fillColor"
					:fill-opacity="stopMarker.fillOpacity"
					:radius="stopMarker.radius"
					@l-add="$event.target.openPopup()">
					<l-popup>
						<div>[{{stop.code}}] <b>{{stop.name}}</b></div>
						<div><a @click="showStop(stop.code)">Pokaż odjazdy z tego przystanku</a></div>
					</l-popup>
				</l-circle-marker>
			</l-layer-group>
			<l-layer-group ref="vehiclesLayer" :visible="vehiclesLayerOn">
				<l-circle-marker v-for="(vehicle, id) in vehicles" :key="id"
					:lat-lng="vehicle.coords"
					:color="vehicleMarker.color"
					:fill-color="vehicleMarker.fillColor"
					:fill-opacity="vehicleMarker.fillOpacity"
					:radius="vehicleMarker.radius"
					@l-add="$event.target.openPopup()">
					<l-popup>
						<!-- <div>Num: <b>{{vehicle.a}}</b></div> -->
						<div>Pojazd: <b>{{vehicle.id}}</b></div>
						<div>Linia: <b>{{vehicle.routeId}}</b></div>
						<div v-if="vehicle.trip_info">Kierunek: <b>{{vehicle.trip_info.trip_headsign}}</b></div>
						<div>Brygada: <b>{{vehicle.label}}</b></div>
						<div>Prędkość: <b>{{vehicle.speed.toFixed(2)}}</b></div>
						<div><a v-if="!polyline || (vehicle.trip_info && polyline.shape_id != vehicle.trip_info.shape_id)" @click="showPath(vehicle.trip_info.shape_id)">Pokaż trasę przejazdu</a></div>
						<div><a v-if="polyline && vehicle.trip_info && polyline.shape_id == vehicle.trip_info.shape_id"@click="hidePath()">Ukryj trasę przejazdu</a></div>
					</l-popup>
				</l-circle-marker>
			</l-layer-group>
			<l-polyline :lat-lngs="polyline.latlngs" :color="vehicleMarker.fillColor"></l-polyline>
		</l-map>
	</div>
</template>

<script>
module.exports = {
  data () {
    return {
		map: null,
		tileLayer: null,
		layers: [{
	        id: 0,
	        name: 'Stops',
	        active: true,
	        features: [],
  		}],
		stopMarker: {
			color: "#FFFFFF",
			fillColor: "#00437B",
			fillOpacity: 1,
			radius: 5,
		},
		vehicleMarker: {
			color: "#FFFFFF",
			fillColor: "#008145",
			fillOpacity: 1,
			radius: 5,
		},
		icon: L.divIcon({
    	className: 'stop-marker',
			iconSize: [8, 8],
		}),
		options: {
			preferCanvas: true
		},
		zoom: 13,
		zoom_vehicle: 0,
		initialZoom: 13,
		vehicles: {},
		stopsLayerOn: true,
		vehiclesLayerOn: true,
		polyline: {
        	latlngs: [],
        	color: 'green',
			shape_id: ''
      	}
    }
  },
	components: {
		'switcher': window.httpVueLoader('http://51.178.29.39/transit-monitor/components/Switcher.vue'),
	    'l-map': window.Vue2Leaflet.LMap,
		'l-marker': window.Vue2Leaflet.LMarker,
		'l-circle': window.Vue2Leaflet.LCircle,
		'l-circle-marker': window.Vue2Leaflet.LCircleMarker,
		'l-icon': window.Vue2Leaflet.LIcon,
		'l-layer-group': window.Vue2Leaflet.LLayerGroup,
		'l-popup': window.Vue2Leaflet.LPopup,
		'l-polyline': window.Vue2Leaflet.LPolyline,
	},
	created: function() {
		var path = this.$route.path;
		path = path.replace(/^\/+|\/+$/g, '');
		path = path.split("/");
		console.log(path);
		if(path[0] == 'map') {
			if(path.length > 1) {
				this.zoom_vehicle = path[1];
				if (this.vehicles[this.zoom_vehicle]) {
					coords = this.vehicles[this.zoom_vehicle].coords;
					console.log(coords);
					this.$refs.mymap.mapObject.setView(coords, 18);
				}
			}
		}
		console.log(this.$route.path);
	},
	mounted: function() {
		this.initMap();
		// this.initLayers();
		this.getStops();
		this.getVehiclesUpdate(false);
		setInterval(() => {
			this.getVehiclesUpdate(true);
		}, 10000);
	},
	watch: {
		zoom: function() {
			var diff = this.zoom - this.initialZoom;
			if (diff > 4) {
				this.stopMarker.radius = 10;
			} else {
				this.stopMarker.radius = 5;
			}
		},
		$route: function() {
      		var path = this.$route.path;
      		path = path.replace(/^\/+|\/+$/g, '');
      		path = path.split("/");
      		console.log(path);
      		if(path[0] == 'map') {
        		if(path.length > 1) {
          			this.zoom_vehicle = path[1];
					if (this.vehicles[this.zoom_vehicle]) {
						coords = this.vehicles[this.zoom_vehicle].coords;
						console.log(coords);
						this.$refs.mymap.mapObject.setView(coords, 18);
					}
	        	}
	      	}
      		console.log(this.$route.path);
    	},
	},
	computed: {
    dynamicSize () {
      return [this.iconSize, this.iconSize * 1.15];
    },
    dynamicAnchor () {
      return [this.iconSize / 2, this.iconSize * 1.15];
    }
  },
	methods: {
		zoomUpdated (zoom) {
      this.zoom = zoom;
    },
		showStop(stop_code) {
			this.$router.push({ path: '/monitor/'+stop_code });
		},
		showPath(shape_id) {
			// this.polyline.latlngs = path;
			// this.polyline.shape_id = shape_id;
			url = 'http://51.178.29.39/api/shapes/' + shape_id;
			this.$http.get(url).then(response => {
				this.polyline.latlngs = response.body.path;
				this.polyline.shape_id = shape_id;
				console.log('Loaded path');
		  	});
		},
		hidePath() {
			this.polyline.latlngs = [];
			this.polyline.shape_id = '';
		},
		initMap() {
			console.log('Map Component:');
			console.log(this.$refs.mymap);
			this.$refs.mymap.mapObject.setView([52.40, 16.93], 13);
			this.tileLayer = L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/rastertiles/voyager/{z}/{x}/{y}.png', {
				maxZoom: 18,
				attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>, &copy; <a href="https://carto.com/attribution">CARTO</a>',
			});
			this.tileLayer.addTo(this.$refs.mymap.mapObject);
		},
		initLayers() {
			let self = this;
      this.layers.forEach((layer) => {
        const markerFeatures = layer.features.filter(feature => feature.type === 'marker');
        const polygonFeatures = layer.features.filter(feature => feature.type === 'polygon');

        markerFeatures.forEach((feature) => {
          feature.leafletObject = self.$refs.mymap.mapObject.marker(feature.coords).bindPopup(feature.name);
					feature.leafletObject.setIcon(stopIcon);
					feature.leafletObject.addTo(self.$refs.mymap.mapObject);
        });

        polygonFeatures.forEach((feature) => {
          feature.leafletObject = L.polygon(feature.coords).bindPopup(feature.name);
        });

      });
    },
		getStops() {
			url = 'http://51.178.29.39/api/stops';
		  this.$http.get(url).then(response => {
				response.body.forEach((stop) => {
					this.layers[0].features.push({
						id: stop.stop_id,
						name: stop.stop_name,
						code: stop.stop_code,
						type: 'marker',
						coords: [stop.stop_lat, stop.stop_lon],
					});
				});
		  });
		},
		getVehiclesUpdate(isFull) {
			if (isFull) {
				url = 'http://51.178.29.39/api/vehicles/live';
			} else {
				console.log('Live Simple');
				url = 'http://51.178.29.39/api/vehicles/livesimple';
			}
			this.$http.get(url).then(response => {
				this.vehicles = response.body;
				console.log('Live Info Loaded');
				// var num = 1;
				// for(var key in this.vehicles) {
   				// 	this.vehicles[key].a = num++;
				// }
		  	});
		}
	}
}
</script>

<style scoped>
	.switcher {
		/* background-color: #ffbc14; */
		color: white;
		font-size: 16px;
		font-weight: bold;
		border: none;
		cursor: pointer;
		box-sizing: border-box;
	}

	.switcher .no-grey {
		padding: 3px 12px;
	}

	.switcher .grey {
		background-color: rgb(70, 70, 70);
		padding: 3px 12px;
	}
	.stop-marker {
	  height: 5px;
	  width: 5px;
	  background-color: #00437B;
	  box-shadow: 3px 3px 5px 0 rgba(50, 50, 50, .24);
	  border-radius: 50%;
	  border: 2px solid #FFFFFF;
	}

	.leaflet-popup-pane, .leaflet-control {
    	cursor: pointer;
	}
</style>
