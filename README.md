- initilize the the project, run
```
make init
```

- after entering MySQL connection details into db.json, run
```
make schemaCheck
```

- to create cron job, run (this will modify the cronjob file, copy and paste the content of it to to crontab (or cron.d in debain))
```
make createCron
```
