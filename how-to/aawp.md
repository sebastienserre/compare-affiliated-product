# How to integrate Compare Affiliated Products and AAWP
Compare Affiliated Products is coming with a folder called 'aawp' which
include all the aawp original template modified to display the products
from other affiliation platform.

## How to customize and use it?
If you customize directly the aawp folder inside the Compare Affiliated
Product plugin folder, you will loose all customization on next update.  
To avoid it, please copy the aawp folder on you **child** theme root
folder.  
If you already have a aawp folder in your child theme, you'll have to
add the hook do_action( 'thfo_compare_after_price', $this ); where you
want to see the price comparaison.