<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; // Correct namespace
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserBadge;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge as HttpUserBadge;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;



class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->router->generate('app_login');
    }

    public function authenticate(Request $request): Passport
    {
        // Get the email/username and password from the request
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
        ];

        if (empty($credentials['username'])) {
            throw new \InvalidArgumentException('Email/User is required.');
        }

        // Determine if the value is an email or username
        $userIdentifier = $credentials['username'];
        $userBadge = new HttpUserBadge($userIdentifier);

        // Return the passport with the user badge (which can be email or username)
        return new Passport(
            $userBadge,
            new PasswordCredentials($credentials['password'])
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_profile'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $emailOrUsername = $request->request->get('_username');

        // Default error message
        $errorMessage = 'Invalid username/email or password.';

        // Check the specific error
        if ($exception instanceof UserNotFoundException) {
            $errorMessage = 'The username/email you entered does not exist.';
        } elseif ($exception instanceof BadCredentialsException) {
            $errorMessage = 'The password is incorrect.';
        }

        // Store the error message in the session
        $request->getSession()->set('login_error_message', $errorMessage);

        return new RedirectResponse($this->router->generate('app_login'));
    }

}
