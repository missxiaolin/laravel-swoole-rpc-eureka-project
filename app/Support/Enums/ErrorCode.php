<?php
namespace App\Support\Enums;

use Xin\Phalcon\Enum\Enum;

class ErrorCode extends Enum
{
    /**
     * @Message('系统错误')
     */
    public static $ENUM_SYSTEM_ERROR = 400;

    /**
     * @Message('Eureka配置有误')
     */
    public static $ENUM_EUREKA_CONFIG_INVALID = 500;
}
