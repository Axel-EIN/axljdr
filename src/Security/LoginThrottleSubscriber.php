<?php

namespace App\Security;

use App\Service\Throttle;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginThrottleSubscriber implements EventSubscriberInterface
{
    private const ACTION = 'login_failure';
    private const MAX_FAILURES_PER_HOUR = 10;

    private Throttle $throttle;
    private RequestStack $requestStack;

    public function __construct(Throttle $throttle, RequestStack $requestStack)
    {
        $this->throttle = $throttle;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 512],
            LoginFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        if ($this->throttle->exceeded(self::ACTION, $this->requestStack->getMainRequest(), self::MAX_FAILURES_PER_HOUR)) {
            throw new CustomUserMessageAuthenticationException('Trop de tentatives de connexion. Réessayez dans une heure.');
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        if (!$this->throttle->exceeded(self::ACTION, $event->getRequest(), self::MAX_FAILURES_PER_HOUR)) {
            $this->throttle->hit(self::ACTION, $event->getRequest());
        }
    }
}
