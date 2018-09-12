# Actions & Filters
In order to help developpers to make Compare Affiliated Products their
own, I put some hooks, action and filter in the code.  

**Disclaimer**; If i've forgotten ou you need an Hook somewhere, please
contacontact me [here](https://www.thivinfo.com/soumettre-un-ticket/)

## Actions
- do_action( 'thfo_compare_after_price', $this );  
  This hook fire in the AAWP Template to show the affiliated products

## Filters
- apply_filters( 'compare_partner_name', $p['partner_name'] )  
Allow you to filter the partners logo in
[class-awin.php](../classes/class-awin.php#L236)

- apply_filters( 'compare_currency_unit', $currency );  
Allow you to customize the currency unit or replace the letter code by
the symbol.
[class-compare-basic-shortcode.php](../shortcode/class-compare-basic-shortcode.php#L26)

