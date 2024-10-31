=== Order and Inventory Manager for WooCommerce ===
Plugin Name: Order and Inventory Manager for WooCommerce
Plugin URI: https://www.oimwc.com/
Contributors: wphydracode, prismitsystems, freemius
Tags: woocommerce, order management, inventory management
Requires at least: 4.0
Tested up to: 6.0
Requires PHP: 5.6
Stable tag: 1.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage inventory stock levels and product purchase orders in WooCommerce.

== Description ==
Order and Inventory Manager plugin for WooCommerce is a complete inventory management plugin that collects all products that have reached their low stock threshold level in one single page. From there, you can fast and easily create professional purchase orders with just a few clicks.

**Low stock page**
Here is where all your products including product variants that have reached their low stock threshold level will be listed. You then also get the option to sort by a supplier.

**Suppliers**
Create your suppliers and fill out all information about your suppliers so you have it saved all in one place. You can then assign suppliers to all your products so you know where you buy them from.

**Filter products by supplier**
With the new supplier assigned to your product, you can now filter by supplier your products that have reached their low stock threshold level to easily create purchase orders.

= Premium Versions =
The premium version is where this plugin really shines. This plugin was created to make purchase order management a breeze to handle. You can easily create a purchase order from the products that have low stock. Simply just download the purchase order file and send it to your supplier or send it directly to your supplier’s email address via the plugin itself. When the products arrive, you can simply just mark them as received and the stock will automatically update with the number of ordered products. 

But that is far from all it can do, here are some more things it can do to improve your WooCommerce experience.

* Add **supplier information** to each product such as purchase price, product ID, and pack size.
* **Pack sizes** - This makes it possible to give each product a pack size that will also be displayed to the customer on the product page. The pack size also helps you when you are going to make purchases from your suppliers.
* **Order management tools** - Have you ever started to pack an order to realize you did not have all products in stock? For all of you who have many different products, you know how impossible it is to keep track of all your products and how many are still in stock. Well with this plugin you don't have to think about it. On the order page, status icons are shown for each order that will let you know if you have all products needed in stock to ship the order. It will even consider earlier made orders into the calculations. You can also filter out all these orders for very fast bulk management.
* **Set products as discontinued**. When stock is empty, they will be disabled in the shop but their page will remain to maintain SEO traffic.
* **Admin Quick Search Bar** - Don't you just hate that you have to go to the order/product page to search orders or products? It now adds a quick-search bar at the top that you can use wherever you are in the WordPress admin.
* **Inventory stock value** - Each month the plugin records your whole stock value so you can keep track of how much your stock is worth. You cal also see the current value of your stock.
* **Physical stock quantity** - Do you know how many of each product you really have in stock? Nope, you can check that in WooCommerce, the value you see there is physical stock minus products in pending orders. This plugin adds that value so you can easily check to see if your physical stock is the same as the stock in your shop.
* **Limit access to sensitive information** - You can control who will have access to your supplier info. Maybe you don't want to share all your business secrets with all your employees.

Interested in the premium features? You can read all about it on the official [OIMWC's HOMEPAGE](https://www.oimwc.com/ "Order & Inventory Manager Homepage").

== Screenshots ==
1. Page where all products with low stock are shown. You can filter products on suppliers.
2. Collect your active and finalized purchase orders all in one place.
3. Create and assign suppliers to all your products.
4. All products now have a ton of new data fields such as purchase price, pack size, supplier, and much more.
5. Display the product pack size to the customer directly on the product page.
6. Update stock levels when products arrive by simply entering the qty or pressing the fully arrived button.

== Installation ==
1. Upload the 'Order and Inventory Manager for WooCommerce' plugin to the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.

== Changelog ==

= 1.4.3 =
* Fix: Resolved an error when filtering the supplier-wise products on the low stock products page.
* Fix: Resolved an issue of products that were not able to be added to existing purchase orders.
* Fix: Resolved an issue with the product image on the purchase order page.
* Fix: Resolved stock issue on the Inventory Stock values page.
* Fix: Resolved the pack size changes issue when the customer placed an order. 
* Fix: Resolved an issue of “Add to list”-products that did not move to the Purchases Order Preview table.
* Fix: Resolved the stock issue when admin completes the order even if the ordered product has negative stock values.
* Tweak: Allow to set negative values in the low stock field in the product edit page.

= 1.4.2 =
* Fix: Added and modified some of the fields of the supplier info column on the low stock products page.
* Fix: Removed duplicate temporary products on the view purchase order page.
* Fix: Enabled pagination on the supplier page.

= 1.4.1 =
* Fix: Temporary products are now saved until removed or placed in an order.
 
= 1.4.0 =
* Fix: Changed Product ID in the purchase order file to the Supplier Product ID.
 
= 1.3.9 =
* Fix: Resolved low stock page product listing issue for the individual supplier.
* Fix: Made changes in the query to remove discontinued products from the low stock products page.

= 1.3.8 =
* Fix: Resolved temporary product & purchase order creation issue.
* Fix: Resolved blank product thumbnail image on the low stock products page.

= 1.3.7 =
* New Feature: It’s now possible to add a temporary product to a purchase order that is not registered in your store.
* Upgrade: A product can now be added to multiple purchase orders.
* Change: The way a product is calculated as low in stock has been modified. For it to be considered as low in stock it needs to have a stock value less than or equal to the low stock threshold level plus any amount in any active purchase order.
* Change: Discontinued variant will no longer be disabled when it is set as discontinued and run out of stock.
* Fix: Resolved PDF error when downloading PDF purchase order file.

= 1.3.6 =
* Change: Shows a message to select a supplier instead of showing all products on the low stock products table.
* Change: Modified the code for listing products in the low stock products when changing the supplier.
* Fix: Resolved JS issue of sending e-mails to a supplier.
* Fix: Resolved a CSS issue getting overwritten by other plugins’ CSS.

= 1.3.5 =
* Upgrade: An additional table is added on the low stock page named “Purchase Order Preview. You can see the products in the purchase order preview table when you enter the qty in the box and then press the “Update PO preview”-button.
* Fix: Updated library files to the latest version for PDF, XLS, and DOC file format.
* Fix: Updated correct values of physical stock and units in stock when a new order is created.

= 1.3.4 =
* Fix: Sometimes old values were reverted when updating values through the custom OIMWC update button and then pressed the WooCommerce update button.
* Fix: Corrected the stock value when filling the stock from the arrived stock section on the purchase orders page. This issue was caused when the product was out of stock.
* Fix: Adjusted so all new duplicated products’ stock value will be based on the source’s physical stock value.
* Fix: Corrected total count of the low stock products on the Inventory Overview page.

= 1.3.3 =
* Fix: Purchase order notes were not written in the PO.
* Fix: Some image types were not visible when selected as the PO logotype.

= 1.3.2 =
* Important information: Purchase order file and options have got a major overhaul this patch. Many new settings have been added so be sure to update your information on the settings page and enter your supplier’s e-mail address for orders.
* New Feature: Order Overview Page Filters. It’s now possible to filter all orders on the Order Overview page by orders that have products Out of stock, In stock, or are discontinued.
* New Feature: Send a purchase order directly to the supplier’s email address for ordering. You can set the default subject, default reply-to, and default message on the settings page.
* New Feature: Purchase order files now have many new data options to include such as product image, purchase price, business office address, and much more.
* Upgrade: Purchase Order File Improvements. With this upgrade, we have done major improvements in the purchase order file structure. It’s now possible to download purchase files in 3 formats such as PDF, DOC, and XLS (Note: PDF and DOC formats are only included in the Gold & Platinum version). You can add your business logo and change the color scheme of the PO from the settings page.
* Upgrade: The order overview page table is now more compact and adjustable with the browser size. You don't have to scroll the whole page to see the last orders.
* Upgrade: The products on the order overview page now include SKU.
* Fix: Product stock history was never intended to be higher than a few rows of text, this has now been fixed.

= 1.3.1 = 
* Fix: A discontinued product was in some cases disabled even if it had products still in stock.
* Fix: Discontinued product texts have been corrected.
* Fix: Corrected an error where all stock-related data for all product variants were locked if just one product variant were changed.

= 1.3.0 = 
* New Feature: Added function to enter a replacement product for a discontinued product. The replacement product will be shown on the product page in the form of a link.

= 1.2.9 = 
* New Feature: Added save buttons to simple and variable product pages. It saves all supplier information as well as OIMWC related data without reloading the page.
* New Feature: Added new filter options for orders on the orders page.
* Upgrade: Added support to automatically adjust and update the stock-related data using WP All Import Pro plugin. IMPORTANT!! When importing pack size ANY stock-related data must also be imported even if it is the same data that already exists in the database. This rule only applies when using the wp all import plugin to import data with.
* Upgrade: If an order contains a product that is set as discontinued and is out of stock then a warning notification will be displayed on the OIMWC pages. 
* Change: Added product SKU beside the product name in the order overview table.

= 1.2.8 = 
* New Feature (All users): To prevent stock mismatch errors we have created a background script that checks if the product's WooCommerce stock, Physical stock, Units in stock, and Pack size are matching. If any mismatching data is found, it will be displayed as a notification message on the OIMWC pages. If there are more than 10 products detected with incorrect stock values, you get the option to download the whole product list with mismatching data. If this is the case we recommend you check your inventory and update only the physical stock you have, the plugin will then calculate and change all other stock values based on your new physical stock and pack size.
* New Feature (All users): Product stock history. This info is available on the product page under the inventory tab. Product stock history includes all stock-related changes made to WooCommerce stock, Physical stock, Units in stock, and Pack size.
* Upgrade (All users): Added setting to disable/enable the OIMWC GTIN field on the product page if a known installed and active third-party plugin is already managing GTIN on the site.
* Upgrade - Importing stock-related data with the default WordPress import tool will now automatically adjust the remaining stock data. For example, if importing Physical stock values the WooCommerce stock and Units in stock will be updated to match the imported value. This will always be done when WooCommerce stock, Physical stock, Units in stock, or Pack size are imported.
* Change - Setting a product with unmanaged stock to discontinued will now also set it as out of stock to prevent orders of that product.
* Change - Renaming Meta: oimwc_supplier_total_pieces in the database to Meta: oimwc_physical_units_stock.
* Change: Inventory stock value is now estimated on values from the supplier with the lowest purchase price.
* Change: Restrict users to change only one stock-related field at a time while updating the product. Restricted fields are WooCommerce stock, Physical Stock, Units in Stock, and Pack Size. This tweak is made to prevent wrong stock value calculations.
* Change: Removed unnecessary fields from the OIMWC tab for the variable products such as GTIN number, Physical stock, Units in stock, and supplier info.
* Change: Added a warning message when the pack size of a product that is currently included in the active order is changed.
* Fix: Stock data were not always synchronized when changes in products were made.
* Fix: Fixed the low stock product count issues while importing. Also added support to calculate and update the values of units in stock, physical stock based on the WooCommerce stock added in the product import file.
* Fix: Fixed popup design while adding the products to a purchase order. Added error message if there are no possible purchase orders available to add the product to.
* Fix: Fixed notification visual bug on the inventory overview page.
* Fix: Fixed quick search bar product search issue on the 'All Pages' page.
* Fix: Changed the stock value from decimal to integer while saving it to the database.
* Fix: Fixed minor layout issue on the orders page.
* Fix: Fixed the physical stock value issue while updating products as fully arrived on the purchase orders page.
* Fix: Fixed the negative physical stock visual error on the Inventory Overview page.
* Fix: Fixed the special character that appears in the product name while adding the product to a purchase order.
* Fix: Fixed the visual error of “order meta” of “supplier product id” on the order detail page.

= 1.2.7 =
* Fix: It was not possible to add new suppliers to the product edit page.
* Fix: User roles access was not working properly in the user restriction feature.
* Fix: Javascript template was missing in the Free version.

= 1.2.6 =
* New Feature (Gold and above): Added support for multiple suppliers. The user is now able to add more than one supplier for simple and variable products.
* Upgrade: It’s now possible to create purchase orders for suppliers that do not have products that have reached their low stock threshold level. 
* Upgrade: All suppliers are now categorized in the dropdown menu based on their low stock products on the low stock page.
* Upgrade: Added supplier product ID and supplier URL in the order quick view and order detail page for the products that do not manage stocks.
* Change: Changed the text of total ordered items based on the pack size in the 'Products awaiting delivery' and 'View Purchase Order' page in the 'Order Info' column.
* Change: Displayed warning popup when the user tries to create a purchase order without choosing any supplier. Added a tooltip message to the disabled QTY field.
* Change: Added 'Admin login page URL' in the support contact form.
* Fix: Missing supplier link.

= 1.2.5 =
* Fix: issue in the units in the stock field.

= 1.2.4 =
* Fix: Table prefix in the wp queries.
* Fix: CSS issue of the page design.

= 1.2.3 =
* Change: Deactivate the free version plugin if the premium version is active.
* Fix: Fatal error of redeclaration of the function.
* Fix: Stock issue while updating the product in the silver version plugin.

= 1.2.2 =
* New Feature: Added sub-total at the bottom of the low stock products table when a user creates a purchase order.
* New Feature: Implemented autoload product functionality for the Receiving, Pending, and Completed purchase orders.
* Upgrade: The user is now able to add the same product to different orders of the same supplier.
* Upgrade: Display confirmation pop-up with the product name in it when the user tries to add a product to the purchase order.
* Design Changes: Removed sticky header for the mobile devices.
* Fix: Responsive design issue in mobile devices.
* Fix: The missing plugin icon in the plugin update listing table.
* Fix: An issue of showing error messages of the paid plans to the trial plan users.

= 1.2.1 =
* New feature: Add ‘Add to list’ functionality in the Purchase Order detail page. It allows users to manually add products to any pending purchase order.
* Upgrade: Modified ‘Add to order’ functionality using ajax. No need to reload the page when working with different suppliers.
* Upgrade: Modified low stock supplier counter functionality to reduce the page load times.
* Design: A dark overlay is applied to information when new data loads.
* Fix: When filtered by a supplier on the Inventory Overview page, all products were not listed.
* Fix: Manually adding product to existing order was not working as intended.
* Fix: Table separator issue in Awaiting delivery table.

= 1.2.0 =
* New feature: Enable users to add multiple shipping addresses in the settings page. Any saved shipping address can be used when creating a PO file.
* New feature: Added low stock threshold level field on the supplier page.
* New feature: Implemented searching for the supplier using ajax on the supplier’s page.
* New feature: Possibility to sort all the columns on the supplier page table using ajax.
* New feature: Possibility to sort all columns of the purchase orders tables using ajax.
* New feature: Possibility to filter the purchase orders by suppliers in the Purchase Orders page tables using ajax.
* New feature: Added search box to search the products in the table as well as supplier wise in the Inventory Overview page tables.
* Upgrade: All pages have been speed optimized.
* Upgrade: Removed default pagination of the tables. And added the possibility to ‘Auto Load’ products while scrolling down the page in the Inventory Overview page tables.
* Upgrade: Possibility to filter the products by the supplier in the Inventory Overview page tables using ajax.
* Upgrade: Orders in the Purchase Order pages will ‘Auto Load’ while scrolling down the page.
* Upgrade: Loaded order overview page data using ajax.
* Upgrade: Minimized the time of the page-load of the Inventory Stock Values page. Get the current value of the stock using ajax.
* Upgrade: Load scripts and styles of the plugin on the necessary pages only.
* Design change: Made header sticky so that the user can easily access all the functions wherever the user is on the page. Sticky header on Inventory Overview pages, Purchase Order pages, and Suppliers page.
* Design change: Implemented compact and clear new design for all the pages.
* Design change: Implemented a new design of the settings page.
* Design change: All admin pages are now responsive.
* Removed: Removed the ‘Estimate Arrival Date’ column from the completed purchase orders table.
* Removed: Removed search button from the admin search bar to enable users to search by pressing the enter key.
* Change: Made 1st topic open when the user lands on the help page.
* Bug: Bug in units in the stock calculation.
* Bug: Fix OIMWC Inquiry email template format.
* Bug: Estimated arrival date on the front product page.
* Bug: Tool-tip content when changing status by locking/unlocking the order.

= 1.1.9 =
* Upgrade: Now order overview page includes all orders with custom order statuses.
* Upgrade: Missing stock qty functionality that shows in the order preview and the order details page now works with custom order status also.
* Fix: Bug when downloading PO file in a different language while there is another language selected in the user profile.
* Fix: Bug in the calculation of the physical stock when the user tries to change stock directly from the product edit page.
* Fix: Select all checkboxes missing on the orders listing page.
* Fix: Javascript error while selecting variations in a variable product if there is any filter removed from the variable product page.

= 1.1.8 =
* Minor bug fixes.

= 1.1.7 =
* Upgrade:  Added Supplier on the WooCommerce product page with the possibility to sort and filter by a supplier.
* Upgrade: New supplier info – Tax/VAT number and contact person.
* Upgrade: Added purchase order buttons directly to the purchase order page.
* Upgrade: GTIN numbers (EAN, UPC, and so on) can now be added to each product. The GTIN number is displayed on the product front page.
* Upgrade: Purchase Order Status is now changed from Active/Finalized to Pending, Ordered, Receiving, and Completed.
* Upgrade: Added low stock threshold level for suppliers. When X number of different product types from the same supplier has reached low stock value a notification in the admin menu will appear. The supplier name will be marked red in the supplier filter list.
* Upgrade: Added settings to change purchase order language.
* Upgrade: Added settings to select what data to include in purchase orders.
* Upgrade: Physical stock can now be directly changed.
* Fix: Some orders had the wrong order status icon.
* Fix: OIMWC email template removed.
* Fix: Select lists are now alphabetically sorted.
* Fix: Physical stock qty was not calculated correctly for custom order statuses. If you are using custom order statuses, please update your settings.
* Fix: RankMath was not showing correct URLs.
* Other: After a purchase order is created the user is sent to the purchase order page.

= 1.1.6 =
* Credit cards are no longer required to start trials.

= 1.1.5 =
* Fix: Bug in the missing quantity status in the order details page and order preview.

= 1.1.4 =
* Fix: Bug in the fulfilled orders list filter.

= 1.1.3 =
* Minor bug fixes.

= 1.1.2 =
* Minor bug fixes.
* Moved premium feature 'Admin Quick Search Bar' to the free version of the plugin.

= 1.1.1 =
* Initial release.