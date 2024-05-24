<?php

$res = ['success' => false, 'msg' => 'gagal'];

$pin = $_GET['pin'];
$nama = $_GET['nama'];
$pass = $_GET['pass'];
$foto = $_GET['foto'];
$hak = $_GET['hak'];

// konfig mesin
$konfig = (object) [];
$konfig->ip = "192.168.1.102";
$konfig->username = "admin";
$konfig->pass = "admin123";
// konfig mesin

function add_user($konfig, $pin, $nama, $pass, $foto, $hak){

    $post_data = 
    '{
        "UserList":[{
            "UserID": "'.$pin.'",
            "UserName": "'.$nama.'",
            "UserType": 0,
            "Authority": '.intval($hak).',
            "Password": "'.$pass.'",
            "ValidFrom": "1970-01-01 00:00:00",
            "ValidTo": "2037-12-31 23:59:59"
        }]
    }';

    $url = "http://".$konfig->ip."/cgi-bin/AccessUser.cgi?action=insertMulti";
    $add_userinfo = curl_gas($url, $konfig, 'POST', $post_data);
    if($add_userinfo){
        $delete_photo = delete_photo($konfig, $pin);
        if($delete_photo){
            $insert_photo = add_photo($konfig, $pin, $foto);
            if($insert_photo){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }else{
        return false;
    }


}

function delete_photo($konfig, $pin){
    $url = "http://".$konfig->ip."/cgi-bin/AccessFace.cgi?action=removeMulti&UserIDList[0]=$pin";
    $delete_photo = curl_gas($url, $konfig, 'GET');
    if($delete_photo){
        return TRUE;
    }   
    return FALSE;
}

function add_photo($konfig, $pin, $foto){
    

    $url = "http://".$konfig->ip."/cgi-bin/AccessFace.cgi?action=insertMulti";

    $emp_photo = 'foto/'.$foto;
    if( ! file_exists($emp_photo)){
        return false;
    }

    $template_face = '
        {
            "FaceList": [
                {
                    "UserID":"'.$pin.'",
                    "PhotoData": ["'.base64_encode(file_get_contents($emp_photo)).'"]
                }
            ]
        }	
    ';

    $result = curl_gas($url, $konfig, 'POST', $template_face);

    return $result;
}

function curl_gas($url, $konfig, $type_method, $post_data = NULL){

    $ispost = false;
    if($type_method = "post"){
        $ispost = true;
    }
    
    $ip = $konfig->ip;
    $username = $konfig->username;
    $pass = $konfig->pass;
    
    $url = $url;
    $username = $username;
    $password = $pass;
    
    if($ispost){ 
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,    
            CURLOPT_VERBOSE        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,    // for https
            CURLOPT_USERPWD        => $username . ":" . $password,
            CURLOPT_HTTPAUTH       => CURLAUTH_DIGEST,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post_data,
            CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/javascript',
            ), 
        );
    }else{
        // get
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => true,    
            CURLOPT_VERBOSE        => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,    // for https
            CURLOPT_USERPWD        => $username . ":" . $password,
            CURLOPT_HTTPAUTH       => CURLAUTH_DIGEST,
            CURLOPT_CUSTOMREQUEST => 'GET'
        );
    }
    
    $ch = curl_init();
    
    curl_setopt_array( $ch, $options );
    
    try {
        $raw_response  = curl_exec( $ch );
    
        // validate CURL status
        if(curl_errno($ch)){
            var_dump(curl_error($ch));
            return false;
        }
    
      
    } catch(Exception $ex) {
        if ($ch != null){ curl_close($ch); }
    }
    
    if ($ch != null){ curl_close($ch); }

    return true;

    
}



// exec
$is_success = add_user($konfig, $pin, $nama, $pass, $foto, $hak);
if($is_success){
    $res = ['success' => true, 'msg' => 'sukses brow'];
}
echo json_encode($res);
