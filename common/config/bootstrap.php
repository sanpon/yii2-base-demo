<?php
//设置快捷方式及Yii的自动加载[yii非组件新模块默认不会自动加载]
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('@backend', dirname(dirname(__DIR__)) . '/backend');
Yii::setAlias('@entry', dirname(dirname(__DIR__)) . '/entry');
Yii::setAlias('@console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('@config', dirname(dirname(__DIR__)) . '/config');
Yii::setAlias('@library', dirname(dirname(__DIR__)) . '/library');
Yii::setAlias('@runtime', dirname(dirname(__DIR__)) . '/runtime');
Yii::setAlias('@images', dirname(dirname(__DIR__)) . '/images');
