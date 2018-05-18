<?php
/**
 * Utilidades diversas para facilitar a manufatura dos códigos e simplificá-lo.
 *
 * @author willian
 */
class utilidades {
            
        /**
         * Retira caracteres especiais da string $str fornecida.
         * @param string $str String a ser traduzida
         * @param array $pares Array de pares com "de" => "para"
         * @return string $traduzida 
         */
        public function retirarAssentos($str, $pares){
            $traduzida = strtr($str, $pares);
            return $traduzida;
        }
}
?>