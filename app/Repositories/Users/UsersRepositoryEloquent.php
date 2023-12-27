<?php

namespace App\Repositories\Users;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Entities\Users\Users;

/**
 * Class UsersRepositoryEloquent.
 *
 * @package namespace App\Repositories\Users;
 */
class UsersRepositoryEloquent extends BaseRepository implements UsersRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Users::class;
    }



    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
