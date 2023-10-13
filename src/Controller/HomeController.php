<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Security\Provider\KeycloakProvider;
use App\Service\JwtUtils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, SessionInterface $session, JwtUtils $jwtManager): Response
    {
        $client = $session->get('oidc_config', new Client());
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('oidc_config', $client);

            return $this->redirectToRoute('connect_start');
        }

        $token = $session->get('last_token', null);

        return $this->render('home.html.twig', [
            'form' => $form,
            'token' => $token,
            'decoded_token' => $token ? $jwtManager->parse($token) : null
        ]);
    }


    #[Route(path: "/connect", name:"connect_start")]
    public function connectAction(RequestStack $requestStack)
    {
        $client = $this->getOAuth2Client($requestStack);

        if (!$client) {
            return $this->redirectToRoute('app_home');
        }

        return $client->redirect([], []);
    }

    #[Route(path: "/connect/check", name: "connect_check")]
    public function connectCheckAction(RequestStack $requestStack, ClientRegistry $clientRegistry): Response
    {
        $client = $this->getOAuth2Client($requestStack);

        if (!$client) {
            return $this->redirectToRoute('app_home');
        }

        $accessToken = $client->getAccessToken();

        $requestStack->getSession()->set('last_token', $accessToken);

        return $this->redirectToRoute('app_home');
    }

    public function getOAuth2Client(RequestStack $requestStack): ?OAuth2Client
    {
        $oidcConfig = $requestStack->getSession()->get('oidc_config');

        if(!$oidcConfig instanceof Client) {
            return null;
        }

        return new OAuth2Client(
            new KeycloakProvider(
                [
                    'discoveryUrl' => $oidcConfig->getWellknown(),
                    'realm' => 'region-bretagne-pfs',
                    'clientId' => $oidcConfig->getClientId(),
                    'clientSecret' => md5($oidcConfig->getClientSecret()),
                    'redirectUri' => $this->generateUrl(route: 'connect_check', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                ]
            ),
            $requestStack
        );
    }
}
