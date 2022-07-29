
// Include gulp
var gulp = require('gulp');


// Include our plugins
var jshint = require('gulp-jshint');
var less = require('gulp-less');
var minifyCss = require('gulp-clean-css');
var rtlcss = require('gulp-rtlcss');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');


// Lint task
gulp.task('lint', function() {
    return gulp
        .src('assets/js/core/app.js')                 // lint core JS file. Or specify another path
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});


// Compile less files of a full version
gulp.task('less_full_LTR', function() {
    return gulp
        .src('assets/less/LTR/_main_full/*.less')         // locate /less/ folder root to grab 4 main files
        .pipe(less())                                 // compile
        .pipe(gulp.dest('assets/css/LTR'))                // destination path for normal CSS
        .pipe(minifyCss({                             // minify CSS
            keepSpecialComments: 0                    // remove all comments
        }))
        .pipe(rename({                                // rename file
            suffix: ".min"                            // add *.min suffix
        }))
        .pipe(gulp.dest('assets/css/LTR'));               // destination path for minified CSS
});

// Compile less files of starter kit
gulp.task('less_starters_LTR', function() {
    return gulp
        .src('assets/less/LTR/_main_starters/*.less')     // locate /less/ folder root to grab 4 main files
        .pipe(less())                                 // compile
        .pipe(gulp.dest('starters/assets/css/LTR'))       // destination path for normal CSS
        .pipe(minifyCss({                             // minify CSS
            keepSpecialComments: 0                    // remove all comments
        }))
        .pipe(rename({                                // rename file
            suffix: ".min"                            // add *.min suffix
        }))
        .pipe(gulp.dest('starters/assets/css/LTR'));      // destination path for minified CSS
});

// Compile less files of a full version
gulp.task('less_full_RTL', function() {
    return gulp
        .src('assets/less/RTL/_main_full/*.less')         // locate /less/ folder root to grab 4 main files
        .pipe(less())                                 // compile
        .pipe(gulp.dest('assets/css/RTL'))                // destination path for normal CSS
        .pipe(rtlcss())                               // Generate RTL layout styles
        .pipe(gulp.dest('assets/css/RTL'))                // Set destination path
        .pipe(minifyCss({                             // minify CSS
            keepSpecialComments: 0                    // remove all comments
        }))
        .pipe(rename({                                // rename file
            suffix: ".min"                            // add *.min suffix
        }))
        .pipe(gulp.dest('assets/css/RTL'));               // destination path for minified CSS
});


// Compile less files of starter kit
gulp.task('less_starters_RTL', function() {
    return gulp
        .src('assets/less/RTL/_main_starters/*.less')     // locate /less/ folder root to grab 4 main files
        .pipe(less())                                 // compile
        .pipe(gulp.dest('starters/assets/css/RTL'))       // destination path for normal CSS
        .pipe(rtlcss())                               // Generate RTL layout styles
        .pipe(gulp.dest('starters/assets/css/RTL'))       // Set destination path
        .pipe(minifyCss({                             // minify CSS
            keepSpecialComments: 0                    // remove all comments
        }))
        .pipe(rename({                                // rename file
            suffix: ".min"                            // add *.min suffix
        }))
        .pipe(gulp.dest('starters/assets/css/RTL'));               // destination path for minified CSS
});


// Minify template's core JS file
gulp.task('minify_core', function() {
    return gulp
        .src('assets/js/core/app.js')                 // path to app.js file
        .pipe(uglify())                               // compress JS
        .pipe(rename({                                // rename file
            suffix: ".min"                            // add *.min suffix
        }))
        .pipe(gulp.dest('assets/js/core/'));          // destination path for minified file
});


// Watch files for changes
gulp.task('watch', function() {
    gulp.watch('assets/js/core/app.js', [             // listen for changes in app.js file and automatically compress
        'lint',                                       // lint
        //'concatenate',                              // concatenate & minify JS files (uncomment if in use)
        'minify_core'                                 // compress app.js
    ]);
    gulp.watch('assets/less/**/*.less', ['less_full_LTR', 'less_starters_LTR', 'less_full_RTL', 'less_starters_RTL']);    // listen for changes in all LESS files and automatically re-compile
});


// Default task
gulp.task('default', [                                // list of default tasks
    'lint',                                           // lint
    'less_full_LTR',                                      // full version less compile
    'less_starters_LTR',                                  // starter kit less compile
    'less_full_RTL',                                      // full version less compile
    'less_starters_RTL',                                  // starter kit less compile
    'minify_core',                                    // compress app.js
    'watch'                                           // watch for changes
]);