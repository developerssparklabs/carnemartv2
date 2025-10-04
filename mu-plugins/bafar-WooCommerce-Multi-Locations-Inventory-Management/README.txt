=== WooCommerce Multi Locations Inventory Management ===
Contributors: techspawn
Tags: woocommerce, warehouse, multi warehouse, multi locations, simple, variable, products, product
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin will help you manage WooCommerce Products stocks through multiple locations.

== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).


= Features =

- New taxonomy for stock warehouses / locations
- Works on both, simple and variable products
- Easy management of stock with multiple Warehouses / locations
- Auto order allocation for warehouses / locations stock reduction
- Find your nearest Warehouse using Zipcode / Pincode
- Auto Warehouse assign to the product

= Compatibility =

- PHP 7+


== Installation ==

1. Upload "wcmlim" to the "/wp-content/plugins/" directory.
2. Check if you have WooCommerce 3.4+ plugin activated
3. Activate WooCommerce Multi Locations Inventory Management plugin through the "Plugins" menu in WordPress.

**Simple Products**

1. Enable Manage Stock in Inventory Tab > Update Post
2. Under Inventory Tab > Manage the stock for the Warehouse / Locations

**Variable Products**

1. Under Variations Tab > Create variations based on attributes
2. In each variation > Activate Manage Stock & Add Price > Update Post
3. In each variation > Manage the stock for the Warehouses / Locations for each variation

For documentation visit our [Website](https://techspawn.com/docs/woocommerce-multi-locations-inventory-management/).

== Frequently Asked Questions ==

== Screenshots ==
== Changelog ==

=Version 4.1.0
Fix :- Added new functionality when the product status is set to any status then it should be set the status which is set for the product in all products page
Fix :- On location change if location doesn't have stock then also product is add to cart for variable product.
Fix :- when cart count is 0 then also on location change  error message of clear cart is showing when Limit 1 location per order setting is on
Add :- Plugin gives fatal error on activation when Flatsome theme is active (Theme Compatibility).
Add :- After location on home page products are not displaying as per location,shows all location.
Add :- Added new feature - When the location popup and location switch shortcode is off and enable default location setting is enabled then product should get add to cart from the location which is set as a default location by the user for that product.

=Version 4.0.20
Fix :- Detect location on page load + group location > remove the extra test LIFE from the group dropdown
Fix :- the nearest location is not setting automatically in select location and group location dropdown.

=Version 4.0.19
Fix :- The order is placed if stock quantity is greater than location quantity.

=Version 4.0.18
Fix :- Add to cart button is not displaying when mange stock is "NO" on client site.

=Version 4.0.17
Fix :- In Product edit and Product list , when entering negative stock quantity then it is getting saved and due to that total stock value is getting affected
Fix :- Add to cart button is not displaying when mange stock is "NO".

=Version 4.0.16
Fix :- Simple Product Status Displays as 'Out of Stock' Instead of 'In Stock' After Updating and Refreshing

=Version 4.0.15
Fix :- Warnings and UI Issue

=Version 4.0.14
Fix :- HPOS compatible issue with WCMLIM plugin.

=Version 4.0.13
Add :- Compatibility with Rey Child theme.
Fix :- Locations are not displaying for the varible products.

=Version 4.0.12
Fix :- Warnings in file wcmlim-cart-item-name 44 and 49
Fix :- When the "BOM > None" rule is applied, stock is not deducted from the chosen location for a simple product.
Fix :- BOM -None Rule - For variable prodcut it is not working second location . Screenrecording attached.
Fix :- If the administrator selects the location for the second product and updates it, then later edits to select a location for the first product, the stock is also reduced from the second location.

=Version 4.0.11
Fix :- Existing Products' Stock Status Not Saving as 'In Stock' with WCMLIM plugin 

=Version 4.0.10
Fix:-Get parent Id of variation [Improved] 

=Version 4.0.9
Fix:-added Related Product Shortcode 

=Version 4.0.8
Fix:-default location is not autoselected if display list view is setting is on for variable product

=Version 4.0.7
Fix:-Inventory Management[variable product]: Consistent Increase /decrease of 2 Units During Product Inventory Management
Fix:-Stock is not display as expected on prodcut detail page when certain settings toggle on (config file attached)

=Version 4.0.6
Fix:- Load Dependency Improvements
Fix:- valid Google API fix 

=Version 4.0.5
Fix:- REST API endpoint auth
Fix:- Load Dependency Improvements
Fix:- Add to cart not working on Hide dropdown

=Version 4.0.4
Fix:- Advanced List view Fix
Fix:- Out Of Stock Label Fix

=Version 4.0.3
Fix:- Backend Only Mode Fix

=Version 4.0.2
Fix:- Minor Fix

=Version 4.0.1
Add:- No. of post per page option to product central with pagination.

= Version 4.0.0 = 
FIX:- JS Code Optimization
FIX:- Speed Optimization

= Version 3.5.14 = 
FIX:- Incorrect warehouse being selected. 

= Version 3.5.13 = 
Add:- Added setting "Auto-Populate Shipping and Billing Addresses"
Add:- Added setting "Nearby location finder suggestion off"

= Version 3.5.12 = 
FIX:- UI Issue - Proper Alignment of Location Group

= Version 3.5.11 = 
FIX:- MAXMIND API Fixes
FIX:- JS Library Optimization

= Version 3.5.10 = 
Add:- UI Fixes

= Version 3.5.9 = 
Add:-Maxmind Geocode Integration with setting option

= Version 3.5.9 = 
Fix:-Limit 1 location per order >The "Clear Cart" message appears even when "Manage Stock" is disabled for a specific product.

= Version 3.5.7 = 
Fix:-The stock does not increase after cancelling the order for banckend only mode for 1st rule.

= Version 3.5.7 = 
Fix: Page loading time enhancement in backend

= Version 3.5.7 = 
Fix: Hide location dropdown/list on product page >>Add to Cart Button Missing on Product Page When User Directly Accesses It Without Selecting a Location from Shop Page

= Version 3.5.6 =
Fix: The cart page does not display the location after adding the product and selecting it from the shop page using the shortcode.


= Version 3.5.5 =
Add: WC Shipping Instance Setting includes Locations Enablement for Shipping method mapping

= Version 3.5.4 =
Fix: Shipping error in Backend only mode's 4th rule, when manage stock is disabled for particular product.


= Version 3.5.3 =
Fix: Issue when manage stock is disable


= Version 3.5.2 =
Fix:  POS NINJA Compatibility

= Version 3.5.1 =
Fix:  Restrict User Setting Not Working


= Version 3.5.0 =
Enhancement: Product listing page speed delivery issue.

= Version 3.4.9 =
Fix: Product is adding, without selecting location.

= Version 3.4.9 =
Fix: Product is adding, without selecting location.

= Version 3.4.9 =
Fix: Product is adding to the cart even if they are out of stock.

= Version 3.4.9 = 
Fix: Showing quantity of restricted location to user.

= Version 3.4.9 =
Fix: Restrict User for specific location, now user can see only location which is restricted for them if any.

= Version 3.4.8 =
Fix: Location Enable/Disable For Each Product (also added validation on shop page)

= Version 3.4.8 =
Fix: Tax Mapping Critical bug fixed

= Version 3.4.7 =
Fix: Stock status issue on Product listing page.


= Version 3.4.6 =
Fix: Back end only mode Closes Location PHP 8+ Fix.

= Version 3.4.5 =
Add: Added message for closest location.

= Version 3.4.1 =
Fix: Added code for allow Backorder for each location for Simple Product for list view

= Version 3.4.0 =
Fix: Sort Filter Issue
Fix: UI Issue.
Fix: Added allow Backorder for each location code for Simple Product

= Version 3.3.9 =
Fix: UI Issue.
Fix: Restock issue fix according to location

= Version 3.3.8 =
Fix: Update validation for Custom Location fee.

= Version 3.3.7 =
Fix: Flatsome Theme Issue.

= Version 3.3.5 =
Fix: Limit location per order Issue.

= Version 3.3.4 =
Fix: Order Filter Issues.

= Version 3.3.3 =
Add: Location open time and close time in advanced list view.

= Version 3.3.2 =
ADD- Custom Fee for each location
FIX- Shop Add to Cart instock  product product 

= Version 3.3.1 =
ADD- Given provision for location wise stock management on product level with manage stock disablement
Add: Code for assign only one Openpos outlet for one location
Fix: Improved code for Openpos outlet was auto assigned to every location.
Add: Code for restore location stock value for Failed /Refund order status

= Version 3.2.9 =
Fix: UI Fix
Fix: Improved code for BOM - Location as per shipping zone for simple and variable product.

= Version 3.2.8 =
Fix: location filter widget settings its refreshed and navigates to other page
Fix: Improved user specific location restriction for guest users.

= Version 3.2.7 =
Fix: validation on Location register

= Version 3.2.6 =
Fix: Location is not selected in location dropdown view for variable product
Fix: UI issue
Add: Code for set selected location from location switcher on location list view for variable product
Fix: Hide location to Guest User to choose


== Changelog ==
= Version 3.2.5 = 
Fix: Total stock is not updated on product central when order is placed from WCPOS frontend.[Git Issue]

== Changelog ==
= Version 3.2.4 =
Fix:Total stock is not reducing after deleting the location.

== Changelog ==
= Version 3.3.7= October 01, 2022
[Fix] Simplify backend-only mode settings.
[Fix] Backorder setting is not working for variable product
[Fix] The checkout page is always loading with Backend only modes 2nd rule 
[Fix] Show address details >It displays the street address twice on the product details page with list view.
[Fix] Hide the location dropdown on the product page if the stock status for a specific product variation is disabled.

== Changelog ==
= Version 3.2.1= September 24, 2022
[Fix] Facing error on checkout page "Error processing checkout. Please try again" Can't place an order.
[Fix] Local pickup location(Back end only mode) Issue for both simple and variable product
[Fix] Added validation > Multilocation > Local Pickup location when the product stock is 0


== Changelog ==
= Version 3.2.0 =
Fix: UI and UX fixes
Fix: Stock Notification[Total Stock == Location Stock]

== Changelog ==
= Version 3.1.10 =
Fix: Backend Only Mode select location as per priority fix

== Changelog ==
= Version 3.1.9 =
Fix: Local Pickup Location on checkout
Fix: UI and UX fixes

= Version 3.1.8 =
Fix: UI UX Improvements
Fix: Force user to select location popup fix


= Version 3.1.7 =
Fix: Detect location setting location selection
Fix: Select Closest location to Customers shipping address [Backend only mode]

= Version 3.1.6 =
Fix: Select Closest location to Customers shipping address [Backend only mode]

= Version 3.1.5 =
Add: Added locations for add to cart on shop page.

= Version 3.1.4 =
Add: Display locations on order edit page for Nearby Instock Location by Shipping Address

= Version 3.1.3 =
Fix: Minor Fixes

= Version 1.2.9 =
Add: Inline location stock and price edit on product listing page

= Version 1.1.5 =
Update: User interface design preview for stock information box
Add: Control options to updated for Front End Visual Display like color, border, text input
Add: Live Preview mode added on display setting tab

= Version 1.1.4 =
Add: Create Sub-location under locations
Add: Assign Payment Methods to locations
Add: Hide/show location on frontend 

= Version 1.1.3 =
Add: assign Shop Managers to locations
Fix: Enhanced way to store inventory in database

= Version 1.1.2 =
Add: Restrict customers to specific locations
Add: Compressed js files for better speed

= Version 1.1.1 =
Add: Locations Distance from the entered address 
Add: Option to add location-wise price

= Version 1.1.0 =
Note: If you are upgrading from 1.0.0, previously created locations will be deleted, and need to create those once again.
Add:  Assign Shipping zones to each location
Add:  Shortcode to select a sitewide location
Add:  Option to detect visitors location and set nearby location

= Version 1.0.0 =
- Initial Realase.
