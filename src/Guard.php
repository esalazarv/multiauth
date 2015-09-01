<?php namespace Ollieread\Multiauth;

use Illuminate\Auth\Guard as OriginalGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
/**
 * Class Guard
 * @package Ollieread\Multiauth
 */
class Guard extends OriginalGuard
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $name;
    protected $impersonatorName = null;

    /**
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $name
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(UserProvider $provider, SessionInterface $session, $name, Request $request = null)
    {
        parent::__construct($provider, $session, $request);

        $this->name = $name;
    }

    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'login_' . $this->name . '_' . md5(get_class($this));
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_' . $this->name . '_' . md5(get_class($this));
    }

    /**
     * Get the authenticated user instance.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function get()
    {
        return $this->user();
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function setUser(UserContract $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        Auth::uses($this->name);
    }

    /**
     * Log the given user ID into the application.
     *
     * @param  mixed  $id
     * @param  bool   $remember
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function loginUsingId($id, $remember = false, $impersonator = null)
    {
        $this->session->set($this->getName(), $id);

        $this->login($user = $this->provider->retrieveById($id), $remember);

        if(!is_null($impersonator)){
          $this->impersonatorName = $impersonator;
        }

        return $user;
    }

    /**
     * Impersonate an authenticated user.
     *
     *
     * @param string $type
     * @param int $id
     * @param bool $remember
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function impersonate($type, $id, $remember = false)
    {
        if ($this->check()) {
            return Auth::uses($type)->loginUsingId($id, $remember, $this->name);
        }
    }

    public function isImpersonated(){
      return (!is_null($this->impersonatorName));
    }

    public function getImpersonator(){
      return $this->impersonatorName;
    }
}
