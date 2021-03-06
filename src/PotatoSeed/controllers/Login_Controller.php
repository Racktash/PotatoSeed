<?php

require_once(REGISTRY_ENGINE_PATH . "models/Login_Model.php");

class Controller_Login extends Controller
{

	private $login_errors = false;

	public function execute()
	{
		$this->model = new Login_Model(new mysqli(REGISTRY_DBVALUES_SERVER, REGISTRY_DBVALUES_USERNAME, REGISTRY_DBVALUES_PASSWORD, REGISTRY_DBVALUES_DATABASE));

		$this->viewLoginform();

		$passer = $_POST['passer'];
		$logout = $_GET['logout'];

		if ($logout == "yes" and LoggedInUser::isLoggedin())
		{
			$user_id = LoggedInUser::getUserID();
			$this->logout($user_id);
		}

		if (LoggedInUser::isLoggedin() and $logout != "yes")
			$this->alreadyLoggedin();

		if ($passer == "PASS")
			$this->attemptLogin();
		else if ($passer == "PASS2")
			$this->attemptUpdatePassword();
	}

	private function viewLoginform()
	{
		$this->page_title = "Log In";
		$this->inner_view = "loginform.php";
	}

	private function alreadyLoggedin()
	{
		header("Location: " . REGISTRY_POST_LOGIN_REDIRECT_TO);
		echo "You're already logged in... <a href='" . REGISTRY_POST_LOGIN_REDIRECT_TO . "'>continue</a>";
		exit();
	}

	private function logout($user_id)
	{
		setcookie(REGISTRY_COOKIES_USER, "", time() - 3600, REGISTRY_COOKIE_PATH, REGISTRY_COOKIE_DOMAIN);
		setcookie(REGISTRY_COOKIES_SESSION, "", time() - 3600, REGISTRY_COOKIE_PATH, REGISTRY_COOKIE_DOMAIN);

		$this->model->logout($user_id);
	}

	private function attemptLogin()
	{
		$username = psafe($_POST['username']); #we check our username after it is stripped of non-[a-z0-9] characters
		$username_lowercase = strtolower($username);
		$password = $_POST['password'];

		if (!$this->model->userNameExists($username))
		{
			$this->login_errors = true;
			$this->validation_errors[] = "Couldn't log in - no user with that username could be found.";
		}// we have no users...
		else
		{
			$user_id = $this->model->fetchUserID($username);
			if ($this->model->doesPasswordMatch($user_id, $password))
			{
				$this->model->createSession($user_id);

				LoggedInUser::login($user_id);
				$this->alreadyLoggedin();
			}
			else if ($this->model->doesPasswordMatchLegacy($user_id, $password))
			{
				$this->displayLegacyPasswordScreen();
			}
			else
			{
				$this->login_errors = true;
				$this->validation_errors[] = "Couldn't log in - password does not match that which is on record.";
			}
		}
	}

	private function displayLegacyPasswordScreen()
	{
		$this->page_title = "Update Your Password";
		$this->inner_view = REGISTRY_LOGIN_CHANGE_LEGACY_VIEW_FORM;
	}

	private function attemptUpdatePassword()
	{
		$username = psafe($_POST['username']); #we check our username after it is stripped of non-[a-z0-9] characters
		$username_lowercase = strtolower($username);
		$password = $_POST['password'];

		if (!$this->model->userNameExists($username))
		{
			$this->displayLegacyPasswordScreen();
			$this->login_errors = true;
			$this->validation_errors[] = "No user with that username could be found.";
		}
		else
		{
			$user_id = $this->model->fetchUserID($username);
			
			if ($this->model->doesPasswordMatchLegacy($user_id, $password))
			{
				$password1 = $_POST['newpassword1'];
				$password2 = $_POST['newpassword2'];

				if($this->newPasswordsValid($password1, $password2))
				{
					$this->updatePassword($user_id, $password1);
					$this->clearLegacyPassword($user_id);
				}	
			}
			else
			{
				$this->displayLegacyPasswordScreen();
				$this->login_errors = true;
				$this->validation_errors[] = "Password does not match that which is on record.";
			}
		}
	}

	private function newPasswordsValid($password1, $password2)
	{
		if($password1 != $password2)
		{
			$this->addValidationError("The two new passwords must match!");
			return false;
		}
		else if ($password1 == "" or $password1 == null)
		{
			$this->addValidationError("New password cannot be empty!");
			return false;
		}
		else
		{
			return true;
		}
	}

	private function updatePassword($user_id, $password)
	{
		$this->model->updatePassword($user_id, $password);
	}

	private function clearLegacyPassword($user_id)
	{
		$this->model->removeLegacyPassword($user_id);
	}


	public function getLegacyPassword()
	{
		$password = $_POST['password'];
		return pdisplay($password);
	}

	public function getUsername()
	{
		$username = $_POST['username'];
		$filtered_username = psafe($username);
		return $filtered_username;
	}
	
}

?>
