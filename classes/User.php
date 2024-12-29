<?php
require_once "Database.php";

class User extends Database{

    public function store($request){
        //The store() method inserts a record into the users table

        //$request holds the data from the form. This will catch the value of $_post from actions/register.php
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $username = $request['username'];
        $password = $request['password'];

        //Encrypt the password
        $password = password_hash($password, PASSWORD_DEFAULT);

        //Insert Query
        $sql = "INSERT INTO users(`first_name`, `last_name`, `username`, `password`) VALUES ('$first_name', '$last_name', '$username', '$password')";

        if($this->conn->query($sql)){
            header('location:../views'); //go to index.php
            exit; //same as die
        }else{
            die('Error creating the user: ' . $this->conn->error);
        }
    }

    public function login($request){
        //login() method will authenticate the login details
        $username = $request['username'];
        $password = $request['password'];

        //Select Query
        $sql = "SELECT * FROM users WHERE username = '$username'";
        $result = $this->conn->query($sql);

        //Check the username
        if($result->num_rows == 1){
            //check if the password is correct
            $user = $result->fetch_assoc();
            if(password_verify($password, $user['password'])){
                //create session variables for future use
                session_start();
                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['first_name'] . " " . $user['last_name'];
                header("location: ../views/dashboard.php");
                exit;
            }else{
                die("password is incorrect");
            }
        }else{
            die('Username not found.');
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header("location: ../views");
        exit;
    }

    public function getAllUsers(){
        $sql = "SELECT id, first_name, last_name, username, photo FROM users";
        
        if($result = $this->conn->query($sql)){
            return $result;
        }else{
            die("Error retrieving all users: " . $this->conn->error);
        }
    }

    //get specific user information
    public function getUser(){
        $id = $_SESSION['id'];

        $sql = "SELECT first_name, last_name, username, photo FROM users WHERE id = $id";

        if($result = $this->conn->query($sql)){
            return $result->fetch_assoc();
        }else{
            die("Error retrieving the user:" .$this->conn->error);
        }
    }

    public function update($request, $files) {
        session_start();
        $id = $_SESSION['id'];
        $first_name = $request['first_name'];
        $last_name = $request['last_name'];
        $username = $request['username'];

        $photo = $files['photo']['name'];
        $tmp_photo = $files['photo']['tmp_name'];

        #sql query string
        $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', username ='$username' WHERE id = $id";

        if($this->conn->query($sql)){
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = "$first_name $last_name";

            #check if the user upload a photo save it to the db and save the file to images folder
            if($photo){
                $sql = "UPDATE users SET photo = '$photo' WHERE id = $id";
                $destination = "../assets/images/$photo";

                #save the image to the db by executing the second query
                if($this->conn->query($sql)){
                    #save the file to the images folder

                    if(move_uploaded_file($tmp_photo, $destination)){
                        header("location: ../views/dashboard.php");
                        exit;
                    }else{
                        die("Error uploading photo: " . $this->conn->error);
                    }
                }else{
                    die("Error uploading photo: " . $this->conn->error);
                }
            }
            header("location: ../views/dashboard.php");
            exit;
        }else{
            die("Error updating the user:" . $this->conn->error);
        }
    }

    public function delete(){
        session_start();
        $id = $_SESSION['id'];

        $sql = "DELETE FROM users WHERE id = $id";

        if($this->conn->query($sql)){
            $this->logout();
        }else{
            die("Error in deleting your account: " . $this->conn->error);
        }
    }
}

?>