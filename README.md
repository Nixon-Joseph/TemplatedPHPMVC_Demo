Template project to help people get started with the TemplatedPHPMVC library/framework [here](https://github.com/Nixon-Joseph/TemplatedPHPMVC)

To run this, simply clone or download. Install the dependencies via composer. (See "Getting Started" [here](https://packagist.org/)).

You may need to set up a virtual host for routing to work properly. Mine looks like this in ubuntu. If you're not using LAMP, your configuration will likely look different:

```
<VirtualHost mvc_demo.local:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/TemplatedPHPMVC_Demo

        ServerName mvc_demo.local
        ServerAlias www.mvc_demo.local
        <Directory /var/www/TemplatedPHPMVC_Demo>
                AllowOverride all
                Require all granted
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/error.log
        CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

run the `mvc_demo.sql` file on your local db, and in a neighboring folder to your cloned project's root, create a folder named `private`, and add in a `mvc_db_creds.php` file with the following contents:

```php
<?php

$dbServer = "localhost"; // your local db instance location
$dbName = "mvc_demo";
$dbUser = "root"; // your username password
$dbPass = ""; // your local password

?>
```
