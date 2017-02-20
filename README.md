Lanapp
===============================
Simple web application for creating and maintaining Local Area Network physical topology map.

[License](../LICENSE.md)

Application is licensed under MIT license

If you have any problems please submit ticket at project page on [GitHub](https://github.com/proenix/lanapp/issues)

[Change Log](../CHANGELOG.md)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```

REQUIREMENTS
-------------------
Required envinroment to install and run Lanapp:
* webserver: apache2 or nginx
* PHP 5.4+ or 7.0+
* MySQL 5.6+
* [composer](https://getcomposer.org/) with "fxp/composer-asset-plugin:^1.2.0"

### Sample configuration files for webservers.
Change `example.com` to domain of your choice and `/path/to/application` to root directory of Lanapp.

#### apache2:
 ```
<VirtualHost frontend.example.com:80>
   ServerName frontend.example.com
   DocumentRoot "/path/to/application/frontend/web"

   <Directory "/path/to/application/frontend/web">
	   # use mod_rewrite for pretty URL support
	   RewriteEngine on
	   # If a directory or a file exists, use the request directly
	   RewriteCond %{REQUEST_FILENAME} !-f
	   RewriteCond %{REQUEST_FILENAME} !-d
	   # Otherwise forward the request to index.php
	   RewriteRule . index.php

	   # use index.php as index file
	   DirectoryIndex index.php

	   # ...other settings...
	   Require all granted
   </Directory>
</VirtualHost>

<VirtualHost backend.example.com:80>
   ServerName backend.example.com
   DocumentRoot "/path/to/application/backend/web"

   <Directory "/path/to/application/backend/web">
	   # use mod_rewrite for pretty URL support
	   RewriteEngine on
	   # If a directory or a file exists, use the request directly
	   RewriteCond %{REQUEST_FILENAME} !-f
	   RewriteCond %{REQUEST_FILENAME} !-d
	   # Otherwise forward the request to index.php
	   RewriteRule . index.php

	   # use index.php as index file
	   DirectoryIndex index.php

	   # ...other settings...
	   Require all granted
   </Directory>
</VirtualHost>
```

#### nginx:
```
server {
    listen 80; ## listen for ipv4
    listen [::]:80 ipv6only=on; ## listen for ipv6

    server_name frontend.example.com;
    root        /path/to/application/frontend/web/;
    index       index.php;

    access_log  /var/log/access_log.log
    error_log   /var/log/error_log.log

    location / {
       # Redirect everything that isn't a real file to index.php
       try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
        try_files $uri =404;
    }

    location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
    }

    location ~ /\.(ht|svn|git) {
      deny all;
    }
}
server {
    listen 80; ## listen for ipv4
    listen [::]:80 ipv6only=on; ## listen for ipv6

    server_name backend.example.com;
    root        /path/to/application/backend/web/;
    index       index.php;

    access_log  /var/log/access_log.log
    error_log   /var/log/error_log.log

    location / {
       # Redirect everything that isn't a real file to index.php
       try_files $uri $uri/ /index.php?$args;
    }

    # uncomment to avoid processing of calls to non-existing static files by Yii
    location ~ \.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)$ {
        try_files $uri =404;
    }

    location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
    }

    location ~ /\.(ht|svn|git) {
      deny all;
    }
}
```

INSTALLATION
-------------------
1. Prepare database credentials and configure webserver.
2. Prepare 3rd-party graphics pack.
    * download flag pack from link in @vendor/flags/Download.txt and unpack it there. (~15MB)

3. Run the following from console:
    * composer install
    * init

4. Change application configuration in files:
    * @common/config/main-local.php
        * Set up mail transfer protocol. eg. SMTP
        * Set DB connection.
    * @common/config/params.php
        * supportEmail - Support email address
        * user.passwordResetTokenExpire - Set max time when reset token is valid (in seconds)
        * supportedLanguages - Supported languages with translations.
        * defaultLanguage - Default language.
        * allowSignup - Allow or disallow self signup.

5. Run the following from console:
    * yii migrate --migrationPath=@yii/rbac/migrations
    * yii migrate/up
    * yii rbac/init

6. Log into application using default administrator credentials:
administrator/administrator

DOCUMENTATION
-------------------
Additional documentation can be found in `docs/` directory. Or generated from files using `.\vendor\bin\apidoc.bat api "/path/to/app" "/path/to/app/docs"`
