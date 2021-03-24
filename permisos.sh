sudo chown -R alfonso:www-data /home/alfonso/www/Adopool/Website
find /home/alfonso/www/Adopool/Website -type d -exec chmod 775 {} \;
find /home/alfonso/www/Adopool/Website -type f -exec chmod 775 {} \;

sudo chown -R alfonso:www-data /var/www/Adopool/Website
find /var/www/Adopool/Website -type d -exec chmod 775 {} \;
find /var/www/Adopool/Website -type f -exec chmod 775 {} \;


find /var/www/disenoadopool -type d -exec chmod 775 {} \;
find /var/www/disenoadopool -type f -exec chmod 775 {} \;

find /home/alfonso/www/disenoadopool -type d -exec chmod 775 {} \;
find /home/alfonso/www/disenoadopool -type f -exec chmod 775 {} \;


