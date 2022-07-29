const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
var rtlcss = require('gulp-rtlcss');
var concat = require('gulp-concat');
/*=============================================
=            Section wavescss (CSS) Task            =
=============================================*/
// path welldone/vendor/waves/waves.css
gulp.task('compile:wavescss', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('vendor/waves/waves.css')
        .pipe(gulp.dest('vendor/waves/'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('vendor/waves/'));
});
/*=====  End of Section wavescss (CSS) Task  ======*/
/*=============================================
=            Section fontstyle (CSS) Task            =
=============================================*/
// path welldone/font/style.css
gulp.task('compile:fontstyle', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('font/style.css')
        .pipe(gulp.dest('font'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('font'));
});
/*=====  End of Section fontstyle (CSS) Task  ======*/
/*=============================================
=            Section custom Ltr (CSS) Task            =
=============================================*/
// path welldone/css/custom.css
gulp.task('compile:cssLtr', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/custom.css')
        .pipe(gulp.dest('css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css'));
});
/*=====  End of Section custom Ltr (CSS) Task  ======*/
/*=============================================
=            Section custom Rtl (CSS) Task            =
=============================================*/
// path welldone/custom-RTL.css
gulp.task('compile:cssRtl', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('css/custom-RTL.css')
        .pipe(gulp.dest('css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('css'));
});
/*=====  End of Section custom Rtl (CSS) Task  ======*/
/*=============================================
=            Section settingsCss (CSS) Task            =
=============================================*/
// path welldone/vendor/rs-plugin/css/settings.css
gulp.task('compile:settingsCss', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('vendor/rs-plugin/css/settings.css')
        .pipe(gulp.dest('vendor/rs-plugin/css'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('vendor/rs-plugin/css'));
});
/*=====  End of Section settingsCss (CSS) Task  ======*/
/*=============================================
=            Section colorboxCss (CSS) Task            =
=============================================*/
// path welldone/js/jquery/colorbox/colorbox.css
gulp.task('compile:colorboxCss', () => {
    let plugins = [
        autoprefixer({
            browsers: ['last 10 versions'],
            cascade: true
        }),
        cssnano()
    ];
    return gulp
        .src('js/jquery/colorbox/colorbox.css')
        .pipe(gulp.dest('js/jquery/colorbox'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(postcss(plugins))
        .pipe(gulp.dest('js/jquery/colorbox'));

});
/*=====  End of Section colorboxCss (CSS) Task  ======*/
/*=============================================
=            Section customjs (JS) Task            =
=============================================*/
// path welldone/js/custom.js
gulp.task('compile:customjs', () => {
    return gulp
        .src('js/custom.js')
        .pipe(concat('custom.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customjs (JS) Task  ======*/
/*=============================================
=            Section commonjs (JS) Task            =
=============================================*/
// path welldone/js/common.js
gulp.task('compile:commonjs', () => {
    return gulp
        .src('js/common.js')
        .pipe(concat('common.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section commonjs (JS) Task  ======*/
/*=============================================
=            Section photoswipeSlider (JS) Task            =
=============================================*/
// path welldone/js/photoswipe-slider.js
gulp.task('compile:photoswipeSlider', () => {
    return gulp
        .src('js/photoswipe-slider.js')
        .pipe(concat('photoswipe-slider.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section photoswipeSlider (JS) Task  ======*/
/*=============================================
=            Section modernizrJS (JS) Task            =
=============================================*/
// path welldone/vendor/modernizr/modernizr.js
gulp.task('compile:modernizrJS', () => {
    return gulp
        .src('vendor/modernizr/modernizr.js')
        .pipe(concat('modernizr.js'))
        .pipe(gulp.dest('vendor/modernizr'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('vendor/modernizr'));
});
/*=====  End of Section modernizrJS (JS) Task  ======*/
/*=============================================
=            Section tabsJs (JS) Task          =
=============================================*/
// path welldone/vendor/modernizr/modernizr.js
gulp.task('compile:tabsJs', () => {
    return gulp
        .src('js/jquery/tabs.js')
        .pipe(concat('tabs.js'))
        .pipe(gulp.dest('js/jquery'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js/jquery'));
});
/*=====  End of Section tabsJs (JS) Task  ======*/
/*=============================================
=            Section customlayout1 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout1.js
gulp.task('compile:customlayout1', () => {
    return gulp
        .src('js/custom-layout1.js')
        .pipe(concat('custom-layout1.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout1 (JS) Task  ======*/

/*=============================================
=            Section customlayout2 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout2.js
gulp.task('compile:customlayout2', () => {
    return gulp
        .src('js/custom-layout2.js')
        .pipe(concat('custom-layout2.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout2 (JS) Task  ======*/
/*=============================================
=            Section customlayout3 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout3.js
gulp.task('compile:customlayout3', () => {
    return gulp
        .src('js/custom-layout3.js')
        .pipe(concat('custom-layout3.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout3 (JS) Task  ======*/
/*=============================================
=            Section customlayout4 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout4.js
gulp.task('compile:customlayout4', () => {
    return gulp
        .src('js/custom-layout4.js')
        .pipe(concat('custom-layout4.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout4 (JS) Task  ======*/
/*=============================================
=            Section customlayout5 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout5.js
gulp.task('compile:customlayout5', () => {
    return gulp
        .src('js/custom-layout5.js')
        .pipe(concat('custom-layout5.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout5 (JS) Task  ======*/
/*=============================================
=            Section customlayout6 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout6.js
gulp.task('compile:customlayout6', () => {
    return gulp
        .src('js/custom-layout6.js')
        .pipe(concat('custom-layout6.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout6 (JS) Task  ======*/

/*=============================================
=            Section customlayout7 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout7.js
gulp.task('compile:customlayout7', () => {
    return gulp
        .src('js/custom-layout7.js')
        .pipe(concat('custom-layout7.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout7 (JS) Task  ======*/
/*=============================================
=            Section customlayout8 (JS) Task          =
=============================================*/
// path welldone/js/custom-layout8.js
gulp.task('compile:customlayout8', () => {
    return gulp
        .src('js/custom-layout8.js')
        .pipe(concat('custom-layout8.js'))
        .pipe(gulp.dest('js'))
        .pipe(uglify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('js'));
});
/*=====  End of Section customlayout8 (JS) Task  ======*/

// /////////////////////////////////////////////////////////////////
/*=============================================
=            Section Watch CSS Tasks FILE ALL            =
=============================================*/
gulp.task('watch:wavescss', () => {
    gulp.watch('vendor/waves/waves.css', ['compile:wavescss']);
});
gulp.task('watch:fontstyle', () => {
    gulp.watch('font/style.css', ['compile:fontstyle']);
});
gulp.task('watch:cssLtr', () => {
    gulp.watch('css/custom.css', ['compile:cssLtr']);
});
gulp.task('watch:cssRtl', () => {
    gulp.watch('css/custom-RTL.css', ['compile:cssRtl']);
});
gulp.task('watch:settingsCss', () => {
    gulp.watch('vendor/rs-plugin/css/settings.css', ['compile:settingsCss']);
});
gulp.task('watch:colorboxCss', () => {
    gulp.watch('js/jquery/colorbox/colorbox.css', ['compile:colorboxCss']);
});
/*=====  End of Section Watch CSS Tasks FILE ALL  ======*/
/*=============================================
=            Section Watch JS Tasks FILE ALL            =
=============================================*/
gulp.task('watch:customjs', () => {
    gulp.watch('js/custom.js', ['compile:customjs']);
});
gulp.task('watch:commonjs', () => {
    gulp.watch('js/common.js', ['compile:commonjs']);
});
gulp.task('watch:photoswipeSlider', () => {
    gulp.watch('js/photoswipe-slider.js', ['compile:photoswipeSlider']);
});
gulp.task('watch:modernizrJS', () => {
    gulp.watch('vendor/modernizr/modernizr.js', ['compile:modernizrJS']);
});
gulp.task('watch:tabsJs', () => {
    gulp.watch('js/jquery/tabs.js', ['compile:tabsJs']);
});
gulp.task('watch:customlayout1', () => {
    gulp.watch('js/custom-layout1.js', ['compile:customlayout1']);
});
gulp.task('watch:customlayout2', () => {
    gulp.watch('js/custom-layout2.js', ['compile:customlayout2']);
});
gulp.task('watch:customlayout3', () => {
    gulp.watch('js/custom-layout3.js', ['compile:customlayout3']);
});
gulp.task('watch:customlayout4', () => {
    gulp.watch('js/custom-layout4.js', ['compile:customlayout4']);
});
gulp.task('watch:customlayout5', () => {
    gulp.watch('js/custom-layout5.js', ['compile:customlayout5']);
});
gulp.task('watch:customlayout6', () => {
    gulp.watch('js/custom-layout6.js', ['compile:customlayout6']);
});
gulp.task('watch:customlayout7', () => {
    gulp.watch('js/custom-layout7.js', ['compile:customlayout7']);
});
gulp.task('watch:customlayout8', () => {
    gulp.watch('js/custom-layout8.js', ['compile:customlayout8']);
});

/*=====  End of Section Watch JS Tasks FILE ALL  ======*/
/*=============================================
=            Section  gulp  default  Tasks       =
=============================================*/
gulp.task('default', [
    'watch:wavescss',
    'watch:fontstyle',
    'watch:cssLtr',
    'watch:cssRtl',
    'watch:settingsCss',
    'watch:colorboxCss',
    'watch:customjs',
    'watch:commonjs',
    'watch:photoswipeSlider',
    'watch:modernizrJS',
    'watch:tabsJs',
    'watch:customlayout1',
    'watch:customlayout2',
    'watch:customlayout3',
    'watch:customlayout4',
    'watch:customlayout5',
    'watch:customlayout6',
    'watch:customlayout7',
    'watch:customlayout8',
]);
/*=====  End of Section gulp  default Tasks   ======*/