#!/usr/bin/perl
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Using the ads database, select ads to play, merge into one file and move
#  location ready for playout 
#===========================================================================
#---------------------------------------------------------------------------

use strict;

use Time::HiRes qw(gettimeofday);
use File::Copy;
use DBI;

use lib "/coastfm/perllib";
use general;

$LOG_file = "/var/log/create_ad.log";
$LOG_batch = 'yes';
$LOG_debug = "3";

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Populate the playlist from valid ads and set a random value
#---------------------------------------------------------------------------
sub populate_playlist
{

my ($sql, $STH);
my ($playlist_id);

my %p = (@_);
my $DBH = $p{DBH};

logit(5, "Truncating playlist before population");
$sql = "truncate playlist";
$DBH->do($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$sql = "insert into playlist (ad_id) select ad_id from ads where now() between start and end";
$sql = "insert into playlist (ad_id) select ad_id from ads a, ad_type at where now() between a.start and a.end and a.ad_type_id = at.ad_type_id and at.name <> 'internal'";
$DBH->do($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$sql = "select playlist_id from playlist";
$STH = $DBH->prepare($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$STH->execute();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

($playlist_id) = $STH->fetchrow_array();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);


while ($playlist_id)
{
  $sql = "update playlist p, ads a, ad_type at set random = -log(1 -" . rand() . "/ (a.multiplier + at.multiplier)) "
        ."where playlist_id = $playlist_id and p.ad_id = a.ad_id and a.ad_type_id = at.ad_type_id";
  $DBH->do($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
logit(5, $sql);
  ($playlist_id) = $STH->fetchrow_array();
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
}

}#EOS
#---------------------------------------------------------------------------

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Keep picking ads until we have filled the time slot
#---------------------------------------------------------------------------
sub pick_ads
{
my ($sql, $STH, $STH1, $ad_id);
my ($length_s, $total_ad_length, $ads_left_to_play, $genre_id);

my $play_order = 0;
$genre_id = 0;

my %p = (@_);
my $DBH = $p{DBH};
my $break_length_s = $p{break_length_s};
$total_ad_length = 0;

#+
#Get the number of ads available to play
#-
$sql = "select count(*) from playlist where to_play=0 and over_played=0 and spread=0";
($ads_left_to_play) = $DBH->selectrow_array($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

while ($total_ad_length < $break_length_s and $ads_left_to_play != 0)
{
  $sql = "select p.ad_id, a.genre_id, a.length_s "
         ."from playlist p, ads a "
         ."where p.to_play = 0  and p.spread = 0 and p.over_played = 0 and a.ad_id = p.ad_id and a.genre_id <> $genre_id "
         ."order by random "
         ."limit 1";

  $STH = $DBH->prepare($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

  ($ad_id, $genre_id, $length_s) = $DBH->selectrow_array($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

  if ($ad_id == 0)
  {
    logit(1, "Bugger, we have run out of ads. This is a bug and should not happen!!!");
    logit(1, "--It can happen if the last two ads to play are the same genre, so note the fact and procced.");
#+
#Since we have determined there is nowt else to do, we might as well leave the loop, bad programming though.
#-
    last;
  }

#+
#Log that we have played the ad
#-
  $sql = "insert into ad_played (ad_id) values ($ad_id)";
  $DBH->do($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

#+
#Increment play order and update the playlist that we want to play this ad
#-
  $play_order++;
  $sql = "update playlist set to_play = $play_order where ad_id = $ad_id";
  $DBH->do($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

#+
#Add ad length to total ad break length
#-
#  $sql = "select length_s from ads where ad_id = $ad_id";
#  ($length_s) = $DBH->selectrow_array($sql);
#  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

  $total_ad_length = $total_ad_length + $length_s;

#+
#Need to get number of possible ads left to play
#-
  $sql = "select count(*) from playlist where to_play = 0 and over_played = 0 and spread = 0";
  ($ads_left_to_play) = $DBH->selectrow_array($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

}

#+
#May need to do stuff here, but for now make a note that we failed to pick any ads (or not enough)
#-
if ($ads_left_to_play == 0 and $total_ad_length < $break_length_s)
{
  logit(1, "Bugger!!! We don't have any ads left to play and the ad break is not long enough. We only have $total_ad_length seconds and we needed $break_length_s seconds. ...now what?");
  if ($total_ad_length == 0)
  {
    logit(1, "We didn't find anything to add, that sucks, maybe we should add a CoastFM ad?");
    $sql = "insert into playlist (ad_id, to_play) select a.ad_id, 1 from ads a, ad_type at where now() between a.start and a.end and a.ad_type_id = at.ad_type_id and at.name = 'internal'";
    $DBH->do($sql);
    check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
  } else
  {
    logit(1, "We did find something to play, even though we didn't fill the ad break up, maybe this is OK? I think so, so we will continue with what we have");
  }
}

}#EOS
#---------------------------------------------------------------------------

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#Loop for each ad in the playlist and remove the ads that have exceeded the
# number of plays allowed
# Only do this if we are in core hours
#---------------------------------------------------------------------------
sub remove_over_played_ads
{
my($sql, $STH);

my %p = (@_);
my $DBH = $p{DBH};
my $core_end_time = $p{core_end_time};
my $core_start_time = $p{core_start_time};

$sql = "select a.ad_id, a.name, at.name, at.plays_allowed, count(*) from ads a, ad_played ap, ad_type at "
      ."where a.ad_id = ap.ad_id and at.ad_type_id = a.ad_type_id and date(ap.played) = curdate() "
      ."and ap.played between date(now()) + interval $core_start_time hour and date(now()) + interval $core_end_time hour group by (ap.ad_id)";
$STH = $DBH->prepare($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$STH->execute();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

my ($ad_id, $name, $ad_name, $plays_allowed, $plays_today) = $STH->fetchrow_array();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

while ($ad_id)
{
#+
#If we have exceeded the daily ad play count then delete it from the playlist
#-
  if ($plays_allowed <= $plays_today)
  {
    logit(1, "Disabling $ad_name ad (ad=$ad_id) '$name' from playlist. It has played $plays_today times today and is only allowed $plays_allowed plays");
    $sql = "update playlist set over_played = 1 where ad_id = $ad_id";
    $DBH->do($sql);
    check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
  }

  ($ad_id, $name, $ad_name, $plays_allowed, $plays_today) = $STH->fetchrow_array();
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
}
}#EOS
#---------------------------------------------------------------------------

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#Get setting from setting table
#---------------------------------------------------------------------------
sub get_setting
{

my ($sql);

my %p = (@_);
my $DBH = $p{DBH};
my $setting = $p{setting};

$sql = "select value from settings where setting = \"$setting\"";
my ($value) = $DBH->selectrow_array($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

if ($value eq "")
{
  logit(1, "Setting $setting is blank (or missing) in the DB. FIX It!!");
  stack_dump(1);
}

return $value;

}#EOS
#---------------------------------------------------------------------------

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#Try to be clever about when ads play. spread them over the core hours
#
# Loop through each ad in the playlist and deactivate if we have played it too recently
#---------------------------------------------------------------------------
sub ad_spreading
{

my ($sql, $STH);
my ($ad_id);
my ($ad_name, $ad_type_name, $play_it, $played, $dont_play_until);

my %p = (@_);
my $DBH = $p{DBH};
my $core_time_minutes = $p{core_time_minutes};
my $core_end_time = $p{core_end_time};
my $core_start_time = $p{core_start_time};

$sql = "select ad_id from playlist";
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
$STH = $DBH->prepare($sql);

$STH->execute();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

($ad_id) = $STH->fetchrow_array();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

while ($ad_id)
{

  $sql = "select a.name, at.name, if(now() >  ap.played + interval (${core_time_minutes} / (at.plays_allowed) - at.fudge_factor) minute, 'YES','NO'), "
        ."ap.played, ap.played + interval (${core_time_minutes} / at.plays_allowed) - at.fudge_factor minute from playlist p, ad_type at, ads a "
        ."left join ad_played ap on a.ad_id = ap.ad_id  where a.ad_id = p.ad_id and p.ad_id = a.ad_id and a.ad_id=$ad_id "
        ."and ap.played >= date(now()) + interval $core_start_time hour and ap.played < date(now()) + interval  $core_end_time hour "
        ."and a.ad_type_id = at.ad_type_id order by played desc limit 1";

  ($ad_name, $ad_type_name, $play_it, $played, $dont_play_until) = $DBH->selectrow_array($sql);
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

  if ($play_it eq 'NO')
  {
    logit(1, "Disabling $ad_type_name ad (id=$ad_id) '$ad_name' from playlist due to spreading. Last played '$played' and don't play until '$dont_play_until'");
    $sql = "update playlist set spread = 1 where ad_id = $ad_id";
    $DBH->do($sql);
    check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
  }

  ($ad_id) = $STH->fetchrow_array();
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

}

}#EOS
#---------------------------------------------------------------------------
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#main MAIN Main
#---------------------------------------------------------------------------
{
my ($cgi_params);
my ($DBH, $sql, $STH);
my ($valid_ad_count, $ad_type, $break_length_s, $merge_output);
my ($ad_length);
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst);
my ($files_to_merge, $merge_cmd);

my ($core_start_time, $core_end_time, $flac_output_file);

my ($flac_merge);

($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst)=localtime(time);

logit(1, "Hello world!");
#stack_dump(1);

#+
#connect to ads DB
#-
$DBH = DBI->connect ('dbi:mysql:ads', 'ads_o', '???', {PrintError => 0} ) or die "Can't connect to mSQL database: $DBI::errstr\n" ;

logit(5, "Connected to ads DB");

#+
#Getting settings from settings table
#-
$core_start_time = get_setting(DBH => $DBH, setting => 'start_time');
$core_end_time = get_setting(DBH => $DBH, setting => 'end_time');
$flac_output_file = get_setting(DBH => $DBH, setting => 'flac_output_file');

$sql = "select length_s from ad_length where now() between start and end order by ad_length_id desc limit 1";
($break_length_s) = $DBH->selectrow_array($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$sql = "select count(*) from ads where now() between start and end";
$sql = "select count(*) from ads a, ad_type at where time(now()) between a.start and a.end and a.ad_type_id = at.ad_type_id and at.name <> 'internal'";
($valid_ad_count) = $DBH->selectrow_array($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

logit(1, "There are $valid_ad_count ads that can be played and our ad length is $break_length_s seconds");

if ($valid_ad_count == 0)
{
  logit(1,"We have no ads we can play... now what?");
  stack_dump(1);
}

populate_playlist(DBH => $DBH);

#+
#Test to see if we are in core hours
#-
if ((localtime)[2] >= $core_start_time and (localtime)[2] < $core_end_time)
{
  logit(1, "We are in core hours. ${core_start_time}:00 to ${core_end_time}:00");
  remove_over_played_ads(DBH => $DBH, core_start_time => $core_start_time, core_end_time => $core_end_time);
  ad_spreading(DBH => $DBH, core_time_minutes => ($core_end_time - $core_start_time) * 60, core_start_time => $core_start_time, core_end_time => $core_end_time);
}

#+
# When we get here then we have identified all the ads we can play, so pick the ads we want to play
#-
pick_ads(DBH => $DBH, break_length_s => $break_length_s);

#
#Generate mp3 with all ads combined
#-

$sql = "select p.to_play, at.name, a.name, a.length_s, a.path from playlist p, ads a,ad_type at where a.ad_type_id = at.ad_type_id and a.ad_id = p.ad_id and to_play > 0 order by to_play";
$sql = "select a.ad_id, p.to_play, at.name, g.genre, a.name, a.length_s, a.path "
      ."from playlist p, ads a,ad_type at, genre g "
      ."where a.ad_type_id = at.ad_type_id and a.genre_id = g.genre_id and a.ad_id = p.ad_id and to_play > 0 order by to_play";
$STH = $DBH->prepare($sql);
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$STH->execute();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

my ($ad_id, $to_play, $ad_type, $genre, $name, $length_s, $path) = $STH->fetchrow_array();
check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);

$ad_length = 0;

while ($to_play)
{
  logit (1, "$to_play... $length_s second ${ad_type}-${genre}, (ad=$ad_id) '$name' from '$path'");
  $files_to_merge++;
  if ($flac_merge ne '')
  {
    $flac_merge .= " ";
  }

  $flac_merge .= $path;
  $ad_length = $ad_length + $length_s;
  ($ad_id, $to_play, $ad_type, $genre, $name, $length_s, $path) = $STH->fetchrow_array();
  check_dbi_error(err => $DBI::err, errstr => $DBI::errstr, msg => $sql);
}

if ($files_to_merge > 1)
{
  $merge_cmd = sprintf("/usr/bin/shntool join %s -rnone -Oalways -Pnone -oflac -a%s 2>&1", $flac_merge, $flac_output_file);
} else
{
  $merge_cmd = sprintf("/bin/cp %s %s.flac", $flac_merge, $flac_output_file);
}

logit(1, $merge_cmd);
$merge_output = qx($merge_cmd);
logit(1, "\n$merge_output");

copy($flac_output_file . ".flac", $flac_output_file. ".flac" . sprintf("-%02d%02d%02d%02d%02d%02d", $year-100,$mon+1,$mday,$hour,$min,$sec));

logit(1, "all Done!!!");

}#EOM
#---------------------------------------------------------------------------
