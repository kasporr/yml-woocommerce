<? 
/*
	Name: Yml for Woocommerce
	Author: Kaspor Company
	Ver: 1.0
*/
require_once(dirname(__FILE__) . '/wp-config.php');
$wp->init();
$wp->parse_request();
$wp->query_posts();
$wp->register_globals();
$wp->send_headers();
header("Content-Type: application/xml");


/*
	DELIVERY
	true — курьерская доставка есть;
	false — курьерской доставки нет.

	DELIVERY_PRICE
	Стоимость доставки
*/
define('DELIVERY', 'true');
define('DELIVERY_PRICE', 500);
define('DELIVERY_DAYS', 5);


/*
	PICKUP
	true — самовывоз есть;
	false — самовывоза нет.
*/
define('PICKUP', 'true');


$yml = new Yml();
$yml->getHeader(get_woocommerce_currency());

$args = array(
  'post_type'              => ['product'],
  'post_status'            => ['publish'],
  'nopaging'               => true,
);
$query = new WP_Query( $args );
while ($query->have_posts()) {
	$query->the_post(); 
	global $product;
	if($product->get_price() && $product->get_image_id()) {
		$term = get_the_terms($product->ID, 'product_cat');
		$yml->getOffer(
				$product->get_id(), 
				$product->get_title(), 
				wp_get_attachment_url($product->get_image_id()),
				$product->get_permalink(),
				$product->get_price(),
				get_woocommerce_currency(),
				$term[0]->term_id,
				$term[0]->name,
				get_the_excerpt(),
			);
	}

 }
wp_reset_query();


class Yml {

	private $categories;
	private $blogname;
	private $site_url;

	function __construct() {
		$args = [
			'taxonomy' => 'product_cat'
		];
		$this->categories = get_categories( $args );
		$this->blogname = get_bloginfo('name');
		$this->site_url = get_site_url();
	}

	function __destruct() {
		$xml .= '</offers>';
		$xml .= '</shop>';
		$xml .= '</yml_catalog>';
		echo $xml;
	}

	
	public function getHeader($currency) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<yml_catalog date="'.date("Y-m-d").'T04:00:01+04:00">';
		$xml .= '<shop>';
		$xml .= '<name>'.$this->blogname.'</name>';
		$xml .= '<company>'.$this->blogname.'</company>';
		$xml .= '<url>'.$this->site_url.'</url>';
		$xml .= '<currencies><currency id="'.$currency.'" rate="1"/></currencies>';
		$xml .= '<categories>';
		foreach($this->categories as $item_cat ) {
			$xml .= '<category id="'.$item_cat->term_id.'">'.$item_cat->name.'</category>';
		}
		$xml .= '</categories>';
		$xml .= '<offers>';
		echo $xml;
	}
	


	public function getOffer($id, $name, $picture, $url, $price, $currencyId, $categoryId, $category, $description) {
		$xml = '<offer id="'.$id.'">';
		$xml .= '<name>'.$name.'</name>';
		$xml .= '<picture>'.$picture.'</picture>';
		$xml .= '<url>'.$url.'</url>';
		$xml .= '<price>'.$price.'</price>';
		$xml .= '<currencyId>'.$currencyId.'</currencyId>';
		$xml .= '<categoryId>'.$categoryId.'</categoryId>';
		$xml .= '<category>'.$category.'</category>';
		$xml .= '<delivery>'.DELIVERY.'</delivery>';
    	$xml .= '<pickup>'.PICKUP.'</pickup>';
		$xml .= '<description><![CDATA['.$description.']]></description>';
		$xml .= '<delivery-options><option cost="'.DELIVERY_PRICE.'" days="'.DELIVERY_DAYS.'"/></delivery-options>';
     	$xml .= '</offer>';
		echo $xml;
	}
}
?>