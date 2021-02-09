<?php

namespace App\Security;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class UserAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        # https://symfonycasts.com/screencast/symfony-security/api-token-authenticator
        # I'm guessing I should create a new authenticator for this but I think I spent enough time on this test
        $authorizationHeader = $request->headers->get('Authorization');
        if ($authorizationHeader
            && strpos($authorizationHeader, 'Bearer ') === 0) {
            $credentials['header_bearer_token'] = substr($authorizationHeader, 7);
        }

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {

        $userRepository = $this->entityManager->getRepository(Users::class);
        if (isset($credentials['header_bearer_token'])) {
            # I should encrypt this api key assymetrically like a private key so it is not exposed
            $user = $userRepository->findOneBy(['api_key' => $credentials['header_bearer_token']]);
            return $user;
        }
// I have to disable this while I have no solution for getting the api key via postman
//
// I thought of enabling this only on browser, but does it make sense security wise?
//        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
//        if (!$this->csrfTokenManager->isTokenValid($token)) {
//            throw new InvalidCsrfTokenException();
//        }


        $user = $userRepository->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if (isset($credentials['header_bearer_token'])
            && $user instanceof Users
            && $credentials['header_bearer_token'] === $user->getApiKey()) {
            return true;
        }
        if ($user->getPassword() === sha1($credentials['password'])) {
            return true;
        }
        throw new CustomUserMessageAuthenticationException("Username/Password is wrong");
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return new JsonResponse([
                'api_key' => $token->getUser()->getApiKey()
            ]);
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }


        return new RedirectResponse("/products");
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
