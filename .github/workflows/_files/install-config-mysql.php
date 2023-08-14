<?php
return [
    'db-host' => '{{mysql-host}}',
    'db-user' => '{{mysql-user}}',
    'db-password' => '{{mysql-password}}',
    'db-name' => '{{mysql-db-name}}',
    'db-prefix' => '',
    'backend-frontname' => 'backend',
    'search-engine' => 'elasticsearch7',
    'elasticsearch-host' => '{{elasticsearch-host}}',
    'elasticsearch-port' => '{{elasticsearch-port}}',
    'admin-user' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
    'admin-password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
    'admin-email' => \Magento\TestFramework\Bootstrap::ADMIN_EMAIL,
    'admin-firstname' => \Magento\TestFramework\Bootstrap::ADMIN_FIRSTNAME,
    'admin-lastname' => \Magento\TestFramework\Bootstrap::ADMIN_LASTNAME
];
