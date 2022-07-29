#!/bin/bash
GIT_MYSQL=~/projects/expandcart/OnlineStores/database
for T in `/Applications/XAMPP/xamppfiles/bin/mysql -u root -N -B -e 'show tables from ashawqy_expandcart'`;
do
    echo "Backing up $T"
    /Applications/XAMPP/xamppfiles/bin/mysqldump --skip-comments --compact -u root ashawqy_expandcart $T > $GIT_MYSQL/$T.sql
done;




cat <(echo "SET NAMES 'utf8';SET CHARACTER SET utf8;CREATE DATABASE testec;") | /Applications/XAMPP/xamppfiles/bin/mysql -u root
&& cat <(echo "SET NAMES 'utf8';SET CHARACTER SET utf8;SET FOREIGN_KEY_CHECKS=0;") ~/projects/expandcart/OnlineStores/database/*.sql <(echo "SET FOREIGN_KEY_CHECKS=1;") | /Applications/XAMPP/xamppfiles/bin/mysql -u root testec