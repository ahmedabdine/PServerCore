<?php

return [
    'router' => [
        'routes' => [
            'PServerCore' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'PServerCore\Controller\Index',
                        'action' => 'index',
                        'page' => 1
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'site-news' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'news-[:page].html',
                            'constraints' => [
                                'page' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Index',
                                'action' => 'index',
                                'page' => 1
                            ],
                        ],
                    ],
                    'site-detail' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'detail-[:type].html',
                            'constraints' => [
                                'type' => '[a-zA-Z]+',
                            ],
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Site',
                                'action' => 'page'
                            ],
                        ],
                    ],
                    'site-download' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'download.html',
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Site',
                                'action' => 'download'
                            ],
                        ],
                    ],
                    'user' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'panel/account[/:action].html',
                            'constraints' => [
                                'action' => '[a-zA-Z-]+',
                            ],
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Account',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'panel_donate' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'panel/donate[/:action].html',
                            'constraints' => [
                                'action' => '[a-zA-Z-]+',
                            ],
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Donate',
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'info' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'info[/:action].png',
                            'constraints' => [
                                'action' => '[a-zA-Z-]+',
                            ],
                            'defaults' => [
                                'controller' => 'PServerCore\Controller\Info',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'pserver_caching_service' => function () {
                $cache = \Zend\Cache\StorageFactory::factory([
                    'adapter' => 'filesystem',
                    'options' => [
                        'cache_dir' => __DIR__ . '/../../../../data/cache',
                        'ttl' => 86400
                    ],
                    'plugins' => [
                        'exception_handler' => [
                            'throw_exceptions' => false
                        ],
                        'serializer'
                    ],
                ]);
                return $cache;
            },
        ],
        'aliases' => [
            'translator' => 'MvcTranslator',
            'payment_api_log_service' => 'PServerCore\Service\PaymentNotify',
        ],
        'invokables' => [
            'PServerCore\Service\PaymentNotify' => 'PServerCore\Service\PaymentNotify',
            'pserver_mail_service' => 'PServerCore\Service\Mail',
            'pserver_download_service' => 'PServerCore\Service\Download',
            'pserver_server_info_service' => 'PServerCore\Service\ServerInfo',
            'pserver_news_service' => 'PServerCore\Service\News',
            'pserver_usercodes_service' => 'PServerCore\Service\UserCodes',
            'pserver_configread_service' => 'PServerCore\Service\ConfigRead',
            'pserver_pageinfo_service' => 'PServerCore\Service\PageInfo',
            'pserver_playerhistory_service' => 'PServerCore\Service\PlayerHistory',
            'pserver_donate_service' => 'PServerCore\Service\Donate',
            'pserver_cachinghelper_service' => 'PServerCore\Service\CachingHelper',
            'pserver_user_block_service' => 'PServerCore\Service\UserBlock',
            'pserver_secret_question' => 'PServerCore\Service\SecretQuestion',
            'pserver_log_service' => 'PServerCore\Service\Logs',
            'pserver_user_role_service' => 'PServerCore\Service\UserRole',
            'pserver_login_history_service' => 'PServerCore\Service\LoginHistory',
            'pserver_coin_service' => 'PServerCore\Service\Coin',
            'pserver_timer_service' => 'PServerCore\Service\Timer',
            'pserver_add_email_service' => 'PServerCore\Service\AddEmail',
            'pserver_format_service' => 'PServerCore\Service\Format',
            'small_user_service' => 'PServerCore\Service\User',
            'payment_api_ip_service' => 'PServerCore\Service\Ip',
            'payment_api_validation' => 'PServerCore\Service\PaymentValidation',
            'zfcticketsystem_ticketsystem_service' => 'PServerCore\Service\TicketSystem',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'PServerCore\Controller\Index' => 'PServerCore\Controller\IndexController',
            'SmallUser\Controller\Auth' => 'PServerCore\Controller\AuthController',
            'PServerCore\Controller\Auth' => 'PServerCore\Controller\AuthController',
            'PServerCore\Controller\Site' => 'PServerCore\Controller\SiteController',
            'PServerCore\Controller\Account' => 'PServerCore\Controller\AccountController',
            'PServerCore\Controller\Donate' => 'PServerCore\Controller\DonateController',
            'PServerCore\Controller\Info' => 'PServerCore\Controller\InfoController',
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.twig',
            'p-server-core/index/index' => __DIR__ . '/../view/p-server-core/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/403' => __DIR__ . '/../view/error/403.twig',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'email/tpl/register' => __DIR__ . '/../view/email/tpl/register.phtml',
            'email/tpl/password' => __DIR__ . '/../view/email/tpl/password.phtml',
            'email/tpl/country' => __DIR__ . '/../view/email/tpl/country.phtml',
            'email/tpl/secretLogin' => __DIR__ . '/../view/email/tpl/secret_login.phtml',
            'email/tpl/ticketAnswer' => __DIR__ . '/../view/email/tpl/ticket_answer.phtml',
            'email/tpl/addEmail' => __DIR__ . '/../view/email/tpl/add_email.phtml',
            'helper/sidebarWidget' => __DIR__ . '/../view/helper/sidebar.phtml',
            'helper/sidebarLoggedInWidget' => __DIR__ . '/../view/helper/logged-in.phtml',
            'helper/sidebarServerInfoWidget' => __DIR__ . '/../view/helper/server-info.phtml',
            'helper/formWidget' => __DIR__ . '/../view/helper/form.phtml',
            'helper/formNoLabelWidget' => __DIR__ . '/../view/helper/form-no-label.phtml',
            'helper/newsWidget' => __DIR__ . '/../view/helper/news-widget.phtml',
            'helper/sidebarTimerWidget' => __DIR__ . '/../view/helper/timer.phtml',
            'helper/playerHistory' => __DIR__ . '/../view/helper/player-history.phtml',
            'helper/sidebarLoginWidget' => __DIR__ . '/../view/helper/login-widget.phtml',
            'zfc-ticket-system/new' => __DIR__ . '/../view/zfc-ticket-system/ticket-system/new.twig',
            'zfc-ticket-system/view' => __DIR__ . '/../view/zfc-ticket-system/ticket-system/view.twig',
            'zfc-ticket-system/index' => __DIR__ . '/../view/zfc-ticket-system/ticket-system/index.twig',
            'small-user/login' => __DIR__ . '/../view/p-server-core/auth/login.twig',
            'small-user/logout-page' => __DIR__ . '/../view/p-server-core/auth/logout-page.twig',
            'p-server-core/paginator' => __DIR__ . '/../view/helper/paginator.phtml',
            'p-server-core/navigation' => __DIR__ . '/../view/helper/navigation.phtml',
        ],
        'template_path_stack' => [
            'p-server-core' =>__DIR__ . '/../view',
        ],
    ],
    // Placeholder for console routes
    'console' => [
        'router' => [
            'routes' => [
            ],
        ],
    ],
    /**
     *  DB Connection-Setup
     */
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                // mssql db @ windows  => 'GameBackend\DBAL\Driver\PDOSqlsrv\Driver'
                // mssql db @ linux  => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => [
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'username',
                    'password' => 'password',
                    'dbname' => 'dbname',
                ],
                'doctrine_type_mappings' => [
                    'enum' => 'string'
                ],
            ],
            'orm_sro_account' => [
                // mssql db @ windows  => 'GameBackend\DBAL\Driver\PDOSqlsrv\Driver'
                // mssql db @ linux  => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'driverClass' => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'params' => [
                    'host' => 'local',
                    'port' => '1433',
                    'user' => 'foo',
                    'password' => 'bar',
                    'dbname' => 'ACCOUNT',
                ],
            ],
            'orm_sro_shard' => [
                // mssql db @ windows  => 'GameBackend\DBAL\Driver\PDOSqlsrv\Driver'
                // mssql db @ linux  => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'driverClass' => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'params' => [
                    'host' => 'local',
                    'port' => '1433',
                    'user' => 'foo',
                    'password' => 'bar',
                    'dbname' => 'SHARD',
                ],
            ],
            'orm_sro_log' => [
                // mssql db @ windows  => 'GameBackend\DBAL\Driver\PDOSqlsrv\Driver'
                // mssql db @ linux  => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'driverClass' => 'GameBackend\DBAL\Driver\PDODblib\Driver',
                'params' => [
                    'host' => 'local',
                    'port' => '1433',
                    'user' => 'foo',
                    'password' => 'bar',
                    'dbname' => 'LOG',
                ],
            ],
        ],
        'entitymanager' => [
            'orm_default' => [
                'connection' => 'orm_default',
                'configuration' => 'orm_default'
            ],
        ],
        'driver' => [
            'application_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/PServerCore/Entity'
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'PServerCore\Entity' => 'application_entities',
                    'SmallUser\Entity' => null,
                    'ZfcTicketSystem\Entity' => null
                ],
            ],
        ],
    ],
    'pserver' => [
        'general' => [
            'datetime' => [
                'format' => [
                    'time' => 'Y-m-d H:i'
                ],
            ],
            'cache' => [
                'enable' => false
            ],
            'max_player' => 1000,
            'image_player' => [
                'font_color' => [
                    0,
                    0,
                    0
                ],
                'background_color' => [
                    237,
                    237,
                    237
                ],
            ],
            /**
             * send a mail to the ticket owner
             * after new entry in admin panel
             */
            'ticket_answer_mail' => false
        ],
        'register' => [
            /**
             * role after register
             */
            'role' => 'user',
            /**
             * mail confirmation after register?
             * WARNING for pw lost|country|ticket answer, we need a valid mail
             */
            'mail_confirmation' => false,
            /**
             * With that feature it is possible to add the user from the game-database to the web-database
             * Why we need a web-database with user information?
             * Easy reason the system support different games, and we have create a central interface for the login,
             * to add roles, create a history-log, 2 pw-system and and and
             */
            'dynamic-import' => true,
            /**
             * its allowed to use more as 1 time a email-address on different accounts
             * warning it can come to duplicate emails with the dynamic import feature
             */
            'duplicate_email' => true
        ],
        'mail' => [
            'from' => 'abcd@example.com',
            'from_name' => 'team',
            'subject' => [
                'register' => 'RegisterMail',
                'password' => 'LostPasswordMail',
                'country' => 'LoginIpMail',
                'secretLogin' => 'SecretLoginMail',
                'ticketAnswer' => 'TicketAnswer',
            ],
            'basic' => [
                'name' => 'localhost',
                'host' => 'smtp.example.com',
                'port' => 587,
                'connection_class' => 'login',
                'connection_config' => [
                    'username' => 'put your username',
                    'password' => 'put your password',
                    'ssl' => 'tls',
                ],
            ],
        ],
        'login' => [
            'exploit' => [
                'time' => 900, //in seconds
                'try' => 5
            ],
            /**
             * for more security we can check if the user login from a allowed country
             * WARNING YOU HAVE TO FILL THE "country_list" TABLE WITH IP COUNTRY MAPPING
             * That is the reason why its default disabled
             */
            'country_check' => false,
            /**
             * set the list of roles, which must confirm, there mail after login
             */
            'secret_login_role_list' => [],
        ],
        'password' => [
            /*
             * set other pw for web as ingame
             */
            'different_passwords' => true,
            /**
             * work with secret pw system, there is atm no admin view to handle the question =[
             */
            'secret_question' => false,
            /**
             * some games does not allowed so long password
             */
            'length' => [
                'min' => 6,
                'max' => 32
            ],
        ],
        'validation' => [
            'username' => [
                'length' => [
                    'min' => 3,
                    'max' => 16
                ],
            ],
        ],
        'user_code' => [
            'expire' => [
                /**
                 * null means we use the general value
                 */
                'general' => 86400,
                'register' => null,
                'password' => null,
                'country' => null,
                'add_email' => null,
                'secret_login' => 60
            ]
        ],
        'news' => [
            /**
             * limit of the news entries of the first page
             */
            'limit' => 5
        ],
        'pageinfotype' => [
            'faq',
            'rules',
            'guides',
            'events'
        ],
        'blacklisted' => [
            'email' => [
                /**
                 * example to block all emails ending with @foo.com and @bar.com, the @ will added automatic
                 * 'foo.com', 'bar.com'
                 */

            ],
        ],
        'entity' => [
            'available_countries' => 'PServerCore\Entity\AvailableCountries',
            'country_list' => 'PServerCore\Entity\CountryList',
            'donate_log' => 'PServerCore\Entity\DonateLog',
            'download_list' => 'PServerCore\Entity\DownloadList',
            'ip_block' => 'PServerCore\Entity\IpBlock',
            'login_failed' => 'PServerCore\Entity\LoginFailed',
            'login_history' => 'PServerCore\Entity\LoginHistory',
            'logs' => 'PServerCore\Entity\Logs',
            'news' => 'PServerCore\Entity\News',
            'page_info' => 'PServerCore\Entity\PageInfo',
            'player_history' => 'PServerCore\Entity\PlayerHistory',
            'secret_answer' => 'PServerCore\Entity\SecretAnswer',
            'secret_question' => 'PServerCore\Entity\SecretQuestion',
            'server_info' => 'PServerCore\Entity\ServerInfo',
            'user' => 'PServerCore\Entity\User',
            'user_block' => 'PServerCore\Entity\UserBlock',
            'user_codes' => 'PServerCore\Entity\UserCodes',
            'user_extension' => 'PServerCore\Entity\UserExtension',
            'user_role' => 'PServerCore\Entity\UserRole',
        ],
        'navigation' => [
            'home' => [
                'name' => 'Home',
                'route' => [
                    'name' => 'PServerCore',
                ],
            ],
            'download' => [
                'name' => 'Download',
                'route' => [
                    'name' => 'PServerCore/site-download',
                ],
            ],
            'ranking' => [
                'name' => 'Ranking',
                'route' => [
                    'name' => 'PServerRanking/ranking',
                ],
                'children' => [
                    '1_position' => [
                        'name' => 'TopPlayer',
                        'route' => [
                            'name' => 'PServerRanking/ranking',
                            'params' => [
                                'action' => 'top-player',
                            ],
                        ],
                    ],
                    '2_position' => [
                        'name' => 'TopGuild',
                        'route' => [
                            'name' => 'PServerRanking/ranking',
                            'params' => [
                                'action' => 'top-guild',
                            ],
                        ],
                    ],
                ],
            ],
            'server-info' => [
                'name' => 'ServerInfo',
                'route' => [
                    'name' => 'PServerCore/site-detail',
                ],
                'children' => [
                    '1_position' => [
                        'name' => 'FAQ',
                        'route' => [
                            'name' => 'PServerCore/site-detail',
                            'params' => [
                                'type' => 'faq',
                            ],
                        ],
                    ],
                    '2_position' => [
                        'name' => 'Rules',
                        'route' => [
                            'name' => 'PServerCore/site-detail',
                            'params' => [
                                'type' => 'rules',
                            ],
                        ],
                    ],
                    '3_position' => [
                        'name' => 'Guides',
                        'route' => [
                            'name' => 'PServerCore/site-detail',
                            'params' => [
                                'type' => 'guides',
                            ],
                        ],
                    ],
                    '4_position' => [
                        'name' => 'Events',
                        'route' => [
                            'name' => 'PServerCore/site-detail',
                            'params' => [
                                'type' => 'events',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'logged_in' => [
            'character' => [
                'name' => 'CharacterPanel',
                'route' => [
                    'name' => 'PServerPanel/character',
                ],
                'class' => 'fa fa-diamond'
            ],
            'account_panel' => [
                'name' => 'AccountPanel',
                'route' => [
                    'name' => 'PServerCore/user',
                ],
                'class' => 'glyphicon glyphicon-user'
            ],
            'ticket_system' => [
                'name' => 'TicketSystem',
                'route' => [
                    'name' => 'zfc-ticketsystem',
                ],
                'class' => 'fa fa-graduation-cap'
            ],
            'donate' => [
                'name' => 'Donate',
                'route' => [
                    'name' => 'PServerCore/panel_donate',
                ],
                'class' => 'fa fa-usd'
            ],
            'vote4coins' => [
                'name' => 'Vote4Coins',
                'route' => [
                    'name' => 'PServerPanel/vote',
                ],
                'class' => 'fa fa-gamepad'
            ],
            'admin_panel' => [
                'name' => 'AdminPanel',
                'route' => [
                    'name' => 'PServerAdmin/home',
                ],
                'class' => 'fa fa-graduation-cap'
            ],
        ],
    ],
    'authenticationadapter' => [
        'odm_default' => [
            'objectManager' => 'doctrine.documentmanager.odm_default',
            'identityClass' => 'PServerCore\Entity\User',
            'identityProperty' => 'username',
            'credentialProperty' => 'password',
            'credentialCallable' => 'PServerCore\Entity\User::hashPassword'
        ],
    ],
    'small-user' => [
        'user_entity' => [
            'class' => 'PServerCore\Entity\User'
        ],
        'login' => [
            'route' => 'PServerCore'
        ],
    ],
    'payment-api' => [
        // more config params check https://github.com/kokspflanze/PaymentAPI/blob/master/config/module.config.php
        'payment-wall' => [
            /**
             * SecretKey
             */
            'secret-key' => '',
        ],
        'super-reward' => [
            /**
             * SecretKey
             */
            'secret-key' => ''
        ],
        'ban-time' => '946681200',
    ],
    'zfc-ticket-system' => [
        'entity' => [
            'ticket_category' => 'PServerCore\Entity\TicketSystem\TicketCategory',
            'ticket_entry' => 'PServerCore\Entity\TicketSystem\TicketEntry',
            'ticket_subject' => 'PServerCore\Entity\TicketSystem\TicketSubject',
            'user' => 'PServerCore\Entity\User',
        ],
    ],
    'ZfcDatagrid' => [
        'settings' => [
            'export' => [
                'enabled' => false,
            ],
        ],
    ],
];
