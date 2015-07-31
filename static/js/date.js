var adapt_date = {
    
    convert_date: function(input_pattern, output_pattern, value){
        var output = '';
        var date = {
            day_of_month: '',
            month: '',
            year: '',
            hour: '',
            minutes: '',
            seconds: ''
        };
        
        for(var c = 0; c < input_pattern.length; c++){
            var chr = input_pattern[c];
            
            switch (chr){
            case "d":
                if (value.length >= 2){
                    var val = value.substr(0, 2);
                    value = value.substr(2);
                    
                    if (val.match(/^[0-9]{2,2}$/)){
                        date.day_of_month = parseInt(val);
                    }
                }
                break;
            case "D":
                value = value.replace(/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun)$/i, '');
                break;
            case "j":
                if (value.match(/^[0-9]{1,2}/)) {
                    var val = value.substr(0, 1);
                    value = value.substr(1);
                    
                    if (val.match(/[1-3]/) && value.match(/^[0-9]{1,1}/)){
                        val = val + value.subtr(0, 1);
                        value = value.substr(1);
                        
                        date.day_of_month = parseInt(val);
                    }
                }
                break;
            case "l":
                value = value.replace(/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)$/i, '');
                break;
            case "N":
                value = value.replace(/^[1-7]/, '');
                break;
            case "S":
                value = value.replace(/^(st|nd|rd|th)/i, '');
                break;
            case "w":
                value = value.replace(/^([0-6])/, '');
                break;
            case "z":
                value = value.replace(/^([0-9]{1,3})/, '');
                break;
            case "W":
                value = value.replace(/^([0-9]{1,2})/, '');
                break;
            case "F":
                var months = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];
                var pattern = new RegExp("^(" + months.join('|') + ")", 'i');
                
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    val = val.toLowerCase();
                    
                    value = value.substr(val.length);
                    
                    for (var i = 0; i < months.length; i++){
                        if (val == months[i]){
                            date.month = i + 1;
                            break;
                        }
                    }
                }
                
                break;
            case "m":
                var months = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
                var pattern = new RegExp("^(" + months.join('|') + ")", 'i');
                
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    
                    value = value.substr(val.length);
                    
                    for (var i = 0; i < months.length; i++){
                        if (val == months[i]){
                            date.month = i + 1;
                            break;
                        }
                    }
                }
                
                break;
            case "M":
                var months = ["jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec"];
                var pattern = new RegExp("^(" + months.join('|') + ")", 'i');
                
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    val = val.toLowerCase();
                    
                    value = value.substr(val.length);
                    
                    for (var i = 0; i < months.length; i++){
                        if (val == months[i]){
                            date.month = i + 1;
                            break;
                        }
                    }
                }
                
                break;
            case "n":
                var months = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
                var pattern = new RegExp("^(" + months.join('|') + ")", 'i');
                
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    val = val.toLowerCase();
                    
                    value = value.substr(val.length);
                    
                    for (var i = 0; i < months.length; i++){
                        if (val == months[i]){
                            date.month = i + 1;
                            break;
                        }
                    }
                }
                
                break;
            case "t":
                value = value.replace(/^(28|29|30|31)/, "");
                break;
            case "L":
                value = value.replace(/^([0-1])/, "");
                break;
            case "o":
            case "Y":
                var pattern = new RegExp("^[0-9]{4,4}");
                var match = pattern.exec(value);
                
                if (match != null){
                    date.year = parseInt(match[0]);
                    
                    value = value.substr(4);
                }
                break;
            case "y":
                var pattern = new RegEx("^[0-9]{2,2}");
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = parseInt(match[0]);
                    
                    if (val >= 50){
                        date.year = val + 1900;
                    }else{
                        date.year = val + 2000;
                    }
                    
                    value = value.substr(2);
                }
                break;
            case "a":
            case "A":
                var pattern = new RegExp("^(am|pm)", 'i');
                var match = pattern.exec(value);
                
                if (match != null){
                    var offset = 0;
                    var val = match[0];
                    
                    if (val.toLowerCase() == 'pm'){
                        offset = 12;
                    }
                    
                    if (date.hour == '') {
                        date.hour = offset;
                    }else{
                        date.hour = date.hour + offset;
                        
                        if (date.hour == 24){
                            date.hour = 0;
                        }
                    }
                }
                
                value = value.substr(2);
                break;
            case "B":
                value = value.replace(/^([0-9]{3,3})/i, '');
                break;
            case "g":
            case "h":
                var pattern = new RegExp("^([0-9]{1,2})");
                var match = pattern.exec(value);
                if (match != null){
                    var val = match[0];
                    value = value.substr(val.length);
                    val = parseInt(val);
                    
                    if (date.hour != '') {
                        val = parseInt(date.hour) + val;
                    }
                    
                    if (val == 24){
                        val = 0;
                    }
                    
                    date.hour = val;
                }
                break;
            case "G":
            case "H":
                var pattern = new RegExp("^([0-9]{1,2})");
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    value = value.substr(val.length);
                    val = parseInt(val);
                    
                    date.hour = val;
                }
                break;
            case "i":
                var pattern = new RegExp("^([0-9]{1,2})");
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    value = value.substr(val.length);
                    val = parseInt(val);
                    
                    date.minutes = val;
                }
                break;
            case "s":
                var pattern = new RegExp("^([0-9]{1,2})");
                var match = pattern.exec(value);
                
                if (match != null){
                    var val = match[0];
                    value = value.substr(val.length);
                    val = parseInt(val);
                    
                    date.seconds = val;
                }
                break;
            case "u":
                value = value.replace(/^([0-9]{6,6})/, '');
                break;
            default:
                value = value.substr(1);
                break;
            }
        }
        
        for(var c = 0; c < output_pattern.length; c++){
            var chr = output_pattern[c];
            
            switch (chr){
            case "d":
                if (date.day_of_month < 10){
                    output = output + '0';
                }
                output = output +  date.day_of_month.toString();
                break;
            case "j":
                output = output +  date.day_of_month.toString();
                break;
            case "m":
                if (date.month < 10){
                    output = output + '0';
                }
                output = output +  date.month.toString();
                break;
            case "M":
                var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                output = output + months[date.month - 1];
                break;
            case "F":
                var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                output = output + months[date.month - 1];
                break;
            case "n":
                output = output +  date.month.toString();
                break;
            case "Y":
                output = output +  date.year.toString();
                break;
            case "y":
                output = output +  date.year.toString().substr(2);
                break;
            case "g":
                var hour = date.hour;
                if (date.hour > 12) {
                    hour = hour - 12;
                }
                output = output + hour.toString();
                break;
            case "G":
                var hour = date.hour;
                if (date.hour > 12) {
                    hour = hour - 12;
                }
                
                hour = hour.toString();
                if (hour.length == 1){
                    output = output + '0';
                }
                
                output = output + hour;
                break;
            case "h":
                var hour = date.hour;
                output = output + hour.toString();
                break;
            case "H":
                var hour = date.hour;
                hour = hour.toString();
                if (hour.length == 1){
                    output = output + '0';
                }
                
                output = output + hour;
                break;
            case "i":
                if (date.minutes < 10){
                    output = output + '0';
                }
                
                output = output + date.minutes.toString();
                break;
            case "s":
                if (date.seconds < 10){
                    output = output + '0';
                }
                
                output = output + date.seconds.toString();
                break;
            case "a":
                if (date.hour < 12) {
                    output = output + 'am';
                }else{
                    output = output + 'pm';
                }
                break;
            case "A":
                if (date.hour < 12) {
                    output = output + 'AM';
                }else{
                    output = output + 'PM';
                }
                break;
            default:
                output = output + chr;
                break;
            }
        }
        
        return output;
    }
    
};


