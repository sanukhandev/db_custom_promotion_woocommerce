# DB Custom WooCommerce Offers

DB Custom WooCommerce Offers is a plugin that adds custom discount rules to WooCommerce. It allows you to define specific discount rules based on different product categories.

## Installation

1. Download the plugin ZIP file.
2. Log in to your WordPress admin panel.
3. Go to Plugins > Add New.
4. Click on the "Upload Plugin" button.
5. Select the downloaded ZIP file and click "Install Now".
6. Activate the plugin.

## Usage

The plugin provides three custom discount rules:

1. Buy One Get One:

   - To apply this discount, assign the category slug "buy-one-get-one" to the products that should be included in the offer.
   - The discount will be calculated based on the highest price item in pairs.

2. Buy Four Pay For Two:

   - Assign the category slug "buy-four-pay-for-two" to the products that should be included in the offer.
   - The discount will be calculated by considering the highest two prices out of every group of four items.

3. Pick Any 3 At Fixed Cost:
   - Assign the category slug "pick-any-3-at-{fixed-price}" to the products that should be included in the offer.
   - Replace "{fixed-price}" with the actual fixed price for the offer.
   - The discount will be calculated based on the fixed price, and it will be applied when the total quantity of eligible products is divisible by 3.

Please make sure to assign the appropriate category slugs to the corresponding products to enable the desired discount rules.

## License

This project is licensed under the MIT License. See the LICENSE file for more information.
