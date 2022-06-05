<?php
class JWT{

    public static function create(array $data, $secret_key, $expire = 3600){
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $time = time();
        $values = array(
            'jti' => base64_encode(openssl_random_pseudo_bytes(32)),
            'iat' => $time, 
            'exp' => $time + $expire,
            'data' => $data
        );
        $payload = json_encode($values);
        $base64url_header = self::base64url_encode($header);
        $base64url_payload = self::base64url_encode($payload);
        $signature = hash_hmac('sha256', $base64url_header . "." . $base64url_payload, $secret_key, true);
        $base64url_signature = self::base64url_encode($signature);
        $jwt = $base64url_header . "." . $base64url_payload . "." . $base64url_signature;
        return $jwt;
    }
    
    public static function verify_signature($jwt, $secret_key){
        $jwt_values = explode('.', $jwt);
        if(count($jwt_values)<3) return false;
        $received_signature = $jwt_values[2];
        $received_header_payload = $jwt_values[0] . "." . $jwt_values[1];
        $resulted_signature = self::base64url_encode(hash_hmac('sha256', $received_header_payload, $secret_key, true));
        if($resulted_signature == $received_signature)
            return true;
        else
            return false;
    }

    public static function check_exp($jwt, $secret_key){
            $payload = self::get_payload($jwt, $secret_key);
            if($payload!=NULL)
                if(time()>$payload['exp'])
                    return true;
                else
                    return false;
            else 
                return true;
    }

    public static function verify($jwt, $secret_key){
        if(self::verify_signature($jwt, $secret_key))
            if(!self::check_exp($jwt, $secret_key))
                return 0;
            else
                return 2;
        else
            return 1;
    }

    public static function get_data($jwt, $secret_key){
        if(self::verify_signature($jwt, $secret_key)){
            $jwt_values = explode('.', $jwt);
            $payload = json_decode(self::base64url_decode($jwt_values[1]),true);
            return $payload['data'];
        }else{
            return NULL;
        }
    }

    public static function get_payload($jwt, $secret_key){
        if(self::verify_signature($jwt, $secret_key)){
            $jwt_values = explode('.', $jwt);
            $payload = json_decode(self::base64url_decode($jwt_values[1]),true);
            return $payload;
        }else{
            return NULL;
        }
    }

    public static function base64url_encode($data) { 
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 
      
    public static function base64url_decode($data) { 
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
}