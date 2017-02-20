<?php
/**
* @license MIT license
* @copyright Copyright (c) 2016 Piotr Trzepacz
*/
namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
* RBAC Controller.
* Create defined Roles and Permissions for usage in app logic.
* Can be used from console.
*/
class RbacController extends Controller
{
    /**
    * Initialize RBAC for application.
    * Should be used only once.
    */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // add "manageUsers" permission
        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users';
        $auth->add($manageUsers);

        // add "managePlanes" permission
        $managePlanes = $auth->createPermission('managePlanes');
        $managePlanes->description = 'Manage planes';
        $auth->add($managePlanes);

        // add "manageTypes" permission
        $manageTypes = $auth->createPermission('manageTypes');
        $manageTypes->description = 'Manage types';
        $auth->add($manageTypes);

        // add "manageLan" permission
        $manageLan = $auth->createPermission('manageLan');
        $manageLan->description = 'Manage LAN';
        $auth->add($manageLan);

        // add "manageLan" permission
        $viewLan = $auth->createPermission('viewLan');
        $viewLan->description = 'View LAN';
        $auth->add($viewLan);

        $viewer = $auth->createRole('viewer');
        $viewer->description = "User has only view permissions.";
        $auth->add($viewer);
        $auth->addChild($viewer, $viewLan);

        // add "editor" role and give the "manageLan" permission
        // as well as the permissions of the "viewer" role
        $editor = $auth->createRole('editor');
        $editor->description = "User has modification permissions";
        $auth->add($editor);
        $auth->addChild($editor, $manageLan);
        $auth->addChild($editor, $viewer);

        // add "admin" role and give this role the "manageUsers", "managePlanes", "manageTypes" permission
        // as well as the permissions of the "editor" role
        $admin = $auth->createRole('admin');
        $admin->description = "User has administrative rights.";
        $auth->add($admin);
        $auth->addChild($admin, $manageUsers);
        $auth->addChild($admin, $managePlanes);
        $auth->addChild($admin, $manageTypes);
        $auth->addChild($admin, $editor);

        // Give admin role to administrator.
        if ($auth->assign($auth->getRole('admin'), 1)) {
            echo "Administrator role set.\n";
        }

        echo "RBAC initialized.\n";
    }
}
