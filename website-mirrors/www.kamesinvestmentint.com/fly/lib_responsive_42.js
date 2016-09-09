
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: LIB_RESPONSIVE_42.JS
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* temporary fix */
if ( typeof console == 'undefined' ) {
	window.console = {
		log: function () {}
	};
}

var currencyConverterClass = function(){
	/**
	* plugin configs
	**/
	this.config = new Array();
	
	/**
	* plugin phrases
	**/
	this.phrases = new Array();
	
	/**
	* currency rates
	**/
	this.rates = new Array();
	
	/**
	* reference to this object
	**/
	var self = this;

	/**
	* convert currency on listing details page
	**/	
	this.listingDetails = function(code){
		if ( !this.rates[code] ) {
			console.log('FLYNAX DEBUG: User currency does not exists in rates list');
			return;
		}

		var obj = $('div#df_field_converted_price div.value');
		var item = trim($('div.listing-details div.price-tag').text());
		var curLine = self.decodePrice(item);

		if ( curLine && curLine.key != code && curLine.price  ) {
			var curRate = self.inRange(curLine.currency);
			var curDollar = curLine.price/curRate;
			var findRate = self.inRange(code, true);
			var newPrice = curDollar*findRate;
			
			$(obj).find('> span').html(self.encodePrice(newPrice));
			$(obj).parent().show();

			/* change flag */
			if ( this.config.show_flags ) {
				var flag = this.rates[code][1][0];
				var flag_src = $(obj).find('> img').css('background-image').replace(/([\w]{3})\.png+/g, flag.toLowerCase()+'.png');
				$(obj).find('> img').css('background-image', flag_src);
			}
		}
		else {
			$(obj).parent().hide();
		}
	};

	/**
	* convert currency in grid
	**/	
	this.grid = function(code){
		if ( !code ) {
			code = this.config['currency'];
		}

		if ( !this.rates[code] ) {
			console.log('FLYNAX DEBUG: User currency does not exists in rates list');
			return;
		}

		$('section#listings > article div.price-tag').each(function(){
			// wrap price in span
			if ( $(this).find('span').length <= 0 ) {
				$(this).wrapInner('<span class="tmp"></span>');
			}

			var curLine = self.decodePrice($(this).find('span:first').text());
			if ( curLine && curLine.key != code && curLine.price  ) {
				var curRate = self.inRange(curLine.currency);
				var curDollar = curLine.price/curRate;
				var findRate = self.inRange(code, true);
				var newPrice = curDollar*findRate;
				
				if ( $(this).find('span.converted').length <= 0 ) {
					$(this).append('<span class="converted"></span>');
				}

				$(this).find('> span.converted').show().html(self.encodePrice(newPrice));
				$(this).find('> span:not(.tmp,.converted)').hide();
			}
			else {
				$(this).find('> span.converted').hide();
				$(this).find('> span:not(.tmp,.converted)').show();
			}
		});
	};

	/**
	* convert currency in featured gallery on the home page
	**/	
	this.featuredGallery = function(code){
		if ( !this.rates[code] ) {
			console.log('FLYNAX DEBUG: User currency does not exists in rates list');
			return;
		}

		var obj = $('div.featured_gallery div.fg-price');

		// wrap price in span
		if ( $(obj).find('span').length <= 0 ) {
			$(obj).wrapInner('<span class="tmp"></span>');
		}

		var item = trim(obj.find('> span:first').text());
		var curLine = self.decodePrice(item);

		if ( curLine && curLine.key != code && curLine.price  ) {
			var curRate = self.inRange(curLine.currency);
			var curDollar = curLine.price/curRate;
			var findRate = self.inRange(code, true);
			var newPrice = curDollar*findRate;
			
			if ( $(obj).find('span.converted').length <= 0 ) {
				$(obj).append('<span class="converted"></span>');
			}

			$(obj).find('> span.converted').html(self.encodePrice(newPrice)).show();
			$(obj).find('span.tmp').hide();
		}
		else {
			$(obj).find('span.tmp').show();
			$(obj).find('span.converted').hide();
		}
	}

	this.featured = function(code){
		if ( !this.rates[code] ) {
			console.log('FLYNAX DEBUG: User currency does not exists in rates list');
			return;
		}

		/* featured listings conversion disabled */
		if ( !this.rates[this.config['currency']] )
			return;

		$('ul.featured > li > ul > li.price_tag div').each(function(){
			// wrap price in span
			if ( $(this).find('span').length <= 0 ) {
				$(this).wrapInner('<span class="tmp"></span>');
			}

			var curLine = self.decodePrice($(this).find('span:first').text());
			if ( curLine && curLine.key != code && curLine.price  ) {
				var curRate = self.inRange(curLine.currency);
				var curDollar = curLine.price/curRate;
				var findRate = self.inRange(code, true);
				var newPrice = curDollar*findRate;
				
				if ( $(this).find('span.converted').length <= 0 ) {
					$(this).append('<span class="converted"></span>');
				}

				$(this).find('> span.converted').show().html(self.encodePrice(newPrice));
				$(this).find('> span.tmp').hide();
			}
			else {
				$(this).find('> span.converted').hide();
				$(this).find('> span.tmp').show();
			}
		});
	};

	this.convertFeatured = function(){
		this.featured(this.config['currency']);
	};
	
	/**
	* is in range
	*
	* @param string val - requested value
	* @param bool keySearch - key search mode
	*
	**/
	this.inRange = function(val, keySearch){
		if ( !this.rates )
			return false;
		
		if ( keySearch ) {
			return this.rates[val][0];
		}
		else {
			for (var i in this.rates) {
				if ( $.inArray(val, this.rates[i][1]) >= 0 ) {
					return this.rates[i][0];
				}
			}
		}
		
		return false;
	}
	
	/**
	* encode price
	*
	* @param string str - requested string
	* @param bool currency - hide currency
	* @param bool currency - hide separator
	**/
	this.encodePrice = function(str, hide_currency, hide_separator){
		if ( !str )
			return false;

		str = Math.floor(str * 100) / 100;
		str = str.toFixed(2);
	
		/* convert price to string */
		eval("var converted = '"+ str +"'");
		
		var index = converted.indexOf('.');
		var rest = this.config['show_cents'] ? '.00' : '';
		
		if ( index >= 0 ) {
			rest = converted.substring(index);
			
			if ( !this.config['show_cents'] ) {
				rest = '';
			}
			
			converted = converted.substring(0, index);
		}
		
		/* revers string */
		var res = '';
		if ( !hide_separator ) {
			converted = converted.reverse();
			for(var i = 0; i < converted.length; i++) {
				var char = converted.charAt(i);
				res += char;
				var j = i+1;
				if ( j % 3 == 0 && j != converted.length ) {
					res += this.config['price_delimiter'];
				}
			}
			converted = res.reverse();
		}

		if (hide_currency) {
			converted = converted+rest;
		}
		else {
			converted = this.rates[this.config['currency']][1][0]+' '+converted+rest;
		}

		converted = !this.config['show_flags'] && this.config['position'] == 'after' ? ' - '+ converted : converted;

		return converted;
	}
	
	/**
	* decode price
	*
	* @param string str - requested string
	**/
	this.decodePrice = function(str){
		if ( !str )
			return false;
			
		var out = new Array();
		out['currency'] = out['key'] = false;
		
		var patter = new RegExp('(<.*?>)', 'ig');
		str = trim(str.replace(patter, ''));
		
		/* parse currency */
		var pattern = '^([A-Za-z\\W]{1,3})?\\s*?([0-9\.\,\'\:\;\"]+)\\s*?([A-Za-z\\W]{1,3})?$';
		var matches = str.match(pattern);

		if ( !matches ) {
			return false;
		}
		
		if (matches[1] || matches[3]) {
			out['currency'] = matches[1] ? trim(matches[1]) : trim(matches[3].replace(',',''));
			out['currency'] = out['currency'].replace(',', '');
			for (var i in this.rates) {
				if ( $.inArray(out['currency'], this.rates[i][1]) >= 0 ) {
					out['key'] = i;
				}
			}
			
			str = matches[2];
		}
		else {
			return false;
		}
		
		if ( this.config['price_delimiter'] != '' ) {
			var s = new RegExp("\\"+this.config['price_delimiter'], 'gi');
			str = str.replace(s, '');
		}
		
		str = parseInt(str);
		
		out['price'] = str;
		
		return out;
	}
}

var currencyConverter = new currencyConverterClass();

if ( typeof window.reverse != 'function' ) {
	String.prototype.reverse = function() {
		var s = "";
		var i = this.length;
		while (i>0) {
			s += this.substring(i-1,i);
			i--;
		}
		return s;
	}
}

$(document).ready(function(){
	$('#currency_selector li > a').click(function(){
		$('#currency_selector li > a.active').removeClass('active');
		$(this).addClass('active');

		var code = $(this).attr('accesskey');
		createCookie('curConv_code', code, 31);

		currencyConverter.config['currency'] = code;

		if ( currencyConverter.rates[code][1][1] ) {
			var sign = currencyConverter.rates[code][1][1];
			$('#currency_selector > .default > span').attr('class', 'symbol');
		}
		else {
			var sign = currencyConverter.rates[code][1][0];
			$('#currency_selector > .default > span').attr('class', 'code');
		}

		$('#currency_selector > .default > span').text(sign);
		$('#currency_selector > .default').trigger('click');

		if ( rlPageInfo['controller'] == 'listing_details' ) {
			currencyConverter.listingDetails(code);
		}
		if ( rlPageInfo['controller'] == 'home' ) {
			currencyConverter.featuredGallery(code);
		}
		currencyConverter.grid(code);
		currencyConverter.featured(code);
	});

	$('#currency_selector > .default').click(function(){
		if ( $(this).parent().hasClass('circle') ) {
			$('span.circle_opened').not($(this).parent()).removeClass('circle_opened');
			$(this).parent().toggleClass('circle_opened');
		}
		else {
			$(this).next().toggle();
		}

		if ( !$('#currency_selector span.content > div').hasClass('mCustomScrollbar') ) {
			$('#currency_selector span.content > div').mCustomScrollbar();
		}
	});

	$(document).bind('click touchstart', function(event){
		if ( !$(event.target).parents().hasClass('circle_opened') ) {
			$('#currency_selector').removeClass('circle_opened');
		}
		if ( !$(event.target).parents().hasClass('currency-selector') ) {
			$('#currency_selector > span.content').attr('style', '');
		}
	});

	/* default conversion */
	if ( rlPageInfo['controller'] == 'listing_details' ) {
		currencyConverter.listingDetails(currencyConverter.config['currency']);
	}
	currencyConverter.grid(currencyConverter.config['currency']);
	currencyConverter.featured(currencyConverter.config['currency']);
});
