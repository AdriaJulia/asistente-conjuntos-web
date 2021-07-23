sudo chown -R alfonso:www-data /home/alfonso/www/Aodpool/Website
find /home/alfonso/www/Aodpool/Website -type d -exec chmod 775 {} \;
find /home/alfonso/www/Aodpool/Website -type f -exec chmod 775 {} \;

sudo chown -R alfonso:www-data /var/www/Aodpool/Website
find /var/www/Aodpool/Website -type d -exec chmod 775 {} \;
find /var/www/Aodpool/Website -type f -exec chmod 775 {} \;


find /var/www/disenoAodpool -type d -exec chmod 775 {} \;
find /var/www/disenoAodpool -type f -exec chmod 775 {} \;

find /home/alfonso/www/disenoAodpool -type d -exec chmod 775 {} \;
find /home/alfonso/www/disenoAodpool -type f -exec chmod 775 {} \;


