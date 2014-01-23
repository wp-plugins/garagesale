=== GarageSale ===
Contributors: leo.eibler
Donate link: http://www.eibler.at/
Tags: Wordpress, GarageSale, Garage Sale, Yard Sale, Flohmarkt, Online-Flohmarkt, Plugin, Wordpress Plugin, Subscriber
Requires at least: 3.3.1
Tested up to: 3.8
Stable tag: 1.2.4
License: Apache License, Version 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0

This plugin is a lightweight solution to put a kind of garage sale on your wordpress page. 

== Description ==

This plugin is a lightweight solution to put a kind of garage sale on your wordpress page. 

Users can put their stuff with a picture, description, price and contact on a wordpress site. 
The users are wordpress users with access right Subscriber (so every registered user can use the garage sale). 

Put the string "[GarageSaleList]" on any page or article post where you want to display the list of sale items.

This Plugin creates an own subfolder within the upload folder for the pictures.

look at http://www.eibler.at/garagesale/ for detailled description of usage and installation

== Installation ==

* Upload the plugin to /wp-content/plugins/ directory.
* Activate the plugin as an administrator in the plugin section. 
* Place the string "[GarageSaleList]" on the page where you want to display the sale item list.
* look at http://www.eibler.at/garagesale/ for detailled description of usage and installation

== Changelog ==

= 1.2.4 =
* modify css attack bugfix in newitem page
* add support for multisite
* tested with wordpress 3.8 and wordpress multisite 3.8

= 1.2.3 =
* bugfix css attack in footer, newitem and user page
* bugfix file upload - convert filename to lower case (upper case in extension produces error in some systems) 

= 1.2.2 =
* bugfix pagination for permanent links
* bugfix pagination total pages check in admin area

= 1.2.1 =
* bugfix pagination
* bugfix use _REQUEST instead of _GET for specials in wordpress 3.5
* bugfix use wp_get_image_editor() for wordpress greater or equal version 3.5
* number of items per page is now defined at garagesale.php - see define GARAGESALE_ITEMS_PER_PAGE

= 1.2 =
* bugfix remove prepare calls with only 1 argument to work with wordpress 3.5

= 1.1 =
* add license
* modify type of price from float to varchar
* bugfix bulk actions if using other language than english
* bugfix administrator actions
* only show img tag if an image is set
* show user display_name instead of user_nicename

= 1.0 =
* first public release
* don't use this version with german localization
