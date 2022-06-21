echo "Tablda:"

cd ./main
composer install
npm install
chmod -R 777 storage/
chmod -R 777 bootstrap/
cd ./..

php ./main/artisan make:database app_sys mysql_empty 2>&1
php ./main/artisan make:database app_data mysql_empty 2>&1
php ./main/artisan make:database app_correspondence mysql_empty 2>&1
php ./main/artisan migrate
php ./main/artisan db:seed
rm ./main/public/storage
php ./main/artisan storage:link

echo "Tablda Ready!"



echo "E3C:"

cd ./e3c
composer install
npm install
chmod -R 777 storage/
chmod -R 777 bootstrap/
cd ./..
rm ./e3c/public/storage
php ./e3c/artisan storage:link

echo "E3C Ready!"



echo "WID:"

cd ./tablda_apps/WID
composer install
npm install
chmod -R 777 storage/
chmod -R 777 bootstrap/
cd ./../..
rm ./tablda_apps/WID/public/storage
php ./tablda_apps/WID/artisan storage:link

echo "WID Ready!"



echo "DPoSS:"

cd ./tablda_apps/general/calcs/DPoSS
composer install
npm install
chmod -R 777 storage/
chmod -R 777 bootstrap/
cd ./../../../..
rm ./tablda_apps/general/calcs/DPoSS/public/storage
php ./tablda_apps/general/calcs/DPoSS/artisan storage:link

echo "DPoSS Ready!"