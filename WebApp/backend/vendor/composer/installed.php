<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '088ce6485f2e00e880523d30bc400ed786f94741',
        'type' => 'library',
        'install_path' => __DIR__ . '/../Prosjekt/',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '088ce6485f2e00e880523d30bc400ed786f94741',
            'type' => 'library',
            'install_path' => __DIR__ . '/../Prosjekt/',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'phpmailer/phpmailer' => array(
            'pretty_version' => 'v6.9.3',
            'version' => '6.9.3.0',
            'reference' => '2f5c94fe7493efc213f643c23b1b1c249d40f47e',
            'type' => 'library',
            'install_path' => __DIR__ . '/../phpmailer/phpmailer',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roave/security-advisories' => array(
            'pretty_version' => 'dev-latest',
            'version' => 'dev-latest',
            'reference' => '786548323f3a9452244ddf83672e34c260d6b717',
            'type' => 'metapackage',
            'install_path' => null,
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => true,
        ),
    ),
);
