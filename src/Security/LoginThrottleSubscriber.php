<?php

namespace App\Security;

use App\Service\Throttle;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class LoginThrottleSubscriber implements EventSubscriberInterface
{
    private const ACTION = 'login_failure';
    private const MAX_FAILURES_PER_HOUR = 10;

    private Throttle $throttle;
    private RequestStack $requestStack;
    private LoggerInterface $securityLogger;

    public function __construct(Throttle $throttle, RequestStack $requestStack, LoggerInterface $securityLogger)
    {
        $this->throttle = $throttle;
        $this->requestStack = $requestStack;
        $this->securityLogger = $securityLogger;
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
        $request = $this->requestStack->getMainRequest();

        if ($this->throttle->exceeded(self::ACTION, $request, self::MAX_FAILURES_PER_HOUR)) {
            $this->securityLogger->warning('Connexion bloquée : trop de tentatives.', $this->context($request, $event->getPassport()));

            throw new CustomUserMessageAuthenticationException('Trop de tentatives de connexion. Réessayez dans une heure.');
        }
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $this->securityLogger->warning('Échec de connexion.', $this->context($event->getRequest(), $event->getPassport()));

        if (!$this->throttle->exceeded(self::ACTION, $event->getRequest(), self::MAX_FAILURES_PER_HOUR)) {
            $this->throttle->hit(self::ACTION, $event->getRequest());
        }
    }

    private function context(?Request $request, ?PassportInterface $passport): array
    {
        $badge = $passport !== null && $passport->hasBadge(UserBadge::class) ? $passport->getBadge(UserBadge::class) : null;

        return [
            'pseudo' => $badge?->getUserIdentifier(),
            'ip' => $request?->getClientIp(),
        ];
    }
}
