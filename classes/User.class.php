<?php

require_once ROOT_DIR . '/classes/DB.class.php';
require_once ROOT_DIR . '/classes/Company.class.php';

class User {

    public $id;
    public $name;
    public $linkedin_id;
    public $pic_url;
    public $company_id;
    public $profile_url;

    //Constructor is called whenever a new object is created.
    //Takes an associative array with the DB row as an argument.
    function __construct($data = null) {
        if ($data != null) {
            $this->id = (isset($data[0]['id'])) ? $data[0]['id'] : "";
            $this->name = (isset($data[0]['name'])) ? $data[0]['name'] : "";
            $this->linkedin_id = (isset($data[0]['linkedin_id'])) ? $data[0]['linkedin_id'] : "";
            $this->pic_url = (isset($data[0]['pic_url'])) ? $data[0]['pic_url'] : "";
            $this->company_id = (isset($data[0]['company_id'])) ? $data[0]['company_id'] : "";
            $this->profile_url = (isset($data[0]['profile_url'])) ? $data[0]['profile_url'] : "";
        }
    }

    public function save($isNewUser = false) {
        //create a new database object.
        $db = new DB();

        //if the user is already registered and we're
        //just updating their info.
        if (!$isNewUser) {
            //set the data array
            $data = array(
                "name" => "'$this->name'",
                "linkedin_id" => "'$this->linkedin_id'",
                "pic_url" => "'$this->pic_url'",
                "company_id" => "$this->company_id",
                "profile_url" => "'$this->profile_url'"
            );

            //update the row in the database
            $db->update($data, 'users', 'id = ' . $this->id);
        } else {
            //if the user is being registered for the first time.
            $data = array(
                "name" => "'$this->name'",
                "linkedin_id" => "'$this->linkedin_id'",
                "pic_url" => "'$this->pic_url'",
                "company_id" => "$this->company_id",
                "profile_url" => "'$this->profile_url'"
            );

            $this->id = $db->insert($data, 'users');
        }
        return true;
    }

    //Log the user out. Destroy the session variables.
    public function logout() {
        unset($_SESSION['user']);
        unset($_SESSION['login_time']);
        unset($_SESSION['logged_in']);
        session_destroy();
    }

    public function getOrganization() {
        if ($this->company_id !== null) {
            return Company::get($this->company_id);
        } else {
            return null;
        }
    }

    public static function checkUserExists($linkedin_id) {
        $db = new DB();
        $result = $db->select("users", "linkedin_id='$linkedin_id'");
        if ($result === false || sizeof($result) == 0) {
            return false;
        } else {
            return true;
        }
    }

    //get a user
    //returns a User object. Takes the users id as an input
    public static function get($id) {
        $db = new DB();
        $result = $db->select('users', "id = $id");
        return new User($result);
    }
    
    //get a user from linkedin id
    public static function getFromLinkedinId($linkedin_id) {
        $db = new DB();
        $result = $db->select('users', "linkedin_id = '$linkedin_id'");
        return new User($result);
    }

    public static function login($linkedin_id) {
        $db = new DB();
        $result = $db->select('users', "linkedin_id = '$linkedin_id'");
        if (!empty($result)) {
            $user = new User($result);
            $_SESSION["user"] = $user;

            $_SESSION["login_time"] = time();
            $_SESSION["logged_in"] = 1;

            return $_SESSION['user'];
        } else {
            return null;
        }
    }

}
