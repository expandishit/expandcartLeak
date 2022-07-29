var gulp = require('gulp'),
    concat = require('gulp-concat'),
    prefix = require('gulp-autoprefixer'),
    sass = require('gulp-sass')(require('sass')),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglify'),
    notifier = require('node-notifier'),
    cleanCSS = require('gulp-clean-css');
; 
const { watch, series } = require('gulp');

gulp.task('SassCompile', function () {   
    return gulp.src(['content/sass/main-ltr.scss','content/sass/main-rtl.scss'])
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'compressed' }).on('error',function(err){
            console.log(`-----------------------------------------------------------------`);
            console.log(err.message);
            console.log(`-----------------------------------------------------------------`);
             
            notifier.notify(
                {
                  title: 'Sass Error Compiling',
                  message: `Error in File : ${err.relativePath} \nError in Line : ${err.line} , Column : ${err.column} `,
                  sound: false, 
                  wait: false,
                  timeout: 1
                },
                function(err, response) {
                  // Response is response from notification
                }
              );
            this.emit('end');
        }))
        .pipe(prefix('last 2 versions'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('content/css'));
});

gulp.task('concatMinifyAllLtrCss', function () {
  return gulp.src([
   'content/css/vendor/ltr/bootstrapLTR.min.css',
   'content/css/vendor/*.css',
   'content/css/main-ltr.css'
  ])
  .pipe(sourcemaps.init())
      .pipe(concat('ltrMinStyle.min.css'))
      .pipe(cleanCSS())
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest('content/css/minifiedStyles'));
});

gulp.task('concatMinifyAllRtlCss', function () {
  return gulp.src([
   'content/css/vendor/rtl/bootstrapRTL.min.css',
   'content/css/vendor/*.css',
   'content/css/main-rtl.css' 
  ])
  .pipe(sourcemaps.init())
      .pipe(concat('rtlMinStyle.min.css'))
      .pipe(cleanCSS())
      .pipe(sourcemaps.write('.'))
      .pipe(gulp.dest('content/css/minifiedStyles'));
});
 gulp.task('jsCompress', function () { 
        return gulp.src([
            'content/scripts/vendor/core-js.js',
            // 'content/scripts/vendor/jquery-3.6.0.min.js',
            // 'content/scripts/vendor/bootstrap.bundle.min.js',
            'content/scripts/vendor/font-awesome.js',
            'content/scripts/pages/*.js',
            'content/scripts/main.js',
            ])
         .pipe(concat('scripts.min.js'))
         .pipe(uglify()) 
         .pipe(gulp.dest('content/scripts/MinifiedJs'));
 }); 

exports.default = function() {
  // You can use a single task
  watch('src/*.css', css);
  // Or a composed task
  watch('src/*.js', series(clean, javascript));
};

gulp.task('watch', function () {
  gulp.watch(['content/sass/*.scss',
              'content/sass/*/*.scss',
              'content/scripts/pages/*.js',
              'content/scripts/main.js'],
  gulp.series(['SassCompile','concatMinifyAllLtrCss','concatMinifyAllRtlCss','jsCompress']));
});
