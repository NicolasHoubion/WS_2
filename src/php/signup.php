<?php 
if(isset($_POST['fname']) && 
   isset($_POST['uname']) && 
   isset($_POST['pass']) && 
   isset($_POST['email'])){

    include "./dbconn.php";  

    $fname = $_POST['fname'];
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];
    $email = $_POST['email'];

    $data = "fname=".$fname."&uname=".$uname."&email=".$email;
    
    // vérif des champs
    if (empty($fname)) {
        $em = "Full name is required";
        header("Location: ../../signup.php?error=$em&$data");  
        exit;
    } else if(empty($uname)){
        $em = "User name is required";
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    } else if(empty($pass)){
        $em = "Password is required";
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    } else if(empty($email)){
        $em = "Email is required";
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    }

    // Vérifier si le nom d'utilisateur existe déjà
    $sql = "SELECT * FROM Users WHERE Username = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$uname]);
    if($stmt->rowCount() > 0) {
        $em = "Username already exists";
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    }

    // Vérifier si l'email existe déjà
    $sql = "SELECT * FROM Users WHERE mail = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0) {
        $em = "Email already exists";
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    }

    // Hashage du mot de passe
    $pass = password_hash($pass, PASSWORD_DEFAULT);

    // role par défaut : user
    $role_id = 2; 

    // ajout des données
    try {
        $sql = "INSERT INTO Users (Firstname, Username, Password, mail, Role_id, Status) 
                VALUES (?, ?, ?, ?, ?, 'Y')";
        $stmt = $db->prepare($sql);
        $stmt->execute([$fname, $uname, $pass, $email, $role_id]);

        header("Location: ../../index.php?success=Your account has been created successfully");  
        exit;
    } catch (PDOException $e) {
        $em = "Error: " . $e->getMessage();
        header("Location: ../../signup.php?error=$em&$data");
        exit;
    }

}
?>
