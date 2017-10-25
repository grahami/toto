# Toto - A Laravel sample application</p>

## About Toto

Toto is a sample application to demonstrate a possible approach to building a core funcctionality processing engine that can be accessed from multiple front ends using a RESTful API and based on laravel 5.5 (LTS).

The code based provides a very small web app, rendered using Laravel blades, that in turn make use fo the RESTful API to provide some functionality. The default root URI provides a list of the the two functions currently implemented as well as some information about the usage of the functionality.

The RESTful API can be accessed from any client, such as the sample web, the Postman Rest Client, an Angular web app, etc.

A sample database can be created using the scripts in the "sql" folder which populates a very small subset of the IPaddress and location data as provided by MaxMind (https://www.maxmind.com/en/home). This data can then be interrogated to find an IP address record that covers the range of the given IP Address, and from that details of the location for that IP Address, to city level, or by Latitude and Longitude with an appropriate radius of accuracy.

Although the data is currently in a MySQL database, data access in the MVC model makes use of a Repository design pattern so that the model need have no knowledge of the mechanism by which data is stored or retrieved. This allows functionality such as database caching (Redis or Memchaced), NoSQL access or indeed any appropriate persisted data access, providing that the appropriate repository class fulfils the repository contract.

As a worked example of making use of persisted backend functionality in a transparent way to the model, the customFind method makes use fo a MySQL stored procedure in order to make use of the MySQL support for IPV4 and IPV6 functions although the sample data does not have any IPV6 records currently. 

The Repository pattern also allows for optional validator classes to be created so that all model data validation is automatically handled before data is persisted. While sample validators are included in the code base, the Create, Update and Delete methods are currently simple stubs and not fully implemented.


## Environment and Assumptions
This application was developed using a Linux back end VM (Ubuntu 16.04) under VMWare Workstation. The following provides a summary of the environment but the code should work properly in any appropriately configured hosting environment.

### Nginx
Install and configure Nginx

sudo apt-get install nginx

in /etc/nginx/sites-available/default, modify line
 
    root /var/www/html;

to be

    root /var/www/html/toto/public;


modify section starting "location / {"

add  

	index index.html index.php;
	
modify

 	try_files $uri $uri/ /index.php?$args;

as a new section, add

     location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
 
### PHP 7
Install and configure PHP 7 (minimal modules listed)

    sudo apt-get install php7.0 php7.0-fpm php7.0-mysql php7.0-zip php7.0-mbstring php7.0-xml

in /etc/php/7.0/fpm/pool.d/www.conf

comment out the line

    ;listen = /run/php/php7.0-fpm.sock

add the line

    listen = 127.0.0.1:9000

### MySQL
Install and configure MySQL Server

    sudo apt-get install mysql-server
Add global user permissions. Connect to MySQL using root password from installation
 
    mysql -uroot -p

    CREATE USER 'toto'@'%' IDENTIFIED BY 'sample';
    GRANT ALL PRIVILEGES ON *.* TO 'toto'@'%' WITH GRANT OPTION;

Restart services to apply configuration changes

    sudo service nginx restart 
    sudo service php7.0-fpm restart

### Composer
Install composer and make globally available (as non root user)

    cd ~
    mkdir composer
    cd composer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    su mv composer.phar /usr/local/bin/composer



### Application code configuration
Get the code and initialise Laravel environment and config

#### The .env file
The .env file generally contains information that is not appropriate to commit into the SVN as is also environment specific.
 
In the /var/www/html/toto directory, create a .env file that contains values such as the following :

    APP_NAME=Toto	
    APP_ENV=local
    APP_KEY=base64:qwWZ4827MkPaGnQzO/FnKSGS4YCK+qnVx3XiZluYpBc=
    APP_DEBUG=true
    APP_LOG_LEVEL=debug
    APP_URL=http://localhost

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=toto
    DB_USERNAME=toto
    DB_PASSWORD=sample

    BROADCAST_DRIVER=log
    CACHE_DRIVER=file
    SESSION_DRIVER=file
    QUEUE_DRIVER=sync

    REDIS_HOST=127.0.0.1
    REDIS_PASSWORD=null
    REDIS_PORT=6379

    MAIL_DRIVER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null

    PUSHER_APP_ID=
    PUSHER_APP_KEY=
    PUSHER_APP_SECRET=

    EVENT_LOG_PATH=/mnt/hgfs/toto/toto/storage/events
    EVENT_LOG_LEVEL=W

    REPOSITORY_CACHE_ENABLED=false
    REPOSITORY_REDIS_CLEAN=true
    REPOSITORY_CACHE_EXPIRY=30

#### Laravel environment installation and configuration
In the /var/www/html/toto directory 
 
    composer update
    composer dump-autoload
    php artisan clear-compiled
    php artisan optimize
    php artisan config:cache

