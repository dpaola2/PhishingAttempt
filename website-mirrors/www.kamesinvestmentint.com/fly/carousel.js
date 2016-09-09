
(function ($, undefined) {

    $.widget( "rl.carousel", {
 
    // options
    options: { 
		btnPrev: null,
		btnNext: null,		
		auto: null,
		speed: 1000,
		diraction: 'ltr',
		vertical: false,
		circular: false,
		visible: 4,
		start: 0,
		scroll: 1,
		number: 0,
		count: 0
    },
	
    // create function
    _create: function() {		
		if ( !this.options.default_visible  ) {
			this.options.default_visible = this.options.visible;
			this.options.default_scroll = this.options.scroll;
		}
		if ( !this.options.vertical ) {
			this._elements();
			this._checkVisible();
		}
		
		if ( this.options.scroll>this.options.visible ) {
			this.options.scroll = this.options.visible;
		}
		
		if ( this.options.circular ) {
			this._elements();
			this._circular();
		}
		
		this._elements();
		this._build();
		
		this._removeActionBlock();
		this._addNextPrevActions();
		
		if ( this.options.auto ) {
			this._bindAutoScroll();
			
			$('#'+this.elements.id).hover(				
				function() {
					$(this).data('carousel')._stopSlide();
				}, function() {
					
					$(this).data('carousel')._bindAutoScroll();
				}				
			);
			
			
		}
    },
	
	_elements: function () {
		var elems = this.elements = {};
		elems.curr =  0;
		elems.interval = 0;
		elems.running = false;
		elems.id = this.element.attr('id');
		elems.ul = this.element.children('ul');
		elems.items = elems.ul.children('li');
		elems.count = elems.items.size();
		
		return;
	},
	
	_build: function () {
		
		if ( this.options.vertical ) {
			this._vertical();
		}
		else {			
			this._horizontal();
		}
		
		return;
	},
	
	_horizontal: function (rebuild) {
		elems = this.elements;
		elems.animCss=this.options.diraction == 'rtl'?"right":"left";
		elems.sizeCss="width";
		elems.ul.removeAttr('style');
		elems.liSize = elems.items.removeAttr('style').width();		
		elems.divSize = this.element.width();
		
		this._checkVisible();
		elems.marginLi = (elems.divSize - elems.liSize*this.options.visible) / (2 * this.options.visible);			
		
		if( rlConfig['template_type'] == 'responsive_42' && /*$(this.element).closest('.ling_top_block').length > 0 &&*/ this.options.visible > 1) {
			new_margin =  (elems.divSize - elems.liSize*this.options.visible) / (this.options.visible-1);
			margin_left = this.options.diraction == 'rtl' ? new_margin : 0;
			margin_right = this.options.diraction != 'rtl' ? new_margin : 0;
			
			elems.items.css({width: elems.items.width(), marginLeft: margin_left+"px",marginRight: margin_right+"px"});
			elems.ulSize = (elems.liSize + elems.marginLi*2) * (this.options.number + elems.count);
			elems.liSize = elems.liSize + new_margin;
			
		}
		else {
			$('#'+elems.id).css({width: elems.divSize});
			elems.items.css({width: elems.items.width(), marginLeft: elems.marginLi+"px",marginRight: elems.marginLi+"px"});
			elems.ulSize = (elems.liSize + elems.marginLi*2) * (this.options.number + elems.count);
			elems.liSize = elems.liSize + elems.marginLi * 2;
		}
		
		if ( this.options.diraction == 'rtl' ) {
			elems.items.css('float', 'right');
		}
		
		elems.ul.css('position', 'relative');
		position = rebuild == true ? elems.curr * elems.liSize : 0;
		elems.ul.css(elems.sizeCss, elems.ulSize+"px").css(elems.animCss, -position);
	},
	
	_vertical: function () {
		elems = this.elements;
		elems.animCss="top";
		elems.sizeCss="height";
		elems.liSize=this._height();	
		elems.divSize = elems.liSize * this.options.visible;			
		elems.ulSize = elems.liSize+(this.options.number + elems.count) ;
		elems.marginLi = parseInt((this.element.width() - elems.items.width()) / 2);
		elems.items.css({height: elems.liSize,marginLeft: elems.marginLi+"px",marginRight: elems.marginLi+"px"});
		this.element.css(elems.sizeCss, elems.divSize+"px");
		elems.ul.css('position', 'absolute');
		elems.ul.css(elems.sizeCss, elems.ulSize+((elems.liSize + elems.marginLi*2))+"px").css(elems.animCss, 0);
		
		return;
	},
	
	_checkVisible: function () {
		var self = this, elems = this.elements, opts = this.options ;
		d_w = parseInt(self.element.width() / elems.items.width());
		
		min_marg = ( self.element.width()-elems.items.width()*d_w ) - ( (d_w - 1) * 8 );	
		
		if(	min_marg <= 0 )
		{	
			d_w = d_w -1;
		}
		d_w = d_w == 0 ? 1 : d_w;
		if ( opts.visible<=d_w && d_w<opts.default_visible ) {
			opts.visible = d_w;
		}
		else if ( opts.visible>=d_w && d_w<opts.default_visible ) {
			opts.visible = d_w;
		}
		else {
			opts.visible = opts.default_visible;
		}
		
		if ( opts.scroll >= d_w && d_w < opts.default_scroll )	{
			opts.scroll = d_w;
		}
		else
		{
			opts.scroll = opts.default_scroll;
		}
		
		return;
	},
		
	_removeActionBlock: function () {
		// remove action for content block
		$(this.element).closest('.content_block').find('[onClick]').removeAttr('onClick');
		// remove action for side block
		$(this.element).closest('.side_block').find('[onClick]').removeAttr('onClick');
		
		return;
	},
	
	_height: function () {
		max_height = '';
		for ( var i = 0; i < this.options.count; i++ ) {
			if ( max_height < this.elements.items.eq(i).height() ) {
				max_height = this.elements.items.eq(i).height();
			}
		}
		
		return max_height;
	},
	
	_circular: function () {
		this.elements.ul.append(this.elements.items.slice(0).clone());	
		return;
	},
	
	_addNextPrevActions: function () {
		if ( !this.options.btnPrev || !this.options.btnNext ) {
			return;
		}
		
		var self = this,
			elems = this.elements,
			opts = this.options;
		
		this.element.before('<div class="'+this.options.btnNext.replace(".", "")+' next" ></div><div class="'+this.options.btnPrev.replace(".", "")+' prev" ></div>');
		
		if ( $(this.element).closest('.ling_top_block').length > 0 )	{
			$(this.options.btnNext).css('right', '-20px');
			$(this.options.btnPrev).css('left', '-20px');
		}
		
		$(this.options.btnNext).bind('click', function() {
			self.next();
		});
		
		$(this.options.btnPrev).bind('click', function() {
			self.prev();
		});
		
		if ( !this.options.vertical ) {
			$(window).bind('resize', function(){
				self._horizontal(true);
			});
		}
		
		return;
	},
		
	next: function () {
		if ( this.options.auto )	{
			this._iterval();
		}
		return this._slide(this.elements.curr, 'next');
	},
	
	prev: function () {
		if ( this.options.auto )	{
			this._iterval();
		}
		return this._slide(this.elements.curr, 'prev');
	},
		
	_iterval: function () {
		var self = this, elems = this.elements, opts = this.options ;
		clearInterval(self.elements.interval);
		this._bindAutoScroll();
	},
	
	_bindAutoScroll: function() {
		var self = this, elems = this.elements, opts = this.options;
		
		self.elements.interval = setInterval(function() {
			self._slide(elems.curr, 'next');
		}, opts.auto);
		
		return;
	},
	
	_stopSlide: function() {
		var self = this, elems = this.elements;
			
		clearInterval(elems.interval);
		
		return;
	},
	
	_beforeStart: function() {		
		var self = this, elems = this.elements,	opts = this.options;
		
		if ( elems.count != elems.ul.children('li').size() ) {
			elems.items = elems.ul.children('li');
			elems.count = elems.items.size();
			
			if ( opts.vertical ) {
				elems.items.css({height: elems.liSize, marginLeft: elems.marginLi+"px",marginRight: elems.marginLi+"px"});
			}
			else {
				if( rlConfig['template_type'] == 'responsive_42' && /*$(this.element).closest('.ling_top_block').length > 0 &&*/ this.options.visible > 1) {
					new_margin =  (elems.divSize - elems.items.width()*opts.visible) / (opts.visible-1);
					margin_left = opts.diraction == 'rtl' ? new_margin : 0;
					margin_right = opts.diraction != 'rtl' ? new_margin : 0;
					elems.items.css({width: elems.items.width(), marginLeft: margin_left+"px",marginRight: margin_right+"px"});
				}
				else {
					elems.items.css({width: elems.items.width(), marginLeft: elems.marginLi+"px",marginRight: elems.marginLi+"px"});
				}
			}
			
			if ( this.options.diraction == 'rtl' )	{
				elems.items.css('float', 'right');
			}
		}
		
	return;
	},
	
	_saleRent: function() {
		$('ul.featured > li ul > li.sale-rent').each(function(){
			var width = Math.floor(($(this).width() + 10) / 2); //+padding
			
			$(this).css('margin-left', '-'+width+'px');
			$(this).find('span:first')
				.css('border-left-width', width+'px')
				.css('border-right-width', width+'px');
			
			width--;
			$(this).find('span:last')
				.css('border-left-width', width+'px')
				.css('border-right-width', width+'px');
		});
	return;
	},
	
	_afterLoadAjax: function( block_key, add_count ) {		
		var self = this, elems = this.elements,	opts = this.options;
		
		if ( block_key ) {
			if ( currencyConverter.config.currency != false ) {
				currencyConverter.convertFeatured(block_key);
			}
		}
		if ( opts.templateName == 'realty_signs') {
			self._saleRent();
		}
		
		if ( !opts.circular ) {			
			self._checkVisible();
			if(opts.scroll > add_count) {
				new_curr = opts.scroll - add_count;
				elems.curr = elems.curr - new_curr;
			}
		}

		self._beforeStart();
		
		if ( typeof caroselCallback== 'function' ) {
		   caroselCallback();
		}
		
		self._animate();		
		
	return;
	},
	
	_slide: function (to, diraction) {
		var self = this,
			elems = this.elements,
			o = this.options;
		
		if ( !elems.running ) {
			curr = diraction=='next' ? to+this.options.scroll : to-this.options.scroll;
			
			if ( o.circular ) {
				if ( curr<=0 ) {
					// If first, then goto last						
					if ( to==0 ) {
						if ((o.diraction=='ltr' && diraction=='prev' && rlCarousel[elems.id] > 0 )|| (o.diraction=='rtl' && diraction=='prev' && rlCarousel[elems.id] > 0)) {
							return;
						}
						else {
							elems.ul.css(elems.animCss, -((elems.count-o.count)*elems.liSize)+"px");						
							// If "scroll" > 1, then the "to" might not be equal to the condition; it can be lesser depending on the number of elements.
							curr = elems.count-o.count-o.scroll;
						}
					}
					else if ( curr<0 ) {
						curr = 0;
					}
					
				} 
				else if ( curr>=elems.count-o.count ) {
					
					if ( to==elems.count-o.visible ) {
						// If last, then goto first
						elems.ul.css(elems.animCss, -(o.count-o.visible)*elems.liSize + "px" );
						// If "scroll" > 1, then the "to" might not be equal to the condition; it can be greater depending on the number of elements.
						curr = o.count-o.visible+o.scroll;
					}
					else if ( curr+o.visible > elems.count ) {
						curr =(elems.count-o.visible);
					}
				} 
			} 
			else {   
				// If non-circular and to points to first or last, we just return.
				if ( curr<0 ) {
					curr = 0;
				}
				else if(curr>elems.count-o.visible && rlCarousel[elems.id] == 0) {
					curr = elems.count-o.visible;
				}
				else {
					curr = curr;
				}
				
			}
			elems.curr = curr;
			
			if ( rlCarousel[elems.id] > 0 ) {
				if ( o.circular ) {
					carent_id = elems.count-o.count-1;
				}
				else {
					// carent_id = diraction == 'next' ? elems.count-1 : o.count-1;
					carent_id = elems.count-1;
				}
				
				elems.running = true;
				xajax_loadListings( carent_id, o.scroll, o.options, rlCarousel[elems.id], o.priceTag );
			}
			else {
				elems.running = true;
				self._animate();
			}
		}		
	},
	
	_animate: function(){
		var self = this, elems = this.elements, o = this.options;
		
		if ( elems.animCss == "right" ) {
			elems.ul.animate({ right: -(elems.curr*elems.liSize) } , o.speed, o.easing,
				function() {
					elems.running = false;
				}
			);
		}
		else {
			elems.ul.animate(
				elems.animCss == "left" ? { left: -(elems.curr*elems.liSize) } : { top: -(elems.curr*elems.liSize) } , o.speed, o.easing,
				function() {
					elems.running = false;
				}
			);
		}
    }
  });

})(jQuery);