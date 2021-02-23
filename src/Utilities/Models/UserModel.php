<?php


namespace coreapi\Utilities\Models;


class UserModel extends BaseModel
{
    protected $table = 'user';

    protected $hidden = ['password'];
}