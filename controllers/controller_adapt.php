<?php

namespace frameworks\adapt{
    
    /* Prevent direct access */
    defined('ADAPT_STARTED') or die;
    
    class controller_adapt extends controller{
        
        public function view_sanitizers(){
            $this->content_type = 'text/javascript';
            
            $store = $this->store('adapt.sanitizer.data');
            
            $validators = array();
            
            if (isset($store['validators'])){
                foreach($store['validators'] as $name => $validator){
                    $validators[$name] = array(
                        'pattern' => $validator['pattern'],
                        'function' => $validator['js_function']
                    );
                }
            }
            
            $output = "var _adapt_validators = " . json_encode($validators) . ";\n";
            
            $formatters = array();
            
            if (isset($store['formatters'])){
                foreach($store['formatters'] as $name => $formatter){
                    $formatters[$name] = array(
                        'pattern' => $formatter['pattern'],
                        'function' => $formatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_formatters = " . json_encode($formatters) . ";\n";
            
            $unformatters = array();
            
            if (isset($store['unformatters'])){
                foreach($store['unformatters'] as $name => $unformatter){
                    $unformatters[$name] = array(
                        'pattern' => $unformatter['pattern'],
                        'function' => $unformatter['js_function']
                    );
                }
            }
            
            $output .= "var _adapt_unformatters = " . json_encode($unformatters) . ";\n";
            
            return $output;
        }
        
    }
}

?>