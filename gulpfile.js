const gulp = require( 'gulp' );
const fs = require( 'fs' );
const $ = require( 'gulp-load-plugins' )();
const mergeStream = require( 'merge-stream' );
const webpack = require( 'webpack-stream' );
const webpackBundle = require( 'webpack' );
const named = require( 'vinyl-named' );
const browserSync = require( 'browser-sync' ).create();
const pngquant = require( 'imagemin-pngquant' );
const mozjpeg = require( 'imagemin-mozjpeg' );

// Sass
gulp.task( 'sass', function () {
	return gulp.src( [
		'./src/scss/**/*.scss'
	] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sourcemaps.init( { loadMaps: true } ) )
		.pipe( $.sassGlob() )
		.pipe( $.sass( require( 'node-sass' ) )( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			includePaths: [
				'./src/scss',
				'./node_modules/uikit/src/scss'
			]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './assets/css' ) );
} );


// Minify All
gulp.task( 'js', function () {
	return gulp.src( [ './src/js/**/*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( named( function ( file ) {
			return file.relative.replace( /\.[^.]+$/, '' );
		} ) )
		.pipe( webpack( {
			mode: 'production',
			devtool: 'source-map',
			module: {
				rules: [
					{
						test: /\.js$/,
						exclude: /(node_modules|bower_components)/,
						use: {
							loader: 'babel-loader',
							options: {
								presets: [ '@babel/preset-env' ],
								plugins: [ '@babel/plugin-transform-react-jsx' ]
							}
						}
					}
				]
			}
		}, webpackBundle ) )
		.pipe( gulp.dest( './assets/js/' ) );
} );


// Eslint
gulp.task( 'eslint', function () {
	return gulp.src( [
		'./src/js/**/*.js'
	] )
		.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// Copy library files.
gulp.task( 'copylib', function () {
	return mergeStream(
		gulp.src( [
			'node_modules/uikit/dist/js/uikit.min.js',
			'node_modules/uikit/dist/js/uikit-icons.min.js',
			'node_modules/fg-loadcss/dist/cssrelpreload.min.js',
			'node_modules/fitie/dist/fitie.js',
			'node_modules/fitie/dist/fitie.js.map',
		] )
			.pipe( gulp.dest( './assets/js' ) )
	);
} );

// Image min
async function images_to_dist() {
	const imagemin = await import('gulp-imagemin');
	const imagePlugins = [
		pngquant( {
			quality: '65-80',
			speed: 1,
			floyd: 0
		} ),
		mozjpeg( {
			quality: 85,
			progressive: true
		} ),
		imagemin.svgo(),
		imagemin.optipng(),
		imagemin.gifsicle(),
	];
	return gulp.src( './src/img/**/*' )
		.pipe( imagemin.default( imagePlugins, { verbose: true } ) )
		.pipe( gulp.dest( './assets/img' ) );
}
gulp.task( 'imagemin', function ( done ) {
	return images_to_dist();
} );

// Pug task
gulp.task( 'pug', function () {
	return gulp.src( [ 'src/pug/**/*', '!src/pug/**/_*' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.pug( {
			pretty: true
		} ) )
		.pipe( gulp.dest( 'assets' ) )
} );

// watch browser sync
gulp.task( 'server', function () {
	return browserSync.init( {
		files: [ "assets/**/*" ],
		server: {
			baseDir: "./assets",
			index: "index.html"
		},
		reloadDelay: 2000
	} );
} );

gulp.task( 'reload', function () {
	gulp.watch( 'assets/**/*', function () {
		return browserSync.reload();
	} );
} );

// watch
gulp.task( 'watch', function () {

	// Make SASS
	gulp.watch( 'src/scss/**/*.scss', gulp.task( 'sass' ) );

	// JS
	gulp.watch( [ 'src/js/**/*.js' ], gulp.parallel( 'js', 'eslint' ) );

	// Minify Image
	gulp.watch( 'src/img/**/*', gulp.task( 'imagemin' ) );

	// Compile HTML
	gulp.watch( 'src/pug/**/*', gulp.task( 'pug' ) );
} );

// Build
gulp.task( 'build', gulp.parallel( 'copylib', 'js', 'sass', 'imagemin' ) );

// HTML task
gulp.task( 'html', gulp.series( 'build', gulp.parallel( 'watch', 'server', 'reload' ) ) );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );
