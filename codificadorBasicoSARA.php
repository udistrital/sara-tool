#!/usr/bin/php
<?php
/**
  * PHP Coding Standards Fixer: php-cs-fixer fix codificadorBasicoSARA.php --level=symfony.
  */
/**
 * IMPORTANTE: La frase de seguridad predeterminada debe cambiarse antes instalar el aplicativo. Cambiarla después puede dejar
 * inservible la instalación si esta depende de variables codificadas con la clave anterior
 * (p.e. si se guardaron datos codificados en la base de datos o en config.inc.php).
 *
 *
 * @todo Mejorar la clase para que acepte otras semillas.
 */
require_once ("aes.class.php");
require_once ("aesctr.class.php");
class Encriptador
{
    private static $instance;
    private $llave;
    private $iv;
    //Se requiere una semilla de 16, 24 o 32 caracteres
    const SEMILLA = 'MI_SEMILLA_ENCRI';

    // Constructor
    public function __construct($llave = '')
    {
        if ($llave === '') {
            // Llave predeterminada
            $this->llave = self::SEMILLA;
        } else {
            $this->llave = $llave;
        }
    }

    function codificar_viejo($cadena) { /* reemplaza valores + / */
  		$cadena = rtrim ( strtr ( AesCtr::encrypt ( $cadena, "", 256 ), '+/', '-_' ), '=' );
  		return $cadena;
  	}

  	function decodificar_viejo($cadena) { /* reemplaza valores + / */
  		$cadena = AesCtr::decrypt ( str_pad ( strtr ( $cadena, '-_', '+/' ), strlen ( $cadena ) % 4, '=', STR_PAD_RIGHT ), "", 256 );
  		return $cadena;
  	}

    public function codificar_nuevo($cadena)
    {
        if (function_exists('mcrypt_encrypt')) {
            $cadena = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->llave, $cadena, MCRYPT_MODE_ECB);
        } else {
            echo 'Instale el paquete php-mcrypt o php5-mcrypt dependiendo de su distritución'.PHP_EOL;
            exit();
        }
        $cadena = trim($this->base64url_encode($cadena));

        return $cadena;
    }

    public function decodificar_nuevo($cadena)
    {
        $cadena = $this->base64url_decode($cadena);
        if (function_exists('mcrypt_decrypt')) {
            $cadena = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->llave, $cadena, MCRYPT_MODE_ECB);
        } else {
            echo 'Instale el paquete php-mcrypt o php5-mcrypt dependiendo de su distritución'.PHP_EOL;
            exit();
        }
        $cadena = trim($cadena);

        return $cadena;
    }

    public function codificar_url_nuevo($cadena, $enlace = '')
    {
        $cadena = $this->codificar_nuevo($cadena);

        return $enlace.'='.$cadena;
    }

    /**
     * Método para decodificar la cadena GET para obtener las variables de la petición.
     *
     * @param
     *        	$cadena
     *
     * @return bool
     */
    public function decodificar_url_nuevo($cadena)
    {
        $cadena = $this->decodificar_nuevo($cadena);

        parse_str($cadena, $matriz);

        foreach ($matriz as $clave => $valor) {
            $_REQUEST [$clave] = $valor;
        }

        return true;
    }
    public function codificarClave($cadena)
    {
        return sha1(md5($cadena));
    }

    public function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

function leerSTDIN()
{
    stream_set_blocking(STDIN, false);//Impide el bloqueo de la terminal
    $stdin = fopen('php://stdin', 'r');
    $input = stream_get_contents($stdin);
    var_dump($input);
    die;
    $lines = explode("\n", $input);

    foreach ($lines as $line) {
        echo "$line\n";
    }
}

function non_block_read($fd, &$data)
{
    $read = array($fd);
    $write = array();
    $except = array();
    $result = stream_select($read, $write, $except, 0);
    if ($result === false) {
        throw new Exception('stream_select failed');
    }
    if ($result === 0) {
        return false;
    }
    $data = stream_get_line($fd, 1);

    return true;
}

$accion = isset($argv[1]) ? $argv[1] : '';
if ($accion == '-h' || $accion == '--help' || $accion == '') {
    echo 'php '.$argv[0].' <codificar o decodificar> <nuevo o viejo (version SARA)> <semilla>'."\n";
    exit();
}

$accion = (strtoupper($accion[0]) == 'C') ? 'codificar' : 'decodificar';

$version = isset($argv[2]) ? (strtoupper($argv[2][0]) == 'N') ? 'nuevo' : 'viejo' : 'nuevo';

$semilla = isset($argv[3]) ? $argv[3] : '';

$enc = new Encriptador($semilla);

echo 'Inserte lineas a '.$accion.':'.PHP_EOL;

$linea = '';
while (1) {
    $char = '';
    if (non_block_read(STDIN, $char)) {
        $linea .= $char;
    } else {
        if ($linea != '') {
            $lineas = explode("\n", $linea);
            echo PHP_EOL;
            foreach ($lineas as $l) {
                if ($l != '') {
                    echo $enc->{$accion.'_'.$version}($l).PHP_EOL;
                }
            }
            echo PHP_EOL;
            $linea = '';
        }
    }
}
?>
