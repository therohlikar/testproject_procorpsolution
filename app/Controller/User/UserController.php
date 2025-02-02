<?php

namespace App\Controller\User;

use App\Controller\BaseController;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\Database\User;

use Apitte\Core\Annotation\Controller\Method;
use Apitte\Core\Annotation\Controller\Path;

/**
 * @Path("/users")
 */
class UserController extends BaseController
{
    public function __construct(private EntityManagerInterface $em){

    }

    /**
     * @Path("/")
     * @Method("GET")
     */
    public function getUsers(ApiRequest $request, ApiResponse $response): ApiResponse
    {
        $users = $this->em->getRepository(User::class)->findAll();

        $response->getBody()->write(Json::encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }
}