# adamatic
Radio advert builder

Uses a DB and filesytem to string flac audio files together for playing.

This is currently very specific to CoastFM 

1. Create your audio ads.
2. Update the DB with the ad details (Use the webscripts if you like)
3. Create a cron job to run create_ad.pl.

Install

1. Create a database (if you don't use ads then you need to alter the connection details)
2. Create the schema using schema.sql (mysql < schema.sql)


