//we need autocomplete jquery ui

(function($){
    $.fn.select = function(options){
        if(typeof options == "string"){
            switch(options){
                case "destroy":
                    $(this).show().next(".input-select").remove();
                case "open":
                    this.each(function(){
                        var $element = $(this);
                        
                        if($element.next(".input-select").length > 0){
                            $input = $element.next(".input-select").find("input");
                            $input.focus();
        					$input.autocomplete("search", "");
                        }
                        
                    });
                case "close":
                    this.each(function(){
                        var $element = $(this);
                        
                        if($element.next(".input-select").length > 0){
                            $input          = $element.next(".input-select").find("input");
                            $autocomplete   = $input.autocomplete("instance");
                            
                            if($autocomplete.menu.element.is(":visible")){
                    			$autocomplete.menu.element.hide();
                    			$autocomplete.menu.blur();
                    		}
                        }
                    });
            }
            
            return;
        }
        
        var o = {
            onNotFound: $.noop,
            markMatch: true,
            inputEnable: true,
            autocomplete: {
                position: { collision: "flip" }
            }
        };
        
        var opts = $.extend({}, o, options);
        
        var wrapper = '<div class="input-select">\
                           <input type="text" />\
                           <div class="arrow-container">\
                               <span>\
                                   <b role="presentation"></b>\
                               </span>\
                           </div>\
                       </div>';
        
        return this.each(function(){
            var $element = $(this);
            
            $element.hide();
            
            if($element.next(".input-select").length > 0){
                return;
            }
            
            var $wrapper = $(wrapper);
            $element.after($wrapper);
            
            var $input = $wrapper.find("input");
            $input.attr("placeholder", $element.data("placeholder"));
            
            if(!opts.inputEnable){
                $input.prop("readonly", true);
            }
            
            var $dropDownContainer  = $wrapper.find(".arrow-container");
            
            var value;
            
            var $selected = $element.children('[selected="selected"]');
            
            if($selected.length){ //fix ":selected"  that if no selected and has disabled, then selected is 2 ... no 1
                value = $selected.html();
            }else{
                value = $element.children().first().html();
            }
            
            $input.val(value);
            
            $input.autocomplete($.extend({}, {
				delay: 0,
				minLength: 0,
				source: function(request, response){
				    var $options = $element.children("option");
                    
				    if(request.term){
				        var replacer = new RegExp("(" + $.ui.autocomplete.escapeRegex(request.term) + ")", "gi");
                        
                        var items = $options.map(function(){
        				    //var text = $(this).text();
                            
                            var markText = this.innerHTML.replace(replacer, "<span class=\"input-select-results-match\">$1</span>");
                            
                            /*
                            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                            matcher.test(text)
                            */
                            
        					if(markText.indexOf("<span class") != -1){
                                return {
        							label: (opts.markMatch ? markText : this.innerHTML),
        							value: this.innerHTML,
        							option: this
        						}
        					}
        				});
				    }else{
                        var items = $options.map(function(){
        				    //var text = $(this).text();
                            
                            return {
    							label: this.innerHTML,
    							value: this.innerHTML,
    							option: this
    						}
        				});
				    }
                    
                    response(items);
    			},
                open: function(){
                    $wrapper.addClass("input-select-opened");
                    
                    var menuTop = $autocomplete.menu.element.offset().top;
                    var wrapperTop = $wrapper.offset().top;
                    
                    if(menuTop > wrapperTop){ //bottom menu
                        $autocomplete.menu.element.removeClass("input-select-results-top");
                        $autocomplete.menu.element.addClass("input-select-results-bottom");
                    }else{ // top menu
                        $autocomplete.menu.element.removeClass("input-select-results-bottom");
                        $autocomplete.menu.element.addClass("input-select-results-top");
                    }
                },
                close: function(){
                    $wrapper.removeClass("input-select-opened");
                }
            }, opts.autocomplete));
            
            $autocomplete = $input.autocomplete("instance");
            
            $autocomplete.menu.element.removeAttr("class").addClass("input-select-results");
            
            $.extend($autocomplete, {
            	__response: function(content){
            		if(content){
            			content = this._normalize(content);
            		}
                    
            		this._trigger("response", null, {content: content});
            		
                    if(!this.options.disabled && content && content.length && !this.cancelSearch){
            			this._suggest(content);
            			this._trigger("open");
            		}else{
                        opts.onNotFound.call(this, $element, $wrapper);
            		}
            	},
                _renderMenu: function(ul, items){
                    var that = this;
                    
                    $.each(items, function(index, item){
                        that._renderItemData(ul, item);
                    });
                },
                _renderItem: function(ul, item){
                    var $li = $('<li>').addClass("ui-menu-item")
                                       .append(item.label);
                                       
                    if(item.option.disabled){
                        $li.attr("disabled", "disabled");
                    }
                                       
                    return $li.appendTo(ul);
                },
                _resizeMenu: function() {
                    $(this.menu.element).outerWidth($wrapper.outerWidth(true));
                },
                _suggest: function(items){
            		var ul = this.menu.element.empty();
            		this._renderMenu( ul, items );
            		this.isNewMenu = true;
            		this.menu.refresh();
            
            		this.open();
            
            		if(this.options.autoFocus){
            			this.menu.next();
            		}
            	},
                _normalize: function( items ) {
            		// assume all items have the right format when the first item is complete
            		if ( items.length && items[ 0 ].label && items[ 0 ].value ) {
            			return items;
            		}
            		return items;
            	},
                open: function(){
                    var ul = this.menu.element;
                    // size and position menu
            		ul.show();
                    
                    this._resizeMenu();
                    
                    ul.position($.extend({
                            of: $input
                        },
                        this.options.position
                    ));
                }
            });
            
            $input.on("autocompleteselect", function(event, ui){
                var currentValue = $element.val();
                
				ui.item.option.selected = true;
                
                $element.trigger("select", event, {
					item: ui.item.option
				});
                
                if(currentValue != ui.item.option.value){
                    $element.trigger("change", event, {
    					item: ui.item.option
    				});
                }
			});
            
            $input.on("autocompletechange", function(event, ui){
				// Selected an item, nothing to do
				if(ui.item){
					return;
				}

				// Search for a match (case-insensitive)
				var value = this.value.toLowerCase();
                
				var	valid = false;
                
				$element.children("option").each(function(){
					if($(this).text().toLowerCase() === value){
						this.selected = valid = true;
						return false;
					}
				});

				// Found a match, nothing to do
				if (valid){
					return;
				}

				// Remove invalid value
				$input.val("");
				$element.val("");
                
				$autocomplete.term = "";
			});
        
            var wasOpen = false;
            
            $dropDownContainer.on("mousedown", function(){
                wasOpen = $input.autocomplete("widget").is(":visible");
            });
            
            $dropDownContainer.on("click", function(){
				// Close if already visible
				if(wasOpen){
				    $input.blur();
					return;
				}

				// Pass empty string as value to search for, displaying all results
				$input.autocomplete("search", "");
            });
            
            if(!opts.inputEnable){
                $input.on("mousedown", function(){
    				wasOpen = $input.autocomplete("widget").is(":visible");
                });
                
                $input.on("click", function(){
    				// Close if already visible
    				if(wasOpen){
    				    $input.blur();
    					return;
    				}
    
    				// Pass empty string as value to search for, displaying all results
    				$input.autocomplete("search", "");
                });
            }
			
        });
    }
})(jQuery);