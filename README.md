# maildodo
邮多多邮件发送器，使用该发送器前需要在邮多多官网（`www.maildodo.cn`）里注册并申请AppID和AppSecret才可使用。

使用 composer 的安装方法：

在项目根目录下增加composer.json文件并加入以下代码：

    "require": {
        "wasoon/maildodo": "^1.1"
    }

然后在命令行执行以下安装命令：

`composer require wasoon/maildodo`
或安装开发版：
`composer require wasoon/maildodo:dev-master`

composer 安装后的使用方法：

1、在您的PHP公共脚本或是需要调用的文件里引入 composer 自动加载文件：

`require 'vendor/autoload.php';`