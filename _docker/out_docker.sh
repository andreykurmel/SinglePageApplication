echo "Tablda:"
git clone git@github.com:flyingcow1999/TablDA.git ./www/main 2>&1

echo "E3C:"
git clone git@github.com:flyingcow1999/E3C_tablda.git ./www/e3c 2>&1

echo "TIA-222:"
git clone git@github.com:flyingcow1999/TIA-222.git ./www/external_api/tia_222 2>&1

echo "WID:"
git clone git@github.com:andreykurmel/tablda-WID.git ./www/tablda_apps/WID 2>&1

echo "DPoSS:"
git clone git@github.com:flyingcow1999/DPoSS.git ./www/tablda_apps/general/calcs/DPoSS 2>&1

echo "Permissions:"
sudo chmod -R 777 ./www/main/storage/
sudo chmod -R 777 ./www/main/bootstrap/
sudo chmod -R 777 ./www/e3c/storage/
sudo chmod -R 777 ./www/e3c/bootstrap/
sudo chmod -R 777 ./www/tablda_apps/WID/storage/
sudo chmod -R 777 ./www/tablda_apps/WID/bootstrap/
sudo chmod -R 777 ./www/tablda_apps/general/calcs/DPoSS/storage/
sudo chmod -R 777 ./www/tablda_apps/general/calcs/DPoSS/bootstrap/