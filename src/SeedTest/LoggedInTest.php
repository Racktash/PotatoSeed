<?php
define("REGISTRY_COOKIES_USER", "ps_user");
define("REGISTRY_COOKIES_SESSION", "ps_session");
require_once 'Seed/entities/LoggedIn.php';
require_once 'SeedTestHelpers/FakeSession_Model.php';
require_once 'SeedTestHelpers/FakeUser_Model.php';

class LoggedInTest extends PHPUnit_Framework_TestCase
{
    private function prepareLoggedIn()
    {
        LoggedIn::reset();
        $fake_session_model = new FakeSession_Model();
        $fake_user_model = new FakeUser_Model();
        LoggedIn::setSessionModel($fake_session_model);
        LoggedIn::setUserModel($fake_user_model);
    }

    private function generateFakeCookieArray($userid, $session)
    {
        $f_array[REGISTRY_COOKIES_USER] = $userid;
        $f_array[REGISTRY_COOKIES_SESSION] = $session;

        return $f_array;
    }
    
    public function test_isLoggedIn_NoCookies_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = array();
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_NullUserIDCookie_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(null, "10.10");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_NullSessionCookie_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, null);
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_EmptyUserIDCookie_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray("", "10.10");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_EmptySessionCookie_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, "");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_InvalidUserID_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(4, "53.2");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_InvalidSessionID_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, "1.1");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_ValidSessionInfo_ReturnsTrue()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, "53.2");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertTrue(LoggedIn::isLoggedIn());
    }
    
    public function test_isLoggedIn_ValidSessionNonExistingUser_ReturnsFalse()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(2, "53.2");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertFalse(LoggedIn::isLoggedIn());
    }

    public function test_getUserID_ValidSessionInfo_ReturnsID()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, "53.2");
        LoggedIn::setCookiesArray($fake_cookie_array);
        $logged_in = LoggedIn::isLoggedIn();

        $this->assertEquals(LoggedIn::getUserID(), 1);
    }

    public function test_getUserID_NoLoginCheck_ReturnsNull()
    {
        $this->prepareLoggedIn();
        $fake_cookie_array = $this->generateFakeCookieArray(1, "53.2");
        LoggedIn::setCookiesArray($fake_cookie_array);

        $this->assertEquals(LoggedIn::getUserID(), null);
    }
}
?>
