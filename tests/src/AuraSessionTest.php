<?php

/**
 * @category   OAuth
 * @package    Tests
 * @author     David Desberg <david@daviddesberg.com>
 * @copyright  Copyright (c) 2012 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace FOA\OAuthSession;

use OAuth\OAuth2\Token\StdOAuth2Token;
use Aura\Session\Manager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;
use Aura\Session\Randval;
use Aura\Session\Phpfunc;
use Aura\Session\MockSessionHandler;

class AuraSessionTest extends \PHPUnit_Framework_TestCase
{
    protected $session;

    protected $storage;

    protected function setUp()
    {
        // session_set_save_handler(new MockSessionHandler);
        $this->session = $this->newSession();
        $this->storage = new AuraSession($this->session);
    }
    
    protected function newSession(array $cookies = [])
    {
        return new Manager(
            new SegmentFactory,
            new CsrfTokenFactory(new Randval(new Phpfunc)),
            $cookies
        );
    }

    public function tearDown()
    {
        // delete
        $this->session->destroy();        
        unset($this->storage);
    }

    /**
     * Check that the token survives the constructor
     */
    public function testStorageSurvivesConstructor()
    {
        $service = 'Facebook';
        $token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));
        $this->storage->storeAccessToken($service, $token);
        // $storedtoken = $this->storage->retrieveAccessToken($service);
        $storedtoken = unserialize($_SESSION['lusitanian_oauth_token'][$service]);
        $this->assertEquals($token, $storedtoken);
    }

    /**
     * Check that the token gets properly stored.
     */    
    public function testStorage()
    {     
        // arrange
        $service_1 = 'Facebook';
        $service_2 = 'Foursquare';

        $token_1 = new StdOAuth2Token('access_1', 'refresh_1', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));
        $token_2 = new StdOAuth2Token('access_2', 'refresh_2', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));

        // act
        $this->storage->storeAccessToken($service_1, $token_1);
        $this->storage->storeAccessToken($service_2, $token_2);

        // assert
        // $extraParams = $this->storage->retrieveAccessToken($service_1)->getExtraParams();
        // $this->assertEquals('param', $extraParams['extra']);
        $this->assertEquals($token_1, $this->storage->retrieveAccessToken($service_1));
        $this->assertEquals($token_2, $this->storage->retrieveAccessToken($service_2));        
    }    

    /**
     * Test hasAccessToken.
     */    
    public function testHasAccessToken()
    {
        // arrange
        $service = 'Facebook';
        $this->storage->clearToken($service);

        // act
        // assert
        $this->assertFalse($this->storage->hasAccessToken($service));        
    }    

    /**
     * Check that the token gets properly deleted.
     */    
    public function testStorageClears()
    {
        // arrange
        $service = 'Facebook';
        $token = new StdOAuth2Token('access', 'refresh', StdOAuth2Token::EOL_NEVER_EXPIRES, array('extra' => 'param'));

        // act
        $this->storage->storeAccessToken($service, $token);
        $this->storage->clearToken($service);

        // assert
        $this->setExpectedException('OAuth\Common\Storage\Exception\TokenNotFoundException');
        $this->storage->retrieveAccessToken($service);        
    }    
}
