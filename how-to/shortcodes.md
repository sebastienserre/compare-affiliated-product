# Shortcodes
If you're using Compare affiliated produts without AAWP then you'll like
to use it with a shortcode to show in your content products and prices
in different e-shop.  
The main shortcode is \[compare_basic_sc] with several parameters:
- ean: add the EAN13 code of the product you want to show.
- layout: future feature, for the moment the shortcode layout is horizontal.
- partner: The main partner to get the image, the title and the
  description from the db.

**Example**:  
\[compare_basic_sc ean='8710103556701' layout='horizontal'
partner='cdiscount'] will produce:

![awin-main-shortcode.png](img/awin-main-shortcode.png)