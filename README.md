[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/build-status/develop)

**XGallery Cli**
A standard alone application based on Joomla! Framework to fetch images from third party
 - Flickr
 
**How to use**
 - git clone https://github.com/jooservices/XGalleryCli.git
	 - master: stable
	 - develop: unstable
- Install database via sql file
- Execute `composer install`
- Update Flickr OAuth. Please use 3rd library to get these information
- Execute php xgallery.php
- Setup cron to execute xgallery.php if needed
	 - php xgallery.php : **_Execute everything_**
	 - php xgallery.php --application=Flickr.Contacts
	 - php xgallery.php --application=Flickr.Photos
	 - php xgallery.php --application=Flickr.Photos --url=userUrl
	 - php xgallery.php --application=Flickr.Photos --nsid=nsid
	 - php xgallery.php --application=Flickr.Download --pid=pid 	  
	 - php xgallery.php --application=Flickr.Cli --method=Url.lookupUser --url=userUrl 
	 - php xgallery.php --application=Nct.Search --title="Title" --singer="Singer" --type="Type"
	 - php xgallery.php --application=Nct.Playlist --url="Url"

TODO
- Support one file to execute all Application
- Support Flickr with images filter
- Support Nct with playlist package
- Database with partitions support