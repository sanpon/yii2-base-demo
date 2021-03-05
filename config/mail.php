<?php
/**
 * 邮件配置
 * @author pawn
 * @date 2017年4月29日23:30:10
 */
return [
    'class' => 'yii\swiftmailer\Mailer',
    'viewPath' => '@common/mail',
    // send all mails to a file by default. You have to set
    // 'useFileTransport' to false and configure a transport
    // for the mailer to send real emails.
    'useFileTransport' => true,
];
