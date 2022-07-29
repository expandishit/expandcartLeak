# system commands
sudo apt-get update

# apache commands
sudo apt-get install -y language-pack-en-base
export LC_ALL=en_US.UTF-8
export LANG=en_US.UTF-8
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo add-apt-repository ppa:ondrej/apache2
sudo apt-get update
sudo apt-get install -y apache2 git curl php7.2 php7.2-bcmath php7.2-bz2 php7.2-cli php7.2-curl php7.2-intl php7.2-json php7.2-mbstring php7.2-opcache php7.2-soap php7.2-sqlite3 php7.2-xml php7.2-xsl php7.2-zip php7.2-mysql php7.2-gd libapache2-mod-php7.2

# Configure Apache
sudo echo "<VirtualHost *:80>
    DocumentRoot /var/www/rising-comercio/OnlineStores
    AllowEncodedSlashes On
    <Directory /var/www/rising-comercio/OnlineStores>
        Options +Indexes +FollowSymLinks
        DirectoryIndex index.php index.html
        Order allow,deny
        Allow from all
        AllowOverride All
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
sudo a2enmod rewrite
sudo a2enmod expires
sudo a2enmod headers
sudo a2enmod include

# sudo service apache2 restart


# mysql
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'

sudo apt-get install -y mysql-server
sudo mysqladmin -uroot -proot create expandcart
cat /var/www/rising-comercio/OnlineStores/database/*.sql | mysql -h 127.0.0.1 -u root -proot -D expandcart --init-command="SET SESSION FOREIGN_KEY_CHECKS = 0;"
cat /var/www/rising-comercio/Config/marketplace.sql | mysql -h 127.0.0.1 -u root -proot -D expandcart --init-command="SET SESSION FOREIGN_KEY_CHECKS = 0;"

# sudo apt-get install -y php-mysql


sudo cp php.ini php.ini.bak

#sed -i 's,^post_max_size =.*$,post_max_size = 2048M,' php.ini
#sed -i 's,^post_max_size =.*$,post_max_size = 2048M,' php.ini
#sudo echo "extension=mysqli" > /etc/php/7.2/mods-available/mysqli.ini
#sudo echo "extension=mbsting" > /etc/php/7.2/mods-available/mbsting.ini
#sudo phpenmod mysqli
#sudo phpenmod mbsting
sudo phpenmod curl
sudo phpenmod fileinfo

# sudo systemctl restart apache2
sudo service apache2 restart

sudo mkdir -p /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/logs
sudo mkdir -p /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/downloads
sudo mkdir -p /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/cache
sudo mkdir -p /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/customtemplates
sudo mkdir -p /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/temp
sudo cp -Rv /var/www/rising-comercio/OnlineStores/image/SourceImage/ /var/www/rising-comercio/OnlineStores/ecdata/stores/QAZ123/image


