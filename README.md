# LocationIP
Location IP
```
// 网站访问IP获取
$response = (new LocationIP([
    'pt' => 'gd', // tx 腾讯 gd 高德
    'key' => 'xxx'
]))->getLocation($ip);
```