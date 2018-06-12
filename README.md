
**XGallery Cli**
A standard alone application based on Joomla! Framework to fetch images from third party
 - Flickr
 
**How to use**
 - git clone https://github.com/jooservices/XGalleryCli.git
	 - master: stable
	 - develop: unstable
- Install database via sql file
- Execute `composer install`
- Update Flickr OAuth
- Execute php xgallery.php
- Setup cron to execute xgallery.php if needed
	 - php xgallery.php : **_Execute everything_**
	 - php xgallery.php --Application=Contacts
	 - php xgallery.php --Application=Photos --url=userUrl
	 - php xgallery.php --Application=Photos --nsid=nsid
	 - php xgallery.php --Application=Download --pid=pid 