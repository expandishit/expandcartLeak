<?php

namespace ExpandCart\Foundation\Analytics;

class UsersManager extends Base
{
    protected $module = 'UsersManager';

    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function setUserLogin($userLogin)
    {
        $this->queryString['userLogin'] = $userLogin;

        return $this;
    }

    public function setPassword($password)
    {
        $this->queryString['password'] = $password;

        return $this;
    }

    public function setEmail($email)
    {
        $this->queryString['email'] = $email;

        return $this;
    }

    public function setAlias($alias)
    {
        $this->queryString['alias'] = $alias;

        return $this;
    }

    public function setMd5Password($md5Password)
    {
        $this->queryString['md5Password'] = $md5Password;

        return $this;
    }

    public function setPreferenceName($preferenceName)
    {
        $this->queryString['preferenceName'] = $preferenceName;

        return $this;
    }

    public function setPreferenceValue($preferenceValue)
    {
        $this->queryString['preferenceValue'] = $preferenceValue;

        return $this;
    }

    public function setAccess($access)
    {
        $this->queryString['access'] = $access;

        return $this;
    }

    public function setIdSites($idSites)
    {
        $this->queryString['idSites'] = $idSites;

        return $this;
    }

    public function addUser()
    {
        $defaults = [];

        if (!$this->method) {
            $this->method = 'addUser';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }

    public function getUser()
    {
        $defaults = [];

        if (!$this->method) {
            $this->method = 'getUser';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }

    public function getTokenAuth()
    {
        $defaults = [];

        if (!$this->method) {
            $this->method = 'getTokenAuth';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }

    public function setUserPreference()
    {
        $defaults = [];

        if (!$this->method) {
            $this->method = 'setUserPreference';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }

    public function setUserAccess()
    {
        $defaults = [];

        if (!$this->method) {
            $this->method = 'setUserAccess';
        }

        $this->queryString = array_merge($this->queryString, $defaults);

        return $this->send();
    }
}
