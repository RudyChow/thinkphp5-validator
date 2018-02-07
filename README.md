## README

在thinkphp5的项目开发过程中经常要自己写验证器

但其实大多数验证规则都是通过数据库中的字段定义上进行修改而来的

由于之前用的是yii2，受到其gii2的启发

想着做一个基于thinkphp5的composer插件，用以生成对应的验证器

于是就有了这个库

## 怎么引用

### × 本地引用

`$ git clone xxxxxxxxxxx`

在开发项目中的composer.json添加

```
    "repositories": {
        "thinkphp5/validator": {
            "type": "path",
            "url": "/yourpath/thinkphp5/validator/"
        }
    },
```

最后在项目中运行composer命令

`$ composer require --dev thinkphp5/validator:dev-master`

### × composer引用

`$ composer require thinkphp5/validator`


## 怎么使用

1.  填写数据库连接信息

2.  浏览器中输入`http://yourproject/validator_builder`

3.  默认生成的验证器会在`APP_PATH/common/validate`中

3.  注意目录权限问题

## 效果
![alt img](http://wx2.sinaimg.cn/large/6337e4fcgy1fo6tfhqqplj21g00pbq5j.jpg)
![alt img](http://wx3.sinaimg.cn/large/6337e4fcgy1fo7n7gezd6j21g00pbaci.jpg)
![alt img](http://wx3.sinaimg.cn/large/6337e4fcgy1fo6tfhrgtkj20kf0pnwi9.jpg)
