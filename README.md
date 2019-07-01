# 该仓库新增2项内容：
  - Logic层热加载，修改业务代码后不用重启能立即生效
  - 高性能、方便使用的表单验证

---
# Logic层热加载

> 我定义了一个业务逻辑层（Logic），该层不受服务启动时扫描，会在work进程启动后进行加载，所以，可以通过server->reload接口对代码进行重载，达到热加载效果，不用频繁重启服务，以加快开发调试速度。

操作步骤如下：
1. 下载该仓库代码：git clone https://github.com/qiao520/hyperf-skeleton
这个仓库代码是fork官方的骨架仓库，并修改了点东西。
2. 下载依赖包：composer install
注意：如果出现安装不了qiao520/swoole-logic，请先执行这个设置命令：composer config repositories.qiao520/swoole-logic vcs https://github.com/qiao520/swoole-logic
3. 启动服务（具体自行操作）
4. 需要先修改\vendor\hyperf\server\src\Server.php文件，增加如下方法，用来获取swoole的$server，代码：
```
    public function getSwServer()
    {
        return $this->server;
    }
```
5. 然后给你的IDE配置启动项，新增一个“PHP HTTP Request”启动项，具体设置，自行摸索，摸索了还不行，请联系我（380552499）
6. 启动Hyperf服务，浏览器访问http://192.168.99.100:9501/logic
7. 修改\logic\Form\DemoForm.php逻辑代码，然后点击IDE上的run按钮（快捷键shift+f10）或者浏览器请求（http://192.168.99.100:9501/reload）
8. 然后再到浏览器访问http://192.168.99.100:9501/logic，代码已秒重载

# 表单验证

组件仓库地址：https://github.com/qiao520/swoole-logic

代码示例：

表单DemoForm类
```
<?php
declare(strict_types=1);

namespace Roers\Demo;

use Roers\SwLogic\BaseForm;

class DemoForm extends BaseForm
{
    // 以下是表单属性
    public $name;
    public $email;
    public $age;
    public $sex;
    public $others;
    public $default = 0;

    // 以下是覆盖父类的默认设置
    protected $isAutoTrim = true;   // 开启自动去空格（默认开启）
    protected $defaultRequired = true;   // 开启所有属性为必填（默认未开启）
    protected $defaultErrorMessage = '{attribute}格式错误';  // 覆盖自定义错误提示信息

    /**
     * 定义验证规则
     * @return array
     */
    public function rules()
    {
        return [
            // 验证6到30个字符的字符串
            ['name', 'string', 'min' => 6, 'max' => 30, 'maxMinMessage' => '名字必须在{min}~{max}个字符范围内'],
            // 验证年龄必须是整数
            ['age', 'integer', 'min' => 18, 'max' => 100],
            // 集合验证器，验证性别必须是1或2
            ['sex', 'in', 'in' => [1, 2],],
            // 使用自定义验证器，验证名字不能重复
            ['name', 'validateName'],
            // 还可以这样用，对多个字段用同一个验证器规则
            [['age', 'sex'], 'integer'],
            // 验证邮箱格式，并且必填required对所有校验器都有效
            [['email'], 'email', 'required' => true],
            // 验证是否是数组，并对数组元素进行格式校验
            [['others'], 'array', 'validator' => 'string'],
        ];
    }

    /**
     * 字段名称映射关系
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => '名字',
            'age' => '年龄',
        ];
    }

    /**
     * 业务处理
     * @return array
     */
    public function handle()
    {
        // do something here

        // 返回业务处理结果
        return ['name' => $this->name, 'age' => $this->age];
    }

    /**
     * 自定义验证器
     * @param $attribute
     * @param $options
     * @return bool
     */
    public function validateName($attribute, $options)
    {
        $value = $this->{$attribute};

        if ($value == 'Roers.cn') {
            $this->addError($attribute, "名字{$value}已存在");
            return false;
        }

        return true;
    }
}

```

demo.php
```
<?php
use Roers\Demo\DemoForm;

function debug($msg) {
    echo $msg, PHP_EOL;
}

// 表单提交的数据
$data = [
    'name' => 'zhongdalong',
    'age' => '31',
    'sex' => '',
];
// 演示默认所有字段为非必填项
$form = DemoForm::instance($data);
if ($form->validate()) {
    $result = $form->handle();
    debug('验证通过，业务处理结果：' . json_encode($result));
} else {
    debug('验证不通过，错误提示信息：' .  $form->getError());
}

debug(str_repeat('-------', 10));

// 演示默认所有字段为必填项
$form = DemoForm::instance($data, true);
if ($form->validate()) {
    $result = $form->handle();
    debug('验证通过，业务处理结果：' . json_encode($result));
} else {
    debug('验证不通过，错误提示信息：' .  $form->getError());
}

debug(str_repeat('-------', 10));


// 演示未成年注册场景
$data['age'] = 17;
$form = DemoForm::instance($data);
if ($form->validate()) {
    $result = $form->handle();
    debug('验证通过，业务处理结果：' . json_encode($result));
} else {
    debug('验证不通过，错误提示信息：' .  $form->getError());
}

debug(str_repeat('-------', 10));


// 演示自定义验证器
$data['age'] = 18;
$data['name'] = 'Roers.cn';
$form = DemoForm::instance($data);
if ($form->validate()) {
    $result = $form->handle();
    debug('验证通过，业务处理结果：' . json_encode($result));
} else {
    debug('验证不通过，错误提示信息：' .  $form->getError());
}

debug(str_repeat('-------', 10));
```

执行结果
```
验证通过，业务处理结果：{"name":"zhongdalong","age":"31"}
----------------------------------------------------------------------
验证不通过，错误提示信息：Sex是必填项
----------------------------------------------------------------------
验证不通过，错误提示信息：年龄必须在18 ~ 100范围内
----------------------------------------------------------------------
验证不通过，错误提示信息：名字Roers.cn已存在
----------------------------------------------------------------------
```


--- 
# Hyperf介绍

Hyperf 是基于 `Swoole 4.3+` 实现的高性能、高灵活性的 PHP 持久化框架，内置协程服务器及大量常用的组件，性能较传统基于 `PHP-FPM` 的框架有质的提升，提供超高性能的同时，也保持着极其灵活的可扩展性，标准组件均以最新的 [PSR 标准](https://www.php-fig.org/psr) 实现，基于强大的依赖注入设计可确保框架内的绝大部分组件或类都是可替换的。
   
框架组件库除了常见的协程版的 `MySQL 客户端`、`Redis 客户端`，还为您准备了协程版的 `Eloquent ORM`、`GRPC 服务端及客户端`、`Zipkin (OpenTracing) 客户端`、`Guzzle HTTP 客户端`、`Elasticsearch 客户端`、`Consul 客户端`、`ETCD 客户端`、`AMQP 组件`、`Apollo 配置中心`、`基于令牌桶算法的限流器`、`通用连接池` 等组件的提供也省去了自己去实现对应协程版本的麻烦，并提供了 `依赖注入`、`注解`、`AOP 面向切面编程`、`中间件`、`自定义进程`、`事件管理器`、`简易的 Redis 消息队列和全功能的 RabbitMQ 消息队列` 等非常便捷的功能，满足丰富的技术场景和业务场景，开箱即用。

# Hyperf框架初衷

尽管现在基于 PHP 语言开发的框架处于一个百花争鸣的时代，但仍旧没能看到一个优雅的设计与超高性能的共存的完美框架，亦没有看到一个真正为 PHP 微服务铺路的框架，此为 Hyperf 及其团队成员的初衷，我们将持续投入并为此付出努力，也欢迎你加入我们参与开源建设。

# Hyperf设计理念

`Hyperspeed + Flexibility = Hyperf`，从名字上我们就将 `超高速` 和 `灵活性` 作为 Hyperf 的基因。
   
- 对于超高速，我们基于 Swoole 协程并在框架设计上进行大量的优化以确保超高性能的输出。   
- 对于灵活性，我们基于 Hyperf 强大的依赖注入组件，组件均基于 [PSR 标准](https://www.php-fig.org/psr) 的契约和由 Hyperf 定义的契约实现，达到框架内的绝大部分的组件或类都是可替换的。   

基于以上的特点，Hyperf 将存在丰富的可能性，如实现 Web 服务，网关服务，分布式中间件，微服务架构，游戏服务器，物联网（IOT）等。

# Hyperf文档

[https://doc.hyperf.io/](https://doc.hyperf.io/)