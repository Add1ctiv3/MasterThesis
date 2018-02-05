<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class Dataset
{

    private $name;
    private $createdOn;
    private $editedOn;
    private $creator;
    private $editor;
    private $visibility;

    private $telephoneNumbers = array();
    private $telecommunications = array();
    //private $persons;
    //private $communications;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @return mixed
     */
    public function getEditedOn()
    {
        return $this->editedOn;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @return mixed
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * @return mixed
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param mixed $createdOn
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }

    /**
     * @param mixed $editedOn
     */
    public function setEditedOn($editedOn)
    {
        $this->editedOn = $editedOn;
    }

    /**
     * @return array
     */
    public function getTelephoneNumbers()
    {
        return $this->telephoneNumbers;
    }

    /**
     * @param array $telephoneNumbers
     */
    public function setTelephoneNumbers($telephoneNumbers)
    {
        $this->telephoneNumbers = $telephoneNumbers;
    }

    /**
     * @return array
     */
    public function getTelecommunications()
    {
        return $this->telecommunications;
    }

    /**
     * @param array $telecommunications
     */
    public function setTelecommunications($telecommunications)
    {
        $this->telecommunications = $telecommunications;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @param mixed $editor
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
    }

    /**
     * @param mixed $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }


    //constructor
    public function __construct($name, $args) {

        if(count($args) > 0 && isset($args['visibility'])) {
            $this->visibility = $args['visibility'];
        }

        if(count($args) > 0 && isset($args['createdOn'])) {
            $this->createdOn = $args['createdOn'];
        }

        if(count($args) > 0 && isset($args['editedOn'])) {
            $this->editedOn = $args['editedOn'];
        }

        if(count($args) > 0 && isset($args['creator'])) {
            $this->creator = $args['creator'];
        }

        if(count($args) > 0 && isset($args['editor'])) {
            $this->editor = $args['editor'];
        }

        if(count($args) > 0 && isset($args['telephoneNumbers'])) {
            $this->telephoneNumbers = $args['telephoneNumbers'];
        }

        if(count($args) > 0 && isset($args['telecommunications'])) {
            $this->telecommunications = $args['telecommunications'];
        }

        $this->name = $name;

    }

    public static function getAllDatasets() {

        $user = User::getLoggedUser();

        $datasets = array();

        try {

            $query = "
                  SELECT 
					  *
				  FROM
				  	  	" . Config::read('mysql.prefix') . "datasets AS sets
                  JOIN 
                        " . Config::read('mysql.prefix') . "users AS creator 
                  ON
                        sets.creator_username = creator.username
				  WHERE 
					  1
                  ORDER BY
                      creation_timestamp
                  DESC";

            if($user['type'] != "administrator") {

                $query = "
                  SELECT
                    *
                    FROM
                            ix_datasets AS sets
                      JOIN
                            ix_users AS creator
                      ON
                            sets.creator_username = creator.username
                      WHERE
                          visibility = 'public' OR creator_username = :unam
                      ORDER BY
                          creation_timestamp
                      DESC
					  ";

            }

            $stmt = DB::get()->dbh->prepare($query);
            $stmt->bindParam(":unam", $user['username'], PDO::PARAM_STR);
            $stmt->execute();

            while($f = $stmt->fetch()) {

                $args = array(
                    "visibility" => $f->visibility,
                    "createdOn" => $f->creation_timestamp,
                    "editedOn" => $f->edit_timestamp,
                    "creator" => $f->creator_username,
                    "editor" => $f->editor_username
                );

                $set = new Dataset($f->name, $args);

                array_push($datasets, $set);

            }

            return $datasets;

        }
        catch(PDOException $e) {
            die(json_encode(array("error" => "Get all datasets query failed: " . $e->getMessage())));
        }

    }


}