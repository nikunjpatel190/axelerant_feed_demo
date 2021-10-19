# axelerant_feed_demo
A Symfony App that will store the feeds URLs and periodically polling the feeds and creating a feed with content.


Load Fixure to add dummy login users and feed dummy content
------------
```bash 
php bin/console doctrine:fixtures:load
```

Run Console Command to create feed content
------------
```bash 
php bin/console app:extract-feed
```


Note : 
------------
Click on Admin and login by admin user provided credentials in login page to add feeds URLS

Admin Login User
```bash
username : john_user
password : kitten
```

Unit Testing : 
------------
Run Below commands to perform unit tests

```bash
php bin/console --env=test doctrine:schema:create
php bin/console doctrine:fixtures:load --env="test"

cd project_directory/
php ./vendor/bin/phpunit
```