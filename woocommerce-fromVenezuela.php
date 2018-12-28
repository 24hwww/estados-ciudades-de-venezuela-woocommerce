<?php
/**
 * Plugin Name: Woocommerce Estados y Ciudades de Venezuela
 * Plugin URI: https://github.com/24hwww/estados-ciudades-de-venezuela-woocommerce/
 * Description: Estados y ciudades con codigo postal para woocommerce.
 * Version: 1.0
 * Author: @programadorve
 * Author URI: https://facebook.com/24hwww
 * Text Domain: woocommerce-extension
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Die if accessed directly
 */
defined( 'ABSPATH' ) or die( 'You can not access this file directly!' );

/**
 * Check if WooCommerce is active
 */
if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
	
add_filter("woocommerce_checkout_fields", "order_fields");

function order_fields($fields) {

    $order = array(
		"billing_first_name",
		"billing_last_name",
		"billing_cedula",
		"billing_email",		
		"billing_phone",
		"billing_country",
		"billing_state",		
        "billing_city", 
        "billing_postcode",
		"billing_address_1",
		"billing_address_2",	
    );
    foreach($order as $field)
    {
        $ordered_fields[$field] = $fields["billing"][$field];
    }

    $fields["billing"] = $ordered_fields;
    return $fields;

}	
	
  
	class WC_States_Places {

		const VERSION = '1.0.0';
		private $states;
		private $places;

		/**
		* Construct class
		*/
		public function __construct() {
			 add_action( 'plugins_loaded', array( $this, 'init') );
		}

		/**
		* WC init
		*/
		public function init() {
			$this->init_states();
			$this->	init_places();
		}

		/**
		* WC States init
		*/
		public function init_states() {
			add_filter('woocommerce_states', array($this, 'wc_states'));
		}

		/**
		* WC States init
		*/
		public function init_places() {
			add_filter( 'woocommerce_billing_fields', array( $this, 'wc_billing_fields' ), 10, 2 );
			add_filter( 'woocommerce_shipping_fields', array( $this, 'wc_shipping_fields' ), 10, 2 );
			add_filter( 'woocommerce_form_field_city', array( $this, 'wc_form_field_city' ), 10, 4 );

			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		}

		/**
	    * Implement WC States
	    * @param mixed $states
	    * @return mixed
	    */
        public function  wc_states($states) {
        	//get countries allowed by store owner
            $allowed = $this->get_store_allowed_countries();

global $states;

$states ['VE' ] = array (
		'AM' => 'Amazonas' ,
		'AZ' => 'Anzoátegui' ,
		'AP' => 'Apure' ,
		'AR' => 'Aragua' ,
		'BA' => 'Barinas' ,
		'BO' => 'Bolívar' ,
		'CB' => 'Carabobo' ,
		'CO' => 'Cojedes' ,
		'DA' => 'Delta Amacuro' ,
		'DC' => 'Distrito Capital' ,		
		'FA' => 'Falcón' ,
		'GO' => 'Guárico' ,
		'LA' => 'Lara' ,
		'ME' => 'Mérida' ,
		'MI' => 'Miranda' ,
		'MO' => 'Monagas' ,
		'NE' => 'Nueva Esparta' ,
		'PO' => 'Portuguesa' ,
		'SU' => 'Sucre' ,
		'TA' => 'Tachira' ,
		'TR' => 'Trujillo' ,
		'VA' => 'Vargas' ,
		'YA' => 'Yaracuy' ,
		'ZU' => 'Zulia' ,
	);

            return $states;
        }

        /**
	    * Modify billing field
	    * @param mixed $fields
	    * @param mixed $country
	    * @return mixed
	    */
        public function wc_billing_fields( $fields, $country ) {
			$fields['billing_city']['type'] = 'city';

			return $fields;
		}

		/**
	    * Modify shipping field
	    * @param mixed $fields
	    * @param mixed $country
	    * @return mixed
	    */
		public function wc_shipping_fields( $fields, $country ) {
			$fields['shipping_city']['type'] = 'city';

			return $fields;
		}

		/**
	    * Implement places/city field
	    * @param mixed $field
	    * @param string $key
	    * @param mixed $args
	    * @param string $value
	    * @return mixed
	    */
		public function wc_form_field_city($field, $key, $args, $value ) {
			// Do we need a clear div?
			if ( ( ! empty( $args['clear'] ) ) ) {
				$after = '<div class="clear"></div>';
			} else {
				$after = '';
			}

			// Required markup
			if ( $args['required'] ) {
				$args['class'][] = 'validate-required';
				$required = ' <abbr class="required" title="' . esc_attr__( 'required', 'woocommerce'  ) . '">*</abbr>';
			} else {
				$required = '';
			}

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
				foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Validate classes
			if ( ! empty( $args['validate'] ) ) {
				foreach( $args['validate'] as $validate ) {
					$args['class'][] = 'validate-' . $validate;
				}
			}

			// field p and label
			$field  = '<p class="form-row ' . esc_attr( implode( ' ', $args['class'] ) ) .'" id="' . esc_attr( $args['id'] ) . '_field">';
			if ( $args['label'] ) {
				$field .= '<label for="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) .'">' . $args['label']. $required . '</label>';
			}

			// Get Country
			$country_key = $key == 'billing_city' ? 'billing_country' : 'shipping_country';
			$current_cc  = WC()->checkout->get_value( $country_key );

			$state_key = $key == 'billing_city' ? 'billing_state' : 'shipping_state';
			$current_sc  = WC()->checkout->get_value( $state_key );

			// Get country places
			$places = $this->get_places( $current_cc );

			if ( is_array( $places ) ) {

				$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" ' . implode( ' ', $custom_attributes ) . ' placeholder="' . esc_attr( $args['placeholder'] ) . '">';

				$field .= '<option value="">'. __( 'Seleccione una Región, Provincia o Estado &hellip;', 'woocommerce' ) .'</option>';

				if ( $current_sc ) {
					$dropdown_places = $places[ $current_sc ];
				} else if ( is_array($places) &&  isset($places[0])) {
					$dropdown_places = array_reduce( $places, 'array_merge', array() );
					sort( $dropdown_places );
				} else {
					$dropdown_places = $places;
				}

	        	foreach ( $dropdown_places as $city_name ) {
	        		if(!is_array($city_name)) {
						$field .= '<option value="' . esc_attr( $dropdown_places ) . '" '.selected( $value, $city_name, false ) . '>' . $city_name .'</option>';
	        		}
				}

				$field .= '</select>';

			} else {

				$field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr( $args['placeholder'] ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
			}

			// field description and close wrapper
			if ( $args['description'] ) {
				$field .= '<span class="description">' . esc_attr( $args['description'] ) . '</span>';
			}

			$field .= '</p>' . $after;

			return $field;
		}
 		/**
	    * Get places
	    * @param string $p_code(default:)
	    * @return mixed
	    */
		public function get_places( $p_code = null ) {
			if ( empty( $this->places ) ) {
				$this->load_country_places();
			}

			if ( ! is_null( $p_code ) ) {
				return isset( $this->places[ $p_code ] ) ? $this->places[ $p_code ] : false;
			} else {
				return $this->places;
			}
		}
		/**
	    * Get country places
	    * @return mixed
	    */
		public function load_country_places() {
			global $places;

$places['VE'] = array(
	'AM' => array(
			'000001'=>'PUERTO AYACUCHO',
		    '001002'=>'PUERTO PAEZ'
		),
	'AZ' => array(
			'000003'=>'ANACO',
		    '000004'=>'ARAGUA DE BARCELONA',
		'000005' => 'BARCELONA', 
		'002006' => 'BOCA DE UCHIRE', 
		'000007' => 'BUENA VISTA', 
		'000008' => 'CANTAURA', 
		'000009' => 'CHUPARIN ABAJO', 
		'000010' => 'CLARINES', 
		'000011' => 'CRIOGENICO JOSE', 
		'000012' => 'EL TIGRE', 
		'000013' => 'EL TIGRITO', 
		'000014' => 'GUANTA', 
		'002015' => 'LA LEONA', 
		'000016' => 'LECHERIAS', 
		'000017' => 'LOS VIDRIALES', 
		'001018' => 'PARIAGUAN', 
		'000019' => 'PIRITU(EDO ANZOATEGUI)', 
		'000020' => 'PUERTO LA CRUZ', 
		'002021' => 'PUERTO PIRITU', 
		'000022' => 'SAN JOSE DE GUANIPA', 
		'000023' => 'SAN TOME', 
		'000024' => 'SANTA ANA (ANZOATEGUI)', 
		'000025' => 'SUPER OCTANOS', 
		'104026' => 'VALLE GUANAPE'			
		),
	'AP' => array(
		'006027' => 'ACHAGUAS', 
		'006028' => 'APURITO', 
		'008029' => 'BIRUACA', 
		'010030' => 'BRUZUAL', 
		'008031' => 'EL AMPARO (APURE)', 
		'010032' => 'EL SAMAN', 
		'010033' => 'ELORZA', 
		'008034' => 'GUASDUALITO', 
		'008035' => 'GUAYABAL', 
		'010036' => 'MANTECAL', 
		'000037' => 'SAN FERNANDO', 
		'001038' => 'SAN JUAN DE PAYARA'
		),
	'AR' => array(
			'100039' => 'CAGUA', 
		'109040' => 'CAMATAGUA', 
		'104041' => 'COLONIA TOVAR', 
		'000042' => 'DOS CAMINOS', 
		'100043' => 'EL CONSEJO', 
		'000044' => 'EL LIMON', 
		'100045' => 'LA ENCRUCIJADA', 
		'100046' => 'LA VICTORIA', 
		'000047' => 'LAS TEJERIAS', 
		'005048' => 'MAGDALENO', 
		'100049' => 'MARACAY', 
		'100050' => 'PALO NEGRO', 
		'109051' => 'SAN CASIMIRO', 
		'100052' => 'SAN FRANCISCO DE ASIS', 
		'100053' => 'SAN MATEO', 
		'002054' => 'SAN SEBASTIAN DE LOS REYES', 
		'100055' => 'SANTA CRUZ DE ARAGUA', 
		'000056' => 'SANTA RITA - ARAGUA', 
		'100057' => 'TURMERO', 
		'103058' => 'VILLA DE CURA' 
		),
	'BA' => array(
			'000059' => 'BARINAS', 
		'003060' => 'BARINITAS', 
		'003061' => 'BARRANCAS', 
		'006062' => 'CAPITANEJO', 
		'006063' => 'CIUDAD BOLIVIA', 
		'006064' => 'CURBATI', 
		'006065' => 'EL COROZO', 
		'006066' => 'LA CARAMUCA', 
		'003067' => 'LIBERTAD - DOLORES', 
		'006068' => 'MIRI', 
		'003069' => 'OBISPO', 
		'006070' => 'PEDRAZA', 
		'003071' => 'PUERTO NUTRIAS - CIUDAD NUTRIAS', 
		'000072' => 'SABANETA (BARINAS)', 
		'003073' => 'SAN JOSE OBRERO', 
		'000074' => 'SANTA BARBARA DE BARINAS', 
		'006075' => 'SOCOPO', 
		'003076' => 'VEGUITAS' 
		),
	'BO' => array(
			'010077' => 'CAICARA DEL ORINOCO', 
		'000078' => 'CIUDAD BOLIVAR', 
		'000079' => 'CIUDAD GUAYANA', 
		'010080' => 'CIUDAD PIAR', 
		'006081' => 'EL CALLAO', 
		'010082' => 'EL DORADO', 
		'006083' => 'GUASIPATI', 
		'010084' => 'GURI', 
		'000085' => 'KM. 88 VIA SANTA ELENA DE UAIREN', 
		'000086' => 'PUERTO ORDAZ', 
		'000087' => 'SAN FELIX', 
		'005088' => 'SANTA ELENA DE UAIREN', 
		'000089' => 'SOLEDAD', 
		'006090' => 'TUMEREMO', 
		'000091' => 'UPATA'
		),
	'CB' => array(
		'002092' => 'BEJUMA', 
		'009093' => 'CENTRAL TACARIGUA', 
		'002094' => 'CHICHIRIVICHE', 
		'007095' => 'EL PALITO', 
		'000096' => 'GUACARA', 
		'009097' => 'GUIGUE', 
		'000098' => 'LOS GUAYOS', 
		'100099' => 'MARIARA', 
		'002100' => 'MIRANDA (CARABOBO)', 
		'002101' => 'MONTALBAN', 
		'002102' => 'MORON', 
		'002103' => 'PALMA SOLA', 
		'002104' => 'PETROQUIMICA', 
		'000105' => 'PUERTO CABELLO', 
		'000106' => 'SAN JOAQUIN', 
		'000107' => 'VALENCIA', 
		'002108' => 'VENEPAL'
		),
	'CO' => array(
			'000109' => 'EL BAUL', 
		'002110' => 'EL ESPINAL', 
		'000111' => 'EL PAO', 
		'002112' => 'LAS VEGAS', 
		'000113' => 'LIBERTAD', 
		'000114' => 'SAN CARLOS', 
		'002115' => 'TINACO', 
		'003116' => 'TINAQUILLO'
		),
	'DA' => array(
			'000117' => 'TUCUPITA'
		),
'DC' => array(
			'100118' => 'CARACAS', 
		'100119' => 'CARRE PANAMERICANA DEL KM 8 AL 30', 
		'000120' => 'CUMBRE ROJA', 
		'010121' => 'EL JUNKO', 
		'110122' => 'EL JUNQUITO', 
		'100123' => 'EL RETIRO', 
		'000124' => 'FUERTE TIUNA', 
		'000125' => 'LA RINCONADA', 
		'103126' => 'LAGUNETICA', 
		'000127' => 'LAS MAYAS'
		),		
	'FA' => array(
			'009128' => 'ADICORA', 
		'003129' => 'AMUAY', 
		'002130' => 'BOCA DE AROA', 
		'002131' => 'BOCA DE TOCUYO', 
		'009132' => 'BUCHUACO', 
		'000133' => 'CABURE', 
		'010134' => 'CAPATARIDA', 
		'000135' => 'CAUJARAO', 
		'010136' => 'CHURUGUARA', 
		'000137' => 'CORO', 
		'000138' => 'CUMAREBO', 
		'009139' => 'DABAJURO', 
		'003140' => 'EL CARDON', 
		'009141' => 'EL SUPI', 
		'003142' => 'ESTACION DE SERVICIO CARORA', 
		'003143' => 'GUANADITO', 
		'003144' => 'JUDIBANA', 
		'000145' => 'LA VELA DE CORO', 
		'000146' => 'LAS PIEDRAS (EDO. FALCON)', 
		'009147' => 'LOS TAQUES', 
		'000148' => 'MATARUCA', 
		'002149' => 'MIRIMIRE', 
		'009150' => 'MORUY', 
		'009151' => 'PEDREGAL', 
		'009152' => 'PUEBLO NUEVO PARAGUANA', 
		'003153' => 'PUNTA CARDON', 
		'000154' => 'PUNTO FIJO', 
		'002155' => 'SAN JUAN DE LOS CAYOS', 
		'002156' => 'SANARE (EDO. FALCON)', 
		'009157' => 'SANTA ANA DE PARAGUANA', 
		'003158' => 'TARATARA', 
		'002159' => 'TOCUYO DE LA COSTA', 
		'002160' => 'TUCACAS', 
		'002161' => 'YARACAL'
		),
	'GO'  => array(
			'109162' => 'ALTAGRACIA DE ORITUCO', 
		'000163' => 'CALABOZO', 
		'008164' => 'CAMAGUAN', 
		'000165' => 'CANTAGALLO (CASERIO)', 
		'003166' => 'CHAGUARAMAS', 
		'002167' => 'EL GUAFAL', 
		'008168' => 'EL RASTRO', 
		'003169' => 'EL SOCORRO', 
		'008170' => 'EL SOMBRERO', 
		'000171' => 'FLORES (CASERIO)', 
		'003172' => 'LAS MERCEDES DEL LLANO', 
		'000173' => 'ORTIZ', 
		'000174' => 'PARAPARA DE ORTIZ', 
		'004175' => 'SAN JOSE DE GUARIBE', 
		'100176' => 'SAN JUAN DE LOS MORROS', 
		'003177' => 'SANTA MARIA DE IPIRE', 
		'007178' => 'TUCUPIDO', 
		'000179' => 'VALLE DE LA PASCUA', 
		'007180' => 'ZARAZA'
		),
	'LA' => array(
			'000181' => 'BARQUISIMETO', 
		'000182' => 'CABUDARE', 
		'000183' => 'CARORA', 
		'006184' => 'CUBIRO', 
		'008185' => 'DUACA', 
		'006186' => 'EL TOCUYO', 
		'000187' => 'HUMACARO ALTO', 
		'000188' => 'HUMACARO BAJO', 
		'007189' => 'LA MIEL', 
		'000190' => 'QUIBOR', 
		'006191' => 'SANARE', 
		'003192' => 'SARARE', 
		'010193' => 'SIQUISIQUE', 
		),
	'ME' => array(
			'004194' => 'APARTADEROS (ESTADO MERIDA)', 
		'004195' => 'BAILADORES', 
		'008196' => 'CANO ZANCUDO', 
		'008197' => 'CAÑO TIGRE', 
		'008198' => 'CAÑO TIGRE', 
		'004199' => 'CHIGUARA', 
		'000200' => 'EJIDO', 
		'006201' => 'EL PARAMO', 
		'006202' => 'EL PEÑON (EDO. MERIDA)', 
		'004203' => 'EL VALLE (EDO. MERIDA)', 
		'000204' => 'EL VIGIA', 
		'004205' => 'ESTANQUEZ', 
		'008206' => 'GUAYABONES', 
		'003207' => 'LA AZULITA', 
		'004208' => 'LA CULATA', 
		'008209' => 'LA PALMITA', 
		'003210' => 'LAGUNILLAS', 
		'006211' => 'LAS PIEDRAS (EDO. MERIDA)', 
		'004212' => 'LOS LLANITOS DE TABAY', 
		'000213' => 'MERIDA', 
		'008214' => 'MESA BOLIVAR', 
		'006215' => 'MUCUCHIES', 
		'008216' => 'MUCUJEPE', 
		'006217' => 'MUCURUBA', 
		'005218' => 'NUEVA BOLIVIA', 
		'006219' => 'PUEBLO LLANO', 
		'004220' => 'SAN JUAN DE LAGUNILLAS', 
		'006221' => 'SAN RAFAEL MUCUCHIES', 
		'004222' => 'SANTA CRUZ DE MORA', 
		'008223' => 'SANTA ELENA DE ARENALES (EDO. MERIDA)', 
		'006224' => 'SANTO DOMINGO', 
		'004225' => 'TABAY', 
		'006226' => 'TIMOTES', 
		'004227' => 'TOVAR', 
		'000228' => 'TUCANIZON', 
		'002229' => 'ZEA'
		),
	'MI' => array(
			'100230' => 'AEREOPUERTO CARACAS', 
		'100231' => 'ARAIRA', 
		'100232' => 'CARRIZAL', 
		'104233' => 'CAUCAGUA (EXCEPTUANDO CAPAYA)', 
		'100234' => 'CHARALLAVE', 
		'100235' => 'CUA', 
		'104236' => 'CUPIRA', 
		'104237' => 'EL GUAPO (EDO MIRANDA)', 
		'100238' => 'EL TAMBOR', 
		'100239' => 'GUARENAS', 
		'100240' => 'GUATIRE', 
		'104241' => 'HIGUEROTE', 
		'100242' => 'LOS TEQUES', 
		'100243' => 'OCUMARE DEL TUY', 
		'100244' => 'PARACOTOS (ZONA INDUSTRIAL LA CUMACA)', 
		'104245' => 'RIO CHICO', 
		'100246' => 'SAN ANTONIO DE LOS ALTOS', 
		'100247' => 'SAN DIEGO DE LOS ALTOS', 
		'100248' => 'SAN FRANCISCO DE YARE', 
		'100249' => 'SAN JOSE DE LOS ALTOS', 
		'103250' => 'SAN PEDRO DE LOS ALTOS', 
		'100251' => 'SANTA LUCIA DEL TUY', 
		'100252' => 'SANTA TERESA DEL TUY', 
		'104253' => 'TACARIGUA DE MAMPORAL'
		),
	'MO' => array(
			'010254' => 'AGUASAY', 
		'007255' => 'ARAGUA DE MATURIN', 
		'000256' => 'BARRANCAS DEL ORINOCO (EDO MONAGAS)', 
		'010257' => 'CAICARA DE MATURIN', 
		'007258' => 'CARIPE', 
		'008259' => 'CARIPITO', 
		'010260' => 'EL FURRIAL', 
		'010261' => 'EL TEJERO', 
		'010262' => 'JUSEPIN', 
		'007263' => 'LA TOSCANA', 
		'000264' => 'MATURIN', 
		'008265' => 'MIRAFLORES', 
		'000266' => 'PUNTA DE MATA', 
		'007267' => 'QUIRIQUIRE', 
		'007268' => 'SAN ANTONIO DE CAPAYACUAR MATURIN', 
		'010269' => 'SANTA BARBARA DE MONAGAS', 
		'000270' => 'TEMBLADOR', 
		'010271' => 'VIENTO FRESCO'
		),
	'NE' => array(
			'007272' => 'BAHIA DE PLATA', 
		'000273' => 'BOCA DE POZO', 
		'000274' => 'BOCA DEL RIO', 
		'000275' => 'EL GUAMACHE', 
		'007276' => 'EL MACO', 
		'003277' => 'EL PIACHE', 
		'000278' => 'EL VALLE DEL ESPIRITU SANTO', 
		'003279' => 'EL YAQUE (AEROPUERTO)', 
		'003280' => 'JUAN GRIEGO', 
		'003281' => 'LA ASUNCION', 
		'007282' => 'LA FUENTE', 
		'003283' => 'LA GUARDIA', 
		'008284' => 'LA SABANA DE GUACUCO', 
		'003285' => 'LA SIERRA', 
		'003286' => 'LAS GILES', 
		'003287' => 'LOS BAGRES', 
		'007288' => 'LOS MILLANES', 
		'000289' => 'LOS ROBLES', 
		'007290' => 'MANZANILLO', 
		'000291' => 'PAMPATAR', 
		'007292' => 'PEDREGALES', 
		'000293' => 'PLAYA EL ANGEL', 
		'000294' => 'PORLAMAR', 
		'003295' => 'PUNTA DE PIEDRAS', 
		'000296' => 'SAN ANTONIO (PORLAMAR)', 
		'003297' => 'SAN FRANCISCO DE MACANAO', 
		'000298' => 'SAN JUAN BAUTISTA', 
		'007299' => 'SAN SEBASTIAN', 
		'003300' => 'TACARIGUA', 
		'003301' => 'VALLE DE PEDRO GONZALEZ', 
		'007302' => 'VALLE VERDE', 
		'000303' => 'VILLA ROSA' 
		),
	'PO' => array(
			'000304' => 'ACARIGUA', 
		'000305' => 'AGUA BLANCA', 
		'000306' => 'ARAURE', 
		'001307' => 'BISCUCUY', 
		'001308' => 'BOCONOITO', 
		'001309' => 'CHABASQUEN', 
		'003310' => 'COLONIA DE TUREN', 
		'003311' => 'EL PLAYON', 
		'000312' => 'GUANARE', 
		'001313' => 'GUANARITO', 
		'003314' => 'LA APARICION DE OSPINO', 
		'003315' => 'OSPINO', 
		'001316' => 'PAPELON', 
		'000317' => 'PAYARA', 
		'003318' => 'PIRITU', 
		'003319' => 'RIO ACARIGUA', 
		'000320' => 'SAN RAFAEL DE ONOTO', 
		'000321' => 'TUREN' 
		),
	'SU' => array(
			'000322' => 'BOHORDAL', 
		'000323' => 'CANCHUNCHU', 
		'002324' => 'CARACOLITO', 
		'002325' => 'CARIACO', 
		'002326' => 'CARIAQUITO', 
		'000327' => 'CARUPANO', 
		'002328' => 'CASANAY', 
		'002329' => 'CEREZAL', 
		'002330' => 'CHACARACUAL', 
		'003331' => 'CHAGUARAMAS DE LOERO', 
		'002332' => 'CHAMARIAPA', 
		'000333' => 'CHARALLAVE (EDO.SUCRE)', 
		'002334' => 'CHURUPAL', 
		'002335' => 'COCOLI', 
		'000336' => 'COCOLLAR', 
		'002337' => 'CRUZ DE PUERTO SANTO', 
		'000338' => 'CUMANA', 
		'001339' => 'CUMANACOA', 
		'002340' => 'EL MORRO DE PUERTO', 
		'002341' => 'EL MORRO DE PUERTO SANTO', 
		'000342' => 'EL MUCO', 
		'003343' => 'EL PEÑON', 
		'008344' => 'EL PILAR', 
		'008345' => 'EL RINCON', 
		'002346' => 'GUACA', 
		'002347' => 'GUATAPANARE', 
		'002348' => 'GUAYABERO', 
		'000349' => 'GUAYACAN DE LAS FLORES', 
		'008350' => 'GUIRIA', 
		'002351' => 'GUIRIA DE LA PLAYA', 
		'008352' => 'IRAPA', 
		'002353' => 'LA ESMERALDA', 
		'002354' => 'LAS CASITAS', 
		'002355' => 'LAS CHARAS', 
		'002356' => 'LAS VEGAS', 
		'002357' => 'LLANADA DE PUERTO SANTO', 
		'002358' => 'LLANADA DE RIO CARIBE', 
		'000359' => 'MACARAPANA', 
		'001360' => 'MARIGUITAR', 
		'002361' => 'MAURACO', 
		'002362' => 'MUELLE DE CARIACO', 
		'002363' => 'PANTOÑO', 
		'000364' => 'PLAYA GRANDE (EDO SUCRE)', 
		'000365' => 'PRIMERO DE MAYO', 
		'002366' => 'PUERTO SANTO', 
		'002367' => 'PUTUCUTAL', 
		'008368' => 'QUEBRADA SECA', 
		'002369' => 'QUEREMENE', 
		'002370' => 'RIO CARIBE', 
		'001371' => 'SAN ANTONIO DEL GOLFO', 
		'002372' => 'SAN JOSE DE AEROCUAR', 
		'000373' => 'SAN JUAN DE LAS GALDONAS', 
		'002374' => 'SAUCEDO', 
		'002375' => 'TERRANOVA', 
		'008376' => 'TUNAPUY', 
		'008377' => 'YAGUARAPARO',
		),
	'TA' => array(
			'000378' => 'ABEJALES', 
		'000379' => 'AGUAS CALIENTES', 
		'002380' => 'CAPACHO', 
		'008381' => 'CHURURU', 
		'003382' => 'COLON', 
		'002383' => 'COLONCITO', 
		'000384' => 'CORDERO', 
		'003385' => 'EL COBRE', 
		'008386' => 'EL NULA', 
		'008387' => 'EL PINAL', 
		'003388' => 'LA FRIA', 
		'003389' => 'LA GRITA', 
		'008390' => 'LA PEDRERA', 
		'002391' => 'LA TENDIDA', 
		'003392' => 'LAS MESAS', 
		'003393' => 'LOBATERA', 
		'003394' => 'MICHELENA', 
		'000395' => 'PALMIRA', 
		'002396' => 'PREGONERO', 
		'000397' => 'RUBIO', 
		'000398' => 'SAN ANTONIO DEL TACHIRA', 
		'000399' => 'SAN CRISTOBAL', 
		'000400' => 'SAN JUAN DE COLON', 
		'002401' => 'SANTA ANA (TACHIRA)', 
		'003402' => 'SEBORUCO', 
		'000403' => 'TARIBA', 
		'000404' => 'TARIBA', 
		'002405' => 'UMUQUENA', 
		'000406' => 'URENA', 
		),
	'TR' => array(
			'000407' => 'AGUA VIVA', 
		'009408' => 'ARAPUEY', 
		'000409' => 'BETIJOQUE', 
		'004410' => 'BOCONO', 
		'002411' => 'BRAMON', 
		'000412' => 'BURBUSAY', 
		'002413' => 'CARVAJAL', 
		'002414' => 'EL AMPARO (TRUJILLO)', 
		'009415' => 'EL DIVIDIVE', 
		'007416' => 'EL PRADO', 
		'005417' => 'ESCUQUE', 
		'009418' => 'FLOR DE PATRIA', 
		'005419' => 'ISNOTU', 
		'005420' => 'JAJO', 
		'000421' => 'LA BEATRIZ', 
		'002422' => 'LA CEJITA', 
		'004423' => 'LA CONCEPCION(TRUJILLO)', 
		'002424' => 'LA HOYADA', 
		'004425' => 'LA MESA DE ESNUJAQUE', 
		'009426' => 'LA PLAZUELA', 
		'006427' => 'LA PUERTA', 
		'005428' => 'LAS MESAS DE ESNOJAQUE', 
		'006429' => 'MENDOZA FRIA', 
		'009430' => 'MONAY', 
		'000431' => 'MOSQUEY', 
		'008432' => 'MOTATAN', 
		'004433' => 'PAMPAN', 
		'004434' => 'PAMPANITO', 
		'005435' => 'SABANA DE LIBRE', 
		'009436' => 'SABANA DE MENDOZA', 
		'000437' => 'SAN MIGUEL', 
		'000438' => 'SANTA ANA(TRUJILLO)', 
		'000439' => 'SANTA APOLONIA', 
		'000440' => 'TOSTOS', 
		'000441' => 'TRUJILLO', 
		'004442' => 'TUÑAME', 
		'000443' => 'VALERA', 
		),
	'VA' => array(
			'100444' => 'CAMURI CHICO', 
		'100445' => 'CAMURI GRANDE', 
		'100446' => 'CARABALLEDA', 
		'100447' => 'CATIA LA MAR', 
		'100448' => 'LA GUAIRA', 
		'100449' => 'MAIQUETIA'
		),
	'YA' => array(
			'005450' => 'AEROPUERTO LAS FLORES', 
		'000451' => 'ALBARICO', 
		'005452' => 'AROA (CENTRO Y CURAIGUIRE)', 
		'000453' => 'BORAURE (AV. PRINCIPAL, MELINTON CAMBERO)', 
		'002454' => 'CARBONERO', 
		'004455' => 'CHIVACOA', 
		'002456' => 'COCOROTE', 
		'000457' => 'FARRIAR', 
		'002458' => 'GUAMA (SOLO SECTOR CENTRO)', 
		'002459' => 'INDEPENDENCIA', 
		'005460' => 'LAS FLORES', 
		'004461' => 'NIRGUA', 
		'004462' => 'SABANA DE PARRA', 
		'000463' => 'SAN FELIPE', 
		'002464' => 'SAN JOSE (YARACUY)', 
		'002465' => 'SAN PABLO', 
		'004466' => 'URACHICHE', 
		'000467' => 'YARITAGUA', 
		'005468' => 'YUMARE',
		),
	'ZU' => array(
			'000469' => 'AEROPUERTO (35 KM)', 
		'004470' => 'AV. INTERCOMUNAL LAS CABILLAS', 
		'003471' => 'BACHAQUERO', 
		'000472' => 'BAJO GRANDE', 
		'000473' => 'BOBURES', 
		'000474' => 'CABIMAS', 
		'005475' => 'CAJA SECA', 
		'000476' => 'CARRASQUERO', 
		'002477' => 'CARRETERA LARA-ZULIA', 
		'002478' => 'CARRETERA N', 
		'000479' => 'CASIGUA EL CUBO', 
		'000480' => 'CIUDAD OJEDA', 
		'002481' => 'EL CARMELO', 
		'002482' => 'EL DANTO', 
		'000483' => 'EL GUAYABO (EDO. ZULIA)', 
		'004484' => 'EL MENE', 
		'003485' => 'EL MENITO', 
		'005486' => 'EL MOJAN (LA GUAJIRA)', 
		'000487' => 'EL MORALITO', 
		'005488' => 'EL PLANETARIO (LA GUAJIRA)', 
		'002489' => 'EL ROSADO', 
		'002490' => 'EL VENADO', 
		'000491' => 'ENCONTRADOS', 
		'005492' => 'KM.01 AL 50 (55 KM) VIA PERIJA', 
		'002493' => 'LA CAÑADA DE URDANETA', 
		'005494' => 'LA CONCEPCION', 
		'002495' => 'LA ENSENADA', 
		'000496' => 'LA SALINA', 
		'003497' => 'LAGUNILLAS (ZULIA)', 
		'003498' => 'LAS DARAS', 
		'003499' => 'LAS MOROCHAS', 
		'000500' => 'LOMA LINDA (LA GUAJIRA)', 
		'004501' => 'LOS PUERTOS DE ALTAGRACIA', 
		'005502' => 'MACHIQUES', 
		'000503' => 'MAR BEACH (LA GUAJIRA)', 
		'000504' => 'MARACAIBO', 
		'002505' => 'MENE GRANDE', 
		'005506' => 'NUEVA LUCHA (LA GUAJIRA)', 
		'004507' => 'PEQUIVEN', 
		'004508' => 'PUERTO MIRANDA', 
		'004509' => 'PUNTA DE LEIVA', 
		'004510' => 'PUNTA DE PALMA', 
		'004511' => 'PUNTA GORDA', 
		'000512' => 'SAN CARLOS DEL ZULIA', 
		'005513' => 'SAN JOSE DE PERIJA', 
		'000514' => 'SANTA BARBARA DEL ZULIA', 
		'005515' => 'SANTA CRUZ DE MARA (LA GUAJIRA)', 
		'000516' => 'SANTA CRUZ DEL ZULIA', 
		'004517' => 'SANTA RITA', 
		'003518' => 'TAMARE', 
		'003519' => 'TAPARITO', 
		'003520' => 'TIA JUANA', 
		'002521' => 'TURIACAS', 
		'002522' => 'VALMORE RODRIGUEZ', 
		'005523' => 'VILLA DEL ROSARIO', 
		'002524' => 'VILLA TAMARE'
		)
	);

			$this->places = $places;
		}

		/**
	    * Load scripts
	    */
		public function load_scripts() {
			if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {

				$city_select_path = $this->get_plugin_url() . 'js/place-select.js';
				wp_enqueue_script( 'wc-city-select', $city_select_path, array( 'jquery', 'woocommerce' ), self::VERSION, true );

				$places = json_encode( $this->get_places() );
				wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
					'cities' => $places,
					'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
				) );
			}
		}

        /**
        * Get plugin root path
	    * @return mixed
        */
        private function get_plugin_path() {
            if (isset($this->plugin_path)) {
				return $this->plugin_path;
			}
			$path = $this->plugin_path = plugin_dir_path( __FILE__ );
			
			return untrailingslashit($path);
        }

        /**
        * Get Store allowed countries
	    * @return mixed
        */
        private function get_store_allowed_countries() {
            return array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );
        }

        /**
        * Get plugin url
	    * @return mixed
        */
        public function get_plugin_url() {

			if (isset($this->plugin_url)) {
				return $this->plugin_url;
			}

			return $this->plugin_url = plugin_dir_url( __FILE__ );
		}
    }
    /**
    * Instantiate class
    */
    $GLOBALS['wc_states_places'] = new WC_States_Places();
};