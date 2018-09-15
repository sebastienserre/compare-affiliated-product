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
  Allow you to filter the partners logo  
  [class-awin.php](../classes/class-awin.php#L236)

- apply_filters( 'compare_currency_unit', $currency );  
  Allow you to customize the currency unit or replace the letter code by
  the symbol.  
  [class-compare-basic-shortcode.php](../shortcode/class-compare-basic-shortcode.php#L26)

- apply_filters( 'compare_settings_tab', $tabs );  
  Allow you to add settings tabs  
  [settings.php](../admin/settings.php#L12)
- apply_filters('compare_time_limit', 600);  
  Allow you to increase PHP limit time in import and register infos.  
  [class-awin.php](../classes/class-awin.php#L56)  
  [class-awin.php](../classes/class-awin.php#L215)
- $partners = apply_filters(	'compare_partners_code',	array(	'Cdiscount'
  => '6948',	'Toy\'R us' => '7108',	'Oxybul eveil et jeux' => '7103',
  'Rue du Commerce' => '6901',	'Darty' => '7735',	)	);  
  Allow you to customize logo & partner code before displaying  
  [class-awin.php](../classes/class-awin.php#L233)


