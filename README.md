# Yii2 Bugsnag integration
To use, configure as such:

    $config = [
        'components' => [
            'errorHandler' => [
                'class' => 'pinfirestudios\yii2bugsnag\BugsnagWebErrorHandler' // For your web configuration
                'class' => 'pinfirestudios\yii2bugsnag\BugsnagConsoleErrorHandler' // For your console configuration
            ],
            'bugsnag' => [
                'class' => 'pinfirestudios\yii2bugsnag\BugsnagComponent', // Or your override of such
                'bugsnag_api_key' => 'YOUR API KEY',
                'notifyReleaseStages' => ['staging', 'production'],
            ],
            'log' => [
                'traceLevel' => 8,
                'targets' => [
                    [
                        'class' => 'pinfirestudios\yii2bugsnag\BugsnagLogTarget',
                        'levels' => ['error', 'warning', 'info', 'trace'],
                        'logVars' => [],
                    ]
                ],
            ],
        ],
    ];

If you would like to use Bugsnag's javascript on your site, you'll need to install *bower-asset/bugsnag*:

1. Add the following to your project's composer.json

    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ]

2. Require bower-asset/bugsnag

    composer require bower-asset/bugsnag

3. Once you have it installed, simply depend on BugsnagAsset in your AppAsset.  This will automatically register Bugsnag's javascript to the page.  Default version is 3.
 
    class AppAsset extends AssetBundle
    {
        public $depends = [
            'pinfirestudios\yii2bugsnag\BugsnagAsset',
        ];
    }

If you need to use version 2 of Bugsnag's javascript, you can specify the version in your configuration.  See [Customizing Asset Bundles](http://www.yiiframework.com/doc-2.0/guide-structure-assets.html#customizing-asset-bundles).
