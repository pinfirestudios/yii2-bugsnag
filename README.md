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

