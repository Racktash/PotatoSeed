<?php
class User_Model extends CommonDBModel
{
    public function __construct(PDO $handle)
    {
        parent::__construct($handle);
        $this->setTableName(REGISTRY_TBLNAME_USERS);

        $this->required_fields = array("username", "lower", "email");
    }

    public function isValid($data, $update=false)
    {
        $validator = new Validator($data);
        $validator->newRule("username", "Username", "required");
        $validator->newRule("lower", "Username", "required|max_len|64");
        $validator->newRule("email", "Username", "required|min_len|2");

        if(!$update)
        {

            if($this->exists("lower", $data["lower"]))
            {
                $this->val_errors[] = "Username (lower) must be unique!";
                return false;
            }

            if($this->exists("username", $data["username"]))
            {
                $this->val_errors[] = "Username must be unique!";
                return false;
            }

            if($this->exists("email", $data["email"]))
            {
                $this->val_errors[] = "Email must be unique!";
                return false;
            }
        }

        if($validator->allValid()) return true;
        else
        {
            $this->val_errors = array_merge($this->val_errors, $validator->getErrors());
            return false;
        }
    }
}
?>