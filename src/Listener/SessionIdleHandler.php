<?php
namespace App\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;

class SessionIdleHandler
{
  protected $session;
  protected $tokenStorage;
  protected $router;
  protected $manager;
  protected $maxIdleTime;
  public function __construct(
    SessionInterface $session,
    TokenStorageInterface $tokenStorage,
    RouterInterface $router,
    EntityManagerInterface $manager,
    $maxIdleTime = 0) {
    $this->session = $session;
    $this->tokenStorage = $tokenStorage;
    $this->router = $router;
    $this->manager = $manager;
    $this->maxIdleTime = $maxIdleTime;
  }
  public function onKernelRequest(RequestEvent $event) {
    if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType()) {
      return;
    }
    if ($this->maxIdleTime > 0) {
      $this->session->start();
      $lapse = time() - $this->session->getMetadataBag()->getLastUsed();
      if ($lapse > $this->maxIdleTime && null !== $this->tokenStorage->getToken()) {
        $token = $this->tokenStorage->getToken();

        if ($token) {
          $this->tokenStorage->setToken(null);
        }

        $event->setResponse(new RedirectResponse($this->router->generate('admin_account_login')));
      }
    }
  }
}