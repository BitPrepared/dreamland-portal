#!/bin/bash

echo "clean"
rm -rf public/assets/css/
rm -rf public/assets/js/dist/
rm -rf .sass-cache

#echo "update"
#bower update

echo "stylesheet"
compass compile

echo "copy resources"
mkdir -p public/assets/img public/assets/js public/assets/js/dist public/assets/css/dist

echo "js"
#cp -rv src/js/ public/js/

echo "dist js"

# viene preso quello di ui-bootstrap
#cp bower_components/bootstrap-sass-official/assets/javascripts/bootstrap.js public/js/dist/

cp bower_components/jquery/dist/jquery.min.js public/assets/js/dist/
cp bower_components/jquery/dist/jquery.min.map public/assets/js/dist/ 
cp bower_components/angular/angular.min.js public/assets/js/dist/ 
cp bower_components/angular/angular.min.js.map public/assets/js/dist/ 
cp bower_components/angular-i18n/angular-locale_it-it.js public/assets/js/dist/ 
cp bower_components/angular-bootstrap/*.min.js public/assets/js/dist/
cp bower_components/angular-sanitize/angular-sanitize.min.js public/assets/js/dist/
cp bower_components/angular-sanitize/angular-sanitize.min.js.map public/assets/js/dist/
cp bower_components/angular-i18n/*.js public/assets/js/dist/ 
cp bower_components/ngDialog/js/ngDialog.min.js public/assets/js/dist/ 
# cp bower_components/angular-route/angular-route.min.js public/assets/js/dist/
# cp bower_components/angular-route/angular-route.min.js.map public/assets/js/dist/
cp bower_components/angular-ui-router/release/angular-ui-router.min.js public/assets/js/dist/ 
cp bower_components/angular-underscore/*.min.js public/assets/js/dist/ 
cp bower_components/underscore/underscore-min.js public/assets/js/dist/ 
cp bower_components/underscore/underscore-min.map public/assets/js/dist/ 
cp bower_components/angular-animate/*.min.js public/assets/js/dist/
cp bower_components/angular-animate/*.min.js.map public/assets/js/dist/
cp bower_components/angular-busy/dist/*.js public/assets/js/dist/
cp bower_components/moment/min/moment-with-locales.min.js public/assets/js/dist/

cp bower_components/tinysort/dist/jquery.tinysort.min.js public/assets/js/dist/

# OLD
#cp bower_components/angular-http-auth/src/*.js public/js/dist/ 

echo "dist css"
cp -r bower_components/bootstrap-sass-official/assets/fonts/bootstrap public/assets/css/
cp bower_components/angular-busy/dist/*.min.css public/assets/css/dist/
cp bower_components/ngDialog/css/ngDialog.min.css public/assets/css/dist/ 
cp bower_components/ngDialog/css/ngDialog-theme-plain.min.css public/assets/css/dist/ 
cp bower_components/ngDialog/css/ngDialog-theme-default.min.css public/assets/css/dist/ 