<?php

$app->post('/auth', 'App\Controller\AuthenticationController:loginAction')->setName('post.auth');
$app->get('/users', 'App\Controller\UserController:getAllUsersAction')->setName('get.users');
$app->post('/users', 'App\Controller\UserController:createUserAction')->setName('post.users');
$app->get('/users/{user_id}', 'App\Controller\UserController:getUserAction')->setName('get.users.single');
$app->put('/users/{user_id}', 'App\Controller\UserController:updateUserAction')->setName('put.users.single');
$app->delete('/users/{user_id}', 'App\Controller\UserController:archiveUserAction')->setName('delete.users.single');