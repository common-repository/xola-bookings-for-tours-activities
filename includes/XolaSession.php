<?php

namespace xola;

class XolaSession
{
    protected $appName = 'XOLA';

    public function __construct()
    {
        $sessionId = session_id();
        // start session if not already started
        if (empty($sessionId)) {
            session_start();
        }
    }

    public function set($key, $value)
    {
        if (!empty($value) && !empty($key)) {
            $_SESSION[$this->appName]->{$key} = $value;
        }

        return true;
    }

    public function get($key, $defaultValue = false)
    {
        return isset($_SESSION[$this->appName]->{$key}) ? $_SESSION[$this->appName]->{$key} : $defaultValue;
    }

    public function clear($key)
    {
        if (isset($_SESSION[$this->appName]->{$key})) {
            unset($_SESSION[$this->appName]->{$key});
        }
    }

    public function clearAll()
    {
        foreach ($_SESSION[$this->appName] as $key => $value) {
            unset($_SESSION[$this->appName]->{$key});
        }

        return true;
    }

    public function getAll()
    {
        return isset($_SESSION[$this->appName]) ? $_SESSION[$this->appName] : array();
    }

    public function addError($error)
    {
        $errors = $this->getErrors();

        if (is_array($error)) {
            foreach ($error as $item) {
                $errors[] = $item;
            }
        } else {
            $errors[] = $error;
        }

        $this->set('errors', $errors);
    }

    public function addMessage($msg)
    {
        $messages = $this->getMessages();

        if (is_array($msg)) {
            foreach ($msg as $item) {
                $messages[] = $item;
            }
        } else {
            $messages[] = $msg;
        }

        $this->set('messages', $messages);
    }

    public function getErrors()
    {
        return $this->get('errors', array());
    }

    public function getMessages()
    {
        return $this->get('messages', array());
    }

    public function clearErrors()
    {
        if (isset($_SESSION[$this->appName]->errors)) {
            unset($_SESSION[$this->appName]->errors);
        }
    }

    public function clearMessages()
    {
        if (isset($_SESSION[$this->appName]->messages)) {
            unset($_SESSION[$this->appName]->messages);
        }
    }

    public function clearInfo()
    {
        $this->clearErrors();
        $this->clearMessages();
    }
}

new XolaSession;