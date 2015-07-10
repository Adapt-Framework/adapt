var adapt = {
    
    sanitize: {
        format: function(key, value){
            if (_adapt_formatters[key]){
                var func = _adapt_formatters[key]['function'];
                func = 'func = ' + func;
                eval(func);
                return func(value);
            }
            
            return value;
        },
        
        unformat: function(key, value){
            if (_adapt_unformatters[key]){
                var func = _adapt_unformatters[key]['function'];
                func = 'func = ' + func;
                eval(func);
                return func(value);
            }
            
            return value;
        },
        
        validate: function(key, value){
            var valid = false;
            
            if (_adapt_validators[key]){
                if (_adapt_validators[key]['function']) {
                    var func = _adapt_validators[key]['function'];
                    func = 'func = ' + func;
                    eval(func);
                    valid = func(value);
                }else if (_adapt_validators[key]['pattern']){
                    var pattern = new RegExp(_adapt_validators[key]['pattern']);
                    if (value.match(pattern)) {
                        valid = true;
                    }
                }
            }else{
                valid = true;
            }
            
            return valid;
        }
    }
    
};


window.addEventListener('load', function(e){
    
    window.document.addEventListener('focusout', function(e){
        if (e.target.getAttribute('data-validator')){
            if (e.target.nodeName.toUpperCase() == 'INPUT') {
                var value = e.target.value;
                
                if (e.target.getAttribute('data-unformatter')){
                    value = adapt.sanitize.unformat(e.target.getAttribute('data-unformatter'), value);
                }
                
                if (value == "" || !value){
                    if (e.target.getAttribute('data-mandatory') && e.target.getAttribute('data-mandatory').toUpperCase() == "YES"){
                        e.target.className = e.target.className.replace(/invalid|valid/, '');
                        e.target.className = e.target.className.trim() + ' invalid';
                    }else{
                        e.target.className = e.target.className.replace(/invalid|valid/, '');
                        e.target.className = e.target.className.trim() + ' valid';
                    }
                }else{
                    if (adapt.sanitize.validate(e.target.getAttribute('data-validator'), value)) {
                        e.target.className = e.target.className.replace(/invalid|valid/, '');
                        e.target.className = e.target.className.trim() + ' valid';
                    }else{
                        e.target.className = e.target.className.replace(/invalid|valid/, '');
                        e.target.className = e.target.className.trim() + ' invalid';
                    }
                }
                
                
            }
        }
        
        if (e.target.getAttribute('data-formatter')){
            if (e.target.nodeName.toUpperCase() == 'INPUT') {
                var value = e.target.value;
                e.target.value = adapt.sanitize.format(e.target.getAttribute('data-formatter'), value);
            }
        }
    });
    
    
});


