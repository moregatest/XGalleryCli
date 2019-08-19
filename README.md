
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/?branch=develop)
[![Build Status](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/jooservices/XGalleryCli/build-status/develop)

**XGalleryCli**

Change logs

***3.0.1***
-  Improve CLI class
-  Basic NOW - CLI application
-  Minor improvement

***3.0.0***

 - Completely refactor by replace **Joomla!** by **Symfony** 4.x & **Doctrine**
 - Completely replace 3rd party: **http-client** & **oauth** by self-develop with **guzzlehttp**
 - Replace custom CLI by **Symfony Console** with tons of improvement, UI / UX are included
 - Completely work & support **Entity** / **Repository** / **DBAL** & **ORM**. No more manual database init
 - Ready for **Web** version

 ---

Flickr CLI application
-  **flickr:contacts**
   -  Fetch contacts
   -  No options are required
-  **flickr:photos**
   -  Fetch photos
   -  _nsid_: Optional. Fetch photos from specific NSID
-  **flickr:photossize**
   -  Fetch photos' size
   -  _nsid_: Optional. Fetch photos' size from specific NSID
   -  _limit_: Optional ( Default 200 ). Number of photos will be processed
-  **flickr:photodownload**
   -  Download photo
   -  _photo_id_: Optional. Download specific photo by ID
   -  _force_: Optional. Force re-download if file local already exists
   -  _no_download_: Optional. Skip download
