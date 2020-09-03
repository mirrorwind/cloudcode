# AppSample

Laravel 7 @ AWS Lightsail

# Deploy on AWS Lightsail

https://docs.bitnami.com/general/infrastructure/lamp/get-started/

-   git clone repo_url_here
-   composer install
-   sudo chown -R daemon:www-data storage
-   sudo chown -R daemon:www-data bootstrap/cache
-   sudo chmod -R 777 storage
-   sudo chmod -R 777 bootstrap/cache
-   cd /opt/bitnami/apache2
-   vi /opt/bitnami/apache2/conf/bitnami/httpd.conf
-   sudo /opt/bitnami/ctlscript.sh restart apache
-   mysql –uroot –p
-   CREATE DATABASE your_db_name_here;
-   quit

# DataDog

Your Agent is running and functioning properly. It will continue to run in the
background and submit metrics to Datadog.

If you ever want to stop the Agent, run:

    sudo systemctl stop datadog-agent

And to run it again run:

    sudo systemctl start datadog-agent

Uninstall

    sudo apt-get remove datadog-agent -y

# Set up cron job

ssh

    crontab -e

add a new line

    * * * * * cd /opt/bitnami/apache2/htdocs/appsample && /opt/bitnami/php/bin/php artisan schedule:run >> /dev/null 2>&1

# Others

WIP
