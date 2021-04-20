# TaBLDa-vue
Requirements:
PHP version ^7.2
MySQL version ^3.1
Python version ^3.6

INSTALL SYSTEM:
> sudo add-apt-repository ppa:ondrej/apache2
> sudo add-apt-repository ppa:ond.erej/php
> sudo apt-get update
> sudo apt-get upgrade
> sudo apt-get update
> sudo apt-get install -y php7.2
> sudo apt-get install -y python3.6
> sudo a2enmod php7.2
> sudo a2enmod ssl
> sudo a2enmod rewrite
> sudo service apache2 restart
> sudo apt install mysql-server
> sudo mysql_secure_installation



Installation:
1 - Clone project.
2 - Fill your .env file.
3 - Create needed databases (app_sys, app_data)
4 - run console commands:
    > composer install
    > npm install
    > npm run production
    > php artisan migrate
    > php artisan db:seed
    > php artisan storage:link
All done!


For activate Auto DropBox Backup:
1 - fill in .env settings something like this:

BACKUP_TO_DBOX=true
DUMP_ALL_DB_USER=dumpmsqldataallbases
DBOX_ADMIN_API=ei0TqpAAAAAAAAD70SXjNwA3L6YWvCheE2ZBB9GyRpzSxuVgcbgw3hi8MNEw
DBOX_UPLOADER_FILE=/var/www/dbox/dropbox_uploader.sh

2 - create user in mysql with name = env(DUMP_ALL_DB_USER) and rights to read all databases.

3 - copy /storage/dropbox_uploader.sh to the path DBOX_UPLOADER_FILE and CHMOD to execute.
(https://github.com/andreafabrizi/Dropbox-Uploader).


STIM MA JSON:
For drilling table should be filled 'Formula Symbol' as properties.
'Field Links' with 'link_type' === 'Record' will be drilled as inherit tables.
CorrespondenceTables define 'drill' rejections (or CorrespondenceFields for Fields rejections).



APACHE:
<Directory /var/www/html>
    Options Indexes FollowSymlinks
    AllowOverride All
    Require all granted
</Directory>


<VirtualHost *:80>
        ServerName tablda.com
        ServerAlias *.tablda.com

        RewriteEngine On
        RewriteCond %{HTTPS} !=on
        RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>


<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile "/etc/apache2/tablda.com.crt"
    SSLCertificateKeyFile "/etc/apache2/tablda.com.key"
    SSLCertificateChainFile "/etc/apache2/tablda.com.bundle.crt"
        SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1

        ServerName e3c.tablda.com

        DocumentRoot /var/www/html/e3c/public

        Alias /_tablda_apps /var/www/html/e3c/public

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>


<VirtualHost *:443>
    SSLEngine on
    SSLCertificateFile "/etc/apache2/tablda.com.crt"
    SSLCertificateKeyFile "/etc/apache2/tablda.com.key"
    SSLCertificateChainFile "/etc/apache2/tablda.com.bundle.crt"
        SSLProtocol all -SSLv2 -SSLv3 -TLSv1 -TLSv1.1

        ServerName tablda.com
        ServerAlias *.tablda.com

        DocumentRoot /var/www/html/home/public

        Alias /_tablda_apps/WID /var/www/html/tablda_apps/WID/public

        Alias /_external_api_ /var/www/html/external_api

        Alias /phpmyadmin /var/www/html/phpmyadmin

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

