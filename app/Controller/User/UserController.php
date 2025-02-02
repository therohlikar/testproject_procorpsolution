<?php

namespace App\Controller\User;

use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Json;
use App\Model\User;

/**
 * @Path("/products")
 */
class ProductsController extends BaseController
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