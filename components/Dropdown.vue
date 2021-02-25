<template>
	<div class="dropdown">
		<button class="dropbtn">{{selected_day}}</button>
		<div class="dropdown-content">
			<a v-for="(day, index) in week" @click="setService(index)" href="#">{{day}}</a>
		</div>
	</div>
</template>

<script>
module.exports = {
  data () {
    return {
		selected_day: undefined,
		week: ['poniedziałek', 'wtorek', 'środa', 'czwartek', 'piątek', 'sobota', 'niedziela'], //js 0->sunday
    }
  },
	props: {
		days: {},
		service_id: {}
	},
	created: function() {
		const now = new Date();
		day = (now.getDay()+6)%7;
		this.selected_day = this.week[day];
	},
	methods: {
		setService(index) {
			day = (index+1)%7;
			this.selected_day = this.week[index];
			this.$emit('update:service_id', this.days[index].service_id);
		}
	}
}
</script>

<style scoped>
.dropdown {
	position: relative;
	display: inline-block;
	width: 120px;
}

.dropbtn {
	background-color: #ffbc14;
	color: white;
	padding: 3px 12px;
	font-size: 16px;
	font-weight: bold;
	border: none;
	cursor: pointer;
	width: 120px;
}

.dropdown:hover .dropbtn {
 	background-color: #f9a800;
}

.dropdown-content {
 	display: none;
 	position: absolute;
	right: 0;
	left: 0;
	text-align: center;
	background-color: #f9f9f9;
 	box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
 	z-index: 1;
}

.dropdown-content a {
  color: black;
  padding: 3px 0;
  text-decoration: none;
  display: block;
}

.dropdown-content a:hover {
	background-color: #f1f1f1
}

.dropdown:hover .dropdown-content {
  display: block;
}
</style>
