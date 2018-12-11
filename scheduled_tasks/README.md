# Running Scheduled Tasks

This web application contains some scripts, found in this directory, that need to be run at scheduled times. This can be done in Linux via *cron jobs* or in Windows via the *task scheduler*

## Linux Instructions
1. Open up the cron tab file with the command "crontab -e".
2. Add in the line "* * * * * <path/to/desired_script.php>", where the 5 asterisks represent Minutes, Hours, Days, Months, and Days of the Week, separated by spaces, and the path is the full filepath to the php script to be run. (NOTE- if using VIM, go into insert mode if you are not already by hitting 'i')
3. Modify the asterisks to schedule the task as desired. To schedule the task to run every 15 minutes, use "\*/15 * * * * ". To schedule the task to run twice a day at 11:00am and 11:00pm, use "00 11,23 * * * ". To schedule the task to run twice a year (bi-annually) on January 15th and July 15th, both at 10:00am, use "00 10 15 1,7 * ".
4. Save and exit the crontab file. (if using VIM, hit 'esc', then ':', then 'x', then enter)

## Windows Instructions
1. Open the Task Scheduler
2. Click 'Create Task'
3. Name it something obvious like "Global Expertise Database Bi-Annual Reminder Emails", and give it a description
4. Choose to 'run whether user is logged on or not'; it is assumed that the server will be running during a scheduled task.
5. Go to the 'Triggers' tab and click 'New'
6. Schedule the task to be daily, weekly, or monthly. For a bi-annual task, choose 'Monthly', then choose 2 opposite months of the year, and finally choose 1 day to trigger on. For a daily task, choose 'Daily' (every 1 day(s)). To do a task multiple times daily, choose 'Repeat task every:' and choose a value (for example, every 12 hours to do something twice a day), and set it for a duration of 1 day at a time. Also select 'Stop all running tasks at end of repetition duration'
7. Optionally set a start time to kick things off at a time that makes sense (like 1:00:00 pm instead of 12:48:23 pm)
8. Now switch over to the 'Actions' tab where the desired action will be specified, and click 'New'
9. To run one of these php scripts, use the command "<path/to/php.exe> -f <path/to/desired_script.php>"; you have to specify the directory where the server's php executable is installed, followed by the -f flag, followed by the directory where the desired php script it (so for example: "C:\wamp64\bin\php\php5.6.31\php.exe -f C:\wamp64\www\International_Database\scheduled_tasks\BiAnnualReminderEmails.php")
10. Hit 'ok', and if it asks you if you want to use the arguments you specify, click yes.
11. Change any other configuration settings you want, then hit 'ok'
12. Input the admin password for approval
