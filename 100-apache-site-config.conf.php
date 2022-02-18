<?php require_once __DIR__."/sys/dev/launchTime.constants.php";?>
<VirtualHost *:80>
	# The ServerName directive sets the request scheme, hostname and port that
	# the server uses to identify itself. This is used when creating
	# redirection URLs. In the context of virtual hosts, the ServerName
	# specifies what hostname must appear in the request's Host: header to
	# match this virtual host. For the default virtual host (this file) this
	# value is not decisive as it is used as a last resort host regardless.
	# However, you must set it for any further virtual host explicitly.
	#ServerName www.example.com

	ServerAdmin webmaster@localhost
	DocumentRoot <?=ROOT_FOLDER?>
	
    # AddHandler cgi-script .mp4
    AddHandler cgi-script .sh
 	AddType video/mp2t mp2t ts
 	AddType audio/x-mpegurl m3u m3u8

	<Location "/Files">
		RewriteEngine on
		RewriteRule ^(.*)$ /getFile.php [L,QSA]
	</Location>

	Alias /audio "/var/lib/asterisk/sounds/custom"
	<Directory "/var/lib/asterisk/sounds/custom">
	    Order allow,deny
	    Allow from all
	    # New directive needed in Apache 2.4.3: 
	    Require all granted
	    Options Indexes FollowSymLinks 
            Options +ExecCGI
	</Directory>
	
	AddHandler application/x-httpd-php .html
	AddHandler application/x-httpd-php .xml

	RewriteEngine on
	RewriteRule /(.*)/phonebook.xml$ /phonebook.xml?phoneBookForUser=$1 [NE]


	# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
	# error, crit, alert, emerg.
	# It is also possible to configure the loglevel for particular
	# modules, e.g.
	#LogLevel info ssl:warn

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined

	# For most configuration files from conf-available/, which are
	# enabled or disabled at a global level, it is possible to
	# include a line for only one particular virtual host. For example the
	# following line enables the CGI configuration for this host only
	# after it has been globally disabled with "a2disconf".
	#Include conf-available/serve-cgi-bin.conf
</VirtualHost>

# vim: syntax=apache ts=4 sw=4 sts=4 sr noet
