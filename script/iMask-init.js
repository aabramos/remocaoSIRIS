window.addEvent('domready', function() {
	new iMask({  
	    onFocus: function(obj) {
                input_focus(obj);
	    },  
	  
	    onBlur: function(obj) {  
                input_blur(obj);
	    },  
	  
	    onValid: function(event, obj) {  
	    },  
	  
	    onInvalid: function(event, obj) {  
	    }  
	});
});