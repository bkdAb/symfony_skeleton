<?php

namespace App\Controller;

use App\EventSubscriber\UserTasksAwareController;
use App\Factory\UserResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController implements UserTasksAwareController
{
    /**
     * @var UserResource
     */
    private UserResource $userResource;

    /**
     * @param UserResource $userResource
     */
    public function __construct(
        UserResource $userResource
    ) {
        $this->userResource = $userResource;
    }

    /**
     * @Route("/delete/{id}", name="delete")
     *
     * @param string $id
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function delete(string $id): Response
    {
        $this->userResource->delete($id);

        return $this->userResource->getResponse();
    }

    /**
     * @Route("/edit/{id}", name="edit")
     *
     * @param Request $request
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function edit(Request $request): Response
    {
        $this->userResource->edit($request);

        return $this->userResource->getResponse();
    }
}
