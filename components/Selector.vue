<template>
	<div class="selector">
		<router-link :to="{path: '/monitor'}" class="nav-link">
			<div v-bind:class="{ active: (mode == 'monitor') }">Monitor</div>
	    </router-link>
		<router-link :to="{path: '/timetable'}" class="nav-link">
			<div v-bind:class="{ active: (mode == 'timetable') }">Rozkład</div>
		</router-link>
		<router-link :to="{path: '/map'}" class="nav-link">
			<div v-bind:class="{ active: (mode == 'map') }">Mapa</div>
		</router-link>
	</div>
</template>

<script>
module.exports = {
	props: ['mode'],
}
</script>

<style scoped>
.selector {
	background-color: #2B2B2B;
	position: relative;
	display: flex;
	flex-flow: row wrap;
}

.selector .nav-link {
	color: white;
	text-align: center;
	text-decoration: none;
	line-height: 40px;;
	flex: 1;
}

.selector .nav-link .active {
	background-color: #F9A800;
}
</style>
