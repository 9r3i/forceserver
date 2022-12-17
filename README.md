
[![Author](https://img.shields.io/badge/author-9r3i-lightgrey.svg)](https://github.com/9r3i)
[![License](https://img.shields.io/github/license/9r3i/forceserver.svg)](https://github.com/9r3i/forceserver/blob/master/LICENSE)
[![Forks](https://img.shields.io/github/forks/9r3i/forceserver.svg)](https://github.com/9r3i/forceserver/network)
[![Stars](https://img.shields.io/github/stars/9r3i/forceserver.svg)](https://github.com/9r3i/forceserver/stargazers)
[![Issues](https://img.shields.io/github/issues/9r3i/forceserver.svg)](https://github.com/9r3i/forceserver/issues)
[![Release](https://img.shields.io/github/release/9r3i/forceserver.svg)](https://github.com/9r3i/forceserver/releases)


# forceserver
A server API for Force client

# Usage
It's really simple
```php
new ForceServer;
```
Ofcourse, with autoloader has applied on the system. If not, just require the files before the call.
```php
require_once('ForceServer.php');
require_once('ForceData.php');
new ForceServer;
```

# Plugins
Plugin are stored at ```force/plugins``` directory, with extension ```<plugin_name>.force.php```. And method to call is ```<plugin_classname>.<plugin_method>```.

Example for plugin ```website``` in website.force.php with method ```all``` from client ForceWebsite:
```js
ForceWebsite.fetch('website.all',result=>{
  /* do something with result */
});
```


# Closing
That's all there is to it. Alhamdulillaah...


