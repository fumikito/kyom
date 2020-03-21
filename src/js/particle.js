/**
 * Draw particle
 */

particlesJS( 'particles-js', {
	particles: {
		number: {
			value: Math.floor( Math.max( 1, Math.random() * 10 ) )  * 10,
			density: {
				enable: false,
				value_area: 561.194221302933
			}
		},
		color: {
			value: '#000000'
		},
		shape: {
			type: 'circle',
			stroke: {
				width: 0,
				color: '#000000'
			},
		},
		opacity: {
			value: 0.5,
			random: false,
			anim: {
				enable: false,
				speed: 1,
				opacity_min: 0.1,
				sync: false
			}
		},
		size: {
			value: 5,
			random: true,
			anim: {
				enable: false,
				size_min: 0.1,
				sync: false
			}
		},
		line_linked: {
			enable: true,
			distance: 300,
			color: '#000000',
			opacity: 0.4,
			width: 0
		},
		move: {
			enable: true,
			speed: 1,
			direction: 'none',
			random: false,
			straight: false,
			out_mode: 'out',
			bounce: false,
			attract: {
				enable: false,
				rotateX: 600,
				rotateY: 1200
			}
		}
	},
	interactivity: {
		detect_on: 'canvas',
		events: {
			onhover: {
				enable: false,
				mode  : 'repulse'
			},
			onclick: {
				enable: false,
				mode: 'push'
			},
			resize: true
		},
	},
	retina_detect: true
} );
