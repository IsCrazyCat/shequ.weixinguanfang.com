/*******************************************************************************
* KindEditor - WYSIWYG HTML Editor for Internet
* Copyright (C) 2006-2011 kindsoft.net
*
* @author Roddy <luolonghao@gmail.com>
* @site http://www.kindsoft.net/
* @licence http://www.kindsoft.net/license.php
*******************************************************************************/

KindEditor.plugin('fenlan',function(K){
	var self = this, name = 'fenlan', lang = self.lang(name + '.');
	self.plugin.fenlan = {
		edit : function() {
			var html = ['<div style="padding:20px;">',
					'<div class="ke-dialog-row">',
					'<label for="keName">' + lang.text + '</label>',
					'<input class="ke-input-text" type="text" id="keName" name="name" value="" style="width:100px;" />',
					'</div>',
					'</div>'].join('');
			var dialog = self.createDialog({
				name : name,
				width : 300,
				title : lang.name,
				body : html,
				yesBtn : {
					name : self.lang('yes'),
					click : function(e) {
						self.insertHtml('<h4 class="fenlan"><div>【' + nameBox.val() + '】</div></h4>').hideDialog().focus();
					}
				}
			});
			var div = dialog.div,nameBox = K('input[name="name"]',div);
			nameBox[0].focus();
			nameBox[0].select();
		}
	};
	self.clickToolbar(name,self.plugin.fenlan.edit);
});
