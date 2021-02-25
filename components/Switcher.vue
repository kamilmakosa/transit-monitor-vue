<template>
	<div @click="click()" class="switcher" :style="getStyle" v-bind:class="{ grey: (!controller) }">{{ text }}</div>

</template>

<script>
module.exports = {
	data () {
		return {
			text: '',
		}
	},
	props: {
		texton: { type: String, required: true },
		textoff: { type: String, required: true },
		controller: { type: Boolean, required: true },
		width: { required: false },
		bgcolor: { required: false }
	},
	mounted: function() {
		if (this.controller) {
			this.text = this.textoff;
		} else {
			this.text = this.texton;
		}
	},
	watch: {
		controller: function() {
			if (this.controller) {
				this.text = this.textoff;
			} else {
				this.text = this.texton;
			}
		}
	},
	computed: {
		getStyle() {
			if (this.bgcolor && this.controller) {
				return {
					width: this.width + 'px',
					backgroundColor: this.bgcolor
				}
			} else if (this.controller) {
				return {
					width: this.width + 'px',
					backgroundColor: 'rgb(249, 168, 0)'
				}
			} else {
				return {
					width: this.width + 'px'
				}
			}
		}
	},
	methods: {
		click() {
			this.$emit('update:controller', !this.controller);
		}
	}
}
</script>

<style scoped>
	.switcher {
		color: white;
		font-size: 16px;
		font-weight: bold;
		border: none;
		cursor: pointer;
		box-sizing: border-box;
		padding: 3px 12px;
		text-align: center;
	}

	.switcher.grey {
		background-color: rgb(70, 70, 70);
	}
</style>
