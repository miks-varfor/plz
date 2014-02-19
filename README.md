# PLZ - Student organization event and membership manager webapp

## Installation instructions

Install MySQL. Create a database and a user for that database with all privileges. E.g.:

    CREATE DATABASE members;
	CREATE USER 'members_user'@'localhost' IDENTIFIED BY 'somepass';
	GRANT ALL ON members.* to 'members_user'@'localhost';
	
Use docs/create_tables.sql to create the database tables. E.g.:

    $> mysql -umembers_user -psomepass members < create_tables.sql
	
This will also insert an initial user with administrator privileges with username 'admin' and password 'testi'. Remember to remove this user in production use.
	
Configure the database name, database user and password in app/config/database.php.

Install HTTP server, PHP and configure them. Strict standards mode (in PHP >=5.4) is not supported, so something like this should be used in php.ini:

    error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT

Put the root directory (e.g. plz) somewhere accessible by your web server. Change the paths in .htaccess to refer to this location.

Sample Apache config:

    <VirtualHost *:80>
        ServerName plz.local
        ServerAdmin webmaster@myhost.fi
        DocumentRoot "/srv/plz"

        <Directory />
                Options +Indexes +FollowSymLinks +MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
                RewriteEngine on
                DirectoryIndex index.php index.html
        </Directory>

		LogLevel warn
        ServerSignature On
        ErrorLog /var/log/apache2/plz-error.log
        CustomLog /var/log/apache2/plz-access.log combined

	</VirtualHost>

