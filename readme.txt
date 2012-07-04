=== GarageSale ===
Contributors: leo.eibler
Donate link: http://www.eibler.at/
Tags: Wordpress, GarageSale, Garage Sale, Flohmarkt, Online-Flohmarkt, Plugin, Wordpress Plugin, Subscriber
Requires at least: 3.3.1
Tested up to: 3.3.1
Stable tag: 1.1
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