# HIHAT
The High Interaction Honeypot Analysis Toolkit

NB: Development on this tool has been ceased.

I. Prerequisites
----------------

A web server running Apache and PHP.

II. Installation
----------------

a) Preparation of the logserver:

At first the logserver needs to be prepared. Remember, according to the architecture of the web-based honeynet this logserver should not be a honeypot or located in the honeynet itself.

1. Prepare the logserver and make sure it has a secure configuration. This comprises the installation of a minimalistic system, holding only the software and tools that are absolutely necessary to run the honeypot. Furthermore all non-required services should be disabled or removed if possible, resulting in a minimalistic system. In order to secure the system further all available patches need to be downloaded - the system has to be kept completely updated at the time of installation and also during the entire time of use. Make sure privileges are configured properly.
2. Install an SQL database, preferably MySQL version 5.0 or higher.
3. Create a new database named "honeyweb".
4. Use the file "HIHAT-Create-Tables.sql" to create the required tables in the database.
5. Create a new login profile in the SQL database and restrict its privileges to the database "honeyweb". This profile is used in b) and c) to store and access the data at the logserver.

b) Installation of a web-based honeypot:

Description of the necessary steps for the installation of a web-based honeypot:
1. Select a PHP-based web application to transform into a web-based honeypot(=module) and store the source code of the application in a directory. For the transformation process write permission is needed in the directory.
2. Set the name of the honeypot by configuring the name of the module for the Honeypot-Creator. Therefore, open the file "insertionFile.txt" and change the content of the variable "$thisModule_23cdx_" to a module name of your choice. Remember, the module name identifies the honeypot in the analysis process later on and has to match the name of the module-file in the analysis tool (see below).
3. Set the login information for the SQL database at your logserver. Therefore, open the file "insertionFile.txt" and change the server-address, username and password in the line "$link23 = mysql_connect" at the beginning of the file.
4. Install the Java Runtime Environment 1.4x or higher.
5. Run the Honeypot-Creator by typing "java honeypot_creator XXX", where XXX stands for the directory your PHP-based web application is located in. Example: "java honeypot_creator /var/www/phpmyadmin/". Remember, for the transformation process write permission is needed in the directory.

Now your web application is ready to serve as a honeypot, which recognizes and stores any access at the logserver you selected.

c) Installation of the analysis tool HIHAT:

Description of the necessary steps for the installation of the analysis tool HIHAT:
1. Create a new subdirectory in the www-path of your webserver. Remember, according to the architecture of the web-based honeynet this webserver should not be a honeypot or located in the honeynet itself.
2. Make sure the access to the directory is protected, e.g. by using the ".htaccess" and ".htusers" files of the Apache webserver.
3. Copy the analysis tool HIHAT (including subdirectories) to the webserver.
4. Adjust the file "inc/constant.php" to your configuration settings. Mandatory is the definition of the SQL database, username and password to access the data. For using the geolocation mapping function the corresponding database and a key for Google Maps has to be configured as well. Additional configuration options can be selected in that file.
5. To enable automatic downloading of malicious tools make sure "check_downloads.php" is accessed regularly. Therefore I suggest to install a new cronjob which runs the following command every five minutes: "lynx -dump http://localhost/your-directory/autodownloader/check_downloads.php?verobse=false".
6. If you want to use graphical statistics, please check if you need to acquire a license for JpGraph. The installation of additional files and the GD Libraray v2 may be necessary, too.

Now the analysis tool HIHAT should be installed properly and can be accessed via web browser.
