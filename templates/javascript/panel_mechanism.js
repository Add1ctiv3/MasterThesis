// JavaScript Document
//Required library -> jQuery

function createJPanel(panelTarget, jOptions) {
	
	var JPanel = {
		
		panel: panelTarget,
		
		visible: false,
		
		initialHtml: panelTarget.html(),
		
		blackscreen: jOptions.blackScreen | false,
		
		draggable: jOptions.draggable | false,
		
		onCloseCallback: function() { if(jOptions.onCloseCallback) {jOptions.onCloseCallback();}; },		
		
		onBeforeShow: function() { if(jOptions.onBeforeShow) {jOptions.onBeforeShow();}; },
		
		show: function() { //start of show method
							
							var _this = this;
							
							_this.panel.css({'zIndex': '999'});
							
							//creating the blackscreen
							if(_this.blackscreen) {
								
								var bs = "<div id='dumpener'>&nbsp;</div>";
								$(bs).insertAfter("body");
								
								$("#dumpener").css({
									'userSelect': 'none',
									'display': 'none',
									'zIndex': '900',
									'width': $(document).width(),
									'height': $(document).height(),
									'position': 'absolute',
									'top': '0',
									'left': '0',
									'background': 'rgba(0, 0, 0, 0.75)'									
								});
								
								$("#dumpener").fadeIn(200, 'swing');
								
							} //end of blackscreen creation
							
							var topPos = 150;
							var leftPos = ($(document).width()/2 - _this.panel.width()/2);
							
							_this.panel.css({ top: topPos,
											  left:  leftPos
											 });
											 
							_this.onBeforeShow();
							
							_this.panel.fadeIn(300, 'swing', function() {
								_this.panel.draggable();
								_this.visible = true;
							});
							
							//setting the close panel triggers
							_this.panel.find(".panel-header .close-panel-icon").click(function() {								
								_this.hide();
							});
														
						}, //end of show method
		
		hide: function() { //start of hide method
							
							var _this = this;
							
							if(_this.blackscreen) {
								$("#dumpener").fadeOut(200).remove();
							}
							
							_this.panel.fadeOut(200, 'swing', function() {
								_this.panel.removeAttr("style").hide();
								_this.panel.html(_this.initialHtml);
								_this.onCloseCallback();
								_this.panel.find("img").each(function() {
									$(this).attr("src", $(this).attr("src") + "?" + new Date().getTime());
								});
							});
														
							_this.visible = false;							
						
						}, // end of hide method
						
		toggle: function() { //start of toggle method
							
							var _this = this;
							
							if(_this.visible) {
								_this.hide();
							} else {
								_this.show();
							}
							
						} // end of toggle method
		
	}
	
	return JPanel;
	
}