var system = require('system');
var address = system.args[1];

var page = require('webpage').create();

page.viewportSize = {
	width: 1024,
	height: 768
};

page.clipRect = {
  top: 50,
  left: 200,
  width: 800,
  height: 600
};

page.open('http://'+address+'/', function() {
  page.render('images/'+address+'.png',{format: 'png', quality: '50'});
  phantom.exit();
});
