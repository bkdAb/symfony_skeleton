<?php

namespace App\Factory;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UserResource
{
    /**
     * @var Response
     */
    private Response $response;
    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;
    /**
     * @var Translator
     */
    private TranslatorInterface $translator;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;
    /**
     * @var RequestStack
     */
    private RequestStack $flash;

    /**
     * @var Environment
     */
    private Environment $twig;

    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $factory;

    /**
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $manager
     * @param RequestStack $flash
     * @param RouterInterface $router
     * @param FormFactoryInterface $factory
     * @param Environment $twig
     */
    public function __construct(
        TranslatorInterface    $translator,
        EntityManagerInterface $manager,
        RequestStack           $flash,
        RouterInterface        $router,
        FormFactoryInterface   $factory,
        Environment            $twig,
    ) {
        $this->manager = $manager;
        $this->translator = $translator;
        $this->flash = $flash;
        $this->router = $router;
        $this->factory = $factory;
        $this->twig = $twig;
    }

    /**
     * @param string $id
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function delete(string $id): void
    {
        /** @var User $user */
        $user = $this->manager->find(User::class, $id);
        $session = $this->flash->getSession();

        if ($user) {
            $user->setActive(false);
            $this->manager->flush();

            $session->getFlashBag()->add('success', $this->translator->trans('home.toast.user_deleted'));
        } else {
            $session->getFlashBag()->add('alert', $this->translator->trans('home.toast.user_not_found'));
        }

        $this->setRedirectResponse('homepage');
    }

    /**
     * @param Request $request
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function edit(Request $request):void {

        $userId = $request->get('id');

        $user = $this->manager->find(User::class, $userId);
        $session = $this->flash->getSession();

        $form = $this->factory->create(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            $this->manager->persist($user);
            $this->manager->flush($user);

            $session->getFlashBag()->add('success', $this->translator->trans('home.toast.user_updated'));

            $this->setRedirectResponse('homepage');
            return;
        }

        $this->setResponse('views/edit_user.html.twig', [
            'form' => $form->createView(),
            'id' => $userId
        ]);
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param string $view
     * @param array $params
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function setResponse(string $view, array $params = []): Response
    {
        $view = $this->twig->render($view, $params);

        $response = new Response();

        $response->setContent($view);

        return $this->response = $response;
    }

    /**
     * @param string $route
     * @param array $params
     * @return Response
     */
    public function setRedirectResponse(string $route, array $params = []): Response
    {
        $url = $this->router->generate($route, $params);

        $response = new RedirectResponse($url);

        return $this->response = $response;
    }
}