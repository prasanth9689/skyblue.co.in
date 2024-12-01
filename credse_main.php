<?php

include "connect.php";
include "functions.php";

$access = $_POST["acc"];

switch ($access) {
    case "login":
        $data = array();
        if(!empty($_POST['mobile']) && !empty($_POST['password'])){
              $mobile = $_POST['mobile'];
              $password = $_POST['password'];
              
              $query    = "SELECT password_hash , salt FROM staff WHERE mobile = ?";
              if($stmt = $con->prepare($query)){
              $stmt->bind_param("s",$mobile);
              $stmt->execute();
              $stmt->bind_result($passwordHashDB,$salt);
              if($stmt->fetch()){
                  if(password_verify(concatPasswordWithSalt($password,$salt),$passwordHashDB)){
                      // success
                      include "connect.php";
                      $query2 = "SELECT id , name FROM staff WHERE mobile = $mobile ";
                      $result = mysqli_query($con, $query2);
                      while($row = mysqli_fetch_assoc($result)){
                          array_push($data, array("access auth"=>"true" , "status"=>"1" , "message"=>"Success" , "user_id"=>$row["id"] , "user_name"=>$row["name"]));
                      }
                      
                  }else{
                      // Check mobile and password!
                      array_push($data, array("access auth"=>"true" , "status"=>"2" , "message"=>"Check mobile and password!" ));
                  }
              }else{
                  // Account not found!
                  array_push($data, array("access auth"=>"true" , "status"=>"3" , "message"=>"Account not found!" ));
              }
            }
        }else{
                // Empty field
                 array_push($data, array("access auth"=>"true" , "status"=>"4" , "message"=>"Empty parameter!" ));
        }
                 header('Content-Type:Application/json');
                 print(json_encode(($data)));
        break;

    case "create_staff":
        
        $mMobile = $_POST["mobile"];
        $data = [];
        
        // Check user already exists
        $check="SELECT mobile FROM staff WHERE mobile = $mMobile";
        $rs = mysqli_query($con,$check);
        $data1 = mysqli_fetch_array($rs, MYSQLI_NUM);
        if($data1[0] > 1) {
             array_push($data, array("access auth"=>"true" , "status"=>"2" , "message"=>"User already exits!" ));
             header("Content-Type:Application/json");
             print json_encode($data);
            return;
        }
        
        $response = [];
        
        $mName = $_POST["name"];
        $mPassword = $_POST["password"];
        $mCreatedDate = $_POST["cr_date"];
        $mAccountStatus = $_POST["ac_status"];
        $mCreatedDateTime = $_POST["cr_date_time"];
        
        //Get a unique Salt
        $salt = getSalt();
        
        //Generate a unique password Hash
        $passwordHash = password_hash(concatPasswordWithSalt($mPassword, $salt),PASSWORD_DEFAULT);
        
        // $Query = "INSERT INTO staff (mobile, name, password_hash, salt , account_status, created_date, cr_date_time) 
        //                     VALUES ('$mMobile', '$mName' '$passwordHash' , '$salt', '$mAccountStatus', '$mCreatedDate', '$mCreatedDateTime')";
        
        $Query = "INSERT INTO staff (mobile, name, password_hash, salt , account_status , created_date , cr_date_time) 
                             VALUES ('$mMobile' , '$mName', '$passwordHash' , '$salt' , '$mAccountStatus' , '$mCreatedDate' , '$mCreatedDateTime')";
        if (mysqli_query($con, $Query)) {
            /*
              Success
              Get newely created user id and details
            */
             $Sql_Query = "SELECT id FROM staff WHERE mobile = '$mMobile' ";
             $result = mysqli_query($con, $Sql_Query);
             
              while ($row = mysqli_fetch_assoc($result)) {
               //   array_push($data, ["user_id" => $row["id"] , "status" => true]);
                  array_push($data, array("access auth"=>"true" , "status"=>"1" , "message"=>"Success" ,  "user_id" => $row["id"]));
              }
              header("Content-Type:Application/json");
              print json_encode($data);
        }else{
            // Failed
             array_push($data, ["status" => false]);
             header("Content-Type:Application/json");
             print json_encode($data);
        }
        
        break;
        
        case "get_staff":
            
             include "connect.php";
            
             $Query = "SELECT * FROM staff";
             $result = mysqli_query($con, $Query);
             $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
             
             $data = array();
             if($row) {
                 
                 
            $Query = "SELECT * FROM staff";
            $result=mysqli_query($con, $Query);
            while($row=mysqli_fetch_assoc($result)){
             array_push($data, array("status"=>"true", "name"=>$row["name"], "mobile"=>$row["mobile"]));   
            }
            
            
            print(json_encode(array("status"=>"true" , "data" =>$data))); 
            
                return;
             }
                print(json_encode(array("status"=>"false"))); // Empty table
            break;
            
            case "send_aadhar_otp":
                $aadharNo = $_POST["aadhar_no"];
                
                $data = json_encode(array(
                    "id_number"  => $aadharNo
                    ));
                
               $curl = curl_init();

               curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://kyc-api.surepass.io/api/v1/aadhaar-v2/generate-otp',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>$data,
                    CURLOPT_HTTPHEADER => array(
                                             'Content-Type: application/json',
                                             'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcmVzaCI6ZmFsc2UsImlhdCI6MTY2NTY0NzkyOCwianRpIjoiMTdiNWY5YjMtZTE2Yi00ZTc1LWI3NTEtOTYwNzBkNDRkNmI0IiwidHlwZSI6ImFjY2VzcyIsImlkZW50aXR5IjoiZGV2LmNyZWRzZUBzdXJlcGFzcy5pbyIsIm5iZiI6MTY2NTY0NzkyOCwiZXhwIjoxOTgxMDA3OTI4LCJ1c2VyX2NsYWltcyI6eyJzY29wZXMiOlsid2FsbGV0Il19fQ.zWVTupVBGiwyolw0G00DDfO_vgwbQScnrfsBf2mABD8'
                                               ),));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    echo $response;
                            
                break;
                
                case "verify_aadhar_otp":
                    
                    $client_id = $_POST["client_id"];
                    $otp = $_POST["otp"];
                    
                     $data = json_encode(array(
                    "client_id"  => $client_id ,
                    "otp"  => $otp ,
                    ));
                    
                    $curl = curl_init();

                   curl_setopt_array($curl, array(
                   CURLOPT_URL => 'https://kyc-api.surepass.io/api/v1/aadhaar-v2/submit-otp',
                   CURLOPT_RETURNTRANSFER => true,
                   CURLOPT_ENCODING => '',
                   CURLOPT_MAXREDIRS => 10,
                   CURLOPT_TIMEOUT => 0,
                   CURLOPT_FOLLOWLOCATION => true,
                   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                   CURLOPT_CUSTOMREQUEST => 'POST',
                   CURLOPT_POSTFIELDS =>$data,
                   CURLOPT_HTTPHEADER => array(
                                             'Content-Type: application/json',
                                             'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJmcmVzaCI6ZmFsc2UsImlhdCI6MTY2NTY0NzkyOCwianRpIjoiMTdiNWY5YjMtZTE2Yi00ZTc1LWI3NTEtOTYwNzBkNDRkNmI0IiwidHlwZSI6ImFjY2VzcyIsImlkZW50aXR5IjoiZGV2LmNyZWRzZUBzdXJlcGFzcy5pbyIsIm5iZiI6MTY2NTY0NzkyOCwiZXhwIjoxOTgxMDA3OTI4LCJ1c2VyX2NsYWltcyI6eyJzY29wZXMiOlsid2FsbGV0Il19fQ.zWVTupVBGiwyolw0G00DDfO_vgwbQScnrfsBf2mABD8'
                                               ),));
                   $response = curl_exec($curl);
                   curl_close($curl);
                   echo $response;
                    break;
                    
                    case "save_aadhar_details":
                        
                       $photo = $_POST['photo'];
                       $title = $_POST['title'];
                       $first_name = $_POST['first_name'];
                       $surname = $_POST['surname'];
                       $father_name = $_POST['father_name'];
                       $aadhar_no = $_POST['aadhar_no'];
                       $aadhar_data = $_POST['aadhar_data'];
                       
                       $mother_name = $_POST['mother_name'];
                       $gender = $_POST['gender'];
                       $dob = $_POST['dob'];
                       $country = $_POST['country'];
                       $district = $_POST['district'];
                       
                       $state = $_POST['state'];
                       $po = $_POST['po'];
                       
                       $loc = $_POST['loc']; // Not queried
                       $vt = $_POST['vtc']; // Not queried
                       
                       $subdist = $_POST['subdist']; // Not queried
                       $street = $_POST['street']; // Not queried
                       
                       $house = $_POST['house'];
                       $landmark = $_POST['landmark'];
                       $zip = $_POST['zip'];
                       
                       $Query = "INSERT INTO a_accounts (aadhar_data, aadhar_no, photo, title, first_name, surname, father_name, mother_name, gender, dob , c_country , c_district, c_state, c_postoffice , c_street, c_door_no, c_landmark , c_pincode)VALUES ('$aadhar_data' , '$aadhar_no' , '$photo', '$title', '$first_name', '$surname', '$father_name' , '$mother_name' , '$gender' , '$dob' , '$country' , '$district' , '$state' , '$po' , '$street' , '$house' , '$landmark' , '$zip')";
                          if(mysqli_query($con, $Query)){
                              // Success
                              print(json_encode(array("status"=>"true")));
                          }else{
                              // Failure
                              print(json_encode(array("status"=>"true")));
                          }
                    
                    break;
                    
                    case "get_verified_aadhar":
                             
            
                          $Query = "SELECT * FROM a_accounts";
                          $result = mysqli_query($con, $Query);
                          $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
             
                          $data = array();
                          if($row) {
                 
                 
                          $Query = "SELECT * FROM a_accounts";
                          $result=mysqli_query($con, $Query);
                          while($row=mysqli_fetch_assoc($result)){
                                    array_push($data, array("status"=>"true", "id"=>$row["id"], "first_name"=>$row["first_name"] , "aadhar_no"=>$row["aadhar_no"] , "dob"=>$row["dob"] , "gender"=>$row["gender"]  , "father_name"=>$row["father_name"]  , "door_no"=>$row["c_door_no"] , "street"=>$row["c_street"] , "landmark"=>$row["c_landmark"] , "post_office"=>$row["c_postoffice"] ,  "photo"=>$row["photo"]));   
                          }
            
            
                          print(json_encode(array("status"=>"true" , "data" =>$data))); 
            
                          return;
                         }
                           print(json_encode(array("status"=>"false"))); // Empty table
                    break;

    default:
        echo "Wrong access key";
        break;
      
}

?>
