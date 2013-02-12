UIFeedback
==========

To install
* download the extension
  	git clone https://lb42@bitbucket.org/lb42/UIFeedback.git

* add the following to `LocalSettings.php`:
  	require_once( "$IP/extensions/UIFeedback/UiFeedback.php" );

* create the table in your database
  	run update.php from maintenance-folder
  	when that dont work run the table.sql from the extensions folder manualy
