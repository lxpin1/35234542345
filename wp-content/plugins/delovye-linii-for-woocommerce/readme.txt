=== Delovye Linii for WooCommerce ===
Contributors: devdellin
Donate link: https://dev.dellin.ru/
Tags: woocommerce, shipping, woocommerce shipping
Requires at least: 4.7
Tested up to: 6.3
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html 

Official Delovye Linii delivery plugin for WooCommerce allows calculating the cost and the date of delivery of the products in the cart, finding out the available methods and terminals of delivery, selecting Delovye Linii as delivery service, checking order status and estimated delivery date.

== Description ==
Delovye Linii for WooCommerce is a delivery plugin that makes the buyer’s and website owner’s communication with Delovye Linii easier.

With the help of the plugin your customers can calculate the cost and the date of delivery of the goods in the cart, find out the available methods and terminals of delivery, select Delovye Linii as delivery service, check latest status and estimated delivery date.

The plugin gives the website owner an opportunity to place an order with just a few clicks – all the details are automatically copied from store order form. 

The plugin helps to:
- calculate delivery price and dates;
- find available delivery methods;
- find available delivery terminals;
- set Delovye Linii as a delivery service;
- display the information on delivery in customer personal account;
- place an order for transportation by Delovye Linii.

Installation and Setup:
For detailed installation and setup manuals see developers website https://dev.dellin.ru/cms/ 

Technical support:
In case of any questions on setting or using the plugin please use the contact form https://dev.dellin.ru/feedback/ 


== Frequently Asked Questions ==
= Can I see the cost and dates of delivery on the product page? =

No. The cost and dates of delivery are calculated on the checkout page.

= Is personal discount applied when calculating the cost of delivery? =

The personal discount is applied if the website owner has logged in to Delovye Linii personal account. 

= How can I place an order if the customer has selected Delovye Linii as a carrier? =

To place an order the website owner should open admin dashboard, select the customer’s order and place an order for transportation by Delovye Linii. A unique number is assigned to the created order for transportation. Path: WooCommerce – Orders – Action.Create_an_order.

= The plugin has been set up in accordance with the manual, but the delivery method is not displayed on the checkout page. What can be the cause? =

Make sure that the size and the weight of the product are indicated. If the size and the weight are indicated, but the delivery method is still not displayed, please use contact form to get in touch with the support.


= The delivery to the customer’s address has been selected while setting up the plugin, however terminals are displayed on the checkout page. Which settings need to be changed?  =

To set up the delivery to the customers’ address the website owner needs to select a checkbox “Выгрузка по адресу” (“Unloading at the address”).


== Screenshots ==
1.	Delivery cost and dates on the checkout page.
2.	Info on transportation status in the customer’s account. 
3.	Placing an order from the website admin dashboard. 
4.	Info on transportation status in admin dashboard.


== Changelog ==

= 2.0.0 от 23.05.2024 =
* Изменена архитектура модуля;
* Изменён дизайн страницы настроек модуля;
* Исправлена проблема с отображением терминалов на странице оформления заказа;
* Добавленная поддержка актуальных версий php;
* Добавлено логгирование;
* Прочие незначительные изменения;

= 1.1.4 от 10.12.23 =
* Изменена фраза

= 1.1.3 от 23.10.23 =
* Fixed locations search
* tested at new version wp and woo
* other fix 

= 1.1.2 от 15.05.22 =
* Fixed bug related with price in insurance


= 1.1.1 от 16.02.22 =
* Tested on current versions of wordpress and woocommerce
* Minor bug fixed.

= 1.1.0 от 29.12.2020 =
* Provided possibility to place an order for transportation based on the client’s order.
* Provided possibility to work with legal entities.
* Minor bug fixed.

= 1.0.1 =
* Bug fixed: access to API restored.
* Bug fixed: JS files loading on the plugin setup page restored.

= 1.0.0 =
* First public release

== Upgrade Notice ==

= 1.1.0 =
The process of placing an order for transportation has been simplified. Currently the website owner just needs to open admin dashboard, select the client’s order and place an order for transportation – no need to select delivery method, enter client’s details or address – all the data are filled in automatically. 

= 1.0.1 =
Bugs fixed, plugin stability increased. 

