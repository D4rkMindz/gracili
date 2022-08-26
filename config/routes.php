<?php

use App\Controller\Auth\LoginAction;
use App\Controller\Auth\LoginGoogleAction;
use App\Controller\Auth\LoginGoogleCallbackAction;
use App\Controller\Auth\RegisterAction;
use App\Controller\Group\GroupArchiveAction;
use App\Controller\Group\GroupCreateAction;
use App\Controller\Group\GroupDeleteAction;
use App\Controller\Group\GroupEditAction;
use App\Controller\Group\GroupViewAction;
use App\Controller\Group\GroupViewAllAction;
use App\Controller\Group\Role\GroupRoleAddAction;
use App\Controller\Group\Role\GroupRoleRemoveAction;
use App\Controller\Group\Role\GroupRolesViewAllAction;
use App\Controller\IndexAction;
use App\Controller\Monitor\MonitorQueueAction;
use App\Controller\Role\RoleEditAction;
use App\Controller\Role\RoleViewAction;
use App\Controller\Role\RoleViewAllAction;
use App\Controller\User\Group\UserGroupsAddAction;
use App\Controller\User\Group\UserGroupsRemoveAction;
use App\Controller\User\Group\UserGroupsViewAllAction;
use App\Controller\User\Role\UserRolesAddAction;
use App\Controller\User\Role\UserRolesRemoveAction;
use App\Controller\User\Role\UserRolesViewAllAction;
use App\Controller\User\UserArchiveAction;
use App\Controller\User\UserCreateAction;
use App\Controller\User\UserDeleteAction;
use App\Controller\User\UserEditAction;
use App\Controller\User\UserViewAction;
use App\Controller\User\UserViewAllAction;
use App\Middleware\AuthMiddleware;
use App\Middleware\LanguageMiddleware;
use App\Middleware\RouteParserInjectorMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

if (!defined('HASH_REGEX')) {
    define("HASH_REGEX", '[a-zA-Z0-9]*');
}

return static function (App $app) {
    $container = $app->getContainer();
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', IndexAction::class)->setName(IndexAction::ROUTE);

        /**
         * Version 1
         */
        $group->group('/v1', function (RouteCollectorProxy $v1) {
            $v1->group('/auth', function (RouteCollectorProxy $auth) {
                $auth->post('/register', RegisterAction::class)
                    ->setName(RegisterAction::NAME);

                $auth->post('/login', LoginAction::class)
                    ->setName(LoginAction::NAME);

                $auth->post('/google', LoginGoogleAction::class)
                    ->setName(LoginGoogleAction::NAME);

                $auth->post('/google/verify', LoginGoogleCallbackAction::class)
                    ->setName(LoginGoogleCallbackAction::NAME);
            });

            /**
             * Users
             */
            $v1->group('/users', function (RouteCollectorProxy $user) {
                $user->get('[/]', UserViewAllAction::class)
                    ->setName(UserViewAllAction::NAME);

                $user->post('[/]', UserCreateAction::class)
                    ->setName(UserCreateAction::NAME);

                $user->get('/{user_hash:' . HASH_REGEX . '}', UserViewAction::class)
                    ->setName(UserViewAction::NAME);

                $user->put('/{user_hash:' . HASH_REGEX . '}', UserEditAction::class)
                    ->setName(UserEditAction::NAME);

                $user->patch('/{user_hash:' . HASH_REGEX . '}', UserArchiveAction::class)
                    ->setName(UserArchiveAction::NAME);

                $user->delete('/{user_hash:' . HASH_REGEX . '}', UserDeleteAction::class)
                    ->setName(UserDeleteAction::NAME);

                /**
                 * User Roles
                 */
                $user->group('/{user_hash:' . HASH_REGEX . '}/roles', function (RouteCollectorProxy $userRole) {
                    $userRole->get('[/]', UserRolesViewAllAction::class)
                        ->setName(UserRolesViewAllAction::NAME);

                    $userRole->post('/{role_hash:' . HASH_REGEX . '}', UserRolesAddAction::class)
                        ->setName(UserRolesAddAction::NAME);

                    $userRole->delete('/{role_hash:' . HASH_REGEX . '}', UserRolesRemoveAction::class)
                        ->setName(UserRolesRemoveAction::NAME);
                });

                /**
                 * User Groups
                 */
                $user->group('/{user_hash:' . HASH_REGEX . '}/groups', function (RouteCollectorProxy $userGroup) {
                    $userGroup->get('[/]', UserGroupsViewAllAction::class)
                        ->setName(UserGroupsViewAllAction::NAME);

                    $userGroup->post('/{group_hash:' . HASH_REGEX . '}', UserGroupsAddAction::class)
                        ->setName(UserGroupsAddAction::NAME);

                    $userGroup->delete('/{group_hash:' . HASH_REGEX . '}', UserGroupsRemoveAction::class)
                        ->setName(UserGroupsRemoveAction::NAME);
                });

            });

            /**
             * Groups
             */
            $v1->group('/groups', function (RouteCollectorProxy $group) {
                $group->get('[/]', GroupViewAllAction::class)
                    ->setName(GroupViewAllAction::NAME);

                $group->post('[/]', GroupCreateAction::class)
                    ->setName(GroupCreateAction::NAME);

                $group->get('/{group_hash:' . HASH_REGEX . '}', GroupViewAction::class)
                    ->setName(GroupViewAction::NAME);

                $group->put('/{group_hash:' . HASH_REGEX . '}', GroupEditAction::class)
                    ->setName(GroupEditAction::NAME);

                $group->patch('/{group_hash:' . HASH_REGEX . '}', GroupArchiveAction::class)
                    ->setName(GroupArchiveAction::NAME);

                $group->delete('/{group_hash:' . HASH_REGEX . '}', GroupDeleteAction::class)
                    ->setName(GroupDeleteAction::NAME);

                /**
                 * Group roles
                 */
                $group->group('/{group_hash:' . HASH_REGEX . '}/roles', function (RouteCollectorProxy $userGroup) {
                    $userGroup->get('[/]', GroupRolesViewAllAction::class)
                        ->setName(GroupRolesViewAllAction::NAME);

                    $userGroup->post('/{role_hash:' . HASH_REGEX . '}', GroupRoleAddAction::class)
                        ->setName(GroupRoleAddAction::NAME);

                    $userGroup->delete('/{role_hash:' . HASH_REGEX . '}', GroupRoleRemoveAction::class)
                        ->setName(GroupRoleRemoveAction::NAME);
                });
            });

            /**
             * Roles
             */
            $v1->group('/roles', function (RouteCollectorProxy $role) {
                // deleting/archiving/creating roles is not possible -> would not make sense to create hard-coded roles only in the db
                $role->get('[/]', RoleViewAllAction::class)
                    ->setName(RoleViewAllAction::NAME);

                $role->get('/{role_hash:' . HASH_REGEX . '}', RoleViewAction::class)
                    ->setName(RoleViewAction::NAME);

                $role->put('/{role_hash:' . HASH_REGEX . '}', RoleEditAction::class)
                    ->setName(RoleEditAction::NAME);
            });

            $v1->group('/monitoring', function (RouteCollectorProxy $monitoring) {
                $monitoring->get('/queue', MonitorQueueAction::class)->setName(MonitorQueueAction::NAME);
            });
        });
    })
        // exception middleware is added in middleware.php
        ->addMiddleware($container->get(RouteParserInjectorMiddleware::class))
        ->addMiddleware($container->get(LanguageMiddleware::class))
        ->addMiddleware($container->get(AuthMiddleware::class));
};
