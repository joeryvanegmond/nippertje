git pull origin main

# cp -r source/public .

# rm -rf source/public

# cp -r .temp/index.php public

# mkdir source/public

# cp public/mix-manifest.json source/public/

# cd source

php composer.phar install --optimize-autoloader --no-dev

php artisan migrate --force

php artisan config:cache

php artisan event:cache

php artisan route:cache

php artisan view:cache

php artisan optimize