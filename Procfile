web:  vendor/bin/heroku-php-nginx -C nginx.conf  -F fpm_custom.conf public/
# web: heroku-php-apache2 public/
release: bin/console importmap:install && bin/console asset-map:compile && bin/console d:m:m -n --allow-no-migration &&  bin/console fos:js-routing:dump --format=js --target=public/js/fos_js_routes.js --callback="export default"

