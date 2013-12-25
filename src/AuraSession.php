<?php
namespace FOA\OAuthSession;
use Aura\Session\Manager;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;

class AuraSession implements TokenStorageInterface
{
    /**
     * 
     * Aura\Session\Manager
     * 
     */
    private $session;
    
    /**
     * 
     * @var string
     * 
     */
    protected $sessionVariableName;

    /**
     * 
     * @param Aura\Session\Manager $session
     * 
     * @param bool $startSession Whether or not to start the session upon construction.
     * 
     * @param string $sessionVariableName the variable name to use within the _SESSION superglobal
     */
    public function __construct(Manager $session, $startSession = false, $sessionVariableName = 'lusitanian_oauth_token')
    {        
        $this->session = $session;
        $this->sessionVariableName = $sessionVariableName;        
        if ($startSession) {
            $this->session->start();
        }        
    }


    /**
    * @param string $service
    *
    * @return TokenInterface
    *
    * @throws TokenNotFoundException
    */
    public function retrieveAccessToken($service)
    {        
        if ($this->hasAccessToken($service)) {
            // get from session
            $segment = $this->session->newSegment($this->sessionVariableName);

            // one item
            return unserialize($segment->{$service});
        }

        throw new TokenNotFoundException('Token not found in session, are you sure you stored it?');
    }

    /**
    * @param string $service
    * @param TokenInterface $token
    *
    * @return TokenStorageInterface
    */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $serializedToken = serialize($token);
        // get previously saved tokens
        $segment = $this->session->newSegment($this->sessionVariableName);
        $segment->{$service} = $serializedToken;
        // save session
        $this->session->commit();
                
        // allow chaining
        return $this;
    }

    /**
    * @param string $service
    *
    * @return bool
    */
    public function hasAccessToken($service)
    {
        // get from session
        $segment = $this->session->newSegment($this->sessionVariableName);

        return isset($segment->{$service});
    }

    /**
    * Delete the users token. Aka, log out.
    *
    * @param string $service
    *
    * @return TokenStorageInterface
    */
    public function clearToken($service)
    {
        // get previously saved tokens
        $segment = $this->session->newSegment($this->sessionVariableName);
        if ($this->hasAccessToken($service)) {
            unset($segment->{$service});
        }        
        $this->session->commit();
        // allow chaining
        return $this;
    }

    /**
    * Delete *ALL* user tokens. Use with care. Most of the time you will likely
    * want to use clearToken() instead.
    *
    * @return TokenStorageInterface
    */
    public function clearAllTokens()
    {
        $segment = $this->session->newSegment($this->sessionVariableName);
        $segment->clear();
        $this->session->commit();
        // allow chaining
        return $this;
    }
}
