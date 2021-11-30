# QLWeCom

## 安装

- Linux
- Nginx
- MySQL >= 5.6
- PHP   == 7.4

```
cd /www/wwwroot/你的网站文件夹名
git clone https://github.com/SaintWe/QLWeCom.git ${PWD}
git config core.filemode false
wget https://getcomposer.org/installer -O composer.phar
php composer.phar
php composer.phar install
chmod -R 755 ${PWD}
chown -R www:www ${PWD}
```

将运行目录修改为 `/public`

伪静态：

```
location / {
    try_files $uri /index.php$is_args$args;
}
```

将 `/sql/all.sql` 导入

将 `.env.example` 重命名为 `.env` 并填上数据库信息

生成一个字符串填入 **APP_KEY**

修改 **APP_URL**

在企业微信创建应用

设置对接地址为：`https://你的域名/wechat`，**推荐使用 https**

将相关信息填入 `.env` 之后再保存企业微信的对接

重新生成一个 API_TOKEN，并按提示去 QL 内填写

重新生成伪装青龙部分的字符串，可对接 NOLANJDC

将多个[可能] QL 节点的信息配置好

在企业微信给该应用发送 **帮助**，然后绑定节点，对应数字

设置定时任务，每分钟 `cd /www/wwwroot/你的网站文件夹名 && php artisan schedule:run >> /dev/null 2>&1`

---

以下步骤可选

创建第二个应用

然后在其他推送中配置

将 `app/Http/Service/JdParse/Other.php.example` 重命名为 `Other.php`

将 `app/Http/Service/JdParse/Task.php.example` 重命名为 `Task.php`

将文件中的 push_id 修改为你新增的其他推送的 ID

第三个应用...

第四个应用...

...


## 其他

内置了对 iOS 用户友好的 Cookie 更新脚本

登录网址：`https://home.m.jd.com/myJd/newhome.action`

证书信任步骤： 设置 → 通用 → 关于本机 → 证书信任设置，把你刚安装的证书开启

### Surge

请打开 MITM、脚本，并且在 MITM 内安装证书，然后按上面的证书信任步骤信任证书

如果你的 Surge 拥有模组功能，添加一个远程模组
`https://你的域名/get_update_ck_plugin?type=surge.module`

如果你的 Surge 没有解锁模组功能，添加一个新配置
`https://你的域名/get_update_ck_plugin?type=surge.conf`

然后启动 Surge，打开上面的登录网址登录并刷新，此时你的微信会收到 Cookie 更新的推送

### Loon

请打开 MITM、脚本，并在证书管理内安装证书，然后按上面的证书信任步骤信任证书
在插件内，添加一个新插件，备注随意
`https://你的域名/get_update_ck_plugin?type=loon.plugin`

然后启动 Loon，打开上面的登录网址登录并刷新，此时你的微信会收到 Cookie 更新的推送

### Quantumult X

请打开 MITM、重写，并在配置证书内安装证书，然后按上面的证书信任步骤信任证书

1. 添加一个重写
2. 类型：`script-request-header`
3. 用以匹配的 URL：`^https:\/\/me-api\.jd\.com\/user_new\/info\/GetJDUserInfoUnion`
4. 脚本路径：`https://你的域名/get_update_ck_plugin?type=update_jd_ck.js`
5. 保存
6. 在 MITM 添加主机名 `me-api.jd.com`

然后启动 Quantumult X，打开上面的登录网址登录并刷新，此时你的微信会收到 Cookie 更新的推送

### Shadowrocket

点击底部的「配置」然后右上角「＋」添加一个远程配置，粘贴 `https://你的域名/get_update_ck_plugin?type=shadowrocket.conf`，下载完成后点击该配置



