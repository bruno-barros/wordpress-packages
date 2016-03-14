<?php namespace WpPack\Presenters;

/**
 * AuthorPresenter
 *
 * @link http://codex.wordpress.org/Template_Tags/the_author_meta
 *
 * @author Bruno Barros  <bruno@brunobarros.com>
 * @copyright    Copyright (c) 2014 Bruno Barros
 */
class AuthorPresenter extends BasePresenter
{


	/**
	 * Return a object with author information
	 *
	 * @return \stdClass
	 */
	public function presentInfos()
	{
		$a              = new \stdClass();
		$a->id          = $this->getId();
		$a->avatar      = $this->presentAvatar();
		$a->name        = $this->presentName();
		$a->email       = $this->presentEmail();
		$a->description = $this->presentDescription();
		$a->permalink   = $this->presentLink();
		$a->site        = $this->presentSite();

		return $a;
	}

	/**
	 * Return the author ID
	 *
	 * @return int
	 */
	public function presentId()
	{
		return $this->getId();
	}

	/**
	 * Return the <img> with the author avatar
	 *
	 * @return string
	 */
	public function presentAvatar()
	{
		return $this->getAuthorAvatar();
	}

	/**
	 * Return the <img> with the author avatar
	 *
	 * @param int $size Img size
	 * @param string $default Alternative image if not exists
	 * @param string $alt Alt text of the image
	 * @return string
	 */
	public function getAuthorAvatar($size = 96, $default = '', $alt = false)
	{
		return get_avatar($this->presentEmail(), $size, $default, $alt);
	}

	/**
	 * Return the author site
	 *
	 * @return string
	 */
	public function presentSite()
	{
		return get_the_author_meta('user_url', $this->getId());
	}

	/**
	 * Return the author posts url
	 *
	 * @return string
	 */
	public function presentLink()
	{
		return esc_url(get_author_posts_url($this->getId()));
	}

	/**
	 * Alias for presentLink()
	 *
	 * @return string
	 */
	public function presentPermalink()
	{
		return $this->presentLink();
	}


	/**
	 * Return the login of the author
	 *
	 * @return string
	 */
	public function presentLogin()
	{
		return get_the_author_meta('user_login', $this->getId());
	}

	/**
	 * Return the password of the author
	 *
	 * @return string
	 */
	public function presentPassword()
	{
		return get_the_author_meta('user_pass', $this->getId());
	}


	/**
	 * Return the nicename of the author
	 *
	 * @return string
	 */
	public function presentNicename()
	{
		return get_the_author_meta('user_nicename', $this->getId());
	}


	/**
	 * Return the nickname of the author
	 *
	 * @return string
	 */
	public function presentNickname()
	{
		return get_the_author_meta('nickname', $this->getId());
	}


	/**
	 * Return the name of the author
	 *
	 * @return string
	 */
	public function presentName()
	{
		return get_the_author_meta('display_name', $this->getId());
	}


	/**
	 * Return the first name of the author
	 *
	 * @return string
	 */
	public function presentFirstName()
	{
		return get_the_author_meta('first_name', $this->getId());
	}

	/**
	 * Return the last name of the author
	 *
	 * @return string
	 */
	public function presentLastName()
	{
		return get_the_author_meta('last_name', $this->getId());
	}


	/**
	 * Return the e-mail of the author
	 *
	 * @return string
	 */
	public function presentEmail()
	{
		return get_the_author_meta('user_email', $this->getId());
	}

	/**
	 * Return the description of the author
	 *
	 * @return string
	 */
	public function presentDescription()
	{
		return get_the_author_meta('description', $this->getId());
	}


	/**
	 * Return the author registered datetime
	 *
	 * @return string
	 */
	public function presentRegistered()
	{
		return get_the_author_meta('user_registered', $this->getId());
	}

	/**
	 * Return the author activation key
	 *
	 * @return string
	 */
	public function presentActivationKey()
	{
		return get_the_author_meta('user_activation_key', $this->getId());
	}

	/**
	 * Return the author status
	 *
	 * @return string
	 */
	public function presentStatus()
	{
		return get_the_author_meta('user_status', $this->getId());
	}


	/**
	 * Return the author roles
	 *
	 * @return array
	 */
	public function presentRoles()
	{
		return get_the_author_meta('roles', $this->getId());
	}


	/**
	 * Return the author roles
	 *
	 * @return int
	 */
	public function presentLevel()
	{
		return (int)get_the_author_meta('user_level', $this->getId());
	}


	/**
	 * Return the author ID
	 *
	 * @return mixed|string
	 */
	private function getId()
	{
		if ($this->isMainQuery)
		{
			return get_the_author_meta('ID');
		}
		else
		{
			return $this->post_author;
		}
	}

}