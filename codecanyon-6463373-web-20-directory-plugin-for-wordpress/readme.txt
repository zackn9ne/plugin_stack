﻿=== Web 2.0 Directory ===
Contributors: Mihail Chepovskiy
Donate link: http://www.salephpscripts.com/
Tags: business directory, cars directory, classifieds, classifieds directory, directory, events directory, google maps, listings directory, locations, pets directory, real estate directory, vehicles dealers directory, wordpress directory, yellow pages, youtube videos
Requires at least: 3.6.2
Tested up to: 4.3.1
Stable tag: tags/1.10.0
License: Commercial

Build Directory or Classifieds site in some minutes. The plugin combines flexibility of WordPress and functionality of Directory and Classifieds

== Description ==

The plugin provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal.
In other words - whatever you want.

Look at our [demo](http://www.salephpscripts.com/wordpress_directory/demo/)

= Features of the plugin =
* Restrict ads by listings levels
* PayPal payment gateway including PayPal subscriptions
* Stripe payments service
* Frontend dashboard for regular users
* Invoices management
* Sticky and featured listings options
* Ability to raise up directory listings - this option may be payment
* Ability to renew expired listings - this option may be payment
* Ability to upgrade/downgrade listings levels - this option may be payment
* 5-star ratings for listings
* Customizable content fields of different types
* Icons for custom content fields
* Category-based content fields
* Order directory listings by content fields
* Powerful search by content fields
* Icons for categories
* Search by categories and locations
* SEO friendly - fully compatible with Yoast WordPress SEO plugin
* Locations search in radius - results displaying on map
* Set up any number of locations for one listin
* Google Maps integrated
* Custom map markers
* YouTube videos attachments for listings
* Images AJAX uploading
* 2 types of images gallery on listings pages
* Contact listing owner form + integration with Contact Form 7 plugin
* Bookmarks list functionality
* 'Print listing' option
* 'Get listing in PDF' option
* Adapted for reCaptcha
* Fully customizable and easy in configuration
* Responsive design based on Twitter Bootstrap
* 4 widgets: search widget, categories widget, listings widget, social accounts widget
* Custom Google Map styles
* The plugin uses custom post types and taxonomies
* Responsive design based on Twitter Bootstrap
* 4 widgets: search widget, categories widget, listings widget, social accounts widget
* Custom Google Map styles
* Fully compatible with WPML plugin
* CSV importer with ability to import images files

= Plugin conception =
Levels of listings control the functionality amount of listings and their directory/classifieds conception.
Each listing may belong to different levels, some may have eternal active period, have sticky status and enabled
google maps, other may have greater number of allowed attached images or videos. It is perfect base for business model of your directory site.

Each content field field belongs to the type that defines its behaviour and display. You may hide field name, select custom field icon,
set field as required, manage visibility on pages. Also listings may be ordered by some fields.
Note that you may assign fields for specific categories. This is important feature allows to build category-specific content fields.
For instance: there may be special *'price'* field especially in *'Classifieds/For sale'* category and all its subcategories, so this field will appear
only in listings, those were assigned with this category.

== Changelog ==

= Version 1.10.0 =
* new feature: use AJAX loading - load maps and listings using AJAX when click on search button, sorting buttons, pagination buttons
* new feature: initial AJAX loading - initially load listings only after the page was completely loaded
* new feature: display "Show More Listings" button instead of default paginator
* new feature: ability to connect search forms, google maps and listings blocks to work together without reloading of page
* new feature: use custom login page for listings submission process
* new feature: use custom login page for login into dashboard
* new shortcode: [webdirectory-levels-table] - listings levels table. Works in the same way as 1st step on Listings submit, displays only pricing table.
* improved: most of javascript code was combined into one file and will be included in the footer of page
* demo was seriously updated
* lots of bug fixes and improvements

= Version 1.9.10 =
* bug fix: broken listings layout in Chrome browser on mobile devices

= Version 1.9.9 =
* new feature: 'view listing' button on frontend dashboard
* new feature: automatic rotating slideshow for slider widget
* improved: custom tabs widget instead of bootstrap nav tabs
* improved: unnecessary code was removed from bootstrap.js libarary
* bug fix: double inclusion of localized javascript data and google maps API inclusion

= Version 1.9.8 =
* improved: password generator widget from latest WordPress 4.3
* bug fix: javascript and CSS files inclusions on all pages with directory shortcodes

= Version 1.9.7 =
* new setting: hide claim metabox at the frontend dashboard
* improved: javascript files will be included in the footer of page
* improved: javascript and CSS files will be included only on directory pages and pages those contain directory widgets

= Version 1.9.6 =
* adapted for WordPress 4.3
* new feature: RTL (Right To Left) support - layout, functionality, UI widgets
* new feature: different Terms Of Services pages for different languages (using WPML)
* new setting: exclude logo image from images gallery on single listing page
* improved: adapted to easily change URLs for translations in WPML frontend menus
* improved: added support of 'hreflang' tag in WPML
* improved: opening hours field compatible with 'Week Starts On' setting
* improved: compatibility with Events Manager plugin was added
* improved: user email now does not concatenated with login when new user register in listing submission
* improved: new version of Select2 was integrated
* lots of bug fixes and improvements

= Version 1.9.5 =
* YouTube API updated
* new setting: endable/disable lightbox on images gallery
* new feature: richtext editor for textarea content field
* bug fix: validation errors in opening hours content field
* sample CSV file was included into 'documentation/' folder
* documentation updated

= Version 1.9.4 =
* bug fix: broken payment link for payment gateways
* new setting: hide decimals in levels price table
* new feature: WP SEO Yoast plugin supports titles and metas for locations excerpt pages

= Version 1.9.3 =
* bug fix: 404 error for /%listing_slug%/%postname%/ listings permalinks structure

= Version 1.9.2 =
* requirement for frontend dashboard users to have 'edit_post' permission was removed
* bug fix: ERR_TOO_MANY_REDIRECTS error on 'Create new listing' page
* improved: users can not edit pending and draft listings at frontend dashboard

= Version 1.9.1 =
* new setting: aspect ratio of logo in Grid View (1:1, 4:3, 16:9, 2:1)
* new setting: do not include Google Maps API at backend
* security update: handled add_query_arg and remove_query_arg security vulnerabilities in wordpress
* lots of bug fixes and improvements

= Version 1.9.0 =
* new feature: listings permalinks structure - 6 possible structures
* new feature: location excerpt/archive pages
* new feature: locations block - locations/sublocations navigation menu
* new settings: customize colors of locations block
* new shortcode: [webdirectory-locations] - build locations list
* new widget: locations - build locations/sublocations navigation menu
* new feature: breadcrumbs mode on listing single page - 3 possible modes
* new setting: enable/disable breadcrumbs
* new setting: hide home link in breadcrumbs
* new setting: listings comments mode - 3 modes: always enabled, always disabled, as configured in WP settings
* new feature: random ordering of listings
* new feature: 2 modes for opening hours content field 12-hour clock and 24-hour clock
* new feature: Terms of Services checkbox and link on submission page
* new feature: directions functionality - 2 modes: built-in routing and link to Google Maps
* new setting: priority of opening of listing tabs
* new setting: enable/disable autocomplete on addresses fields
* new setting: enable/disable "Get my location" button on addresses fields
* new setting: Google Maps API key
* new feature: sales taxes functionality + 5 settings
* added: 2 custom fields in users profile: billing name and billing address
* added: by default only admins may change listing expiration date, separate setting to enable this feature for regular users
* improved: awesome font icons as content fields icons instead of images files
* improved: choose-level page adapted for mobile devices 

= Version 1.8.6 =
* new feature: 2 modes for images gallery main slide - cut image to fit width and height of main slide or full image inside main slide
* 5 new settings: ability to disable address fields: address line 1, address line 2, zip or postal index, additional info field, manual coordinates fields
* optimization for giant number of categories and locations
* bug fixed: wordpress customizer compatibility
* bug fixed: qTranslate plugin compatibility
* bug fixed: logo images bug for hover effect #6 in Safari
* bug fixed: number of columns on choose level page
* bug fixed: youtube embedded videos on SSL pages

= Version 1.8.5 =
* adapted for new versions of WordPress SEO plugin
* improvement: listings grid view responsive for mobile devices and tablets

= Version 1.8.4 =
* new feature: new listings view (grid view) + views switcher
* new feature: ability to select the number of columns for grid view (from 2 to 4)
* new feature: tags metabox on frontend submission form
* improvement: slight redesign of the submission page
* improvement: categories selection tree became expandable javascript tree in order to save space when have lots of categories
* new setting: wrap logo image by text content on excerpt pages in list view
* added: 5 new logo hover effects + option to disable hover effects
* added: directory listings filters at the backend

= Version 1.8.3 =
* new feature: claim listings functionality
* new feature: social sharing buttons with counters
* new feature: sort content fields by groups
* new feature: input and search address fields now connected with google maps places autocomplete service
* new feature: 'Get my location' button on input and search address fields
* new setting: set images gallery width in pixels instead of 100% width on single listing page
* improvement: ability to place content fields group on a separate tab on single listing page
* improvement: ability to hide content fields group from anonymous users
* added: notification to admin when new listing created
* added: ability to disable description and excerpt fields
* added: new 'Red-Blue' predefined color schema
* bug fixed: bootstrap tabs hidden after click in some themes

= Version 1.8.2 =
* language files improved
* new setting: use gradient on buttons
* improvement: ability to show active period of listings levels as 'daily', 'monthly', 'annually' words

= Version 1.8.1 =
* all additional modules (frontend submission, payments and ratings) were converted and moved into core plugin
* ability to install the plugin using the built-in WordPress plugin installer - installation instructions updated
* new setting: sticky and featured listings always will be on top

= Version 1.8.0 =
* slight redesign
* new settings framework
* new features for color palettes customization - ability to choose exact colors for different elements of frontend layout
* new setting: listing thumbnail logo width
* new setting: bottom margin between listings
* new setting: listing title font size
* new setting: jQuery UI Style
* new set of map markers icons
* new setting: default map height
* new content field: opening hours
* ability to add special notes for each location separately
* images slider carousel controls by mouse wheel
* new levels setting: enabled/disable listings detailed pages for each level separately
* new levels setting: nofollow attribute of links to detailed listings pages
* ability to restrict content fields by each level individually
* new type of prices/numbers content fields search input - range slider with min-max
* 2 types of search input - checkboxes or selectbox for select, checkboxes and radio content fields
* new setting: the order of address elements
* new setting: excerpt field max length
* new setting: use cropped content as excerpt
* new setting: strip HTML from excerpt
* new setting: ability to disable single payments by paypal
* new setting: default logo image
* new setting: exclude listings with empty values from sorted results
* new settings to control sizes and offsets of map markers and InfoWindow
* some additional bug fixes

= Version 1.7.0 =
* adapted for WP Visual Composer plugin
* 2 additional modules (enhanced locations and enhanced search) were converted and moved into core plugin
* new feature: build Custom Home Page with custom layout
* existed shortcodes were improved with new parameters
* 3 new shortcodes were added: webdirectory-listing, webdirectory-buttons, webdirectory-slider
* bug fixed: meta data from WP SEO plugin was broken
* some additional bug fixes

= Version 1.6.2 =
* adapted for WPML plugin
* adapted and tested with Wordpress 4.0
* new setting allows to not include Google Maps API to avoid conflicts
* bug fixed: media uploader for small images
* some additional bug fixes

= Version 1.6.1 =
* ability to upload/attach several images per one time using WP media library button
* some bug fixes

= Version 1.6.0 =
* new feature: ability to upgrade/downgrade listings levels - this option may be payment
* Stripe payment gateway integration
* WP media library button for registered users instead of custom images uploader
* adapted for new versions of Contact form 7 plugin v3.9+

= Version 1.5.8 =
* ability to change default �order by� parameter was added
* new settings to enable/disable following sorting links: sorting by date, sorting by title, sorting by distance, sorting by ratings
* new setting: default country/state for correct geocoding
* documentation improved

= Version 1.5.7 =
* categories block redesign
* new setting to restrict max number of subcategories in categories block and categories widget
* the number of nesting levels (1-2) and the number of columns (1-4) in categories block now strongly limited
* 5 new settings to show/hide main search filters: keywords, locations, address, categories and radius filters
* new setting: default radius search value
* lots of bugfixes

= Version 1.5.6 =
* search hooks moved to frontend controller
* the output of categories and tags content fields now is comma separated
* custom login form became responsive
* lots of bugfixes

= Version 1.5.5 =
* the plugin adapted to work in WP Multisite
* new setting for widgets: show or hide widget on directory pages

= Version 1.5.4 =
* integration with Contact Form 7 plugin for contact listing owner form
* new setting: ability to hide profile form at the frontend dashboard
* 2 new search settings: minimum and maximum range of radius search
* nested shortcodes supported now
* lots of bugfixes

= Version 1.5.3 =
* now the font size of 'FREE' label is equal to digits of price at the page of first step of frontend listings submission
* bug fixed: flush ratings permission at the frontend
* bug fixed: error of empty google maps object in 4 frontend templates

= Version 1.5.2 =
* set whole width for search radius slider
* bug fixed: rewrite rules
* bug fixed: edit listing button permission changed from 'edit_posts' to 'edit_post'
* adapted for new version of WP SEO plugin

= Version 1.5.1 =
* new feature: Google Maps markers may be loaded by AJAX to reduce the loading time of page (only for 'webdirectory-map' shortcode)
* new feature: Google Maps geolocation (only for 'webdirectory-map' shortcode)
* new images for clusters of Google Maps markers
* bug fixed: AJAX loading problem for non admin users when access to backend restricted
* bug fixed: content field regex validation in CSV importer
* bug fixed: prevent wrong redirect for some WordPress instances 

= Version 1.5.0 =
* new feature: frontend dashboard, ability to manage listings, invoices and profile for regular users
* first step of frontend listings submission was completely redesigned
* 5 new settings
* documentation improved

= Version 1.4.3 =
* bug fixed: problem with access to temp directory during CSV file uploading
* bug fixed: sql queries were not processing during plugins bulk activation

= Version 1.4.2 =
* new customization feature was added: ability to choose color palette for frontend
* documentation improved

= Version 1.4.1 =
* additional module: 5-star ratings for listings
* display 5-star ratings in map marker info window
* new feature: order by rating
* backend ratings management
* the layout was adapted for Schema.org microdata format
* the layout was adapted for facebook Open Graph microdata format

= Version 1.4.0 =
* 4 additional shortcodes were added
* ability to output content fields in map marker info window
* new setting to hide posts counts in locations search dropboxes
* new setting to hide cycle on the map during radius search
* new setting to enable clusters of map markers

= Version 1.3.2 =
* new design of Google Maps markers Info window
* implemented Bootstrap Tabs widget instead of jQuery UI tabs
* restriction for users to see media/attachment posts of another users
* new setting to hide search block in main part of page, but leave search widget functionality
* 2 new settings to hide comments number on index and excerpt pages and hide listings creation date

= Version 1.3.1 =
* new feature was added: CSV importer with ability to import images files
* static Google Map for 'Listing print' and 'Listing in PDF' pages
* improvements in paginator
* documentation improved

= Version 1.3.0 =
* new responsive design based on Twitter Bootstrap CSS framework
* 4 widgets were added: search widget, categories widget, listings widget, social accounts widget
* ability to set custom Google Map style + 10 map styles were added
* 2 new settings
* improvements for WordPress SEO plugin

= Version 1.2.0 =
* payments premium module
* invoices management
* paypal payment gateway
* paypal subscriptions payment gateway
* bank transfer payment gateway
* ability to set expiration dates of limited (not eternal) listings manually
* documentation improved

= Version 1.1.7 =
* new feature was added: icons for categories
* contact form for anonymous users bug was fixed

= Version 1.1.6 =
* translation issues on directory admin page were fixed
* content fields menu page hook now stored in content fields manager object

= Version 1.1.5 =
* new settings was added 'Default map zoom'
* core content fields bug was fixed
* creation of new listing with empty title now renders error message and saves draft instead of unknown action

= Version 1.1.4 =
* Bookmarks list functionality was implemented: Put in/Out button on listings pages and 'My bookmarks list' special page
* new 'Print listing' option
* new 'Get listing in PDF' option
* 'Edit listing' button was placed on listing page, visible only for users, those can edit current listing

= Version 1.1.3 =
* javascript code for dependencies of content fields from selected categories was improved
* the bug that causes problems when some of content fields change its types was fixed
* special condition for edit listing link was added in 'listing_single.tpl.php' template

= Version 1.1.2 =
* 2 new settings were added: ability to hide contact form option, ability to disable rendering of listings on directory home page
* Yoast SEO plugin compatibility bug was fixed
* recaptcha bug on contact form was fixed
* checkboxes content field bug when all checkboxes unchecked was fixed
* the plugin fully adapted for customizations in css and template files
* the plugin fully adapted for new 'Frontend submission' premium module

= Version 1.1.1 =
* locations metabox bug was fixed

= Version 1.1.0 =
* the structure of plugin was redesigned to be compatible with most of wordpress themes
* compatibility with Yoast SEO plugin was added
* 2 unnecessary settings were removed

= Version 1.0.7 =
* new setting was added to manage width of HTML content part of all directory pages

= Version 1.0.6 =
* listings title layout bug fixed - esc_attr() added
* 2 new settings for search panel added

= Version 1.0.5 =
* default installation content fields added

= Version 1.0.4 =
* added support of SSL for https sites when YouTube videos attached

= Version 1.0.3 =
* added support of SSL for https sites
* fixed bug with locations number during new levels creation

= Version 1.0.2 =
* default installation locations terms added

= Version 1.0.1 =
* fixed bug that appears during new content fields creation