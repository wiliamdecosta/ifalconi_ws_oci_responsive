<?php
error_reporting((E_ALL ^ E_DEPRECATED ^ E_WARNING) & ~E_NOTICE);
/*
    Theme Setting
*/
$sysConfig['Theme.siteTitle'] = 'PAYMENT CCBS';
$sysConfig['Theme.defaultTheme'] = 'default';
$sysConfig['Theme.defaultPage'] = 'default';

/* Database Setting */
$sysConfig['DB.name'] = 'ifp_db';
$sysConfig['DB.user'] = 'ifp';
$sysConfig['DB.password'] = 'ifp123';
$sysConfig['DB.host'] = '192.168.1.101:5444';
$sysConfig['DB.type'] = 'postgres';

/*
    Module Setting
*/
$sysConfig['Module.defaultModule'] = 'paymentccbs';
$sysConfig['Module.defaultClass'] = 'paymentccbs';
$sysConfig['Module.defaultMethod'] = 'main';

/* Session Setting */
$sysConfig['Session.Duration'] = 7;
$sysConfig['Session.InactivityTimeout'] = 90;
