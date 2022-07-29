const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
var rtlcss = require('gulp-rtlcss');
var concat = require('gulp-concat');



// css Task
gulp.task('compile:css', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/menu_mobile.css')
        .pipe(gulp.dest('css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css'));

});
// pushCss Task
gulp.task('compile:pushCss', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/libs/push.css')
        .pipe(gulp.dest('css/libs'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css/libs'));

});
// pushCssrtl Task

gulp.task('compile:pushCssrtl', function() {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/libs/push.css')
        .pipe(rtlcss())
        .pipe(rename({ suffix: '_RTL' }))
        .pipe(gulp.dest('css/libs'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css/libs'));
});

// rtlcss Task
gulp.task('compile:rtl', function() {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/menu_mobile.css')
        .pipe(rtlcss())
        .pipe(rename({ suffix: '_RTL' }))
        .pipe(gulp.dest('css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css'));
});
gulp.task('compile:js', () => {
    return gulp
        .src('js/rotate360.js')
        // .pipe(concat('js/rotate360.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});

gulp.task('watch:rtlcss', () => {
    gulp.watch('css/menu_mobile.css', ['compile:rtl']);
});

gulp.task('watch:css', () => {
    gulp.watch('css/menu_mobile.css', ['compile:css']);
});
gulp.task('watch:pushCss', () => {
    gulp.watch('css/libs/push.css', ['compile:pushCss']);
});

gulp.task('watch:pushCssrtl', () => {
    gulp.watch('css/libs/push.css', ['compile:pushCssrtl']);
});
gulp.task('watch:js', () => {
    gulp.watch('js/rotate360.js', ['compile:js']);
});

gulp.task('default', [
    'watch:css',
    'watch:rtlcss',
    'watch:pushCss',
    'watch:pushCssrtl',
    'watch:js'
 
]);
