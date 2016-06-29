<?php
/**
 * ロール用の設定ファイル
 * @copyright 2016 YuTanaka@AmuseOne
 */

return [
    "default_roles" => [
        "default_admin_roles" => [
            "name" => "Administrator",
            "slug" => "admin",
            "permissions" => [
                "admin" => true,
                "user.create" => true,
                "user.delete" => true,
                "user.view" => true,
                "user.update" => true,
                "role.create" => true,
                "role.delete" => true,
                "role.view" => true,
                "role.update" => true,
            ]
        ],
        "default_moderator" => [
            "name" => "Moderator",
            "slug" => "moderator",
            "permissions" => [
                "moderator" => true,
                "user.create" => true,
                "user.delete" => false,
                "user.view" => true,
                "user.update" => false,
                "role.create" => false,
                "role.delete" => false,
                "role.view" => false,
                "role.update" => false,
            ]
        ]
    ]
];
