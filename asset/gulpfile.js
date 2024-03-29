'use strict';

const babel = require('gulp-babel');
const concat = require('gulp-concat');
const {parallel, src, dest, watch} = require('gulp');
const sass = require('gulp-dart-sass');
const terser = require('gulp-terser');

const prism = [
    'core',
    'markup',
    'css',
    'clike',
    'markup-templating',
    'javascript',
    'apacheconf',
    'bash',
    'batch',
    'css-extras',
    'diff',
    'docker',
    'git',
    'handlebars',
    'http',
    'ini',
    'json',
    'less',
    'makefile',
    'markdown',
    'nginx',
    'php',
    'php-extras',
    'powershell',
    'properties',
    'puppet',
    'sass',
    'scss',
    'smarty',
    'sql',
    'twig',
    'vim',
    'yaml'
];

function images() {
    return src('img/*')
        .pipe(dest('../img/'));
}

function js() {
    let prismComponents = [];
    prism.forEach(component => prismComponents.push('node_modules/prismjs/components/prism-' + component + '.js'));

    return src(
        prismComponents.concat(
            [
                'node_modules/prismjs/plugins/normalize-whitespace/prism-normalize-whitespace.js',
                'node_modules/prismjs/plugins/line-highlight/prism-line-highlight.js',
                'node_modules/prismjs/plugins/line-numbers/prism-line-numbers.js',
                'node_modules/prismjs/plugins/treeview/prism-treeview.js',
                'node_modules/jquery/dist/jquery.js',
                'node_modules/popper.js/dist/umd/popper.js',
                'node_modules/popper.js/dist/umd/popper-utils.js',
                'node_modules/bootstrap/dist/js/bootstrap.js',
                'node_modules/anchor-js/anchor.js',
                'node_modules/choices.js/public/assets/scripts/choices.js',
                'js/component-list.js',
                'js/base.js'
            ]
        )
    )
        .pipe(babel({presets: ['@babel/env'], sourceType: 'unambiguous'}))
        .pipe(concat({path: 'scripts.js'}))
        .pipe(terser({mangle: false}).on('error', function (e) {
            console.log(e);
        }))
        .pipe(dest('../js/'));
}

function css() {
    return src('sass/styles.scss')
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(concat({path: 'styles.css'}))
        .pipe(dest('../css/'));
}

exports.js = js;
exports.css = css;
exports.images = images;
exports.watch = () => {
    watch('js/**/*.js', js);
    watch('sass/**/*.scss', css);
};
exports.default = (done) => {
    parallel(js, css, images)(done);
};
