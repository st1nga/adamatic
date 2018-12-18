# adamatic
Radio advert builder

Uses a DB and filesytem to string flac audio files together for playing.

This is currently very specific to CoastFM 

1. Create your audio ads.
2. Update the DB with the ad details (Use the webscripts if you like)
3. Create a cron job to run create_ad.pl.

Install

1. Create a database (if you don't use ads then you need to alter the connection details)
3. Currently uses ads_o and ads_r)
   ads_o is the owner and has delete/update/select/insert where required to tables
   ads_r has read only access to required tables
   This is a starting attempt at restricting access.
2. Create the schema using schema.sql (mysql < schema.sql)

Basic premise.
Our radio station wants to sell ads to customer and restict the number of plays a day.
The number of plays a day are in our core hours (settings table)
Ads can be any length, ad slot length can be any length.
adamatic appends flac file together to make the required total ad length.
It attempts to spread the ads out over the core hours.
IE: If you have core hours of 0700 to 2200 (15 hours) and you sell 5 plays a day to customer, it tries to spread the ads out through the day in this case every 3 hours.
There is also a genre, IE "Pubs" this attemps to stop two pub ads playing in the same ad slot, obviously two many of the same type and you will have a problem. (we have not got there yet)
If there are no ads to play in an ad slot then it can play an internal ad. IE "You can advertise here contact ths station on ...."



Warning:
At this time there is no password protection.
We use it behind our filewall so it is NOT exposed the outside world and I would not expose it in its current form to the outside world.
Since users would have access to the actual playout systems it was deemed unnecessary.

create_ad.pl creates 1 ad slot that will played next
adamatic.pl creates 48 adverts, thats 2 per hour to be played at :15 and :45 minutes past the hour.

todo:
- Fix the genre processing it does not do what it says on the tin.
- Merge create_ad.pl and adamatic.pl and allow the number of ads per hour to be configurable.
