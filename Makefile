init:
	[ -f config/db.json ] && echo 'db.json already exists.' || (cp config/db_temp.json config/db.json && echo 'please change default connection details. then run "make schemaCheck"')

schemaCheck:
	php -f schema/CheckDb.php

cron:
	php -f index.php

createCron:
	echo 'MAILTO="support@infinity.co";' > cronjob;
	echo '\r' >> cronjob;
	echo '* * * * * root cd '$(PWD)'; make cron' >> cronjob
