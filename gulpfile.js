////////////////////////////////////////////////////////////////////////////////
// Variables
////////////////////////////////////////////////////////////////////////////////

var compileScss = true;
var compileJs = true;
var liveReload = true;

////////////////////////////////////////////////////////////////////////////////
// Tasks
////////////////////////////////////////////////////////////////////////////////

// Gulp
var gulp = require('gulp');
var gutil = require('gulp-util');
var notify = require('gulp-notify');
var browserSync = require('browser-sync').create();
var fs = require('fs');
var config = {
  compileScss: true,
  compileJs: true,
  liveReload: true,
  browserSync: {
    hostname: null,
    port: null,
    openAutomatically: null,
    reloadDelay: null,
    injectChanges: null
  }
};

// SCSS/CSS
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var lint = require('gulp-scss-lint');
var prefix = require('gulp-autoprefixer');
var clean = require('gulp-clean-css');

// JavaScript
var uglify = require('gulp-uglify');

// If config.js exists, load that config for overriding certain values below.
function loadConfig() {
  if (fs.existsSync(__dirname + "/./config.js")) {
    config = require("./config");
  }

  return config;
}
loadConfig();

////////////////////////////////////////////////////////////////////////////////
// Compile SCSS
////////////////////////////////////////////////////////////////////////////////

gulp.task('sass', function (){
  gulp.src(['./dev/scss/*.scss'])
    .pipe(lint())
    .pipe(sourcemaps.init())
    .pipe(sass({
      noCache: true,
      outputStyle: 'compressed',
      lineNumbers: false,
      includePaths: ['./dev/scss']
    })).on('error', function(error) {
      gutil.log(error);
      this.emit('end');
    })
    .pipe(prefix(
      "last 2 versions", "> 1%", "ie 8", "ie 7"
      ))
    .pipe(gulp.dest('./css'))
    .pipe(clean())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'))
    .pipe(config.liveReload ? browserSync.stream({match: '**/*.css'}) : gutil.noop())
    .pipe(notify({
      title: "SASS Compiled",
      message: "All SASS files have been recompiled to CSS.",
      onLast: true
    }));
});

////////////////////////////////////////////////////////////////////////////////
// Compile Javascript
////////////////////////////////////////////////////////////////////////////////

gulp.task('js', function (){
  gulp.src(['./dev/js/*.js', './dev/js/**/*.js'])
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./js'))
    .pipe(notify({
      title: "JS Minified",
      message: "All JS files have been minified.",
      onLast: true
    }));
});

////////////////////////////////////////////////////////////////////////////////
// Browser Sync
////////////////////////////////////////////////////////////////////////////////

gulp.task('browser-sync', function() {
  browserSync.init({
    port: config.browserSync.port,
    proxy: config.browserSync.hostname,
    open: config.browserSync.openAutomatically
  });
});

////////////////////////////////////////////////////////////////////////////////
// Default Task
////////////////////////////////////////////////////////////////////////////////

gulp.task('default', function(){

  if (config !== null) {
    if (config.liveReload) {
      gulp.start(['browser-sync']);
    }

    if (config.compileScss) {
      gulp.start('sass');
      gulp.watch(['./dev/scss/*.scss', './dev/scss/**/*.scss'], ['sass']);
    }

    if (config.compileJs) {
      gulp.start('js');
      gulp.watch(['./dev/js/*.js', './dev/js/**/*.js'], ['js']);
    }
  }
});
